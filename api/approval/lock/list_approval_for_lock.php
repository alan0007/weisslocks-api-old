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
if(isset($_REQUEST['company_id']))
{
    $response['status'] = 'false';
    $collection_approval_request = $app_data->approval_for_lock;
    $cursor_approval_request = $collection_approval_request->find( array('company_id'=>(int)$_REQUEST['company_id']) );

    foreach($cursor_approval_request as $approval_request){
        $response['status'] = 'true';
        unset($approval_request['_id']);
        $response['data'][] = $approval_request;
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);