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
use api\modules\v1\lock\controllers\LockRequestApprovalForNormalAccessController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSpecialAccessController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSecondApprovalController;
use api\modules\v1\lock\controllers\ApprovalForLockController;
use api\modules\v1\permit\controllers\PermitController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use api\modules\v1\log\controllers\LogUserActivityController;
use api\modules\v1\log\controllers\LogLockOpeningActivityController;
use api\modules\v1\log\controllers\LogLockActivityController;
use common\config\Constant;
use common\config\Database;
use common\config\Utility;

$response = array();

//Open Locks under Permit
// Updated 2020-03-04
if( isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
    isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
    isset($_REQUEST['lock_id']) && $_REQUEST['lock_id'] != '' &&
    isset($_REQUEST['latitude']) && isset($_REQUEST['longitude']) )
{
    $user_id = $_REQUEST['user_id'];
    $lock_id = $_REQUEST['lock_id'];

    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';
    $response['open_lock'] = FALSE;

    $description = 'Failed: Open lock';
    $category = 'api: open_lock';
    $error_message = '';
    $lock_type = 'bluetooth';

    $datetime = date("c");
    $datetime_now = $datetime;
    $date_now = date("Y-m-d");
    $time_now = date("H:i:s");
    $day_of_week_now = date('w');
    $day_of_week_now_integer = (int)$day_of_week_now;
    $response['datetime_now'] = $datetime;
    $response['date_now'] = $date_now;
    $response['time_now'] = $time_now;
    $response['day_of_week_now'] = $day_of_week_now_integer;

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
    $LogLockActivityController = new LogLockActivityController($Database);
    $LogLockBluetoothActivityController = new LogLockOpeningActivityController($Database);
    $NotificationController = new NotificationController;

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
                            $response['data']['access_control']['all'][$i]['radius'] = $accessControl['radius'];
                            $response['data']['access_control']['all'][$i]['added_by'] = $accessControl['added_by'];

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

                if (isset($locks['display_name']) &&
                    $locks['display_name'] != NULL && $locks['display_name'] != ''){
                    $lock_display_name = $locks['display_name'];
                }else{
                    $lock_display_name = $locks['lock_name'];
                }
                $response['data']['lock']['lock_id'] = $locks['lock_ID'];
                $response['data']['lock']['serial_number'] = $locks['serial_number'];
                $response['data']['lock']['company_id'] = $locks['company_id'];
                $response['data']['lock']['lock_name'] = $locks['lock_name'];
                $response['data']['lock']['lock_type'] = $locks['lock_type'];
                $response['data']['lock']['lock_model'] = $locks['lock_model'];
                $response['data']['lock']['lock_mechanism'] = $locks['lock_mechanism'];
                $response['data']['lock']['brand'] = $locks['brand'];
                $response['data']['lock']['entrance_visibility'] = $locks['entrance_visibility'];
                $response['data']['lock']['lock_group_id'] = $locks['lock_group_id'];
                $response['data']['lock']['log_number'] = $locks['log_number'];
                $response['data']['lock']['site_id'] = $locks['site_id'];
                $response['data']['lock']['geo_fencing'] = $locks['geo_fencing'];
                $response['data']['lock']['latitude'] = $locks['latitude'];
                $response['data']['lock']['longitude'] = $locks['longitude'];
                $response['data']['lock']['unlock_radius'] = $locks['unlock_radius'];

                $response['data']['lock']['lock_name'] = $locks['lock_name'];
                $response['data']['lock']['lock_display_name'] = $lock_display_name;
                $response['data']['lock']['geo_fencing'] = $locks['geo_fencing'];

                $response['data']['lock']['allowed_days'] = $allowed_days;
//                $response['data']['lock_details'] = $locks;
                
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
                    $response['data']['distance'] = $distance_in_meter;

                    // Geo fencing Required
                    $response['data']['ignore_geo_fencing'] = $Utility->checkIgnoreGeoFencing($geo_fencing);
                    $response['data']['require_geo_fencing'] = $geo_fencing;

                    // Distance Comparison
                    $difference = $Utility->calculateDistanceDifference($unlock_radius,$distance_in_meter);
                    $response['data']['difference_with_unlock_radius'] = $difference;
                    $response['data']['within_geo_fencing_distance'] = $Utility->compareGeoFencingDistance($geo_fencing,$difference);
                }
                else{
                    $response['data']['require_geo_fencing'] = $geo_fencing;
                    $response['data']['within_geo_fencing_distance'] = false;
                    $response['error'] = 'Geo fencing distance calculation error';
                }

                $require_geo_fencing = $geo_fencing;
                $within_geo_fencing_distance = $response['data']['within_geo_fencing_distance'];

                //------------
                // Approval For Lock - Added to check if lock needs approval
                //------------
                //Check result of approval for lock
                $approval_for_lock = $ApprovalForLockController->actionGetByLockId($locks['lock_ID']);
                unset($approval_for_lock['_id']);
                $response['data']['approval_for_lock'] = $approval_for_lock;

                if (isset($approval_for_lock)) {
                    $response['data']['approval_setting']['require_admin_approval'] = $approval_for_lock['require_admin_approval'];
                    $response['data']['approval_setting']['require_subadmin_approval'] = $approval_for_lock['require_subadmin_approval'];
                    $response['data']['approval_setting']['require_second_approval'] = $approval_for_lock['require_second_approval'];
                } else {
                    $response['data']['approval_setting']['require_admin_approval'] = false;
                    $response['data']['approval_setting']['require_subadmin_approval'] = false;
                    $response['data']['approval_setting']['require_second_approval'] = false;
                }

                $current_user_approval_request = array();
                $current_user_approval_request['from_date'] = '';
                $current_user_approval_request['to_date'] = '';
                $current_user_approval_request['from_time'] = '';
                $current_user_approval_request['to_time'] = '';

                // Get Lock Approval Status, if require 2nd approval
                if ($approval_for_lock['require_admin_approval'] == true) {
                    $response['data']['approval_setting']['request_approval_required'] = true;
                    //------------
                    // Request Approval for Normal Access
                    //------------
                    // Test all requests that include today
                    $cursor_request_approval_for_normal_access = $LockRequestApprovalForNormalAccessController->actionGetByUserIdAndLockId($user_id,$lock_id);
                    $response['data']['active_request_approval_for_normal_access'] = array();
                    if($cursor_request_approval_for_normal_access->count() > 0) {
                        $i=0;
                        $z=0;
                        foreach($cursor_request_approval_for_normal_access as $approval_request) {
                            unset($approval_request['_id']);
//                                $response['data']['request_approval_for_normal_access'][$i] = $approval_request;
//                            $response['data']['request_approval_for_normal_access'][$i]['approval_status'] =
//                                $Utility->returnApprovalSettingText($approval_request['admin_approved'],$approval_request['admin_approved_by'],$approval_request['admin_rejected']);

                            // Filter only today's request
                            // Date Filter
                            if ($approval_request['is_daily_timeslot'] === true){
                                $response['data']['use_access_control_day'] = false;

                                // Days of Week allowed
                                $response['data']['request_day_allowed'] =
                                    $Utility->daysOfWeekAllowedInteger($day_of_week_now_integer,$approval_request['day_of_week']);

                                // Insert into array if requirement fulfilled
                                if( $approval_request['admin_approved'] === true &&
                                    in_array($day_of_week_now_integer,$approval_request['day_of_week']) &&
                                    strtotime($date_now) >= strtotime($approval_request['date_from']) &&
                                    strtotime($date_now) <= strtotime($approval_request['date_to']) &&
                                    strtotime($time_now) >= strtotime($approval_request['time_from']) &&
                                    strtotime($time_now) <= strtotime($approval_request['time_to'])  )
                                {
                                    $response['data']['active_request_approval_for_normal_access'][$z] = $approval_request;
                                    $z++;
                                }
                            }
                            else{
                                $response['data']['use_access_control_day'] = false;
                                if( $approval_request['admin_approved'] === true &&
                                    strtotime($date_now) >= strtotime($approval_request['date_from']) &&
                                    strtotime($date_now) <= strtotime($approval_request['date_to']) &&
                                    strtotime($time_now) >= strtotime($approval_request['time_from']) &&
                                    strtotime($time_now) <= strtotime($approval_request['time_to'])  )
                                {
                                    $response['data']['active_request_approval_for_normal_access'][$z] = $approval_request;
                                    $z++;
                                }
                            }
                            $i++;
                        }
                    }else{
                        $response['data']['request_approval_for_normal_access'] = array();
                        $response['data']['use_access_control_day'] = false;

                        // Days of Week allowed
                        if( in_array($day_of_week_now_integer,$allowed_days) ){
                            $response['data']['request_day_allowed'] = true;
                        }else{
                            $response['data']['request_day_allowed'] = false;
                        }
                    }

                    //-------------
                    // Request Approval for Second Approval
                    //-------------
                    // Get Lock Approval Status, if require 2nd approval
                    $response['data']['require_second_approval'] = $Utility->checkApprovalSetting($approval_for_lock['require_second_approval']);
                    $response['data']['active_request_approval_for_second_approval'] = array();
                    if( $response['data']['require_second_approval'] === true ){
                        $cursor_second_approval = $LockRequestApprovalForSecondApprovalController->actionGetByUserIdAndLockId($user_id,$lock_id);
                        if ( $cursor_second_approval->count() > 0 ){
                            $j = 0;
                            $y = 0;
                            foreach($cursor_second_approval as $second_approval) {
                                unset($second_approval['_id']);
//                                    $response['data']['request_approval_for_second_approval'][$j] = $second_approval;
//                                $response['data']['request_approval_for_second_approval'][$j]['approval_status'] =
//                                    $Utility->returnApprovalSettingText($second_approval['admin_approved'],$second_approval['admin_approved_by'],$second_approval['admin_rejected']);

                                $response['data']['second_approval'] = $second_approval['admin_approved'];

                                if( $second_approval['admin_approved'] === true &&
                                    strtotime($datetime_now) >= strtotime($second_approval['valid_from']) &&
                                    strtotime($datetime_now) <= strtotime($second_approval['valid_to'])  )
                                {

                                    $response['data']['active_request_approval_for_second_approval'][$y] = $second_approval;
                                    $y++;
                                }
                                $j++;
                            }
                        }
                    }

                    //-------------
                    // Final Check for Opening Lock
                    //-------------
                    // Active Request Approval for Normal Access
                    $response['data']['has_active_normal_access'] = $Utility->hasActiveApproval($response['data']['active_request_approval_for_normal_access']);

                    // Active Request Approval for Second Approval
                    $response['data']['has_active_second_approval'] = $Utility->hasActiveApproval($response['data']['active_request_approval_for_second_approval']);

                    // Open Lock Indicator
                    $open_lock_indicator = $Utility->indicatorForNormalAccessWithoutSpecialAccess(
                        $response['data']['approval_setting']['request_approval_required'],$response['data']['has_active_normal_access'],
                        $response['data']['approval_setting']['require_second_approval'],$response['data']['has_active_second_approval'],
                        $response['data']['require_geo_fencing'],$response['data']['within_geo_fencing_distance']
                    );

                    // Result for Open Lock
                    $response['data']['result'] = $open_lock_indicator;
                }
                // Approval for normal access not required
                else {
                    unset($response['error']);
                    $require_active_normal_access = false;
                    $has_active_normal_access = false;
                    $require_active_second_approval = false;
                    $has_active_second_approval = false;

                    // Open Lock Indicator - Access Control Permission
                    $open_lock_indicator= $Utility->indicatorForAccessControlPermission(
                        $date_now,$time_now,
                        $response['data']['access_control']['active']['date_from'],$response['data']['access_control']['active']['date_to'],
                        $response['data']['access_control']['active']['time_from'],$response['data']['access_control']['active']['time_to'],
                        $day_of_week_now,$allowed_days,
                        $response['data']['require_geo_fencing'],$response['data']['within_geo_fencing_distance']
                    );

                    $response['data']['result'] = $open_lock_indicator;

                }

                if ($response['open_lock'] === true){
                    unset($response['error']);
                    $response['status'] = true;
                    }
                else{ // If opening failed

                }
            }
        }
    }
}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
