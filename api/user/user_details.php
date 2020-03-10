<?php
include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
$response = array();

if(isset($_REQUEST['user_id']))
{
    $response['status'] = 'false';
    $collection = $app_data->users;
    $Profile_Query = array('user_id' =>(int) $_REQUEST['user_id']);
    $cursor = $collection->findOne( $Profile_Query );

    if(isset($cursor)){
        $response['status'] = 'true';
        unset($cursor['_id']);
        unset($cursor['password']);
        unset($cursor['device_id']);
        unset($cursor['UDID_IOS']);
        unset($cursor['token']);
        unset($cursor['_id']);
        $response['data'] = $cursor;
    }

//    if($cursor->count() == 1)
//    {
//        $response['status'] = 'true';
//        foreach ( $cursor as $pf_details)
//        {
//            unset($pf_details['_id']);
//            $response['username'] = $pf_details['username'];
//        }
//    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>