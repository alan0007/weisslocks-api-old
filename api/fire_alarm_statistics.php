<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

//-----------------------------------
//For Initial create collection only
/*
$firealarm = $app_data->createCollection("firealarm");
$msgs = $firealarm->find();

if($msgs->count() > 0) { 
	foreach ($msgs as $msg) {
		//echo $msg['msg']."\n";
		$response['data'] = $msg;
		echo json_encode($response);
	}
}
else{
	$response['status'] = 'false';
	echo json_encode($response);
}
*/

// Get Fire Alarm Statistics



		
?>