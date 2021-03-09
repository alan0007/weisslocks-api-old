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
require dirname(dirname(dirname(__FILE__))).'/modules/v1/accessControl/controllers/AccessControlController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForNormalAccessController.php';
//require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalForLockController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use api\modules\v1\lock\controllers\LockRequestApprovalForNormalAccessController;
//use api\modules\v1\lock\controllers\ApprovalForLockController;
use common\config\Constant;
use common\config\Database;

$response = array();

//----------------------------------
// Request Approval for Locks
//----------------------------------
if(isset($_REQUEST['company_id'])
    && isset($_REQUEST['user_id']))
{
    //date_default_timezone_set('Asia/Singapore');
    $datetime = date("c");

    $response['status'] = 'false';
    $response['error'] = 'Invalid parameters';
    $current_user_id = (int) $_REQUEST['user_id'];
    $i = 0;
    $c = 0;

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $AccessControlController = new AccessControlController($Database);
    $LockRequestApprovalForNormalAccessController = new LockRequestApprovalForNormalAccessController($Database);
//    $ApprovalForLockController = new ApprovalForLockController($Database);
    $NotificationController = new NotificationController;
    //$SmsController = new SmsController;

    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $com['company_id'] = $company_found['company_ID']; //Put in company details array
        $response['data']['company_check']  = 'Valid Company ID';
//        $response['data']['company_user_id']  = $company_found['user_id'];
    }
    else
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Company ID';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }

    // Verify User
    $user_details = $UserController->actionGetOneByIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);

    if(isset($user_details['user_id']))
    {
        $role = $user_details['role'];

//        $response['role'] = $role;

        if (in_array($role, array(2, 3))) {
            $response['is_admin'] = true;
            $response['status'] = 'true';
            $is_admin = true;
        } else {
            $response['is_admin'] = false;
            $is_admin = false;
        }
    }

    $response['data']['locks'] = array();

    $cursor_approval_request = $LockRequestApprovalForNormalAccessController->actionGetByUserIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);
    if ($cursor_approval_request->count() > 0){
        unset($response['error']);
        $response['status'] = 'true';
        foreach($cursor_approval_request as $approval_request) {
            $response['data']['locks'][$i]['id'] = $approval_request['id'];
            $response['data']['locks'][$i]['company_id'] = $approval_request['company_id'];
            $response['data']['locks'][$i]['user_id'] = $approval_request['user_id'];
            $response['data']['locks'][$i]['username'] = $user_details['username'];
            $response['data']['locks'][$i]['first_name'] = $user_details['first_name'];
            $response['data']['locks'][$i]['last_name'] = $user_details['last_name'];
//            $response['data']['locks'][$i]['full_name'] = $user_details['full_name'];

            $response['data']['locks'][$i]['permit_id'] = $approval_request['permit_id'];
            $response['data']['locks'][$i]['lock_id'] = $approval_request['lock_id'];
            $response['data']['locks'][$i]['created_timestamp'] = $approval_request['created_timestamp'];
            $response['data']['locks'][$i]['created_by_user_id'] = $approval_request['created_by_user_id'];
            $response['data']['locks'][$i]['notified_admin_user_id'] = $approval_request['notified_admin_user_id'];
            $response['data']['locks'][$i]['admin_approved'] = $approval_request['admin_approved'];
            $response['data']['locks'][$i]['admin_approved_by'] = $approval_request['admin_approved_by'];
            $response['data']['locks'][$i]['admin_approved_on'] = $approval_request['admin_approved_on'];
            $response['data']['locks'][$i]['admin_rejected'] = $approval_request['admin_rejected'];
            $response['data']['locks'][$i]['admin_rejected_by'] = $approval_request['admin_rejected_by'];
            $response['data']['locks'][$i]['admin_rejected_on'] = $approval_request['admin_rejected_on'];
//            $response['data']['locks'][$i]['subadmin_approved'] = $approval_request['subadmin_approved'];
//            $response['data']['locks'][$i]['subadmin_approved_by'] = $approval_request['subadmin_approved_by'];
//            $response['data']['locks'][$i]['subadmin_approved_on'] = $approval_request['subadmin_approved_on'];
//        $response['data']['locks'][$i]['valid_from'] = $approval_request['valid_from'];
//            $response['data']['locks'][$i]['valid_until'] = $approval_request['valid_until'];
            $response['data']['locks'][$i]['is_daily_timeslot'] = $approval_request['is_daily_timeslot'];
            $response['data']['locks'][$i]['day_of_week'] = $approval_request['day_of_week'];

            // Added 2020-10-14: timestamps
            if (isset($approval_request['date_from'])){
                $response['data']['locks'][$i]['date_from'] = $approval_request['date_from'];
            }else{
                $response['data']['locks'][$i]['date_from'] = '';
            }

            if (isset($approval_request['date_to'])){
                $response['data']['locks'][$i]['date_to'] = $approval_request['date_to'];
            }else{
                $response['data']['locks'][$i]['date_to'] = '';
            }

            if (isset($approval_request['time_from'])){
                $response['data']['locks'][$i]['time_from'] = $approval_request['time_from'];
            }else{
                $response['data']['locks'][$i]['time_from'] = '';
            }

            if (isset($approval_request['time_to'])){
                $response['data']['locks'][$i]['time_to'] = $approval_request['time_to'];
            }else{
                $response['data']['locks'][$i]['time_to'] = '';
            }
            // End Timestamps

            if ($approval_request['admin_approved'] == true){
                $response['data']['locks'][$i]['approval_status'] = "approved";
            }
            else if ($approval_request['admin_rejected'] == false && $approval_request['admin_approved_by'] == 0){
                $response['data']['locks'][$i]['approval_status'] = "pending";
            }

            if ($approval_request['admin_rejected'] == true){
                $response['data']['locks'][$i]['approval_status'] = "rejected";
            }

            //------------------------
            // Lock Name
            //------------------------
            $cursor_lock = $LockBluetoothController->actionGetOneById( $approval_request['lock_id'] );
            if(isset($cursor_lock)){
                $response['data']['locks'][$i]['lock_name'] = $cursor_lock['lock_name'];
                $response['data']['locks'][$i]['serial_number'] = $cursor_lock['serial_number'];
                $response['data']['locks'][$i]['lock_type'] = $cursor_lock['lock_type'];
                $response['data']['locks'][$i]['lock_model'] = $cursor_lock['lock_model'];
                $response['data']['locks'][$i]['lock_mechanism'] = $cursor_lock['lock_mechanism'];
                $response['data']['locks'][$i]['brand'] = $cursor_lock['brand'];
                $response['data']['locks'][$i]['entrance_visibility'] = $cursor_lock['entrance_visibility'];
                $response['data']['locks'][$i]['lock_group_id'] = $cursor_lock['lock_group_id'];
                $response['data']['locks'][$i]['log_number'] = $cursor_lock['log_number'];
                $response['data']['locks'][$i]['site_id'] = $cursor_lock['site_id'];
                $response['data']['locks'][$i]['geo_fencing'] = $cursor_lock['geo_fencing'];
                $response['data']['locks'][$i]['latitude'] = $cursor_lock['latitude'];
                $response['data']['locks'][$i]['longitude'] = $cursor_lock['longitude'];
                $response['data']['locks'][$i]['unlock_radius'] = $cursor_lock['unlock_radius'];
            }

            // Datetime from and To
            $datetime = strtotime ( $approval_request['created_timestamp'] );
            $request_date = date ( 'Y-M-d' , $datetime );

            $response['data']['locks'][$i]['request_datetime'] = $approval_request['created_timestamp'];

            $response['data']['locks'][$i]['valid_from'] = $approval_request['admin_approved_on'];
//            $response['data']['locks'][$i]['valid_to'] = $approval_request['valid_until'];

            //------------------------
            // Admin full name
            //------------------------
            $cursor_approval_admin = $UserController->actionGetOneById( $approval_request['admin_approved_by'] );

            if(isset($cursor_approval_admin)){
                $response['data']['locks'][$i]['admin_full_name'] = $cursor_approval_admin['full_name'];
            }

            $i++;
        }
    }
    else{
        $response['error'] = 'No Request Found';
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);