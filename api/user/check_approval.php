<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/uploads/');

if(isset($_REQUEST['user']))
{
    $collection = new MongoCollection($app_data, 'users');
    $check = $collection->find(array('user_id'=>(int) $_REQUEST['user']));
    if($check->count() > 0)
    {
        $response['status'] = 'true';
        foreach($check as $checker)
        {
            unset($checker['_id']);
            $response['data'] = $checker;
        }
    } else { $response['status'] = 'false'; }
}