<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Database.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Constant.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockBluetoothController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogUserActivityController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogLockActivityController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogLockOpeningActivityController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\log\controllers\LogUserActivityController;
use api\modules\v1\log\controllers\LogLockOpeningActivityController;
use api\modules\v1\log\controllers\LogLockActivityController;
use common\config\Constant;
use common\config\Database;

$response = array();

/*
 * Parameters
 * $company_id,
 * $user_id,
 * $lock_id,
 * $description,
 * $category,
 * $status,
 * $error_message,
 * $datetime
 */

//Open Locks under Permit
// Updated 2020-03-04
if( isset($_REQUEST['company_id']) && isset($_REQUEST['company_id']) &&
    isset($_REQUEST['lock_id']) &&
    isset($_REQUEST['description']) && isset($_REQUEST['status']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';
    $datetime = date("c");

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $LogLockActivityController = new LogLockActivityController($Database);
    $LogLockBluetoothActivityController = new LogLockOpeningActivityController($Database);
    $NotificationController = new NotificationController;

    $company_id = $_REQUEST['company_id'];
    $user_id = $_REQUEST['user_id'];
    $lock_id = $_REQUEST['lock_id'];
    $description = $_REQUEST['description'];
    // Category
    if ( isset($_REQUEST['category']) ){
        $category = $_REQUEST['category']; // default: open_bluetooth_lock
    }else{
        $category = 'open_lock';
    }
    // Error Message
    if ( isset($_REQUEST['error_message']) ){
        $error_message = $_REQUEST['error_message'];
    }else{
        $error_message = '';
    }
    // Status
    $status = $_REQUEST['status']; // sent / success / failed
    $lock_type = 'bluetooth';

    $cursor = $UserController->actionGetOneById($_REQUEST['user_id']);
    if(!isset($cursor)){
        $response['error'] = 'Invalid User';
    }
    else{
        //-------------
        // Log for Lock
        //-------------
        if($LogLockActivityController->actionInsert($company_id,$user_id,$lock_id,$lock_type,
            $description,$category,$status,$error_message,$datetime))
        {
            $response['status'] = 'true';
            unset($response['error']);
            $response['data']['log_lock_added']  = TRUE;
        }
        else{
            $response['data']['log_lock_added']  = FALSE;
        }

        //-------------
        // Log for Lock Opening
        //-------------
        $log_lock_opening_activity = $LogLockBluetoothActivityController->actionInsert($company_id,
            $user_id,$lock_id,$lock_type,$description,$category,$status,$error_message,$datetime);
        $response['data']['log_lock_opening_added'] = $log_lock_opening_activity;
    }
}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
