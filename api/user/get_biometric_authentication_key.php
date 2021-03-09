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

if(isset($_REQUEST['username']) && isset($_REQUEST['password']))
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

    $cursor = $UserController->actionGetAuthenticationKey($username,$password);

    if(isset($cursor['auth_key'])
        && $cursor['auth_key'] != '' && $cursor['auth_key'] != NULL)
    {
        unset($response['error']);
        $response['status'] = 'true';
        $response['data'] = $cursor['auth_key'];
    }
    else{
        $response['error'] = 'Not found';
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);