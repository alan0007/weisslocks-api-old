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

// Check All Fire Alarm
if($_REQUEST['action'] == 'view_fire_alarm_by_superadmin_and_no_one_else')
{
	$collection = new MongoCollection($app_data, 'fire_alarm');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $user_location)
			{
				unset($user_location['_id']);
				$response['data'] = $user_location;
				echo json_encode($response);
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'User Location Not Found';
		echo json_encode($response);
	}

}

// List Fire Alarm Triggered in company before
else if($_REQUEST['action'] == 'fire_alarm' && $_REQUEST['method'] == 'view' && isset($_REQUEST['company_id']) )
{
	$collection = new MongoCollection($app_data, 'fire_alarm');
	$alarm_query = array( 'company_id' => $_REQUEST['company_id'] );
	$cursor_alarm = $collection->find( $alarm_query );
	if($cursor_alarm->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor_alarm as $fire_alarm)
			{
				unset($fire_alarm['_id']);
				$response['data'] = $fire_alarm;
				echo json_encode($response);
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Fire Alarm Not Found';
		echo json_encode($response);
	}

}

// Search for Building ID and Name
else if( $_REQUEST['action'] == 'company_building' && $_REQUEST['method'] == 'view' &&
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '')
{
	$response['status'] = 'false';
	//List
	$collection_location = new MongoCollection($app_data, 'company_building');
	$query = array( 'company_id' => $_REQUEST['company_id'] );
	$cursor = $collection->find( $query );
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $company_building)
			{
				unset($company_building['_id']);
				$response['data'] = $company_building;
				echo json_encode($response);
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Building Not Found';
		echo json_encode($response);
	}
	
}

// Search for Location ID and Name
else if( $_REQUEST['action'] == 'company_location' && $_REQUEST['method'] == 'view' &&
	isset($_REQUEST['building_id']) && $_REQUEST['building_id'] != '')
{
	$response['status'] = 'false';
	//List
	$collection_location = new MongoCollection($app_data, 'company_location');
	$query = array( 'company_id' => $_REQUEST['company_id'] );
	$cursor = $collection->find( $query );
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $company_location)
			{
				unset($company_location['_id']);
				$response['data'] = $company_location;
				echo json_encode($response);
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Location Not Found';
		echo json_encode($response);
	}
	
}


//Create New Fire Alarm
else if( $_REQUEST['action'] == 'fire_alarm' && $_REQUEST['method'] == 'add' && 
	isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
	) 
{
	$response['status'] = 'false';
	// Only User Location Tracking & Fire Alarm System time format has second
	$time_now = date('d/m/Y H:m:s');
	$company_id = (int) $_REQUEST['company_id'];
	
	// Create Fire Alarm
	$collection_fire_alarm = new MongoCollection($app_data, 'fire_alarm');

	$post = array(
		'fire_alarm_id' 	=> getNext_users_Sequence('fire_alarm_id'),
		'company_id'  		=> (int) $_REQUEST['company_id'],
		'trigger_user_id'  	=> (int) $_REQUEST['trigger_user_id'],
		'location_id'     	=> (int) $location_id,
		'location_name'     => $_REQUEST['location_name'],
		'time'  			=> $time_now,
		'purpose'   		=> $_REQUEST['purpose'],
		'message'   		=> $_REQUEST['message']
	);
	if($collection_fire_alarm->insert($post))
	{
		$response['status'] = 'true';
		unset ($post['_id']);
		$response['data'] = $post;
		echo json_encode($response);
	}
	else{
		$response['error'] = 'Database Error - Cannot Insert Data';
		echo json_encode($response);
	}
	
	//Notification
	if($response['status'] == 'true')
	{
		$collection2 = new MongoCollection($app_data, 'users');
		$criteria2 = array('company_id'=>$company_id);
		$cursor_2 = $collection2->find( $criteria2 );
		
		if($cursor_2->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_2 as $user)
			{
				if($_REQUEST['status'] == 1)
				{
					$device_name = $user['device_name'];
					// Send Notifications Start
					if($device_name == 1)
					{
						$passphrase = 'IOSPUSH';
						$deviceToken = $device_id;
						$ctx = stream_context_create();
						stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem);
						stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
						$fp = stream_socket_client(
							'ssl://gateway.sandbox.push.apple.com:2195', $err,
							$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
						$body['aps'] = array(
							'alert' => array(
								'title' => 'Fire Alarm',
								'body' => 'Fire Alarm Triggered: ' . $_REQUEST['purpose'] . '',
							 ),
							'sound' => 'default'
						);
						$payload = json_encode($body);
						$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
						$result = fwrite($fp, $msg, strlen($msg));
						fclose($fp);
					}
					if($device_name == 2)
					{
						$registrationIds = array( $device_id );
						$msg = array
						(
							'title'		=> 'Fire Alarm', 
							'message' 	=> 'Fire Alarm Triggered: ' . $_REQUEST['purpose'] . '',
						);
						$fields = array
						(
							'registration_ids' 	=> $registrationIds,
							'data'			=> $msg
						);
						$headers = array
						(
							'Authorization: key=' . API_ACCESS_KEY,
							'Content-Type: application/json'
						);
						$ch = curl_init();
						// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
						curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
						curl_setopt( $ch,CURLOPT_POST, true );
						curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
						$result = curl_exec($ch );
						curl_close( $ch );
					}
				}
				$response['notification'] = $user;
				echo json_encode($response);
			}
		}
		else{
			$response['status'] = 'false';
			$response['notification_error'] = 'Notification Failed';
			echo json_encode($response);
		}

	}
	
}

