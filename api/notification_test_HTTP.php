<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

$purpose = 'Test';
//$_REQUEST['status'] = 1;
$status =1;
$i=0;

$company_id = 27;

$collection2 = new MongoCollection($app_data, 'users');
$criteria2 = array('company_id'=>$company_id);
$cursor_2 = $collection2->find( $criteria2 );
		
if($cursor_2->count() > 0) { 
	$response['status'] = 'true';
	foreach($cursor_2 as $user)
	{
		$current_user_id = $user['user_id'];
		$firebase_id = $user['device_id'];
		
					//echo "ID is " . $device_id;
					// Send Notifications Start
					/*if($device_name == 1)
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
								'body' => 'Fire Alarm Triggered: ' . $purpose . '',
							 ),
							'sound' => 'default'
						);
						$payload = json_encode($body);
						$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
						$result = fwrite($fp, $msg, strlen($msg));
						fclose($fp);
						
						$response['result'] = $result;
					}
					if($device_name == 2)
					{*/
						$url = 'https://fcm.googleapis.com/fcm/send';
						//$url = 'https://weiocks-7e0ce.firebaseio.com';
						//$url = 'https://fcm.googleapis.com/v1/projects/weiocks-7e0ce/messages:send';
						//$test_token = 'fSeCiJqCmnM:APA91bE7dwKAY2TjXZLjxHROPpTAuPO4-JQgQHPxP80HVIJRXhBW5r-0zFlXCWQnwNoZA_I7Y269y4GuwfHuP0GDQdpxunyfcAOc03OT2orQxENqHyZZEAK24sTwSV5ZutrQirtI16Kb';
						//$test_token = 'ci_SUVcaUeM:APA91bFu2XOuuB1Z32-x3WslPwjc777QmRt-HKTugqhHiP3l9L1HKv7ZbN_cyjXVXf6-SmERAdsmP_sxLYvNCNNYtHM4TIEBV7M8kP1yfqwXt2N6LZr-KL3qUE7BfS9qk9MOIRsiikhY';
						$test_token = $firebase_id;
						
						//$test_token = 'dAWHfX7ucOI:APA91bHyjnuXoFOOVXWHwNKMcS1wMWT-73z4djqncLFPzphYCCBBbj5j6Xk3ob4j4HPE6NbyIpisLCk1ib6qUy0m6jwJtNQIwF-EOWffwQRHjgHOgrFdmyuupzAp4Mee3XNoDfhabkS8"';
						
						//$test_token_2 = 'eIql4jsPGr0:APA91bEX0YWQcW55gm1vKYPyC0K2vsSHirzToniLMyqmpCyi-uskyukTWABzEFrRBJc1_HiAH0W52fJbCwYJU8SWXOs-TIspdCto3_I-wKeFkq1-5HqXCD77c9LDym_z-xOWrgAK9VBN';
						
						//$registrationIds = array( $test_token );
						/*
						$msg = array
						(
							'title'		=> 'Fire Drill', 
							'message' 	=> 'This is to inform that there will be test notifications send out till 4th Nov. Pls do not be alarmed. No action is required. ' . $purpose . ''
							//'android' => array( 'notification' => array('click_action'=>'OPEN_ACTIVITY_1') )
						);
						$fields = array
						(
							'registration_ids' 	=> array($test_token),
							'data'			=> $msg
						);
						
						/*
						$msg = array (
							'notification' => array(
								'title'		=> 'Fire Drill', 
								'body' 		=> 'This is to inform that there will be test notifications send out till 4th Nov. Pls do not be alarmed. No action is required. ' . $purpose . '',
								//'android' => array( 'notification' => array('click_action'=>'OPEN_ACTIVITY_1') )
								
							)
							'token' => $registrationIds
						);
						$fields = array
						(
							//'registration_ids' 	=> array($test_token),
							//'token' 	=> $registrationIds,
							//'data'			=> $msg
							'message'	=> $msg
						);
						
						$headers = array
						(
							'Authorization: key=' . API_ACCESS_KEY,
							'Content-Type: application/json'
						);
						
						*/
						
						//WORKING EXAMPLE
						/*
						$fields = array
						(
							'registration_ids' 	=> array($test_token),
							'data'			=> array(
								'title' => 'Fire Drill', 
								'body' 	=> 'This is to inform that there will be test notifications send out till 4th Dec. Pls do not be alarmed. No action is required. ' . $purpose . ''
								
							//'android' => array('click_action'=>'RESPOND_ALARM')
							)

						);
						*/
						
						$url = 'https://fcm.googleapis.com/fcm/send';
						
						$fields = array
						(
							//'registration_ids' 	=> array($test_token),
							'to' 	=> $test_token,
							
							'notification' => array (
								'title' => 'Fire Drill', 
								'body' 	=> 'Pls do not be alarmed. No action is required. ' . $purpose . '',
								'fireAlarmID' => 140,
								'companyID' => 27,
								'locationName' => 'PA Office',
								'purpose' => 'Fire Drill',
								'triggerUserName' => 'paalan',
								'buildingName' => 'PA Office',
								'activity' 	=> 'Alarm',
								'android_channel_id' => 'FirebaseAlarm',
								'sound' => 'default',
								'tag' => 'FirebaseAlarm',
								'click_action' => 'RESPOND_ALARM'
							),
							
							'data'			=> array(
								'title' => 'Test Fire Drill', 
								'body' 	=> 'Pls do not be alarmed. No action is required. ' . $purpose . '',
								'activity' 	=> 'Alarm',
								'fireAlarmId' => 140,
								'companyId' => 27,
								'locationName' => 'PA Office',
								'purpose' => 'Fire Drill',
								'triggerUserName' => 'paalan',
								'buildingName' => 'PA Office',
								'activity' 	=> 'Alarm'
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
						
						$response['firebase_response'][] = $result;
						$response['firebase_field_sent'][] = $fields;
						$response['token_sent'][] = $test_token;
					//}
				
				
				//$response['notification'] = $user;
				//$response['user_notified'][$i] = $user['user_id'];
				//$response['user_notified']['email'] = $user['email'];
				//echo json_encode($response['user_notified']);
				//unset($response['user_notified']);
	}
}
	
		
		header('Content-Type: application/json');
		echo json_encode($response, JSON_PRETTY_PRINT );
	

	
		

					
?>