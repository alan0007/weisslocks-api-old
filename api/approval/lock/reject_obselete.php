<?php
include(dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php');
include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

$response = array();

$NotificationController = new NotificationController;
//$SmsController = new SmsController;

//date_default_timezone_set('Asia/Singapore');
$datetime = date("c");

// Admin Approval - Approve without checking credentials (Demo Version Only)
if(isset($_REQUEST['company_id'])
    && isset($_REQUEST['user_id'])
    && isset($_REQUEST['approval_request_for_lock_id']))
{
    $response['status'] = 'false';
//    $collection_admin = $app_data->users;
//    $criteria_admin = array(
//    '$and' => array(
//    array( 'company_id'=> $_REQUEST['company_id'] ),
//    array( 'user_id'=> $_REQUEST['admin_user_id'] )
//    )
//    );

    $user_id = (int) $_REQUEST['user_id'];
    $company_id = (int) $_REQUEST['company_id'];

    $collection_admin = $app_data->users;
    $Profile_Query = array('user_id' =>(int) $user_id);
    $cursor_admin = $collection_admin->findOne( $Profile_Query );

    if(isset($cursor_admin)){
        $role = $cursor_admin['role'];
        if (in_array($role, array(2, 3))){
            $response['is_admin'] = true;
        }
        else{
            $response['is_admin'] = false;
        }
    }
    else{
        $response['error'] = 'Error: Not allowed';
    }

//    $response['is_admin'] = true;

    if ($response['is_admin'] == true) {
        $collection = $app_data->approval_request_for_lock;
        $criteria = array('approval_request_for_lock_id'=>(int)$_REQUEST['approval_request_for_lock_id']);

        $valid_time = strtotime("+60 minutes", strtotime($datetime));
        $new_data = array(
            '$set' => array(
                'admin_approved' => false,
                'admin_approved_by' => (int)0,
                'admin_approved_on' => "",
                'admin_rejected' => true,
                'admin_rejected_by' => (int) $_REQUEST['user_id'],
                'admin_rejected_on' => $datetime,
                'valid_until' => ""
        ));

        try {
            $collection->update( $criteria ,$new_data);

            //$collection->update($criteria, $new_data);
            $response['status'] = 'true';

            $collection2 = $app_data->approval_request_for_lock;
            $cursor = $collection2->find( array('approval_request_for_lock_id'=>(int)$_REQUEST['approval_request_for_lock_id']) );
            foreach($cursor as $approval_request){
                unset($approval_request['_id']);
                $response['data'][] = $approval_request;
            }

        } catch (MongoCursorException $e) {
            $response['error']['message'] = $e->getMessage();
            $response['error']['code'] = $e->getCode();
        }

    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);