<?php
include(dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php');
include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

$response = array();

$NotificationController = new NotificationController;
//$SmsController = new SmsController;

//date_default_timezone_set('Asia/Singapore');
$datetime = date("c");

if(isset($_REQUEST['company_id'])
    && isset($_REQUEST['user_id'])
    && isset($_REQUEST['permit_id'])
    && isset($_REQUEST['lock_id']))
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Lock';

    if ( $_REQUEST['require_admin_approval'] == 'true' ){
        $require_admin_approval = true;
    }else if ($_REQUEST['require_admin_approval'] == 'false'){
        $require_admin_approval = false;
    }
    if( $_REQUEST['require_subadmin_approval'] == 'true' ){
        $require_subadmin_approval = true;
    } else if ( $_REQUEST['require_subadmin_approval'] == 'false'){
        $require_subadmin_approval = false;
    }

    //Create collection
    $approval_request_for_lock = $app_data->approval_request_for_lock;
    $post = array(
        'approval_request_for_lock_id'  => getNext_users_Sequence('approval_request_for_lock'),
        'company_id'  => (int) $_REQUEST['company_id'],
        'user_id'  => (int) $_REQUEST['user_id'],
        'permit_id'  => (int) $_REQUEST['permit_id'],
        'lock_id'  => (int) $_REQUEST['lock_id'],
        'created_timestamp'  => $datetime,
        'created_by_user_id'  => (int) $_REQUEST['user_id'],
        'notified_admin_user_id'=> Array(),
        'admin_approved'  => false,
        'admin_approved_by'  => (int) 0,
        'admin_approved_on'  => (String) '',
        'subadmin_approved'  => false,
        'subadmin_approved_by'  => (int) 0,
        'subadmin_approved_on'  => (String)'',
        'valid_until' => (String)''
    );

    unset($response['error']);

    if($approval_request_for_lock->insert($post)){
        $Reg_Query = array('_id' => $post['_id'] );
        $locksData = $approval_request_for_lock->findOne( $Reg_Query );

        $response['status'] = 'true';

    }

    $collection_admin = $app_data->users;
    $criteria_admin = array(
        '$and' => array(
            array( 'company_id'=> (String) $_REQUEST['company_id'] ),
            //array( 'company_id'=> $demo_pa_company_id ),
            array( 'role' => 3 )
        )
    );
    $cursor_admin = $collection_admin->find($criteria_admin);
    $admin_device_id[0] = null; //default admin notification

    if($cursor_admin->count() > 0) {
        //$response['status'] = 'true';
        $response['admin_found'] = true;
        $c = 0;
        foreach ($cursor_admin as $admin) {
            if ($admin['token'] != null && $admin['token'] != '') {
                $admin_user_id[$c] = $admin['user_id'];
                $admin_token[$c] = $admin['token'];
            }
            $c++;
        }
    }

    $notified_admin_user_id = Array();
    $notified_admin_user_id = $admin['user_id'];
    $response['notified_admin_user_id'] = $admin['user_id'];

    // Send Notification
    $NotificationController->token = $admin_device_id[0];
    $NotificationController->notification = array (
        'title' => 'Request Approval for Lock',
        'body' 	=> 'Approve request for user ID = ' . $_REQUEST['user_id'],
        'activity' 	=> 'ApprovalForLock',
        'company_id' => $_REQUEST['company_id'],
        'user_id' => $_REQUEST['user_id'],
        'permit_id' => $_REQUEST['permit_id'],
        'lock_id' => $_REQUEST['lock_id'],
        'android_channel_id' => 'FirebaseApprovalForLock',
        'sound' => 'default',
        'tag' => 'FirebaseApprovalForLock',
        'click_action' => 'APPROVE_LOCK'
    );
    $NotificationController->data = array(
        'title' => 'Request Approval for Lock',
        'body' 	=> 'Approve request for user ID = ' . $_REQUEST['user_id'],
        'activity' 	=> 'ApprovalForLock',
        'company_id' => $_REQUEST['company_id'],
        'user_id' => $_REQUEST['user_id'],
        'permit_id' => $_REQUEST['permit_id'],
        'lock_id' => $_REQUEST['lock_id']
        //'android' => array('click_action'=>'RESPOND_ALARM')
    );
    $NotificationController->message_id = 1;

    $NotificationController->sendNotification();
    $response['notification']['fields'] = $NotificationController->fields;
    $response['notification']['result'] = $NotificationController->result;


}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);