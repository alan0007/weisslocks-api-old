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
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalRequestForLockController.php';
//require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalForLockController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use api\modules\v1\lock\controllers\ApprovalRequestForLockController;
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
    $ApprovalRequestForLockController = new ApprovalRequestForLockController($Database);
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

    $cursor_approval_request = $ApprovalRequestForLockController->actionGetByUserIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);
    if ($cursor_approval_request->count() > 0){
        unset($response['error']);
        $response['status'] = 'true';
        foreach($cursor_approval_request as $approval_request) {
            $response['data']['locks'][$i]['approval_request_for_lock_id'] = $approval_request['approval_request_for_lock_id'];
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
            $response['data']['locks'][$i]['subadmin_approved'] = $approval_request['subadmin_approved'];
            $response['data']['locks'][$i]['subadmin_approved_by'] = $approval_request['subadmin_approved_by'];
            $response['data']['locks'][$i]['subadmin_approved_on'] = $approval_request['subadmin_approved_on'];
//        $response['data']['locks'][$i]['valid_from'] = $approval_request['valid_from'];
//            $response['data']['locks'][$i]['valid_until'] = $approval_request['valid_until'];

            // Added 2020-10-14: timestamps
            if (isset($approval_request['from_date'])){
                $response['data']['locks'][$i]['from_date'] = $approval_request['from_date'];
            }else{
                $response['data']['locks'][$i]['from_date'] = '';
            }

            if (isset($approval_request['to_date'])){
                $response['data']['locks'][$i]['to_date'] = $approval_request['to_date'];
            }else{
                $response['data']['locks'][$i]['to_date'] = '';
            }

            if (isset($approval_request['from_time'])){
                $response['data']['locks'][$i]['from_time'] = $approval_request['from_time'];
            }else{
                $response['data']['locks'][$i]['from_time'] = '';
            }

            if (isset($approval_request['to_time'])){
                $response['data']['locks'][$i]['to_time'] = $approval_request['to_time'];
            }else{
                $response['data']['locks'][$i]['to_time'] = '';
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

//    $collection_approval_request = $app_data->approval_request_for_lock;
//    //    $criteria_admin = array(
//    //        '$and' => array(
//    //        array( 'company_id'=> $_REQUEST['company_id'] ),
//    //        array( 'user_id'=> $_REQUEST['user_id'] )
//    //        )
//    //    );
//    $cursor_approval_request = $collection_approval_request->find( array('company_id'=>(int)$_REQUEST['company_id']) );
//    //    $cursor_approval_request = $collection_approval_request->find( $criteria_admin );
//
//    //    $response['data'] = array();
//    $i=0; // count
//    if ($cursor_approval_request){
//    foreach($cursor_approval_request as $approval_request){
//        $response['status'] = 'true';
//        unset($approval_request['_id']);
//        $user_id = $approval_request['user_id'];
//        //Get Username
//        $collection = $app_data->users;
//        $Profile_Query = array('user_id' =>(int) $user_id);
//        $cursor = $collection->findOne( $Profile_Query );
//
//        if(isset($cursor)){
//            $response['status'] = 'true';
//            unset($cursor['_id']);
//            $username = $cursor['username'];
//            $full_name = $cursor['full_name'];
//        }
//        else{
//            $full_name = "";
//        }
//
//
//        // Full details
//        if ($current_user_id == $user_id){
//            $response['data']['locks'][$i]['approval_request_for_lock_id'] = $approval_request['approval_request_for_lock_id'];
//            $response['data']['locks'][$i]['company_id'] = $approval_request['company_id'];
//            $response['data']['locks'][$i]['user_id'] = $approval_request['user_id'];
//            $response['data']['locks'][$i]['username'] = $username;
//            $response['data']['locks'][$i]['full_name'] = $full_name; // User full name
//            $response['data']['locks'][$i]['permit_id'] = $approval_request['permit_id'];
//            $response['data']['locks'][$i]['lock_id'] = $approval_request['lock_id'];
//            $response['data']['locks'][$i]['created_timestamp'] = $approval_request['created_timestamp'];
//            $response['data']['locks'][$i]['created_by_user_id'] = $approval_request['created_by_user_id'];
//            $response['data']['locks'][$i]['notified_admin_user_id'] = $approval_request['notified_admin_user_id'];
//            $response['data']['locks'][$i]['admin_approved'] = $approval_request['admin_approved'];
//            $response['data']['locks'][$i]['admin_approved_by'] = $approval_request['admin_approved_by'];
//            $response['data']['locks'][$i]['admin_approved_on'] = $approval_request['admin_approved_on'];
//            $response['data']['locks'][$i]['admin_rejected'] = $approval_request['admin_rejected'];
//            $response['data']['locks'][$i]['admin_rejected_by'] = $approval_request['admin_rejected_by'];
//            $response['data']['locks'][$i]['admin_rejected_on'] = $approval_request['admin_rejected_on'];
//            $response['data']['locks'][$i]['subadmin_approved'] = $approval_request['subadmin_approved'];
//            $response['data']['locks'][$i]['subadmin_approved_by'] = $approval_request['subadmin_approved_by'];
//            $response['data']['locks'][$i]['subadmin_approved_on'] = $approval_request['subadmin_approved_on'];
////        $response['data']['locks'][$i]['valid_from'] = $approval_request['valid_from'];
////            $response['data']['locks'][$i]['valid_until'] = $approval_request['valid_until'];
//
//            // Added 2020-10-14: timestamps
//            if (isset($approval_request['from_date'])){
//                $response['data']['locks'][$i]['from_date'] = $approval_request['from_date'];
//            }else{
//                $response['data']['locks'][$i]['from_date'] = '';
//            }
//
//            if (isset($approval_request['to_date'])){
//                $response['data']['locks'][$i]['to_date'] = $approval_request['to_date'];
//            }else{
//                $response['data']['locks'][$i]['to_date'] = '';
//            }
//
//            if (isset($approval_request['from_time'])){
//                $response['data']['locks'][$i]['from_time'] = $approval_request['from_time'];
//            }else{
//                $response['data']['locks'][$i]['from_time'] = '';
//            }
//
//            if (isset($approval_request['to_time'])){
//                $response['data']['locks'][$i]['to_time'] = $approval_request['to_time'];
//            }else{
//                $response['data']['locks'][$i]['to_time'] = '';
//            }
//            // End Timestamps
//
//            if ($approval_request['admin_approved'] == true){
//                $response['data']['locks'][$i]['approval_status'] = "approved";
//            }
//            else if ($approval_request['admin_rejected'] == false && $approval_request['admin_approved_by'] == 0){
//                $response['data']['locks'][$i]['approval_status'] = "pending";
//            }
//
//            if ($approval_request['admin_rejected'] == true){
//                $response['data']['locks'][$i]['approval_status'] = "rejected";
//            }
//
//            // Lock Name
//            $collection_lock = $app_data->locks;
//            $lock_query = array('lock_ID' =>(int) $approval_request['lock_id']);
//            $cursor_lock = $collection_lock->findOne( $lock_query );
//            if(isset($cursor_lock)){
//                $response['data']['locks'][$i]['lock_name'] = $cursor_lock['lock_name'];
//                $response['data']['locks'][$i]['serial_number'] = $cursor_lock['serial_number'];
//            }
//
//            // Datetime from and To
//            $datetime = strtotime ( $approval_request['created_timestamp'] );
//            $request_date = date ( 'Y-M-d' , $datetime );
//
//            $response['data']['locks'][$i]['request_datetime'] = $approval_request['created_timestamp'];
//
//            $response['data']['locks'][$i]['valid_from'] = $approval_request['admin_approved_on'];
////            $response['data']['locks'][$i]['valid_to'] = $approval_request['valid_until'];
//
//            // Admin full name
//            $approval_admin_id = (int) $approval_request['admin_approved_by'];
//            $collection_approval_admin = $app_data->users;
//            $approval_admin_query = array('user_id' =>(int) $approval_admin_id);
//            $cursor_approval_admin = $collection_approval_admin->findOne( $approval_admin_query );
//
//            if(isset($cursor_approval_admin)){
//                $response['data']['locks'][$i]['admin_full_name'] = $cursor_approval_admin['full_name'];
//            }
//
//            $i++;
//        }
//
//
//    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);