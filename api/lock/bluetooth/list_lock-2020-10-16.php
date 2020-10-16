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
use common\config\Constant;
use common\config\Database;

$response = array();

//List Locks under Permit
// Updated 2020-03-04
if(isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
    isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '')
{
    $datetime_now = date("c");
    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';

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

    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $com['company_id'] = $company_found['company_ID']; //Put in company details array
        $response['data']['company_check']  = 'Valid Company ID';
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
            $users_in_company = json_decode($company_found['user_id']);
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

                            $allowed_days[] = $accessControl['allowed_days'];

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
            // Permit
            //----------------
            $response['data']['lock_group_id'] = $lock_group_id;
            $cursor_pte = $PermitController->actionGetByIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);

            if($cursor_pte->count() > 0) {
                //$response['status'] = 'true';
                $c = 0;
                foreach($cursor_pte as $permit_to_enter)
                {
                    unset($permit_to_enter['_id']);
                    $permit_id = $permit_to_enter['permit_id'];

                    //calculation of Duration
                    $start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
                    $since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));

                    //echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
                    //echo $since_start->days.' days total -- ';
                    //echo $since_start->y.' years -- ';
                    //echo $since_start->m.' months -- ';
                    //echo $since_start->d.' days -- ';
                    //echo $since_start->h.' hours -- ';
                    //echo $since_start->i.' minutes -- ';
                    //echo $since_start->s.' seconds---';
                    //echo '<br>';

                    if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
                    {
                        $permit_to_enter['duration'] = 'NOW';
                        //echo 'Now<br><br>';
                    }
                    else if($since_start->d == 0 && $since_start->h >= 0)
                    {
                        $permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
                        //echo 'Before 1 day<br><br>';
                    }
                    else if($since_start->days == 1)
                    {
                        $permit_to_enter['duration'] = 'Yesterday';
                        //echo 'Yesterday<br><br>';
                    }
                    else if($since_start->days >= 2)
                    {
                        $permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
                        //echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
                    }
                    else {
                        $permit_to_enter['duration'] = '--/--';
                    }
                    //End Duration Calculation

                    $permit_date_from = $permit_to_enter['date_from'];
                    $permit_date_to = $permit_to_enter['date_to'];
                    $permit_time_from = $permit_to_enter['time_from'];
                    $permit_time_to = $permit_to_enter['time_to'];
                    //Show Data
                    //$response['data'][] = $permit_to_enter;
                    $response['data']['permit'][$c]['permit_id'] = $permit_to_enter['permit_id'];
                    $response['data']['permit'][$c]['user_id'] = $permit_to_enter['user_id'];
                    $response['data']['permit'][$c]['date_from'] = $permit_to_enter['date_from'];
                    $response['data']['permit'][$c]['date_to'] = $permit_to_enter['date_to'];
                    $response['data']['permit'][$c]['time_from'] = $permit_to_enter['time_from'];
                    $response['data']['permit'][$c]['time_to'] = $permit_to_enter['time_to'];
                    $response['data']['permit'][$c]['registered_time'] = $permit_to_enter['registered_time'];
                    $response['data']['permit'][$c]['approved'] = $permit_to_enter['approved'];
                    $response['data']['permit'][$c]['subadmin_approved'] = $permit_to_enter['subadmin_approved'];
                    $response['data']['permit'][$c]['admin_approved'] = $permit_to_enter['admin_approved'];
                    $response['data']['permit'][$c]['token'] = $permit_to_enter['token'];
                    $response['data']['permit'][$c]['duration'] = $permit_to_enter['duration'];

                    $c++;
                }
            }
            else
            {
                $response['status'] = 'false';
                $response['error'] = 'Invalid Permit';
                exit(json_encode($response, JSON_PRETTY_PRINT));
            }

            // Use Last Permit ID
            //Show permit id that is used for opening bluetooth lock
            $response['data']['permit_id'] = $permit_id;

            //Show Time Now
            $date_time_now = date("Y-m-d h:i:sa");
            //$date_now = date("d-m-Y");
            $date_now = date("d-m-Y");
            //$time_now = date("H:i:s");
            $time_now = date("H:i:s");
            $response['data']['date_now'] = $date_now;
            $response['data']['time_now'] = $time_now;

            //Process Allowed Lock Access

            //Convert Time to m/d/Y H:i:s due to php reading d/m/Y as American Time
            $year_format = 'Y';
            $date_format = 'd/m/Y';
            $time_format = 'd/m/Y H:i:s';
            $american_date_format = 'm/d/Y';
            $american_time_format = 'm/d/Y H:i:s';
            //$time_before_format = DateTime::createFromFormat($time_format, $date_from);
            //$time_after_format =  $time_before_format->format('m/d/Y H:i:s');

            //Permit Date Conversion
            //Replace - with / if old date format is used
            $permit_date_from = str_replace("-","/",$permit_date_from);
            $permit_date_to = str_replace("-","/",$permit_date_to);

            $permit_date_from_before_format = DateTime::createFromFormat($date_format, $permit_date_from);
            $permit_date_from_after_format =  $permit_date_from_before_format->format('d-m-Y');
            $permit_date_from_compare =  strtotime($permit_date_from_after_format);
            $permit_date_to_before_format = DateTime::createFromFormat($date_format, $permit_date_to);
            $permit_date_to_after_format =  $permit_date_to_before_format->format('d-m-Y');
            $permit_date_to_compare =  strtotime($permit_date_to_after_format);

            $response['data']['permit_date_from'] = $permit_date_from_after_format;
            $response['data']['permit_date_to'] = $permit_date_to_after_format;
            $response['data']['permit_time_from'] = $permit_time_from;
            $response['data']['permit_time_to'] = $permit_time_to;

            //Access Time Conversion
            $access_time_from = $access_time_from_hh . ":" . $access_time_from_mm . ":00";
            $access_time_to = $access_time_to_hh . ":" . $access_time_to_mm . ":00";
            //$response['access_time_from'] = $access_time_from;
            //$response['access_time_to'] = $access_time_to;

            //Test Time Allowed
            //
