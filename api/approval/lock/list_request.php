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
    $collection_approval_request = $app_data->approval_request_for_lock;
    $cursor_approval_request = $collection_approval_request->find( array('company_id'=>(int)$_REQUEST['company_id']) );

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
        }

        $response['data'][$i]['approval_request_for_lock_id'] = $approval_request['approval_request_for_lock_id'];
        $response['data'][$i]['company_id'] = $approval_request['company_id'];
        $response['data'][$i]['user_id'] = $approval_request['user_id'];
        $response['data'][$i]['username'] = $username;
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
        $response['data'][$i]['valid_until'] = $approval_request['valid_until'];
        $i++;
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);