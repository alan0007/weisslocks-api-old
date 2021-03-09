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
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForSpecialAccessController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogUserActivityController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogLockActivityController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogLockOpeningActivityController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSpecialAccessController;
use api\modules\v1\log\controllers\LogUserActivityController;
use api\modules\v1\log\controllers\LogLockOpeningActivityController;
use api\modules\v1\log\controllers\LogLockActivityController;
use common\config\Constant;
use common\config\Database;

$response = array();

if(isset($_REQUEST['company_id']) && isset($_REQUEST['user_id']) &&
    isset($_REQUEST['created_by_user_id']) &&
    isset($_REQUEST['lock_id']) &&
    isset($_REQUEST['date']) &&
    isset($_REQUEST['time_from']) && isset($_REQUEST['time_to'])
)
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Lock';

    $company_id = $_REQUEST['company_id'];
    $user_id = $_REQUEST['user_id'];
    $created_by_user_id = $_REQUEST['created_by_user_id'];
    $lock_id = $_REQUEST['lock_id'];
    $lock_type = 'bluetooth';
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
    if ( isset($_REQUEST['status']) ){
        $status = $_REQUEST['status'];
    }else{
        $status = 'success';
    }

    //date_default_timezone_set('Asia/Singapore');
    $datetime = date("c");
    $date = $_REQUEST['date'];
    $time_from = $_REQUEST['time_from'];
    $time_to = $_REQUEST['time_to'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockController = new LockController($Database);
    $LockRequestApprovalForSpecialAccessController = new LockRequestApprovalForSpecialAccessController($Database);
    $LogLockActivityController = new LogLockActivityController($Database);
    $LogLockBluetoothActivityController = new LogLockOpeningActivityController($Database);
    $NotificationController = new NotificationController;
    //$SmsController = new SmsController;

//    if ( $_REQUEST['require_admin_approval'] == 'true' ){
//        $require_admin_approval = true;
//    }else if ($_REQUEST['require_admin_approval'] == 'false'){
//        $require_admin_approval = false;
//    }
//    if( $_REQUEST['require_subadmin_approval'] == 'true' ){
//        $require_subadmin_approval = true;
//    } else if ( $_REQUEST['require_subadmin_approval'] == 'false'){
//        $require_subadmin_approval = false;
//    }

    //-----------------------
    // Lock Bluetooth
    //-----------------------
    // Check if lock is valid
    $lock_details = $LockController->actionGetOneById($_REQUEST['lock_id']);
    if(!isset($lock_details['lock_ID'])){
        $response['error'] = 'Lock not found';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }
    else{
        if (isset($lock_details['display_name']) &&
            $lock_details['display_name'] != NULL && $lock_details['display_name'] != ''){
            $lock_display_name = $lock_details['display_name'];
        }else{
            $lock_display_name = $lock_details['lock_name'];
        }
        $response['data']['lock_display_name'] = $lock_display_name;
    }

    //-----------------------
    // Approval Request
    //-----------------------
    $date = $_REQUEST['date'];
    $time_from = $_REQUEST['time_from'];
    $time_to = $_REQUEST['time_to'];

    $post_array = array(
        'company_id'  => (int) $company_id,
        'user_id'  => (int) $user_id,
        'permit_id'  => (int) 0,
        'lock_id'  => (int) $lock_id,
        'created_timestamp'  => $datetime,
        'created_by_user_id'  => (int) $created_by_user_id,
        'notified_admin_user_id'=> Array(),
        'date' => (String)$date, // 2020-07-02
        'time_from' => (String)$time_from, // 08:05:37
        'time_to' => (String)$time_to,
    );

    if($LockRequestApprovalForSpecialAccessController->actionInsert($post_array) === TRUE){
        unset($response['error']);
        $response['status'] = 'true';

//        $Reg_Query = array('_id' => $post['_id'] );
//        $locksData = $approval_request_for_lock->findOne( $Reg_Query );

        //-------------
        // Log for Lock
        //-------------
        $description = 'Special Request to access lock: '.$lock_display_name;
        $category = 'lock_request_special_access';
        $log_lock_activity = $LogLockActivityController->actionInsert($company_id,$user_id,$lock_id,$lock_type,
            $description,$category,$status,$error_message,$datetime);
        $response['data']['log_lock_added'] = $log_lock_activity;
    }

    //----------------
    // Notification Sending
    //----------------
    $cursor_admin = $UserController->actionGetAdminOfCompany($_REQUEST['company_id']);
    $admin_device_id[0] = null; //default admin notification

    if($cursor_admin->count() > 0) {
        //$response['status'] = 'true';
        $response['admin_found'] = true;
        $c = 0;
        foreach ($cursor_admin as $admin) {
            if ($admin['token'] != null && $admin['token'] != '') {
                $admin_user_id[$c] = $admin['user_id'];
                $admin_token[$c] = $admin['token'];
            }
            $c++;
        }
    }

    $notified_admin_user_id = Array();
    $notified_admin_user_id = $admin['user_id'];
    $response['data']['notified_admin_user_id'] = $admin['user_id'];

    // Send Notification
    $NotificationController->token = $admin_device_id[0];
    $NotificationController->notification = array (
        'title' => 'Request Approval for Lock',
        'body' 	=> 'Approve request for user ID = ' . $_REQUEST['user_id'],
        'activity' 	=> 'ApprovalForLock',
        'company_id' => $_REQUEST['company_id'],
        'user_id' => $_REQUEST['user_id'],
        'permit_id' => NULL,
        'lock_id' => $_REQUEST['lock_id'],
        'android_channel_id' => 'FirebaseApprovalForLock',
        'sound' => 'default',
        'tag' => 'FirebaseApprovalForLock',
        'click_action' => 'APPROVE_LOCK'
    );
    $NotificationController->data = array(
        'title' => 'Request Approval for Lock',
        'body' 	=> 'Approve request for user ID = ' . $_REQUEST['user_id'],
        'activity' 	=> 'ApprovalForLock',
        'company_id' => $_REQUEST['company_id'],
        'user_id' => $_REQUEST['user_id'],
        'permit_id' => NULL,
        'lock_id' => $_REQUEST['lock_id']
        //'android' => array('click_action'=>'RESPOND_ALARM')
    );
    $NotificationController->message_id = 1;

    $NotificationController->sendNotification();
    $response['data']['notification']['fields'] = $NotificationController->fields;
    $response['data']['notification']['result'] = $NotificationController->result;
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);