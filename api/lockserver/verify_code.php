<?php
include(dirname(dirname(dirname(__FILE__))) .'/configurations/config.php');
include(dirname(dirname(__FILE__)).'/modules/v1/lockserver/controllers/LockServerController.php');
include(dirname(dirname(__FILE__)) . '/modules/v1/lock/controllers/LockBluetoothController.php');

$LockServerController = new LockServerController();

if(isset($_REQUEST['token_temporary'])
    && isset($_REQUEST['sms_code'])
    && isset($_REQUEST['mobile_os']))
{
    $response['status'] = "false";

    $LockServerController->token_temporary = urldecode($_REQUEST['token_temporary']);
    $LockServerController->sms_code = urldecode($_REQUEST['sms_code']);
    $LockServerController->mobile_os = urldecode($_REQUEST['mobile_os']);

    $LockServerController->verifyCode();

    $response['source']['url'] = $LockServerController->full_url;
    $response['source']['authorization']  = $LockServerController->authorization;

    $result = json_decode($LockServerController->result, true);

    if ( $response['data'] != null ){
        $response['status'] = "true";
    }

//    $response['result'] = $result;
    $response['data']['token'] = $result['token']['id'];
    $response['data']['expires_at'] = $result['token']['expiresAt'];
    $response['data']['device_id'] = $result['deviceId'];
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);