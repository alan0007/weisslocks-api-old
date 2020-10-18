<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/log/controllers/LogLockOpeningActivityController.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

use api\modules\v1\log\controllers\LogLockOpeningActivityController;
use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

/*
 * Optional parameters: category
 * Optional parameters: error_message
 */
if( isset($_REQUEST['company_id']) && isset($_REQUEST['user_id']) &&
    isset($_REQUEST['lock_id']) && isset($_REQUEST['lock_type']) &&
    isset($_REQUEST['description']) &&
    isset($_REQUEST['status']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid information entered';

    $datetime = date("c");

    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LogLockActivityController = new LogLockOpeningActivityController($Database);

    $company_id = $_REQUEST['company_id'];
    $user_id = $_REQUEST['user_id'];
    $lock_id = $_REQUEST['lock_id'];
    $lock_type = $_REQUEST['lock_type'];
    $description = $_REQUEST['description'];
    $status = $_REQUEST['status']; // sent / success / failed
    // Category
    if ( isset($_REQUEST['category']) ){
        $category = $_REQUEST['category']; // default: open_bluetooth_lock
    }else{
        $category = 'open_bluetooth_lock';
    }
    // Error Message
    if ( isset($_REQUEST['error_message']) ){
        $error_message = $_REQUEST['error_message'];
    }else{
        $error_message = '';
    }

    $cursor = $UserController->actionGetOneById($_REQUEST['user_id']);
    if(!isset($cursor)){
        $response['error'] = 'Invalid User';
    }
    else{
        if($LogLockActivityController->actionInsert($company_id,$user_id,$lock_id,$lock_type,
            $description,$category,$status,$error_message,$datetime))
        {
            $response['status'] = 'true';
            unset($response['error']);
            $response['data']['log_creation']  = 'success';
        }
    }
}


// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);