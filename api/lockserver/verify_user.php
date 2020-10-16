<?php
include(dirname(dirname(dirname(__FILE__))) .'/configurations/config.php');
include(dirname(dirname(__FILE__)).'/modules/v1/lockserver/controllers/LockServerController.php');
include(dirname(dirname(__FILE__)) . '/modules/v1/lock/controllers/LockBluetoothController.php');

$LockServerController = new LockServerController();

if(isset($_REQUEST['token'])
    && isset($_REQUEST['lock_server_username'])
    && isset($_REQUEST['phone_number']))
{
    $response['status'] = "false";

    $LockServerController->token = urldecode($_REQUEST['token']);
    $LockServerController->lock_server_username = urldecode($_REQUEST['lock_server_username']);

    $LockServerController->getUser();

    $response['source']['url'] = $LockServerController->full_url;
    $response['source']['authorization']  = $LockServerController->authorization;

    $result = json_decode($LockServerController->result, true);

    if ( $response['data'] != null ){
        $response['status'] = "true";
    }

//    $response['result'] = $result;
    $response['data']['name'] = $result['name'];
    $response['data']['phone_number'] = $result['mobileNumber'];
    $response['data']['mobile_os'] = $result['mobileOS'];
    $response['data']['activated_on_lock_server'] = $result['activatedOn'];
    $response['data']['last_login_lock_server'] = $result['lastLogin'];

    if($_REQUEST['phone_number'] == $response['data']['phone_number']){
        $response['data']['phone_number_match'] = true;
    }
    else{
        $response['data']['phone_number_match'] = false;
    }

}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);