<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/uploads/');

if($_REQUEST['action'] == 'profile_upd' && isset($_REQUEST['user']) && isset($_REQUEST['phone_number']))
{
    $response['status'] = 'false';
    $response['error'] = 'No Data Found';
    $collection = new MongoCollection($app_data, 'users');
    $criteria = array('user_id'=>(int) $_REQUEST['user']);
    $collection->update( $criteria ,array('$set' => array('phone_number' => $_REQUEST['phone_number'])));
    if(isset($_REQUEST['password']) && $_REQUEST['password'] != '')
    {
        $collection->update( $criteria ,array('$set' => array('password'=> md5($_REQUEST['password']))));
    }

    $Profile_Query = array('user_id' =>(int) $_REQUEST['user']);
    $cursor = $collection->find( $Profile_Query );
    if($cursor->count() == 1)
    {
        $response['status'] = 'true';
        unset($response['error']);
        foreach ( $cursor as $pf_details)
        {
            unset($pf_details['_id']);
            $response['data']['user_id'] = $pf_details['user_id'];
            $response['data']['phone_number'] = $pf_details['phone_number'];
            $response['data']['password'] = $pf_details['password'];
            //$response['data'] = $pf_details; //Not to show full user acount data
        }
    }
}