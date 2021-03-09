<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Database.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Constant.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Utility.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockBluetoothController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForNormalAccessController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForSpecialAccessController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForSecondApprovalController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalForLockController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/permit/controllers/PermitController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/accessControl/controllers/AccessControlController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\lock\controllers\LockRequestApprovalForNormalAccessController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSpecialAccessController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSecondApprovalController;
use api\modules\v1\lock\controllers\ApprovalForLockController;
use api\modules\v1\permit\controllers\PermitController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use common\config\Constant;
use common\config\Database;
use common\config\Utility;

$response = array();

//List Locks under Permit
// Updated 2020-03-04
if( isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
    isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
    isset($_REQUEST['latitude']) && isset($_REQUEST['longitude']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';

    $datetime_now = date("c");
    $date_now = date("Y-m-d");
    $time_now = date("H:i:s");
    $day_of_week_now = date('w');
    $response['datetime_now'] = $datetime_now;
    $response['date_now'] = $date_now;
    $response['time_now'] = $time_now;
    $response['day_of_week_now'] = $day_of_week_now;

    $user_id = $_REQUEST['user_id'];
    $latitude_from = (float) $_REQUEST['latitude'];
    $longitude_from = (float) $_REQUEST['longitude'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $Utility = new Utility();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $LockRequestApprovalForNormalAccessController = new LockRequestApprovalForNormalAccessController($Database);
    $LockRequestApprovalForSpecialAccessController = new LockRequestApprovalForSpecialAccessController($Database);
    $LockRequestApprovalForSecondApprovalController = new LockRequestApprovalForSecondApprovalController($Database);
    $ApprovalForLockController = new ApprovalForLockController($Database);
    $PermitController = new PermitController($Database);
    $AccessControlController = new AccessControlController($Database);

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
                            $radius_in_meter = $accessControl['radius'];

                            //$response['access_control'][] = $accessControl;
                            $response['data']['access_control']['all'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control']['all'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control']['all'][$i]['pairing_name'] = $accessControl['pairing_name'];
                            $response['data']['access_control']['all'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
                            $response['data']['access_control']['all'][$i]['key_group_id'] = $accessControl['key_group_id'];
                            $response['data']['access_control']['all'][$i]['company_id'] = $accessControl['company_id'];
                            $response['data']['access_control']['all'][$i]['users'] = $accessControl['users'];
                            $response['data']['access_control']['all'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
                            $response['data']['access_control']['all'][$i]['date_from'] = $accessControl['date_from'];
                            $response['data']['access_control']['all'][$i]['date_to'] = $accessControl['date_to'];
                            $response['data']['access_control']['all'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
                            $response['data']['access_control']['all'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
                            $response['data']['access_control']['all'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
                            $response['data']['access_control']['all'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
                            $response['data']['access_control']['all'][$i]['lat'] = $accessControl['lat'];
                            $response['data']['access_control']['all'][$i]['long'] = $accessControl['long'];
                            $response['data']['access_control']['all'][$i]['radius_in_m'] = $accessControl['radius'];
                            $response['data']['access_control']['all'][$i]['added_by'] = $accessControl['added_by'];
                            $response['data']['access_control']['all'][$i]['allowed_days'] = $accessControl['allowed_days'];

                            $allowed_days = $accessControl['allowed_days'];

                            //unset($accessControl['keyLockGroup_ID']);
                            //unset($accessControl['pairing_name']);

                            $access_control_time_from = (string) $accessControl['time_from_hh'].':'.(string)$accessControl['time_from_mm'];
                            $access_control_time_to = (string) $accessControl['time_to_hh'].':'.(string)$accessControl['time_to_mm'];
                            $response['data']['access_control']['all'][$i]['time_from'] = $access_control_time_from;
                            $response['data']['access_control']['all'][$i]['time_to'] = $access_control_time_to;

                            // Show last as active
                            $response['data']['access_control']['active'] = $response['data']['access_control']['all'][$i];

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
                            $radius_in_meter = $accessControl['radius'];

                            //$response['access_control'][] = $accessControl;
                            $response['data']['access_control']['all'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control']['all'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
                            $response['data']['access_control']['all'][$i]['pairing_name'] = $accessControl['pairing_name'];
                            $response['data']['access_control']['all'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
                            $response['data']['access_control']['all'][$i]['key_group_id'] = $accessControl['key_group_id'];
                            $response['data']['access_control']['all'][$i]['company_id'] = $accessControl['company_id'];
                            $response['data']['access_control']['all'][$i]['users'] = $accessControl['users'];
                            $response['data']['access_control']['all'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
                            $response['data']['access_control']['all'][$i]['date_from'] = $accessControl['date_from'];
                            $response['data']['access_control']['all'][$i]['date_to'] = $accessControl['date_to'];
                            $response['data']['access_control']['all'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
                            $response['data']['access_control']['all'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
                            $response['data']['access_control']['all'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
                            $response['data']['access_control']['all'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
                            $response['data']['access_control']['all'][$i]['lat'] = $accessControl['lat'];
                            $response['data']['access_control']['all'][$i]['long'] = $accessControl['long'];
                            $response['data']['access_control']['all'][$i]['default_radius'] = $accessControl['radius'];
                            $response['data']['access_control']['all'][$i]['added_by'] = $accessControl['added_by'];

                            //unset($accessControl['keyLockGroup_ID']);
                            //unset($accessControl['pairing_name']);

                            // Additional
                            $access_control_time_from = (string) $accessControl['time_from_hh'].':'.(string)$accessControl['time_from_mm'];
                            $access_control_time_to = (string) $accessControl['time_to_hh'].':'.(string)$accessControl['time_to_mm'];
                            $response['data']['access_control']['all'][$i]['time_from'] = $access_control_time_from;
                            $response['data']['access_control']['all'][$i]['time_to'] = $access_control_time_to;

                            // Show last as active
                            $response['data']['access_control']['active'] = $response['data']['access_control']['all'][$i];

                            $i++;
                        }
                    }
                }
            }

            //----------------
            // Permit Disabled
            //----------------
            $response['data']['lock_group_id'] = $lock_group_id;
            $response['data']['lock_listing'] = 'allowed';

            $cursor_locks = $LockBluetoothController->actionGetByLockGroupId($lock_group_id);

            if($cursor_locks->count() > 0) {
                unset($response['error']);
                $response['status'] = 'true';
                $x=0;
                foreach($cursor_locks as $locks)
                {
                    unset($locks['_id']);
                    //if (in_array($locks['company_id'],$com)){
                    //if (in_array($locks['company_id'],$com)){
                    //Show Data
                    //$response['data']['locks'][$x] = $locks;
                    $response['data']['locks'][$x]['lock_id'] = $locks['lock_ID'];
                    $response['data']['locks'][$x]['serial_number'] = $locks['serial_number'];
                    $response['data']['locks'][$x]['company_id'] = $locks['company_id'];
                    $response['data']['locks'][$x]['lock_name'] = $locks['lock_name'];
                    $response['data']['locks'][$x]['lock_type'] = $locks['lock_type'];
                    $response['data']['locks'][$x]['lock_model'] = $locks['lock_model'];
                    $response['data']['locks'][$x]['lock_mechanism'] = $locks['lock_mechanism'];
                    $response['data']['locks'][$x]['brand'] = $locks['brand'];
                    $response['data']['locks'][$x]['entrance_visibility'] = $locks['entrance_visibility'];
                    $response['data']['locks'][$x]['lock_group_id'] = $locks['lock_group_id'];
                    $response['data']['locks'][$x]['log_number'] = $locks['log_number'];
                    $response['data']['locks'][$x]['site_id'] = $locks['site_id'];
                    $response['data']['locks'][$x]['geo_fencing'] = $locks['geo_fencing'];
                    $response['data']['locks'][$x]['latitude'] = $locks['latitude'];
                    $response['data']['locks'][$x]['longitude'] = $locks['longitude'];
                    $response['data']['locks'][$x]['unlock_radius'] = $locks['unlock_radius'];
                    $response['data']['locks'][$x]['allowed_days'] = $allowed_days;

                    //--------------------
                    // Geo fencing
                    //--------------------
                    $geo_fencing = $locks['geo_fencing'];
                    $latitude_to = $locks['latitude'];
                    $longitude_to = $locks['longitude'];
                    $unlock_radius = $locks['unlock_radius'];
                    $distance_in_meter = $Utility->haversineGreatCircleDistance($latitude_from,$longitude_from,$latitude_to,$longitude_to);
                    if(isset($distance_in_meter)){
                        $response['status'] = 'true';
                        unset($response['error']);
                        $response['data']['locks'][$x]['distance'] = $distance_in_meter;

                        // Geo fencing Required
                        if ($geo_fencing === TRUE){
                            $response['data']['lock']['ignore_geo_fencing'] = FALSE;
                        }
                        else{
                            $response['data']['lock']['ignore_geo_fencing'] = TRUE;
                        }

                        // Distance Comparison
                        $difference = $unlock_radius - $distance_in_meter;
                        $response['data']['locks'][$x]['difference_with_unlock_radius'] = $difference;
                        if ( $difference >= 0 ){
                            $response['data']['locks'][$x]['unlock_with_geo_fencing'] = TRUE;
                        }
                        else{
                            if( $geo_fencing === FALSE){
                                $response['data']['locks'][$x]['unlock_with_geo_fencing'] = TRUE;
                            }
                            else{
                                $response['data']['locks'][$x]['unlock_with_geo_fencing'] = FALSE;
                            }
                        }
                    }
                    else{
                        $response['error'] = 'Geo fencing distance calculation error';
                    }

                    //------------
                    // Request Approval for Normal Access - Added to check if lock needs approval
                    //------------
                    //Check result of approval for lock
                    $approval_for_lock = $ApprovalForLockController->actionGetByLockId($locks['lock_ID']);
                    unset($approval_for_lock['_id']);
                    $response['data']['approval_for_lock'] = $approval_for_lock;

                    if(isset($approval_for_lock)) {
                        $response['data']['locks'][$x]['require_admin_approval'] = $approval_for_lock['require_admin_approval'];
                        $response['data']['locks'][$x]['require_subadmin_approval'] = $approval_for_lock['require_subadmin_approval'];
                    }
                    else{
                        $response['data']['locks'][$x]['require_admin_approval'] = null;
                        $response['data']['locks'][$x]['require_subadmin_approval'] = null;
                    }

                    $current_user_approval_request = array();
                    $current_user_approval_request['from_date']='';
                    $current_user_approval_request['to_date']='';
                    $current_user_approval_request['from_time']='';
                    $current_user_approval_request['to_time']='';

                    // Get Lock Approval Status, if require 2nd approval
                    if ($approval_for_lock['require_admin_approval'] == true){
                        $response['data']['locks'][$x]['request_approval_required'] = TRUE;

                        //------------
                        // Approval Request For Lock
                        //------------
                        $cursor_approval_request = $ApprovalRequestForLockController->actionGetByLockId($locks['lock_ID']);
                        $i=0; // count
                        if($cursor_approval_request->count() >0) {
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

                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['approval_request_for_lock_id'] = $approval_request['approval_request_for_lock_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['company_id'] = $approval_request['company_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['user_id'] = $approval_request['user_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['username'] = $username;
                                    //                                        $response['data']['locks'][$x]['all_previous_approval_request'][$i]['full_name'] = $full_name; // User full name
//                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['permit_id'] = $approval_request['permit_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['lock_id'] = $approval_request['lock_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['created_timestamp'] = $approval_request['created_timestamp'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['created_by_user_id'] = $approval_request['created_by_user_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['notified_admin_user_id'] = $approval_request['notified_admin_user_id'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['admin_approved'] = $approval_request['admin_approved'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['admin_approved_by'] = $approval_request['admin_approved_by'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['admin_approved_on'] = $approval_request['admin_approved_on'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['admin_rejected'] = $approval_request['admin_rejected'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['admin_rejected_by'] = $approval_request['admin_rejected_by'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['admin_rejected_on'] = $approval_request['admin_rejected_on'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['subadmin_approved'] = $approval_request['subadmin_approved'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['subadmin_approved_by'] = $approval_request['subadmin_approved_by'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['subadmin_approved_on'] = $approval_request['subadmin_approved_on'];
                                    // $response['data'][$i]['valid_from'] = $approval_request['valid_from'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['valid_until'] = $approval_request['valid_until'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['valid_from'] = $approval_request['admin_approved_on'];
                                    //                                        $response['data']['locks'][$x]['all_previous_approval_request'][$i]['valid_to'] = $approval_request['valid_until'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['approval_status'] = $approval_status;

                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['from_date'] = $approval_request['from_date'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['to_date'] = $approval_request['to_date'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['from_time'] = $approval_request['from_time'];
                                    $response['data']['locks'][$x]['all_previous_approval_request'][$i]['to_time'] = $approval_request['to_time'];
                                }
                            }
                        }

                        $response['data']['locks'][$x]['last_approval_request'] = $current_user_approval_request;
                        //-------------
                        // Final Check for Opening Lock
                        //-------------
//                        $last_approval_request_for_lock_id = $approval_request['approval_request_for_lock_id'];
                        // Check Approval
                        if ( isset($approval_status) && $approval_status == "approved" ) {
                            $response['data']['locks'][$x]['approved'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['approved'] = FALSE;
                        }

                        // Check Access Control Period
//                        // Access Control Date Allowed
//                        if ( strtotime($date_now) >= strtotime($response['data']['access_control']['active']['date_from']) &&
//                            strtotime($date_now) <= strtotime($response['data']['access_control']['active']['date_to']) ) {
//                            $response['data']['locks'][$x]['request_date_allowed'] = TRUE;
//                        }
//                        else{
//                            $response['data']['locks'][$x]['request_date_allowed'] = FALSE;
//                        }
//
//                        // Access Control Date Allowed
//                        if ( strtotime($time_now) >= strtotime($response['data']['access_control']['active']['time_from']) &&
//                            strtotime($time_now) <= strtotime($response['data']['access_control']['active']['time_to']) ) {
//                            $response['data']['locks'][$x]['request_time_allowed'] = TRUE;
//                        }
//                        else{
//                            $response['data']['locks'][$x]['request_time_allowed'] = FALSE;
//                        }

                        // Allowed Day
                        $response['data']['locks'][$x]['request_day_allowed'] = TRUE;
                        if ( in_array( (string)$day_of_week_now,$allowed_days ) ||
                            in_array( (int)$day_of_week_now,$allowed_days)){
                            $response['data']['locks'][$x]['request_day_allowed'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['request_day_allowed'] = FALSE;
                        }

                        // Check Approval Request Period
                        // Date Allowed
                        if ( isset($current_user_approval_request) &&
                            strtotime($date_now) >= strtotime($current_user_approval_request['from_date']) &&
                            strtotime($date_now) <= strtotime($current_user_approval_request['to_date']) ) {
                            $response['data']['locks'][$x]['request_date_allowed'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['request_date_allowed'] = FALSE;
                        }

                        // Time Allowed
                        // If time_from is later than time_to
                        if (isset($current_user_approval_request) &&
                            strtotime($current_user_approval_request['to_time']) >= strtotime($current_user_approval_request['from_time']))
                        {
                            if (isset($current_user_approval_request) &&
                                strtotime($time_now) >= strtotime($current_user_approval_request['from_time']) &&
                                strtotime($time_now) <= strtotime($current_user_approval_request['to_time'])) {
                                $response['data']['locks'][$x]['request_time_allowed'] = TRUE;
                            } else {
                                $response['data']['locks'][$x]['request_time_allowed'] = FALSE;
                            }
                        }
                        else{
                            if (isset($current_user_approval_request) &&
                                strtotime($time_now) <= strtotime($current_user_approval_request['from_time']))
                            {
                                $response['data']['request_time_allowed'] = TRUE;
                                $response['data']['lock']['request_time_allowed'] = TRUE;
                            }
                            else if (isset($current_user_approval_request) &&
                                strtotime($time_now) >= strtotime($current_user_approval_request['to_time']))
                            {
                                $response['data']['request_time_allowed'] = TRUE;
                                $response['data']['lock']['request_time_allowed'] = TRUE;
                            }
                            else{
                                $response['data']['request_time_allowed'] = FALSE;
                                $response['data']['lock']['request_time_allowed'] = FALSE;
                            }
                        }

                        // Open Lock Indicator
                        // Approved
                        if ( $response['data']['locks'][$x]['approved'] === TRUE ){
                            $response['data']['locks'][$x]['2nd_approval_status'] = 'Active';
                            $response['data']['locks'][$x]['approval_request_required'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_request'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_lock_red'] = FALSE;
                            if ($response['data']['locks'][$x]['request_date_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['request_time_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['request_day_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['unlock_with_geo_fencing'] === TRUE)
                            {
                                $response['data']['locks'][$x]['approved_but_cannot_open_lock'] = FALSE;
                                $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = FALSE;
                                $response['data']['locks'][$x]['show_icon_request'] = FALSE;
                                $response['data']['locks'][$x]['show_icon_lock_red'] = FALSE;
                                $response['data']['locks'][$x]['open_lock'] = TRUE;
                                $response['data']['locks'][$x]['show_icon_lock_green'] = TRUE;
                            }
                            else{
                                $response['data']['locks'][$x]['open_lock'] = FALSE;
                                $response['data']['locks'][$x]['show_icon_lock_green'] = FALSE;
                                $response['data']['locks'][$x]['approved_but_cannot_open_lock'] = TRUE;
                                $response['data']['locks'][$x]['show_icon_condition_not_met'] = FALSE;
                                $response['data']['locks'][$x]['show_icon_lock_red'] = TRUE;

                                if ($response['data']['locks'][$x]['request_date_allowed'] === TRUE &&
                                    $response['data']['locks'][$x]['request_time_allowed'] === TRUE &&
                                    $response['data']['locks'][$x]['request_day_allowed'] === TRUE &&
                                    $response['data']['locks'][$x]['unlock_with_geo_fencing'] === FALSE)
                                {
                                    $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = TRUE;
                                }
                                else{
                                    $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = FALSE;
                                }
                            }
                        }
                        // Not approved
                        else{
                            $response['data']['locks'][$x]['2nd_approval_status'] = 'Required';
                            $response['data']['locks'][$x]['approved_but_cannot_open_lock'] = FALSE;
                            $response['data']['locks'][$x]['approval_request_required'] = TRUE;
                            $response['data']['locks'][$x]['show_icon_request'] = TRUE;
                            $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_lock_red'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_lock_green'] = FALSE;
                            $response['data']['locks'][$x]['open_lock'] = FALSE;
                        }
                    }
                    // 2nd Approval not required
                    else{
                        unset($response['error']);

                        $response['data']['locks'][$x]['2nd_approval_status'] = "Not Applicable";
                        $response['data']['locks'][$x]['request_approval_required'] = FALSE;

                        // Date Allowed
                        if ( strtotime($date_now) >= strtotime($response['data']['access_control']['active']['date_from']) &&
                            strtotime($date_now) <= strtotime($response['data']['access_control']['active']['date_to']) ) {
                            $response['data']['locks'][$x]['request_date_allowed'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['request_date_allowed'] = FALSE;
                        }

                        // Date Allowed
                        if ( strtotime($time_now) >= strtotime($response['data']['access_control']['active']['time_from']) &&
                            strtotime($time_now) <= strtotime($response['data']['access_control']['active']['time_to']) ) {
                            $response['data']['locks'][$x]['request_time_allowed'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['request_time_allowed'] = FALSE;
                        }

                        // Allowed Day
                        $response['data']['locks'][$x]['request_day_allowed'] = TRUE;
                        if ( in_array( (string)$day_of_week_now,$allowed_days ) ){
                            $response['data']['locks'][$x]['request_day_allowed'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['request_day_allowed'] = FALSE;
                        }

                        // Indicator
                        $response['data']['locks'][$x]['approved_but_cannot_open_lock'] = FALSE;
                        $response['data']['locks'][$x]['approval_request_required'] = FALSE;

                        // Can Open Lock
                        if (    $response['data']['locks'][$x]['request_day_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['request_date_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['request_time_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['unlock_with_geo_fencing'] === TRUE )
                        {
                            $response['data']['locks'][$x]['approved_but_cannot_open_lock'] = FALSE;
                            $response['data']['locks'][$x]['approval_request_required'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_request'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_lock_red'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_lock_green'] = TRUE;
                            $response['data']['locks'][$x]['open_lock'] = TRUE;
                        }
                        else{
                            $response['data']['locks'][$x]['approved_but_cannot_open_lock'] = TRUE;
                            $response['data']['locks'][$x]['approval_request_required'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_request'] = FALSE;
                            $response['data']['locks'][$x]['show_icon_lock_red'] = TRUE;
                            $response['data']['locks'][$x]['show_icon_lock_green'] = FALSE;
                            $response['data']['locks'][$x]['open_lock'] = FALSE;

                            if ($response['data']['locks'][$x]['request_date_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['request_time_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['request_day_allowed'] === TRUE &&
                                $response['data']['locks'][$x]['unlock_with_geo_fencing'] === FALSE)
                            {
                                $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = TRUE;
                            }
                            else{
                                $response['data']['locks'][$x]['show_icon_approved_lock_out_of_distance'] = FALSE;
                            }
                        }
                    }

                    // Indicator: Confirm Lock
                    if ($response['data']['locks'][$x]['brand'] == "KNL" &&
                        $response['data']['locks'][$x]['lock_mechanism'] == "Bluetooth"    ){
                        $response['data']['locks'][$x]['show_icon_confirm_lock'] = TRUE;
                    }

                    // Indicator: Confirm Lock
                    if (isset($response['data']['locks'][$x]['maintenance'])){
                        if ($response['data']['locks'][$x]['maintenance'] === TRUE){
                            $response['data']['locks'][$x]['show_icon_maintenance'] = TRUE;
                        }
                    else{
                            $response['data']['locks'][$x]['show_icon_maintenance'] = FALSE;
                        }
                    }
                    else{
                        $response['data']['locks'][$x]['show_icon_maintenance'] = FALSE;
                    }

                    //}
                    $x++;
                }
            }

            //echo $time_after_format;
            //$detection_time = date('d/m/Y H:i:s',strtotime($alarm_time));
            //
//			$exit_building_start_time = date('m/d/Y H:i:s',strtotime($alarm_time));
//			$exit_building_start_time = date('m/d/Y H:i:s',strtotime($time_after_format));
//			$exit_building_start_time_before_format = DateTime::createFromFormat($american_time_format, $exit_building_start_time);
//			$exit_building_start_time_date_first =  $exit_building_start_time_before_format->format('d/m/Y H:i:s');
//			$exit_building_start_time = date('m/d/Y H:i:s', $exit_building_start_time_raw);
            //

        }
    }



}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
