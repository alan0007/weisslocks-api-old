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
				
				
				header('Content-Type: application/json');
				echo json_encode($response, JSON_PRETTY_PRINT);
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'User Location Not Found';
		
		header('Content-Type: application/json');
		echo json_encode($response, JSON_PRETTY_PRINT);
	}

}

// List Fire Alarm Triggered in company before
else if($_REQUEST['action'] == 'fire_alarm' && $_REQUEST['method'] == 'view' && isset($_REQUEST['company_id']) )
{
	$collection = new MongoCollection($app_data, 'fire_alarm');
	$alarm_query = array( 'company_id' => (int)$_REQUEST['company_id'] );
	$cursor_alarm = $collection->find( $alarm_query );
	$cursor_alarm = $cursor_alarm->sort(array('fire_alarm_id' => -1));
																					
	if($cursor_alarm->count() > 0) { 
		$response['status'] = 'true';
		$i=0;
		foreach($cursor_alarm as $fire_alarm)
			{
				unset($fire_alarm['_id']);
				//List all response
				$response['data'][] = $fire_alarm;
				
				$user_id = $fire_alarm['trigger_user_id'];
				$location_id = $fire_alarm['location_id'];
				
				//Find Username
				$collection_users = new MongoCollection($app_data, 'users');
				$cursor_user = $collection_users->find(array('user_id'=>$user_id));
				if($cursor_user->count() > 0)
				{
					foreach($cursor_user as $user)
					{
						$username = $user['username'];
						$role = $user['role'];														
					}
				}
				
				//Find Building
				$collection_location = new MongoCollection($app_data, 'company_location');
				$query_location = array( 'location_id' => (int) $location_id );
				$cursor_location = $collection_location->find( $query_location );
				if($cursor_location->count() > 0) { 
					$response['status'] = 'true';
					foreach($cursor_location as $company_location)
						{
							//unset($company_location['_id']);
							$building_id = $company_location['building_id'];							
						}
				}
				//Find Building Name
				$collection_building = new MongoCollection($app_data, 'company_building');
				$query_building  = array( 'building_id' => (int) $building_id );
				$cursor_building  = $collection_building ->find( $query_building );
				if($cursor_building ->count() > 0) { 
					$response['status'] = 'true';
					foreach($cursor_building as $company_building)
						{
							//unset($company_location['_id']);
							$building_name = $company_building['building_name'];							
						}
				}
				//SP HQ by Default
				else{
					$building_name = "SP Group HQ";
				}
				
				
				//$response['data'][$i]['fire_alarm_id'] = $fire_alarm['fire_alarm_id'];
				//$response['data'][$i]['company_id'] = $fire_alarm['company_id'];
				//$response['data'][$i]['trigger_user_id'] = $fire_alarm['trigger_user_id'];
				$response['data'][$i]['trigger_user_name'] = $username;
				$response['data'][$i]['building_name'] = $building_name;
				
				//$response['data'][$i]['location_id'] = $fire_alarm['location_id'];
				//$response['data'][$i]['location_name'] = $fire_alarm['location_name'];
				//$response['data'][$i]['time'] = $fire_alarm['time'];
				//$response['data'][$i]['purpose'] = $fire_alarm['purpose'];
				//$response['data'][$i]['message'] = $fire_alarm['message'];
				
				$i++;
				
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Fire Alarm Not Found';
	}
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);

}

// Search for Building ID and Name
else if( $_REQUEST['action'] == 'company_building' && $_REQUEST['method'] == 'view' &&
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '')
{
	$response['status'] = 'false';
	//List
	$collection_location = new MongoCollection($app_data, 'company_building');
	$query = array( 'company_ID' => (int) $_REQUEST['company_id'] );
	$cursor = $collection_location->find( $query );
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $company_building)
			{
				unset($company_building['_id']);
				$response['data'][] = $company_building;
				
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Building Not Found';

	}
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);

}

// Search for Location ID and Name
else if( $_REQUEST['action'] == 'company_location' && $_REQUEST['method'] == 'view' &&
	isset($_REQUEST['building_id']) && $_REQUEST['building_id'] != '')
{
	$response['status'] = 'false';
	//List
	$collection = new MongoCollection($app_data, 'company_location');
	$query = array( 'building_id' => (int) $_REQUEST['building_id'] );
	$cursor = $collection->find( $query );
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $company_location)
			{
				unset($company_location['_id']);
				$response['data'][] = $company_location;
				
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Location Not Found';

	}
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
}

