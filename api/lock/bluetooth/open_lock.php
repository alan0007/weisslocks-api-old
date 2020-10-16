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
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalForLockController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/permit/controllers/PermitController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/accessControl/controllers/AccessControlController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/log/controllers/LogLockBluetoothActivityController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\lock\controllers\ApprovalRequestForLockController;
use api\modules\v1\lock\controllers\ApprovalForLockController;
use api\modules\v1\permit\controllers\PermitController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use api\modules\v1\log\controllers\LogLockBluetoothActivityController;
use common\config\Constant;
use common\config\Database;

$response = array();

//Open Locks under Permit
// Updated 2020-03-04
if(isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
isset($_REQUEST['lock_id']) && $_REQUEST['lock_id'] != '')
{
	$user_id = $_REQUEST['user_id'];
	$lock_id = $_REQUEST['lock_id'];

    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';
    $response['open_lock'] = FALSE;

    $description = 'Failed: Open lock';
    $category = 'api: open_lock';

    $datetime_now = date("c");
    $date_now = date("Y-m-d");
    $time_now = date("H:i:s");
    $day_of_week_now = date('w');
    $response['datetime_now'] = $datetime_now;
    $response['date_now'] = $date_now;
    $response['time_now'] = $time_now;
    $response['day_of_week_now'] = $day_of_week_now;

    $user_id = $_REQUEST['user_id'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $ApprovalRequestForLockController = new ApprovalRequestForLockController($Database);
    $ApprovalForLockController = new ApprovalForLockController($Database);
    $PermitController = new PermitController($Database);
    $AccessControlController = new AccessControlController($Database);
    $LogLockBluetoothActivityController = new LogLockBluetoothActivityController($Database);

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

    $user_details = $UserController->actionGetOneByIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);

    if(isset($user_details['user_id']))
    {
        $response['data']['user_check']  = 'Valid User ID';
        if($user_details['role'] == 1)
        {
            $response['data']['error']  = 'Superadmin not allowed';
            exit(json_encode($response, JSON_PRETTY_PRINT));
        }
        else
        {
            // Valid user in company
//            $users_in_company = json_decode($company_found['user_id']);
            $user_list_in_company = str_replace("[","",$company_found['user_id']);
            $user_list_in_company = str_replace("]","",$user_list_in_company);
            $users_in_company = explode(",",$user_list_in_company);
            if(in_array( (int)$user_details['user_id'],$users_in_company) ){
                $com['company_ID'] = $company_found['company_ID'];
                $response['data']['user_in_company_check']  = 'Valid User in Company ID';
            }
            else{
                $response['data']['user_in_company_check']  = 'Invalid User in Company ID';
                exit(json_encode($response, JSON_PRETTY_PRINT));
            }

            $user_id = $user_details['user_id'];
            $username = $user_details['username'];
            $lock_group_id = $user_details['lock_group_id'];

            $com['user_id'] = (int)$user_id; //Put in company details array
            //$com['company_id'] = (int)$user['company_id'];

            if ( $user_details['role'] == 3){
                $response['data']['role'] = 'admin';
            }
            else if ( $user_details['role'] == 4){
                $response['data']['role'] = 'staff';
            }
            else if ( $user_details['role'] == 5){
                $response['data']['role'] = 'contractor';
            }

            //Added by Alan 2018-02-25
            //----------------
            // Access Control (KeyLockGroup)
            //----------------
            $access_control = $AccessControlController->actionGetByCompanyId($_REQUEST['company_id']);

            if($access_control->count() > 0) {
                //$response['status'] = 'true';
                $i=0;
                foreach($access_control as $accessControl)
                {
                    //if(in_array($com['user_id'],$accessControl['user_id'])) //If User company and User ID is correct
                    $response['data']['lock_group_count']= count($lock_group_id);
                    if(is_array($lock_group_id)){
                        if( in_array((int)$accessControl['lock_group_id'], $lock_group_id) ){//If have keygroup
                            unset($accessControl['_id']);
                            $access_date_from = $accessControl['date_from'];
                            $access_date_to = $accessControl['date_to'];
                            $access_time_from_hh = $accessControl['time_from_hh'];
                            $access_time_from_mm = $accessControl['time_from_mm'];
                            $access_time_to_hh = $accessControl['time_to_hh'];
                            $access_time_to_mm = $accessControl['time_to_mm'];
                            $lock_group_id = $accessControl['lock_group_id'];
                            $access_control_id = $accessControl['keyLockGroup_ID'];

                            //$response['access_control'][] = $accessControl;
                            $response['data']['access_control'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control'][$i]['pairing_name'] = $accessControl['pairing_name'];
                            $response['data']['access_control'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
                            $response['data']['access_control'][$i]['key_group_id'] = $accessControl['key_group_id'];
                            $response['data']['access_control'][$i]['company_id'] = $accessControl['company_id'];
                            $response['data']['access_control'][$i]['users'] = $accessControl['users'];
                            $response['data']['access_control'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
                            $response['data']['access_control'][$i]['date_from'] = $accessControl['date_from'];
                            $response['data']['access_control'][$i]['date_to'] = $accessControl['date_to'];
                            $response['data']['access_control'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
                            $response['data']['access_control'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
                            $response['data']['access_control'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
                            $response['data']['access_control'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
                            $response['data']['access_control'][$i]['lat'] = $accessControl['lat'];
                            $response['data']['access_control'][$i]['long'] = $accessControl['long'];
                            $response['data']['access_control'][$i]['radius_in_m'] = $accessControl['radious']; //TODO: change in DB
                            $response['data']['access_control'][$i]['added_by'] = $accessControl['added_by'];
                            $response['data']['access_control'][$i]['allowed_days'] = $accessControl['allowed_days'];

                            $allowed_days = $accessControl['allowed_days'];

                            //unset($accessControl['keyLockGroup_ID']);
                            //unset($accessControl['pairing_name']);

                            $i++;
                        }
                    }
                    else {
                        if ($lock_group_id == $accessControl['$lock_group_id']) //If User company and User ID is correct
                        {
                            //$response['status'] = 'true';
                            unset($accessControl['_id']);
                            $access_date_from = $accessControl['date_from'];
                            $access_date_to = $accessControl['date_to'];
                            $access_time_from_hh = $accessControl['time_from_hh'];
                            $access_time_from_mm = $accessControl['time_from_mm'];
                            $access_time_to_hh = $accessControl['time_to_hh'];
                            $access_time_to_mm = $accessControl['time_to_mm'];
                            $lock_group_id = $accessControl['lock_group_id'];

                            //$response['access_control'][] = $accessControl;
                            $response['data']['access_control'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control'][$i]['pairing_name'] = $accessControl['pairing_name'];
                            $response['data']['access_control'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
                            $response['data']['access_control'][$i]['key_group_id'] = $accessControl['key_group_id'];
                            $response['data']['access_control'][$i]['company_id'] = $accessControl['company_id'];
                            $response['data']['access_control'][$i]['users'] = $accessControl['users'];
                            $response['data']['access_control'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
                            $response['data']['access_control'][$i]['date_from'] = $accessControl['date_from'];
                            $response['data']['access_control'][$i]['date_to'] = $accessControl['date_to'];
                            $response['data']['access_control'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
                            $response['data']['access_control'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
                            $response['data']['access_control'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
                            $response['data']['access_control'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
                            $response['data']['access_control'][$i]['lat'] = $accessControl['lat'];
                            $response['data']['access_control'][$i]['long'] = $accessControl['long'];
                            $response['data']['access_control'][$i]['radius'] = $accessControl['radious']; //TODO: change in DB
                            $response['data']['access_control'][$i]['added_by'] = $accessControl['added_by'];

                            //unset($accessControl['keyLockGroup_ID']);
                            //unset($accessControl['pairing_name']);

                            $i++;
                        }
                    }
                }
            }

            //----------------
            // Permit Disabled
            //----------------
            $response['data']['lock_group_id'] = $lock_group_id;

            //----------------
            // Lock
            //----------------
            $locks = $LockBluetoothController->actionGetOneById($lock_id);
            if(isset($locks)) {
                unset($locks['_id']);
//                $response['data']['lock_details'] = $locks;
                //------------
                // Approval For Lock - Added to check if lock needs approval
                //------------
                //Check result of approval for lock
                $approval_for_lock = $ApprovalForLockController->actionGetByLockId($locks['lock_ID']);
                unset($approval_for_lock['_id']);
                $response['data']['approval_for_lock'] = $approval_for_lock;

                if (isset($approval_for_lock)) {
                    $response['data']['approval']['require_admin_approval'] = $approval_for_lock['require_admin_approval'];
                    $response['data']['approval']['require_subadmin_approval'] = $approval_for_lock['require_subadmin_approval'];
                } else {
                    $response['data']['approval']['require_admin_approval'] = null;
                    $response['data']['approval']['require_subadmin_approval'] = null;
                }

                $current_user_approval_request = array();
                $current_user_approval_request['from_date'] = '';
                $current_user_approval_request['to_date'] = '';
                $current_user_approval_request['from_time'] = '';
                $current_user_approval_request['to_time'] = '';

                // Get Lock Approval Status, if require 2nd approval
                if ($approval_for_lock['require_admin_approval'] == true) {
                    $response['data']['approval']['request_approval_required'] = TRUE;
                    //------------
                    // Approval Request For Lock
                    //------------
                    $cursor_approval_request = $ApprovalRequestForLockController->actionGetByLockId($locks['lock_ID']);
                    $i = 0; // count
                    if ($cursor_approval_request->count() > 0) {
                        foreach ($cursor_approval_request as $approval_request) {
                            // Added 2020-10-14: timestamps
                            if (!isset($approval_request['from_date'])) {
                                $approval_request['from_date'] = '';
                            }
                            if (!isset($approval_request['to_date'])) {
                                $approval_request['to_date'] = '';
                            }
                            if (!isset($approval_request['from_time'])) {
                                $approval_request['from_time'] = '';
                            }
                            if (!isset($approval_request['to_time'])) {
                                $approval_request['to_time'] = '';
                            }
                            // End Timestamps

                            // Full details
                            $approval_status = "";
                            if ($approval_request['user_id'] == $user_id) {
                                if ($approval_request['admin_approved'] == true) {
                                    $approval_status = "approved";
                                }
                                if ($approval_request['admin_rejected'] == false && $approval_request['admin_approved_by'] == 0) {
                                    $approval_status = "pending";
                                }
                                if ($approval_request['admin_rejected'] == true) {
                                    $approval_status = "rejected";
                                }

                                $current_user_approval_request = array(
                                    'approval_request_for_lock_id' => $approval_request['approval_request_for_lock_id'],
                                    'company_id' => $approval_request['company_id'],
                                    'user_id' => $approval_request['user_id'],
//                                        'permit_id' => $approval_request['permit_id'],
                                    'lock_id' => $approval_request['lock_id'],
                                    'created_timestamp' => $approval_request['created_timestamp'],
                                    'created_by_user_id' => $approval_request['created_by_user_id'],
                                    'notified_admin_user_id' => $approval_request['notified_admin_user_id'],
                                    'admin_approved' => $approval_request['admin_approved'],
                                    'admin_approved_by' => $approval_request['admin_approved_by'],
                                    'admin_approved_on' => $approval_request['admin_approved_on'],
                                    'admin_rejected' => $approval_request['admin_rejected'],
                                    'admin_rejected_by' => $approval_request['admin_rejected_by'],
                                    'admin_rejected_on' => $approval_request['admin_rejected_on'],
                                    'subadmin_approved' => $approval_request['subadmin_approved'],
                                    'subadmin_approved_by' => $approval_request['subadmin_approved_by'],
                                    'subadmin_approved_on' => $approval_request['subadmin_approved_on'],
                                    'valid_from' => $approval_request['admin_approved_on'],
                                    //                                            'valid_to' => $approval_request['valid_until'],
                                    'approval_status' => $approval_status,

                                    'from_date' => $approval_request['from_date'],
                                    'to_date' => $approval_request['to_date'],
                                    'from_time' => $approval_request['from_time'],
                                    'to_time' => $approval_request['to_time'],
                                );

                                $response['data']['request']['all_previous_approval_request'][$i]['approval_request_for_lock_id'] = $approval_request['approval_request_for_lock_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['company_id'] = $approval_request['company_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['user_id'] = $approval_request['user_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['username'] = $username;
                                //                                        $response['data']['request']['all_previous_approval_request'][$i]['full_name'] = $full_name; // User full name
//                                    $response['data']['request']['all_previous_approval_request'][$i]['permit_id'] = $approval_request['permit_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['lock_id'] = $approval_request['lock_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['created_timestamp'] = $approval_request['created_timestamp'];
                                $response['data']['request']['all_previous_approval_request'][$i]['created_by_user_id'] = $approval_request['created_by_user_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['notified_admin_user_id'] = $approval_request['notified_admin_user_id'];
                                $response['data']['request']['all_previous_approval_request'][$i]['admin_approved'] = $approval_request['admin_approved'];
                                $response['data']['request']['all_previous_approval_request'][$i]['admin_approved_by'] = $approval_request['admin_approved_by'];
                                $response['data']['request']['all_previous_approval_request'][$i]['admin_approved_on'] = $approval_request['admin_approved_on'];
                                $response['data']['request']['all_previous_approval_request'][$i]['admin_rejected'] = $approval_request['admin_rejected'];
                                $response['data']['request']['all_previous_approval_request'][$i]['admin_rejected_by'] = $approval_request['admin_rejected_by'];
                                $response['data']['request']['all_previous_approval_request'][$i]['admin_rejected_on'] = $approval_request['admin_rejected_on'];
                                $response['data']['request']['all_previous_approval_request'][$i]['subadmin_approved'] = $approval_request['subadmin_approved'];
                                $response['data']['request']['all_previous_approval_request'][$i]['subadmin_approved_by'] = $approval_request['subadmin_approved_by'];
                                $response['data']['request']['all_previous_approval_request'][$i]['subadmin_approved_on'] = $approval_request['subadmin_approved_on'];
                                // $response['data'][$i]['valid_from'] = $approval_request['valid_from'];
                                $response['data']['request']['all_previous_approval_request'][$i]['valid_until'] = $approval_request['valid_until'];
                                $response['data']['request']['all_previous_approval_request'][$i]['valid_from'] = $approval_request['admin_approved_on'];
                                //                                        $response['data']['request']['all_previous_approval_request'][$i]['valid_to'] = $approval_request['valid_until'];
                                $response['data']['request']['all_previous_approval_request'][$i]['approval_status'] = $approval_status;

                                $response['data']['request']['all_previous_approval_request'][$i]['from_date'] = $approval_request['from_date'];
                                $response['data']['request']['all_previous_approval_request'][$i]['to_date'] = $approval_request['to_date'];
                                $response['data']['request']['all_previous_approval_request'][$i]['from_time'] = $approval_request['from_time'];
                                $response['data']['request']['all_previous_approval_request'][$i]['to_time'] = $approval_request['to_time'];
                            }
                        }
                    }

                    $response['data']['last_approval_request'] = $current_user_approval_request;
                    //-------------
                    // Final Check for Opening Lock
                    //-------------
                    $last_approval_request_for_lock_id = $approval_request['approval_request_for_lock_id'];
                    // Check Approval
                    if ($approval_status == "approved") {
                        $response['data']['approved'] = TRUE;
                    } else {
                        $response['data']['approved'] = FALSE;
                    }

                    // Check Request Period
                    // Date Allowed
                    if (strtotime($date_now) >= strtotime($current_user_approval_request['from_date']) &&
                        strtotime($date_now) <= strtotime($current_user_approval_request['to_date'])) {
                        $response['data']['request_date_allowed'] = TRUE;
                    } else {
                        $response['data']['request_date_allowed'] = FALSE;
                    }

                    // Date Allowed
                    if (strtotime($time_now) >= strtotime($current_user_approval_request['from_time']) &&
                        strtotime($time_now) <= strtotime($current_user_approval_request['to_time'])) {
                        $response['data']['request_time_allowed'] = TRUE;
                    } else {
                        $response['data']['request_time_allowed'] = FALSE;
                    }

                    // Allowed Day
                    $response['data']['request_day_allowed'] = TRUE;
                    if (in_array((string)$day_of_week_now, $allowed_days)) {
                        $response['data']['request_day_allowed'] = TRUE;
                    } else {
                        $response['data']['request_day_allowed'] = FALSE;
                    }

                    // Can Open Lock
                    if ($response['data']['approved'] === TRUE &&
                        $response['data']['request_date_allowed'] === TRUE &&
                        $response['data']['request_time_allowed'] === TRUE &&
                        $response['data']['request_day_allowed'] === TRUE) {
                        $response['open_lock'] = TRUE;
                        $description = 'Success: Open lock';
                    } else {
                        $response['open_lock'] = FALSE;
                    }
                } else {
                    unset($response['error']);

                    $response['data']['request_approval_required'] = FALSE;

                    // Allowed Day
                    $response['data']['request_day_allowed'] = TRUE;
                    if (in_array((string)$day_of_week_now, $allowed_days)) {
                        $response['data']['request_day_allowed'] = TRUE;
                    } else {
                        $response['data']['request_day_allowed'] = FALSE;
                    }

                    // Can Open Lock
                    if ($response['data']['request_day_allowed'] === TRUE) {
                        $response['open_lock'] = TRUE;
                        $description = 'Success: Open lock';
                    } else {
                        $response['open_lock'] = FALSE;
                    }

                }
                // End require 2nd approval

                //----------
                // Log for lock (Bluetooth)
                //----------
                $log_open_lock = $LogLockBluetoothActivityController->actionInsert($company_id,$user_id,$lock_id,$description,$category);
                $response['data']['log_added'] = $log_open_lock;
            }
		}
	}
}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
