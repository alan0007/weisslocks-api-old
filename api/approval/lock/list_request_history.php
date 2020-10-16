<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php');
include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

$response = array();

$NotificationController = new NotificationController;
//$SmsController = new SmsController;

//date_default_timezone_set('Asia/Singapore');
$datetime = date("c");

//----------------------------------
// Request Approval for Locks
//----------------------------------
if(isset($_REQUEST['company_id'])
    && isset($_REQUEST['user_id']))
{
    $response['status'] = 'false';
    $current_user_id = (int) $_REQUEST['user_id'];

    $collection_user = $app_data->users;
    $user_query = array('user_id' => $current_user_id);
    $cursor_user = $collection_user->findOne( $user_query );

    if(isset($cursor_user)) {
        $role = $cursor_user['role'];

//        $response['role'] = $role;

        if (in_array($role, array(2, 3))) {
            $response['is_admin'] = true;
            $is_admin = true;
        } else {
            $response['is_admin'] = false;
            $is_admin = false;
        }
    }

    $collection_approval_request = $app_data->approval_request_for_lock;
//    $criteria_admin = array(
//        '$and' => array(
//        array( 'company_id'=> $_REQUEST['company_id'] ),
//        array( 'user_id'=> $_REQUEST['user_id'] )
//        )
//    );
    $cursor_approval_request = $collection_approval_request->find( array('company_id'=>(int)$_REQUEST['company_id']) );
//    $cursor_approval_request = $collection_approval_request->find( $criteria_admin );

//    $response['data'] = array();
    $i=0; // count
    foreach($cursor_approval_request as $approval_request){
        $response['status'] = 'true';
        unset($approval_request['_id']);
        $user_id = $approval_request['user_id'];
        //Get Username
        $collection = $app_data->users;
        $Profile_Query = array('user_id' =>(int) $user_id);
        $cursor = $collection->findOne( $Profile_Query );

        if(isset($cursor)){
            $response['status'] = 'true';
            unset($cursor['_id']);
            $username = $cursor['username'];
            $full_name = $cursor['full_name'];
        }
        else{
            $full_name = "";
        }


        // Full details
        if ($current_user_id == $user_id){
            $response['data'][$i]['approval_request_for_lock_id'] = $approval_request['approval_request_for_lock_id'];
            $response['data'][$i]['company_id'] = $approval_request['company_id'];
            $response['data'][$i]['user_id'] = $approval_request['user_id'];
            $response['data'][$i]['username'] = $username;
            $response['data'][$i]['full_name'] = $full_name; // User full name
            $response['data'][$i]['permit_id'] = $approval_request['permit_id'];
            $response['data'][$i]['lock_id'] = $approval_request['lock_id'];
            $response['data'][$i]['created_timestamp'] = $approval_request['created_timestamp'];
            $response['data'][$i]['created_by_user_id'] = $approval_request['created_by_user_id'];
            $response['data'][$i]['notified_admin_user_id'] = $approval_request['notified_admin_user_id'];
            $response['data'][$i]['admin_approved'] = $approval_request['admin_approved'];
            $response['data'][$i]['admin_approved_by'] = $approval_request['admin_approved_by'];
            $response['data'][$i]['admin_approved_on'] = $approval_request['admin_approved_on'];
            $response['data'][$i]['admin_rejected'] = $approval_request['admin_rejected'];
            $response['data'][$i]['admin_rejected_by'] = $approval_request['admin_rejected_by'];
            $response['data'][$i]['admin_rejected_on'] = $approval_request['admin_rejected_on'];
            $response['data'][$i]['subadmin_approved'] = $approval_request['subadmin_approved'];
            $response['data'][$i]['subadmin_approved_by'] = $approval_request['subadmin_approved_by'];
            $response['data'][$i]['subadmin_approved_on'] = $approval_request['subadmin_approved_on'];
//        $response['data'][$i]['valid_from'] = $approval_request['valid_from'];
//            $response['data'][$i]['valid_until'] = $approval_request['valid_until'];

            // Added 2020-10-14: timestamps
            if (isset($approval_request['from_date'])){
                $response['data'][$i]['from_date'] = $approval_request['from_date'];
            }else{
                $response['data'][$i]['from_date'] = '';
            }

            if (isset($approval_request['to_date'])){
                $response['data'][$i]['to_date'] = $approval_request['to_date'];
            }else{
                $response['data'][$i]['to_date'] = '';
            }

            if (isset($approval_request['from_time'])){
                $response['data'][$i]['from_time'] = $approval_request['from_time'];
            }else{
                $response['data'][$i]['from_time'] = '';
            }

            if (isset($approval_request['to_time'])){
                $response['data'][$i]['to_time'] = $approval_request['to_time'];
            }else{
                $response['data'][$i]['to_time'] = '';
            }
            // End Timestamps

            if ($approval_request['admin_approved'] == true){
                $response['data'][$i]['approval_status'] = "approved";
            }
            else if ($approval_request['admin_rejected'] == false && $approval_request['admin_approved_by'] == 0){
                $response['data'][$i]['approval_status'] = "pending";
            }

            if ($approval_request['admin_rejected'] == true){
                $response['data'][$i]['approval_status'] = "rejected";
            }

            // Lock Name
            $collection_lock = $app_data->locks;
            $lock_query = array('lock_ID' =>(int) $approval_request['lock_id']);
            $cursor_lock = $collection_lock->findOne( $lock_query );
            if(isset($cursor_lock)){
                $response['data'][$i]['lock_name'] = $cursor_lock['lock_name'];
                $response['data'][$i]['serial_number'] = $cursor_lock['serial_number'];
            }

            // Datetime from and To
            $datetime = strtotime ( $approval_request['created_timestamp'] );
            $request_date = date ( 'Y-M-d' , $datetime );

            $response['data'][$i]['request_datetime'] = $approval_request['created_timestamp'];

            $response['data'][$i]['valid_from'] = $approval_request['admin_approved_on'];
//            $response['data'][$i]['valid_to'] = $approval_request['valid_until'];

            // Admin full name
            $approval_admin_id = (int) $approval_request['admin_approved_by'];
            $collection_approval_admin = $app_data->users;
            $approval_admin_query = array('user_id' =>(int) $approval_admin_id);
            $cursor_approval_admin = $collection_approval_admin->findOne( $approval_admin_query );

            if(isset($cursor_approval_admin)){
                $response['data'][$i]['admin_full_name'] = $cursor_approval_admin['full_name'];
            }

            $i++;
        }


    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);