// Search for Location ID and Name
else if( $_REQUEST['action'] == 'company_location_specific' && $_REQUEST['method'] == 'view' &&
	isset($_REQUEST['location_id']) && $_REQUEST['location_id'] != '')
{
	$response['status'] = 'false';
	//List
	$collection = new MongoCollection($app_data, 'company_location');
	$query = array( 'location_id' => (int) $_REQUEST['location_id'] );
	$cursor = $collection->find( $query );
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $company_location)
			{
				unset($company_location['_id']);
				$response['data'][] = $company_location;
				
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Location Not Found';

	}
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//Create New Fire Alarm
else if( $_REQUEST['action'] == 'fire_alarm' && $_REQUEST['method'] == 'add' && 
	isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
	isset($_REQUEST['location_name']) && $_REQUEST['location_name'] != '') 
{
	$response['status'] = 'false';
	//Temporary exclusion: 265,279,361
	//$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364,265,279,361); //Add back after testing
	$user_exclude_list = array(1,179);
	
	//All Count
	//For Fire Alarm Response Count
	$total_user_count=0;
	//$participant_count=0;
	//$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364);
	//User Detected = distinct user detected in building + user scanned qr code													
	$user_detected_list = array();
	$user_beacon_list = array();
	$user_qrcode_list = array();
	$user_full_list = array();
	
	$user_beacon_count=0;
	$user_qrcode_count=0;
	$user_detected_count=0;
	
	//Response List
	$user_safe_list = array();
	$user_not_safe_list = array();
	$user_help_list = array();
	
	//Location List
	$location_list = array();
	$location_user_list = array();
	
	$safe_count=0;
	$not_safe_count=0;
	$responded_count=0;
	$help_count=0;
	
	//For Fire Alarm Attendance Checking
	$full_count =0;
	$exited_building_count=0;
	$in_building_count=0;
	$present_count=0;
	$absent_count=0;
	//Attendance List
	$user_exited_building_list = array();
	$user_in_building_list = array();
	$user_present_list = array();
	$user_absent_list = array();
	
	//$response_user_id = Array();
	//$help_user_id = Array();
	
	$date='';
	$time='';
	
	
	
	
	
	// Only User Location Tracking & Fire Alarm System time format has second
	$time_now = date('d/m/Y H:i:s');
	$company_id = (int) $_REQUEST['company_id'];
	$location_id = (int) $_REQUEST['location_id'];
	
	if ($_REQUEST['message'] == null || $_REQUEST['message'] == ''){
		$_REQUEST['message'] = '';
	}
	if ($_REQUEST['location_id'] == null || $_REQUEST['location_id'] == ''){
		$_REQUEST['location_id'] = 0;
	}
	
	if ($_REQUEST['company_id'] == 12){
		$default_building_name = 'SP HQ';
	}
	else if ($_REQUEST['company_id'] == 25){
		$default_building_name = 'IOS HQ';
	}
	else if ($_REQUEST['company_id'] == 27){
		$default_building_name = 'PA HQ';
	}
	
	// Create Fire Alarm
	$collection_fire_alarm = new MongoCollection($app_data, 'fire_alarm');

	$post = array(
		'fire_alarm_id' 	=> getNext_users_Sequence('fire_alarm_id'),
		'company_id'  		=> (int) $_REQUEST['company_id'],
		'trigger_user_id'  	=> (int) $_REQUEST['user_id'],
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
		$fire_alarm_id = $post['fire_alarm_id'];
		$date_time = $post['time'];	
		$date = $post['time'];		
	}
	else{
		$response['error'] = 'Database Error - Cannot Insert Data';
	}
	
	$trigger_user_id = $_REQUEST['user_id'];
	$location_name = $_REQUEST['location_name'];
	$purpose = $_REQUEST['purpose'];
	
	$collection_triggerUsername = new MongoCollection($app_data, 'users');
	$criteria_triggerUsername = array('user_id'=>$trigger_user_id);
	$cursor_triggerUsername = $collection_triggerUsername->find( $criteria_triggerUsername );
	if($cursor_triggerUsername->count() > 0) {
		foreach($cursor_triggerUsername as $user){
			$triggerUserName = $user['username'];
		}
	}
	
	//-------------
	// Start Logic	
	//-------------
	$date = strstr($date, ' ', true);											
	
	//Set Time Addition 
	$alarm_time = $date_time;										
	//$detection_time = date('d/m/Y H:i:s',strtotime($alarm_time));
	$exit_building_start_time = date('m/d/Y H:i:s',strtotime($alarm_time));
	//$exit_building_start_time = date('m/d/Y H:i:s', $exit_building_start_time_raw); 
	$exit_building_end_time = date('m/d/Y H:i:s',strtotime('+8 minutes',strtotime($alarm_time)));
	$exit_building_extra_time = date('m/d/Y H:i:s',strtotime('+18 minutes',strtotime($alarm_time)));
	$attendance_start_time = date('m/d/Y H:i:s',strtotime('+8 minutes',strtotime($alarm_time)));
	$attendance_end_time = date('m/d/Y H:i:s',strtotime('+60 minutes',strtotime($alarm_time)));
	
	//-------------------------------
	// Start Detected User
	//-------------------------------
	//Find User detected Beacon & push user_id into array
	/*
	$collection_user_location = $app_data->user_location;
	//Exclude Users
	$criteria_exclude_user = array(	
		'$and' => array(
			array( 'user_id'=> array('$ne'=>185) ), 
			array( 'user_id'=> array('$ne'=>186) ),
			array( 'user_id'=> array('$ne'=>196) ),
			array( 'user_id'=> array('$ne'=>252) ),
			array( 'user_id'=> array('$ne'=>237) ),
			array( 'user_id'=> array('$ne'=>238) ),
			array( 'user_id'=> array('$ne'=>239) ),
			array( 'user_id'=> array('$ne'=>201) ),
			array( 'user_id'=> array('$ne'=>255) ),
			array( 'user_id'=> array('$ne'=>363) )
		)
	);
	
	$distinct_user_location = $collection_user_location->distinct("user_id",$criteria_exclude_user);
	//$user_beacon_count = $distinct_user_location->count();
	//print_r($distinct_user_location);
	*/
	
	//Find User detected Beacon & push user_id into array
	//Include Today's Date.
	//echo $date;
	//$safe_query = array( '$and' => array( array('fire_alarm_id' => $fire_alarm_id ), array('response'=>1) ) );	
			
	$collection_user_location_date = $app_data->user_location;													
	$criteria_location_date = array( 
		'$and' => array (															
			//'time'=> array('$in'=>array('27/11/2018')
			array( 'company_id' => $company_id ),
			array( 'location_time' => new MongoRegex('/' . $date. '/i'))
		)														
	);
	$distinct_user_location_date = $collection_user_location_date->find($criteria_location_date);
	//$distinct_user_location_date = $collection_user_location_date->distinct("location_time",$criteria_location_date);
	//echo $date .": " . $distinct_user_location_date->count() . " ";
	
	if($distinct_user_location_date->count() > 0)
	{
		foreach($distinct_user_location_date as $location)
		{
			//echo $location['user_id'] . " ";
			$user_id = $location['user_id'];
			$current_time = $location['location_time'];
			
			//Check for before alarm time
			if( $current_time <= $alarm_time ){
				if ( !in_array($user_id, $user_exclude_list) )
				{
					if (!in_array($user_id, $user_detected_list)){
						array_push($user_detected_list,$user_id);
					}		
				}
			}																
		}
		$user_beacon_count = sizeof($user_detected_list);
		$user_beacon_list = $user_detected_list;
		//echo $date .": " . $user_beacon_count . "<br>";
		/*
		for ($i=0;$i<=$user_beacon_count;$i++){
			echo $user_beacon_list[$i] . " ";
		}
		*/
		//echo "<br>";
		
	}
	
	//Find User scanned QR Code & push user_id into array
	//Include Today's Date.
	//echo $date;
	$collection_qrcode = $app_data->qrcode;													
	$criteria_qrcode = array( 
		'$and' => array (															
			//'time'=> array('$in'=>array('27/11/2018')
			array( 'company_id' => $company_id ),
			array( 'access_time' => new MongoRegex('/' . $date. '/i'))
		)														
	);													
	$distinct_qrcode = $collection_qrcode->find($criteria_qrcode);
	//$distinct_user_location_date = $collection_user_location_date->distinct("location_time",$criteria_location_date);
	//echo $date .": " . $distinct_user_location_date->count() . " ";
	
	if($distinct_qrcode->count() > 0)
	{
		foreach($distinct_qrcode as $qrcode)
		{
			//echo $location['user_id'] . " ";
			$user_id=$qrcode['user_id'];
			$current_time = $qrcode['time'];
			//Check for before alarm time
			if( $current_time <= $alarm_time ){
				if ( !in_array($user_id, $user_exclude_list) ){
					//Push into QR Code Array
					if (!in_array($user_id, $user_qrcode_list)){
						array_push($user_qrcode_list,$user_id);
					}
					//Push into Merged Array
					if (!in_array($user_id, $user_detected_list)){
						array_push($user_detected_list,$user_id);
					}		
				}
			}															
		}
		$user_qrcode_count = sizeof($user_qrcode_list);
		$user_qrcode_list = $user_qrcode_list;
		//echo $date .": " . $user_qrcode_count . "<br>";
		//for ($i=0;$i<=$user_qrcode_count;$i++){
		//	echo $user_qrcode_list[$i] . " ";
		//}
		//echo "<br>";
		
	}
	
	//Push into User Count List - Correct
	$combined_list = array_merge($user_beacon_list, $user_qrcode_list);
	
	// array_unique removed last unique value for no reason
	//$user_full_list =  array_unique($combined_list);
	// remove duplicate values by using array_flip
	$user_full_list = array_keys(array_flip($combined_list)); 
   
	//Count
	$user_detected_count = sizeof($user_full_list);
	
	
	//Notification
	//Notification is sent out to those detected in building only
	if($response['status'] == 'true')
	{
		$collection2 = new MongoCollection($app_data, 'users');
		$criteria2 = array('company_id'=>$company_id);
		$cursor_2 = $collection2->find( $criteria2 );
		
		if($cursor_2->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_2 as $user)
			{
				$current_user_id = $user['user_id'];
				
				if ( in_array($current_user_id, $combined_list) ){
					$device_id = $user['device_id'];
					$firebase_id = $user['device_id'];					
					$device_name = $user['device_name'];
					
					// Send Notifications Start
					if($device_name == 1)
					{
						//APN ONLY
						/*
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
						*/
						
						//Firebase
						$url = 'https://fcm.googleapis.com/fcm/send';
						$fields = array
						(
							//'registration_ids' 	=> array($device_id),
							'to' 	=> $firebase_id,
							
							'notification' => array (
								'title' => $purpose, 
								'body' 	=> $purpose . ' is raised at ' . $location_name . '. Please proceed to the evacuation area.',
								'fireAlarmID' => $fire_alarm_id,
								'companyID' => $company_id,
								'locationName' => $location_name,
								'purpose' => $purpose,
								'triggerUserName' => $triggerUserName,
								'buildingName' => $default_building_name,
								'activity' 	=> 'Alarm',
								'android_channel_id' => 'FirebaseAlarm',
								'sound' => 'default',
								'tag' => 'FirebaseAlarm',
								'click_action' => 'RESPOND_ALARM'
							),
							
							'data'			=> array(
								'title' => $purpose, 
								'body' 	=> $purpose . ' is raised at ' . $location_name . '. Please proceed to the evacuation area.',
								'activity' 	=> 'Alarm',
								'fireAlarmId' => $fire_alarm_id,
								'companyId' => $company_id,
								'locationName' => $location_name,
								'purpose' => $purpose,
								'triggerUserName' => $triggerUserName,
								'buildingName' => $default_building_name								
							//'android' => array('click_action'=>'RESPOND_ALARM')
							),
							'message_id' => 1
						);
						
						$headers = array
						(
							//'Authorization: key=AAAAZe151S4:APA91bGpCZalNz7dZbFFhdOH7bKFnST6P5xOOJip9h-xwKoKetvF-02EJI35sMNQ5UwrT1Zh02Lf6kNeEd4zp0XHqdfb7uSbf2Emr8i49Ct_nvsWcbmZ0mt13v9tDcZLgRsOBlwR1EgG',
							'Authorization: key=AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I',
							'Content-Type: application/json'
						);
						
						
						$ch = curl_init();
						// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
						curl_setopt( $ch,CURLOPT_URL, $url );
						curl_setopt( $ch,CURLOPT_POST, true );
						curl_setopt($ch, CURLOPT_HEADER, true);
						curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
						$result = curl_exec($ch );
						curl_close( $ch );
						
						$response['firebase_result'][] = $result;
						//echo json_encode( $fields );
					}
					
					else if($device_name == 2)
					{
						//Firebase
						$url = 'https://fcm.googleapis.com/fcm/send';
						
						$fields = array
						(
							//'registration_ids' 	=> array($device_id),
							'to' 	=> $firebase_id,
							
							'notification' => array (
								'title' => $purpose, 
								'body' 	=> $purpose . ' is raised at ' . $location_name . '. Please proceed to the evacuation area.',
								'fireAlarmID' => $fire_alarm_id,
								'companyID' => $company_id,
								'locationName' => $location_name,
								'purpose' => $purpose,
								'triggerUserName' => $triggerUserName,
								'buildingName' => $default_building_name,
								'activity' 	=> 'Alarm',
								'android_channel_id' => 'FirebaseAlarm',
								'sound' => 'default',
								'tag' => 'FirebaseAlarm',
								'click_action' => 'RESPOND_ALARM'
							),
							
							'data'			=> array(
								'title' => $purpose, 
								'body' 	=> $purpose . ' is raised at ' . $location_name . '. Please proceed to the evacuation area.',
								'activity' 	=> 'Alarm',
								'fireAlarmId' => $fire_alarm_id,
								'companyId' => $company_id,
								'locationName' => $location_name,
								'purpose' => $purpose,
								'triggerUserName' => $triggerUserName,
								'buildingName' => $default_building_name
								
							//'android' => array('click_action'=>'RESPOND_ALARM')
							),
							'message_id' => 1

						);
						
						$headers = array
						(
							//'Authorization: key=AAAAZe151S4:APA91bGpCZalNz7dZbFFhdOH7bKFnST6P5xOOJip9h-xwKoKetvF-02EJI35sMNQ5UwrT1Zh02Lf6kNeEd4zp0XHqdfb7uSbf2Emr8i49Ct_nvsWcbmZ0mt13v9tDcZLgRsOBlwR1EgG',
							'Authorization: key=AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I',
							'Content-Type: application/json'
						);
												
						$ch = curl_init();
						// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
						curl_setopt( $ch,CURLOPT_URL, $url );
						curl_setopt( $ch,CURLOPT_POST, true );
						curl_setopt($ch, CURLOPT_HEADER, true);
						curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
						$result = curl_exec($ch );
						curl_close( $ch );
						
						$response['firebase_result'][] = $result;
						//echo json_encode( $fields );					
						
					}
					
					else{
						$url = 'https://fcm.googleapis.com/fcm/send';
						$fields = array
						(
							//'registration_ids' 	=> array($device_id),
							'to' 	=> $firebase_id,
							
							'notification' => array (
								'title' => $purpose, 
								'body' 	=> $purpose . ' is raised at ' . $location_name . '. Please proceed to the evacuation area.',
								'fireAlarmID' => $fire_alarm_id,
								'companyID' => $company_id,
								'locationName' => $location_name,
								'purpose' => $purpose,
								'triggerUserName' => $triggerUserName,
								'buildingName' => $default_building_name,
								'activity' 	=> 'Alarm',
								'android_channel_id' => 'FirebaseAlarm',
								'sound' => 'default',
								'tag' => 'FirebaseAlarm',
								'click_action' => 'RESPOND_ALARM'
							),
							
							'data'			=> array(
								'title' => $purpose, 
								'body' 	=> $purpose . ' is raised at ' . $location_name . '. Please proceed to the evacuation area.',
								'activity' 	=> 'Alarm',
								'fireAlarmId' => $fire_alarm_id,
								'companyId' => $company_id,
								'locationName' => $location_name,
								'purpose' => $purpose,
								'triggerUserName' => $triggerUserName,
								'buildingName' => $default_building_name,
								'click_action' => 'RESPOND_ALARM'								
							//'android' => array('click_action'=>'RESPOND_ALARM')
							),
							'message_id' => 1

						);
						
						$headers = array
						(
							//'Authorization: key=AAAAZe151S4:APA91bGpCZalNz7dZbFFhdOH7bKFnST6P5xOOJip9h-xwKoKetvF-02EJI35sMNQ5UwrT1Zh02Lf6kNeEd4zp0XHqdfb7uSbf2Emr8i49Ct_nvsWcbmZ0mt13v9tDcZLgRsOBlwR1EgG',
							'Authorization: key=AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I',
							'Content-Type: application/json'
						);
						
						
						$ch = curl_init();
						// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
						curl_setopt( $ch,CURLOPT_URL, $url );
						curl_setopt( $ch,CURLOPT_POST, true );
						curl_setopt($ch, CURLOPT_HEADER, true);
						curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
						$result = curl_exec($ch );
						curl_close( $ch );
						
						$response['firebase_result'][] = $result;
						//echo json_encode( $fields );
						
					}
					
					
					//$response['notification'] = $user;
					$response['user_notified'][] = $user['user_id'];
					//$response['user_notified']['email'] = $user['email'];
					//echo json_encode($response['user_notified']);
					//unset($response['user_notified']);
				}
			}
		}
		else{
			$response['status'] = 'false';
			$response['user_notification_error'] = 'Notification Failed';
		}

	}
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
	
}

