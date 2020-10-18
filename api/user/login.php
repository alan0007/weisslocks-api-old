<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
//require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/LoginController.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

//use api\modules\v1\user\controllers\LoginController;
use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

if(isset($_REQUEST['username']) && isset($_REQUEST['password']) && isset($_REQUEST['UDID_IOS']))
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Credentials';

    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];

    $Database = new Database();
    $Constant = new Constant();
//    $LoginController = new LoginController($Database);
    $CompanyController = new CompanyController($Database);
//    $collection = $LoginController->actionGetUserCollection();
    $UserController = new UserController($Database);
//    $collection = $UserController->actionGetUserCollection();

//    $Login_Query = array('username' => $_REQUEST['username'], 'password' => md5($_REQUEST['password']));
//    $cursor = $LoginController->actionLogin($collection,$Login_Query);

    $cursor = $UserController->actionLoginUsingUsernameAndPassword($username,$password);

    if(isset($cursor['user_id']))
    {
        // Verify Approved
        $approved = $UserController->actionVerifyApproved($cursor['approved']);
        if ( $approved === FALSE){
            $response['error'] = 'Account not approved';
            exit(json_encode($response, JSON_PRETTY_PRINT));
        }
        else {

            $update_udid = $UserController->actionUpdateUdid($cursor['user_id'], $cursor['role'], $cursor['UDID_IOS']);
            $response['udid_updated'] = $update_udid;

            $response['status'] = 'true';
            unset($response['error']);

            $update_last_login = $UserController->actionUpdateLastLogin($cursor['user_id']);
            $response['login_updated'] = $update_last_login;

//        $cursor = $UserController->actionLoginUsingUsernameAndPassword($username,$password);
//        $cursor['company_name'] = '';
//        $cursor['contracted_company_name'] = '';
            if (!empty($cursor['company_id']) && $cursor['company_id'] != 0) {
//            $collection_com = $CompanyController->actionGetOne($cursor['company_id']);
//            $coms = $collection_com->findOne(array('company_ID'=>(int)$cursor['company_id']));
                $coms = $CompanyController->actionGetOneById($cursor['company_id']);
                if (isset($coms['company_ID'])) {
                    $cursor['company_name'] = $coms['company_name'];
                    $cursor['company_ref_id'] = $coms['company_ref'];

                    for ($k = 0; $k <= count($coms['contracted_name']); $k++) {
                        if (false !== $key = array_search(json_decode($cursor['user_company']), $coms['contracted_ref_id'])) {
                            $cursor['contracted_company_name'] = $coms['contracted_name'][$key];
                        }
                    }
                }
            }
            unset($cursor['_id']);
            unset($cursor['password']);
            unset($cursor['UDID_IOS']);
            unset($cursor['token']);

            $response['data'] = $cursor;
        }
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);