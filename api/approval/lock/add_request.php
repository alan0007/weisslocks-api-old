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
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalRequestForLockController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogLockBluetoothActivityController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\lock\controllers\ApprovalRequestForLockController;
use api\modules\v1\log\controllers\LogLockBluetoothActivityController;
use common\config\Constant;
use common\config\Database;

$response = array();


if(isset($_REQUEST['company_id']) && isset($_REQUEST['user_id']) &&
    isset($_REQUEST['created_by_user_id']) &&
    isset($_REQUEST['lock_id']) &&
    isset($_REQUEST['from_date']) && isset($_REQUEST['to_date']) &&
    isset($_REQUEST['from_time']) && isset($_REQUEST['to_time'])
)
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Lock';

    $company_id = $_REQUEST['company_id'];
    $user_id = $_REQUEST['user_id'];
    $created_by_user_id = $_REQUEST['created_by_user_id'];
    $lock_id = $_REQUEST['lock_id'];
    //date_default_timezone_set('Asia/Singapore');
    $datetime = date("c");
    $from_date = $_REQUEST['from_date'];
    $to_date = $_REQUEST['to_date'];
    $from_time = $_REQUEST['from_time'];
    $to_time = $_REQUEST['to_time'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $ApprovalRequestForLockController = new ApprovalRequestForLockController($Database);
    $LogLockBluetoothActivityController = new LogLockBluetoothActivityController($Database);
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

    if($ApprovalRequestForLockController->actionInsert($company_id,$user_id,$created_by_user_id,$lock_id,
        $datetime,$from_date,$to_date,$from_time,$to_time)){
        unset($response['error']);
        $response['status'] = 'true';

//        $Reg_Query = array('_id' => $post['_id'] );
//        $locksData = $approval_request_for_lock->findOne( $Reg_Query );
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