//Create New Response for Fire Alarm
else if( $_REQUEST['action'] == 'fire_alarm_response' && $_REQUEST['method'] == 'add' && 
	isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
	isset($_REQUEST['fire_alarm_id']) && $_REQUEST['fire_alarm_id'] != '' &&
	isset($_REQUEST['response']) && $_REQUEST['response'] != '') 
{
	$response['status'] = 'false';
	$response['error'] = 'Invalid Response';
	// Only User Location Tracking & Fire Alarm System time format has second
	$time_now = date('d/m/Y H:i:s');
	$fire_alarm_response = (int) $_REQUEST['response'];
		
	if ($_REQUEST['message'] == null || $_REQUEST['message'] == ''){
		$_REQUEST['message'] = '';
	}
	
	//Response in (int) include:
	//1 - Acknowledge, 2 - Ignore, 3 - Help
	
	// Create Fire Alarm Response
	$collection = new MongoCollection($app_data, 'fire_alarm_response');

	//$criteria = array('user_id'=>(int) $_REQUEST['user_id']);
	$criteria = array(	
		'$and' => array( 
			array( 'fire_alarm_id'  => (int) $_REQUEST['fire_alarm_id'] ), 
			array( 'user_id'  	=> (int) $_REQUEST['user_id'] ),
			array( 'company_id'  	=> (int) $_REQUEST['company_id'] )													
		)
	);
	
	//$search = array('user_id'=> (int) $_REQUEST['user_id']);
	$cursor = $collection->find($criteria);
	
	//Update if user already responded
	if($cursor->count() > 0)
	{
		$collection->update( $criteria ,array('$set' => array('response' => (int) $_REQUEST['response']) ) );
		
		$cursorUpdate = $collection->find( $criteria );
		if($cursorUpdate->count()  > 0)
		{
			$response['status'] = 'true';
			unset($response['error']);
			
			foreach ( $cursorUpdate as $alarmResponse)
			{
				unset($alarmResponse['_id']);
				$response['data'] = $alarmResponse;
			}
		}
		
		if ( $_REQUEST['response'] == 3 )
		{
			if ($_REQUEST['location'] == null || $_REQUEST['location'] == ''){
				$_REQUEST['location'] = '';
			}
			// Create Fire Alarm Help
			$collection_help = new MongoCollection($app_data, 'fire_alarm_help');
			$post_help = array(
				'fire_alarm_help_id' 	=> getNext_users_Sequence('fire_alarm_help_id'),
				'fire_alarm_id'  		=> (int) $_REQUEST['fire_alarm_id'],
				'user_id'  	=> (int) $_REQUEST['user_id'],
				'company_id'  	=> (int) $_REQUEST['company_id'],
				'location'     => $_REQUEST['location'],
				'message'     => $_REQUEST['message'],
				'time'     => $time_now
			);
			
			if($collection_help->insert($post_help))
			{
				$response['help_status'] = 'true';
				$criteria_help = array(	
					'$and' => array( 
						array( 'fire_alarm_id'  => (int) $_REQUEST['fire_alarm_id'] ), 
						array( 'user_id'  	=> (int) $_REQUEST['user_id'] ),
						array( 'company_id'  	=> (int) $_REQUEST['company_id'] )													
					)
				);
				$cursorHelp = $collection_help->find( $criteria_help );
				
				if($cursorHelp->count() > 0)
				{
					$response['help_status'] = 'true';					
					
					foreach ( $cursorHelp as $alarmHelp)
					{
						unset($alarmHelp['_id']);
						$response['help_data'][] = $alarmHelp;
					}
				}
			}
			else{
				$response['help_status'] = 'false';
			}				
		}
		
	}
	//Insert if new response
	else{
		
		$post = array(
			'fire_alarm_response_id' 	=> getNext_users_Sequence('fire_alarm_response_id'),
			'fire_alarm_id'  		=> (int) $_REQUEST['fire_alarm_id'],
			'user_id'  	=> (int) $_REQUEST['user_id'],
			'company_id'  	=> (int) $_REQUEST['company_id'],
			'response'     => (int) $_REQUEST['response'],
			'message'     => $_REQUEST['message'],
			'time'     => $time_now
		);
		
		//Insert Data
		if($collection->insert($post))
		{
			$response['status'] = 'true';
			unset($response['error']);
			unset ($post['_id']);
			$response['data'] = $post;
			
			if ( $_REQUEST['response'] == 3 ){
				if ($_REQUEST['location'] == null || $_REQUEST['location'] == ''){
					$_REQUEST['location'] = '';
				}
				// Create Fire Alarm Help
				$collection_help = new MongoCollection($app_data, 'fire_alarm_help');
				$post_help = array(
					'fire_alarm_help_id' 	=> getNext_users_Sequence('fire_alarm_help_id'),
					'fire_alarm_id'  		=> (int) $_REQUEST['fire_alarm_id'],
					'user_id'  	=> (int) $_REQUEST['user_id'],
					'company_id'  	=> (int) $_REQUEST['company_id'],
					'location'     => $_REQUEST['location'],
					'message'     => $_REQUEST['message'],
					'time'     => $time_now
				);
				if($collection_help->insert($post_help))
				{
					$response['help'] = $post_help;
					$criteria_help = array(	
						'$and' => array( 
							array( 'fire_alarm_id'  => (int) $_REQUEST['fire_alarm_id'] ), 
							array( 'user_id'  	=> (int) $_REQUEST['user_id'] ),
							array( 'company_id'  	=> (int) $_REQUEST['company_id'] )													
						)
					);
					$cursorHelp = $collection_help->find( $criteria_help );
					
					if($cursorHelp->count()  > 0)
					{
						$response['help_status'] = 'true';
						unset($response['error']);
						
						foreach ( $cursorHelp as $alarmHelp)
						{
							unset($alarmHelp['_id']);
							$response['help_data'] = $alarmHelp;
						}
					}					
				}
				
			}
			//echo json_encode($response);
		}
		else{
			$response['error'] = 'Database Error - Cannot Insert Data';
			//echo json_encode($response);
		}
	}
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//Fire Alarm Response Update by User Response
else if( $_REQUEST['action'] == 'response' && $_REQUEST['method'] == 'update' && 
	isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '') 
{
	//TODO


}

// Get Fire Alarm Details
else if( $_REQUEST['action'] == 'get_fire_alarm_details' && $_REQUEST['method'] == 'get_statistics' && 
	isset($_REQUEST['fire_alarm_id']) && $_REQUEST['fire_alarm_id'] != '' )
{
	// Get Fire Alarm
	$collection_fire_alarm = new MongoCollection($app_data, 'fire_alarm');
	$query_fire_alarm = array( 'fire_alarm_id' => (int) $_REQUEST['fire_alarm_id'] );
	$cursor_fire_alarm = $collection_fire_alarm->find( $query_fire_alarm );
	if($cursor_fire_alarm->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor_fire_alarm as $fire_alarm)
			{
				unset($fire_alarm['_id']);
				$response['data'] = $fire_alarm;		

				//Start Statistics
				
				//All Count
				//For Fire Alarm Response Count
				$total_user_count=0;
				//$participant_count=0;
				//$user_exclude_list = array(1,179,185,186,187,188,189,190,191,196,197,201,231,237,238,239,252,255,255,262,363,364);
				$user_exclude_list = array(1,185);
				//User Detected = distinct user detected in building + user scanned qr code													
				$user_detected_list = array();
				$user_beacon_list = array();
				$user_qrcode_list = array();
				$user_full_list = array();
				
				$user_beacon_count=0;
				$user_qrcode_count=0;
				$user_detected_count=0;
				
				//Response List
				$user_safe_list = array();
				$user_not_safe_list = array();
				$user_help_list = array();
				
				//Location List
				$location_list = array();
				$location_user_list = array();
				
				$safe_count=0;
				$not_safe_count=0;
				$responded_count=0;
				$help_count=0;
				
				//For Fire Alarm Attendance Checking
				$present_count=0;
				$absent_count=0;
				//Attendance List
				$user_present_list = array();
				$user_absent_list = array();
				
				//$response_user_id = Array();
				//$help_user_id = Array();
				
				$date='';
				$time='';
				
				// Start Logic													
				$fire_alarm_id = $fire_alarm['fire_alarm_id'];
				$company_id = $fire_alarm['company_id'];
				$date = $fire_alarm['time'];
				$date = strstr($date, ' ', true);											
				
				
				//Find Company name
				$collection_company = new MongoCollection($app_data, 'company');												
				$query_company = array( 'company_ID' => (int)$company_id ); 
				$cursor_company = $collection_company->find( $query_company );
				if($cursor_company->count() > 0) { 
					foreach($cursor_company as $company)
					{
						$company_name = $company['company_name'];
						$company_ref_id = $company['company_ref'];
					}										
				}
					
				$user_id = $fire_alarm['trigger_user_id'];
				//Find Username
				$collection_users = new MongoCollection($app_data, 'users');
				$cursor_user = $collection_users->find(array('user_id'=>$user_id));
				if($cursor_user->count() > 0)
				{
					foreach($cursor_user as $user)
					{
						$trigger_username = $user['username'];
						$role = $user['role'];														
					}
				}
				
				//-------------------------------
				// Start Detected User
				//-------------------------------
				//Find User detected Beacon & push user_id into array
				/*
				$collection_user_location = $app_data->user_location;
				//Exclude Users
				$criteria_exclude_user = array(	
					'$and' => array(
						array( 'user_id'=> array('$ne'=>185) ), 
						array( 'user_id'=> array('$ne'=>186) ),
						array( 'user_id'=> array('$ne'=>196) ),
						array( 'user_id'=> array('$ne'=>252) ),
						array( 'user_id'=> array('$ne'=>237) ),
						array( 'user_id'=> array('$ne'=>238) ),
						array( 'user_id'=> array('$ne'=>239) ),
						array( 'user_id'=> array('$ne'=>201) ),
						array( 'user_id'=> array('$ne'=>255) ),
						array( 'user_id'=> array('$ne'=>363) )
					)
				);
				
				$distinct_user_location = $collection_user_location->distinct("user_id",$criteria_exclude_user);
				//$user_beacon_count = $distinct_user_location->count();
				//print_r($distinct_user_location);
				*/
				
				//Find User detected Beacon & push user_id into array
				//Include Today's Date.
				//echo $date;
				$collection_user_location_date = $app_data->user_location;													
				$criteria_location_date = array( 
					//'time'=> array('$in'=>array('27/11/2018')
					//'location_time'=> array('$in'=>array('27/11/2018'))
					'location_time'=> new MongoRegex('/' . $date. '/i')
				);
				$distinct_user_location_date = $collection_user_location_date->find($criteria_location_date);
				//$distinct_user_location_date = $collection_user_location_date->distinct("location_time",$criteria_location_date);
				//echo $date .": " . $distinct_user_location_date->count() . " ";
				
				if($distinct_user_location_date->count() > 0)
				{
					foreach($distinct_user_location_date as $location)
					{
						//echo $location['user_id'] . " ";
						$user_id=$location['user_id'];
						if ( !in_array($user_id, $user_exclude_list) )
						{
							if (!in_array($user_id, $user_detected_list)){
								array_push($user_detected_list,$user_id);
							}		
						}															
					}
					$user_beacon_count = sizeof($user_detected_list);
					$user_beacon_list = $user_detected_list;
					//echo $date .": " . $user_beacon_count . "<br>";
					//for ($i=0;$i<=$user_beacon_count;$i++){
					//	echo $user_beacon_list[$i] . " ";
					//}
					//echo "<br>";
					
				}
				
				//Find User scanned QR Code & push user_id into array
				//Include Today's Date.
				//echo $date;
				$collection_qrcode = $app_data->qrcode;													
				$criteria_qrcode = array( 
					//'time'=> array('$in'=>array('27/11/2018')
					//'location_time'=> array('$in'=>array('27/11/2018'))
					'access_time'=> new MongoRegex('/' . $date. '/i')
				);
				$distinct_qrcode = $collection_qrcode->find($criteria_qrcode);
				//$distinct_user_location_date = $collection_user_location_date->distinct("location_time",$criteria_location_date);
				//echo $date .": " . $distinct_user_location_date->count() . " ";
				
				if($distinct_qrcode->count() > 0)
				{
					foreach($distinct_qrcode as $qrcode)
					{
						//echo $location['user_id'] . " ";
						$user_id=$qrcode['user_id'];
						if ( !in_array($user_id, $user_exclude_list) ){
							//Push into QR Code Array
							if (!in_array($user_id, $user_qrcode_list)){
								array_push($user_qrcode_list,$user_id);
							}
							//Push into Merged Array
							if (!in_array($user_id, $user_detected_list)){
								array_push($user_detected_list,$user_id);
							}		
						}															
					}
					$user_qrcode_count = sizeof($user_qrcode_list);
					$user_qrcode_list = $user_qrcode_list;
					//echo $date .": " . $user_qrcode_count . "<br>";
					//for ($i=0;$i<=$user_qrcode_count;$i++){
					//	echo $user_qrcode_list[$i] . " ";
					//}
					//echo "<br>";
					
				}
				
				//Push into User Count List - Correct
				$combined_list = array_merge($user_beacon_list, $user_qrcode_list);
				
				// array_unique removed last unique value for no reason
				//$user_full_list =  array_unique($combined_list);
				// remove duplicate values by using array_flip
				$user_full_list = array_keys(array_flip($combined_list)); 
			   
				//Count
				$user_detected_count = sizeof($user_full_list);
																	
				//Check All Detected User list
				/*
				echo "Beacon: ";
				for ($i=0;$i<=$user_beacon_count-1;$i++){
						echo $user_beacon_list[$i] . " ";
				}
				echo "<br>";
				echo "QR Code: ";
				for ($i=0;$i<=$user_qrcode_count-1;$i++){
						echo $user_qrcode_list[$i] . " ";
				}
				echo "<br>";
				
				
				echo "Merged: ";
				for ($i=0;$i<=$user_detected_count-1;$i++){
						echo $user_full_list[$i] . " ";
				}
				echo "<br>";
				*/
				
				//-------------------------------
				// Start Response
				//-------------------------------
				$collection_response = $app_data->fire_alarm_response;													
				$criteria_response = array( 'fire_alarm_id'=> $fire_alarm_id);
				$cursor_response = $collection_response->find($criteria_response);
				if($cursor_response->count() > 0)
				{
					foreach($cursor_response as $fire_alarm_response)
					{															
						$user_id=$fire_alarm_response['user_id'];
						if($fire_alarm_response['response'] == 1){
							if ( !in_array($user_id, $user_exclude_list) ){																
								if (!in_array($user_id, $user_safe_list)){
									array_push($user_safe_list,$user_id);
								}		
							}
						}
						if($fire_alarm_response['response'] == 2){
							if ( !in_array($user_id, $user_exclude_list) ){																
								if (!in_array($user_id, $user_not_safe_list)){
									array_push($user_not_safe_list,$user_id);
								}		
							}
						}
						if($fire_alarm_response['response'] == 3){
							if ( !in_array($user_id, $user_exclude_list) ){																
								if (!in_array($user_id, $user_help_list)){
									array_push($user_help_list,$user_id);
								}		
							}
						}
					}
					$safe_count = sizeof($user_safe_list);
					$not_safe_count = sizeof($user_not_safe_list);
					$help_count = sizeof($user_help_list);
					
					$responded_list = array_merge($user_safe_list, $user_not_safe_list);
					$responded_list =  array_unique($responded_list);
					$responded_count = sizeof($responded_list);
					
					/*
					//Check Values														
					echo "Help " . $date .": " . $help_count . "<br>";
					for ($i=0;$i<=$help_count;$i++){
						echo $user_help_list[$i] . " ";
					}
					echo "<br>";
					*/
																		
				}
				
				//Final No Response Count
				$not_responded_count = $user_detected_count - $responded_count;
				
				//-------------------------------
				//Get Location List
				//-------------------------------
				$collection_help_location = new MongoCollection($app_data, 'fire_alarm_help');												
				$query_help_location = array( 'fire_alarm_id' => $fire_alarm_id ); 
				$cursor_help_location = $collection_help_location->find( $query_help_location );
				if($cursor_help_location->count() > 0) {
					foreach( $cursor_help_location as $help){
						$user_id = $help['user_id'];
						$location = $help['location'];
						if ( !in_array($user_id, $user_exclude_list) ){
							if (!in_array($location, $location_list)){
								array_push($location_list,$location);
							}
						}
					}
				}
				$location_count =  sizeof($location_list);
				
				//End Statistics
				
				
				$location_id = $fire_alarm['location_id'];
				if ($location_id == 0){
					$building_name = 'SP Group HQ';
				}
				else{
					//Find Building ID
					$collection_location = new MongoCollection($app_data, 'company_location');
					$cursor_location = $collection_location->find(array('location_id'=>$location_id));
					if($cursor_location->count() > 0)
					{
						foreach($cursor_location as $location)
						{
							$building_id = $location['building_id'];														
						}
					}
					//Find Building Name
					$collection_building = new MongoCollection($app_data, 'company_building');
					$cursor_building = $collection_building->find(array('building_id'=>$building_id));
					if($cursor_building->count() > 0)
					{
						foreach($cursor_building as $building)
						{
							$building_name = $building['building_name'];														
						}
					}				
				}
			
				$response['data']['trigger_username'] = $trigger_username;
				$response['data']['trigger_user_name'] = $trigger_username;
				$response['data']['building_name'] = $building_name;
				
				//Statistics in Response
				$response['statistics']['participant'] = $participant_count;
				$response['statistics']['detected_by_beacon'] = $user_beacon_count;
				$response['statistics']['scanned_qrcode'] = $user_qrcode_count;
				$response['statistics']['responded_count'] = $responded_count;
				$response['statistics']['not_responded_count'] = $participant_count;
				$response['statistics']['safe_count'] = $safe_count;
				$response['statistics']['not_safe_count'] = $not_safe_count;
				$response['statistics']['help_count'] = $help_count;
				$response['statistics']['safe_count'] = $safe_count;
				
								
				//Help List
				$response['help_user_id'] = $user_help_list;
				
				$collection_help = new MongoCollection($app_data, 'fire_alarm_response');
				$criteria_help = array(	
					'$and' => array(
						array( 'response' => 3 ), 
						array( 'fire_alarm_id'=> $fire_alarm_id )
					)
				);
				$cursor_help = $collection_help->find( $criteria_help );
				if($cursor_help->count() > 0) { 
					$full_list_number = 0;
					foreach($cursor_help as $help)
					{
						$help_user_id = $help['user_id'];
						//Find Username
						$collection_username = new MongoCollection($app_data, 'users');
						$cursor_username = $collection_username->find(array('user_id'=>$user_id));
						if($cursor_username->count() > 0)
						{
							foreach($cursor_username as $user)
							{
								$username = $user['username'];
								$full_name = $user['full_name'];
								$role = $user['role'];
								$phone_number = $user['phone_number'];
							}
						}																		
						
						//Find Help Location
						$collection_help_location = $app_data->fire_alarm_help;												
						$query_help_location = array( 'user_id' => $help_user_id ); 
						$cursor_help_location = $collection_help_location->find( $query_help_location );
						if($cursor_help_location->count() > 0) { 
							foreach($cursor_help_location as $location)
							{
								$help_location = $location['location'];
								$help_message = $location['message'];
								$help_time = $location['time'];
							}	
						}																					
						
						//Find last known location
						$collection_user_location = new MongoCollection($app_data, 'user_location');
						$criteria_user_location = array(	
							'$and' => array(
								array( 'user_id' => $help_user_id ), 
								array( 'location_time'=> new MongoRegex('/' . $date. '/i') )
							)
						);
						$query_user_location = array( 'user_id' => $help_user_id );
						$cursor_user_location = $collection_user_location->find( $criteria_user_location )->sort(array("user_location_id"=>-1))->limit(-1);
						//$user_last_beacon = $cursor_user_location['beacon_name'];
						if($cursor_user_location->count() > 0)
						{
							foreach($cursor_user_location as $location)
							{
								$user_last_beacon_name = $location['beacon_name'];
								$user_last_beacon_time = $location['location_time'];
							}
						}
						else{																					
							$user_last_beacon = "No Last know location";																			
							//$last_known_location = $collection_user_location->find($query_user_location)->sort(array("user_location_id"=>-1))->limit(1);																
						}																					
						
						$response['help_list'][$full_list_number]['help_username'] = $username;
						$response['help_list'][$full_list_number]['help_phone_number'] = $phone_number;
						$response['help_list'][$full_list_number]['help_location'] = $help_location;
						$response['help_list'][$full_list_number]['help_user_last_beacon_name'] = $user_last_beacon_name;
						$response['help_list'][$full_list_number]['help_user_last_beacon_time'] = $user_last_beacon_time;

						//Add 1 before end of loop
						$full_list_number++;
					}
				}
				else{
					$response['help_list'] = "No user in help list";
				}
				
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Alarm Not Found';

	}
	
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
	
}

// Get Fire Alarm for Response Screen Details
else if( $_REQUEST['action'] == 'get_fire_alarm_details' && $_REQUEST['method'] == 'get' && 
	isset($_REQUEST['fire_alarm_id']) && $_REQUEST['fire_alarm_id'] != '' )
{
	// Get Fire Alarm
	$collection_fire_alarm = new MongoCollection($app_data, 'fire_alarm');
	$query_fire_alarm = array( 'fire_alarm_id' => (int) $_REQUEST['fire_alarm_id'] );
	$cursor_fire_alarm = $collection_fire_alarm->find( $query_fire_alarm );
	if($cursor_fire_alarm->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor_fire_alarm as $fire_alarm)
		{
			unset($fire_alarm['_id']);
			
			
			$user_id = $fire_alarm['trigger_user_id'];
			//Find Username
			$collection_users = new MongoCollection($app_data, 'users');
			$cursor_user = $collection_users->find(array('user_id'=>$user_id));
			if($cursor_user->count() > 0)
			{
				foreach($cursor_user as $user)
				{
					$trigger_username = $user['username'];
					$role = $user['role'];														
				}
			}
			
			$location_id = $fire_alarm['location_id'];
			if ($location_id == 0){
				$building_name = 'SP Group HQ';
			}
			else{
				//Find Building ID
				$collection_location = new MongoCollection($app_data, 'company_location');
				$cursor_location = $collection_location->find(array('location_id'=>$location_id));
				if($cursor_location->count() > 0)
				{
					foreach($cursor_location as $location)
					{
						$building_id = $location['building_id'];														
					}
				}
				//Find Building Name
				$collection_building = new MongoCollection($app_data, 'company_building');
				$cursor_building = $collection_building->find(array('building_id'=>$building_id));
				if($cursor_building->count() > 0)
				{
					foreach($cursor_building as $building)
					{
						$building_name = $building['building_name'];														
					}
				}				
			}
			
			$response['data'] = $fire_alarm;
			$response['data']['trigger_username'] = $trigger_username;
			$response['data']['trigger_user_name'] = $trigger_username;
			$response['data']['building_name'] = $building_name;
		}
	}		
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Alarm Not Found';

	}	
	
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
	//echo json_encode($response);
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