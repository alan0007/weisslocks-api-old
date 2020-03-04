<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

require (dirname(dirname(__FILE__)).'/composer/vendor/autoload.php');

use Kreait\Firebase\Firebase; 
use Kreait\Firebase\Configuration;

	$config = new Configuration();
	$config->setAuthConfigFile(dirname(dirname(__FILE__)).'weiocks-7e0ce-firebase-adminsdk-lcamy-3065e48407.json');

	$firebase = new Firebase('https://weiocks-7e0ce.firebaseio.com', $config);
	
	


$purpose = 'Test';
//$_REQUEST['status'] = 1;
$status =1;
$i=0;

		$collection2 = new MongoCollection($app_data, 'users');
		$criteria2 = array('user_id'=>186 );
		$cursor_2 = $collection2->find( $criteria2 );
		
		if($cursor_2->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_2 as $user)
			{
				if($status == 1)
				{
					$device_id = $user['device_id'];
					$device_name = $user['device_name'];
					
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
						$test_token = 'fSeCiJqCmnM:APA91bE7dwKAY2TjXZLjxHROPpTAuPO4-JQgQHPxP80HVIJRXhBW5r-0zFlXCWQnwNoZA_I7Y269y4GuwfHuP0GDQdpxunyfcAOc03OT2orQxENqHyZZEAK24sTwSV5ZutrQirtI16Kb';
						$test_token_2 = 'eIql4jsPGr0:APA91bEX0YWQcW55gm1vKYPyC0K2vsSHirzToniLMyqmpCyi-uskyukTWABzEFrRBJc1_HiAH0W52fJbCwYJU8SWXOs-TIspdCto3_I-wKeFkq1-5HqXCD77c9LDym_z-xOWrgAK9VBN';
						
						$registrationIds = array( $test_token );
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
							//'registration_ids' 	=> $registrationIds,
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
						
						$msg = array(
							//'token' 		=> $test_token ,
							'to' 		=> $test_token ,
							'notification'	=> array(
								'title' => 'Fire Drill', 
								'body' 	=> 'This is to inform that there will be test notifications send out till 4th Dec. Pls do not be alarmed. No action is required. ' . $purpose . ''
							//'android' => array('click_action'=>'RESPOND_ALARM')
							)
						);
						
						$fields = array
						(
							'message' 			=> $msg 
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
						curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
						curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
						$result = curl_exec($ch );
						curl_close( $ch );
						
						$response['result'] = $result;
						echo json_encode( $fields );
					//}
				}
				
				//$response['notification'] = $user;
				$response['user_notified'][$i] = $user['user_id'];
				//$response['user_notified']['email'] = $user['email'];
				//echo json_encode($response['user_notified']);
				//unset($response['user_notified']);
				
				$i++;
			}
		}
		else{
			$response['status'] = 'false';
			$response['user_notification_error'] = 'Notification Failed';
		}
		
		
		echo json_encode($response);

		
		

					
?>