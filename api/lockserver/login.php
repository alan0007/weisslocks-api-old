<?php
include(dirname(dirname(dirname(__FILE__))) .'/configurations/config.php');
include(dirname(dirname(__FILE__)).'/modules/v1/lockserver/controllers/LockServerController.php');
include(dirname(dirname(__FILE__)) . '/modules/v1/lock/controllers/LockBluetoothController.php');

$LockServerController = new LockServerController();

if(isset($_REQUEST['lock_server_username'])
    && isset($_REQUEST['lock_server_password']))
{
    $response['status'] = "false";

    $LockServerController->lock_server_username = urldecode($_REQUEST['lock_server_username']);
    $LockServerController->lock_server_password = urldecode($_REQUEST['lock_server_password']);
    $LockServerController->lock_server_device_id = '';

    $LockServerController->login();
    $result = json_decode($LockServerController->result, true);

    if ( $response['data'] != null ){
        $response['status'] = "true";
    }

    $response['data']['username'] = $result['user']['userName'];
    $response['data']['phone'] = $result['user']['phone'];
    $response['data']['token_temporary'] = $result['token']['id'];
    $response['data']['expires_at'] = $result['token']['expiresAt'];
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);