//			if ( time() >= strtotime($access_time_from) && time() <= strtotime($access_time_to) ){
//				$response['permit_time_allowed'] = 'yes';
//			}
//			else{
//				$response['permit_time_allowed'] = 'no';
//			}
//

            if ( strtotime($date_now) >= strtotime($permit_date_from_after_format) ){
                $response['data']['permit_date_after_start_date'] = 'yes';
            } else{
                $response['data']['permit_date_after_start_date'] = 'no';
            }
            if ( strtotime($date_now) <= strtotime($permit_date_to_after_format) ){
                $response['data']['permit_date_before_end_date'] = 'yes';
            } else{
                $response['data']['permit_date_before_end_date'] = 'no';
            }

            //---------------
            // Validity Check
            //---------------
            //if ( $date_now >= $permit_date_from_before_format && $date_now <= $permit_date_to_before_format){
            if ( strtotime($date_now) >= strtotime($permit_date_from_after_format) && strtotime($date_now) <= strtotime($permit_date_to_after_format) ){
                unset($response['error']);
                $response['data']['permit_date_allowed'] = 'yes';
                $response['data']['lock_listing'] = 'allowed';

                if ( time() >= strtotime($permit_time_from) && time() <= strtotime($permit_time_to) ){
                    $response['data']['permit_time_allowed'] = 'yes';

                    $cursor_locks = $LockBluetoothController->actionGetByLockGroupId($lock_group_id);

                    if($cursor_locks->count() > 0) {
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
                            $response['data']['locks'][$x]['allowed_days'] = $allowed_days[0];

                            //------------
                            // Approval For Lock - Added to check if lock needs approval
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


                            // Get Lock Approval Status, if require 2nd approval
                            if ($approval_for_lock['require_admin_approval'] == true){
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
                                        if ($approval_request['user_id'] == $user_id) {
                                            if ($approval_request['admin_approved'] == true) {
                                                $approval_status = "approved";
                                            } else if ($approval_request['admin_rejected'] == false && $approval_request['admin_approved_by'] == 0) {
                                                $approval_status = "pending";
                                            }

                                            if ($approval_request['admin_rejected'] == true) {
                                                $approval_status = "rejected";
                                            }

                                            $current_user_approval_request = array(
                                                'approval_request_for_lock_id' => $approval_request['approval_request_for_lock_id'],
                                                'company_id' => $approval_request['company_id'],
                                                'user_id' => $approval_request['user_id'],
                                                'permit_id' => $approval_request['permit_id'],
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
                                            $response['data']['locks'][$x]['all_previous_approval_request'][$i]['permit_id'] = $approval_request['permit_id'];
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

                                //-------------
                                // Final Check for Opening Lock
                                //-------------
                                $response['data']['locks'][$x]['last_approval_request'] = $current_user_approval_request;
                                if ( strtotime($datetime_now) >= strtotime($current_user_approval_request['valid_from']) && strtotime($datetime_now) <= strtotime($current_user_approval_request['valid_to']) ) {
                                    //$response['locks'][$x]['last_approval_request']['valid'] = true;
                                    $response['data']['locks'][$x]['last_approval_request']['open_lock'] = true;
                                }
                                else{
                                    //$response['locks'][$x]['last_approval_request']['valid'] = false;
                                    $response['data']['locks'][$x]['last_approval_request']['open_lock'] = false;
                                }
                            }

                            //}
                            $x++;
                        }
                    }

                }
                else{
                    $response['data']['permit_time_allowed'] = 'no';
                }
            }
            else{
                $response['data']['permit_date_allowed'] = 'no';
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
