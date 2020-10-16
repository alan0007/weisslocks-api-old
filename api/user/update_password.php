<?php
// Check Error
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
//require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
//use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

//Update password after registration
if(isset($_REQUEST['user_id']) && isset($_REQUEST['password']) && isset($_REQUEST['company_id']))
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid User';

    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);

    $user_id = $_REQUEST['user_id'];
    $password = $_REQUEST['password'];
    $company_id = $_REQUEST['company_id'];

    $cursor = $UserController->actionGetOneByIdAndCompanyId($user_id,$company_id);

//    if($cursor->count() == 1)
    if(isset($cursor['user_id']))
    {
        $approved = $cursor['approved'];
        $response['data']['approved'] = $approved;
        $response['data']['user_id'] = $user_id;

//        unset($response['error']);
//        $set_success = $UserController->actionUpdatePassword($username);
        if($approved == 1)
        {
            unset($response['error']);
            $set_success = $UserController->actionUpdatePassword($user_id,$password);

            $response['status'] = 'true';
            $response['data']['update_success']= $set_success;

//            $cursor_success = $collection->findOne($criteria);
//
//            $cursor_success['company_name'] = '';
//            $cursor_success['contracted_company_name'] = '';
//
//            if(isset($cursor_success['user_id']))
//            {
//
//                if( !empty( $cursor_success['company_id'] ) &&  $cursor_success['company_id'] != 0 )
//                {
//                    $collection_com = new MongoCollection($app_data, 'company');
//                    $coms = $collection_com->findOne(array('company_ID'=>(int)$cursor_success['company_id']));
//                    if(isset($coms['company_ID']))
//                    {
//                        $cursor_success['company_name'] = $coms['company_name'];
//                        $cursor_success['company_ref_id'] = $coms['company_ref'];
//
//                        for($k = 0 ; $k <= count($coms['contracted_name']) ; $k++)
//                        {
//                            if (false !== $key = array_search( json_decode($cursor_success['user_company'])[0] , $coms['contracted_ref_id'] ))
//                            {
//                                $cursor_success['contracted_company_name'] = $coms['contracted_name'][$key];
//                            }
//                        }
//                    }
//                }
//
//                unset($cursor_success['_id']);
//                //Not showing entire user data
//                //$response['data'] = $cursor_success;
//                //Only show required details
//                $response['data']['user_id'] = $cursor_success['user_id'];
//                $response['data']['username'] = $cursor_success['username'];
//                $response['data']['email'] = $cursor_success['email'];
//                $response['data']['role'] = $cursor_success['role'];
//                $response['data']['registered_time'] = $cursor_success['registered_time'];
//                $response['data']['device_id'] = $cursor_success['device_id'];
//                $response['data']['UDID_IOS'] = $cursor_success['UDID_IOS'];
//            }
        }
        else {
            $response['error'] = 'You are not Approved Yet...';
        }
    }
}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);