//Create New Response for Fire Alarm
else if( $_REQUEST['action'] == 'response' && $_REQUEST['method'] == 'add' && 
	isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
	isset($_REQUEST['fire_alarm_id']) && $_REQUEST['fire_alarm_id'] != '' &&
	isset($_REQUEST['response']) && $_REQUEST['response'] != '' &&
	isset($_REQUEST['message']) ) 
{
	$response['status'] = 'false';
	// Only User Location Tracking & Fire Alarm System time format has second
	$time_now = date('d/m/Y H:m:s');
	$company_id = (int) $_REQUEST['company_id'];
	$response = (int) $_REQUEST['response'];

	//Response in (int) include:
	//1 - Acknowledge, 2 - Ignore, 3 - Help
	
	// Create Fire Alarm Response
	$collection = new MongoCollection($app_data, 'fire_alarm_response');

	$post = array(
		'fire_alarm_response_id' 	=> getNext_users_Sequence('fire_alarm_response_id'),
		'fire_alarm_id'  		=> (int) $_REQUEST['fire_alarm_id'],
		'user_id'  	=> (int) $_REQUEST['user_id'],
		'company_id'  	=> (int) $_REQUEST['company_id'],
		'response'     => (int) $_REQUEST['response'],
		'message'     => $_REQUEST['message'],
		'time'     => $time_now
	);
	if($collection->insert($post))
	{
		$response['status'] = 'true';
		unset ($post['_id']);
		$response['data'] = $post;
		echo json_encode($response);
	}
	else{
		$response['error'] = 'Database Error - Cannot Insert Data';
		echo json_encode($response);
	}
	
}

//Fire Alarm Response Update by User Response
else if( $_REQUEST['action'] == 'response' && $_REQUEST['method'] == 'update' && 
	isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '') 
{
	//TODO


}

//Update Sample Only
/*
else if( $_REQUEST['action'] == 'fire_alarm' && $_REQUEST['action'] == 'add' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' ) 
{
	$response['status'] = 'false';
	$response['msg'] = 'User Not Found';
	
	$collection = new MongoCollection($app_data, 'user_id');
    $users = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
    if($users->count() > 0) { 
		$response['status'] = 'true'; 
		unset($response['msg']);
			$user_emailTosend = $user['email'];
			$username = $user['username'];
			$device_id = $user['device_id'];
			$device_name = $user['device_name'];
			$company_ref_id = $user['company_ref_id'];
	}
	else{
		$response['status'] = 'false';
	}
	
	if($response['status'] == 'true')
	{
		$collection2 = new MongoCollection($app_data, 'users');
		$criteria2 = array('company_ref_id'=>$company_ref_id);
		$cursor_2 = $collection2->find( $criteria );
		
		if($cursor_2->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_2 as $user)
			{
				if($_REQUEST['status'] == 1)
				{
					$device_name = $user['device_name'];
					// Send Notifications Start
					if($device_name == 1)
					{
						$passphrase = 'IOSPUSH';
						$deviceToken = $device_id;
						$ctx = stream_context_create();
						stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem);
						stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
						$fp = stream_socket_client(
							'ssl://gateway.sandbox.push.apple.com:2195', $err,
							$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
						$body['aps'] = array(
							'alert' => array(
								'title' => 'Weiss Locks',
								'body' => 'You Are Successfully Approved',
							 ),
							'sound' => 'default'
						);
						$payload = json_encode($body);
						$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
						$result = fwrite($fp, $msg, strlen($msg));
						fclose($fp);
					}
					if($device_name == 2)
					{
						$registrationIds = array( $device_id );
						$msg = array
						(
							'title'		=> 'Weiss Locks', 
							'message' 	=> 'Fire Alarm is triggered',
						);
						$fields = array
						(
							'registration_ids' 	=> $registrationIds,
							'data'			=> $msg
						);
						$headers = array
						(
							'Authorization: key=' . API_ACCESS_KEY,
							'Content-Type: application/json'
						);
						$ch = curl_init();
						// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
						curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
						curl_setopt( $ch,CURLOPT_POST, true );
						curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
						$result = curl_exec($ch );
						curl_close( $ch );
					}
				}
				$response['data'] = $user;
				echo json_encode($response);
			}
		}
		else{
			$response['status'] = 'false';
		}

	}
				
}
*/

		
?>