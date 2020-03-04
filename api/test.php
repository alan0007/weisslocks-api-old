<?php

echo '<pre>';

function deleteIndex($db, $collection, $indexName) {
    if (class_exists("MongoClient")) {
        $m = new MongoClient();
    } else {
        $m = new Mongo();
    }
    $indexes = $m->{$db}->{$collection}->getIndexInfo();
    foreach ($indexes as $index) {
        if ($index['name'] === $indexName) {
            return $m->{$db}->command(array("deleteIndexes" => $this->m->{$db}->{$collection}->getName(), "index" =>$index['key']));
            break;
        }
    }
    return false;
}    


 $response=deleteIndex('testing','inventory','size');
    echo "<pre>";
    print_r($response);
    echo "</pre>";
exit;

$Connection = new MongoClient( ); 
$app_data = $Connection->selectDB('testing');
$users_ = $app_data->inventory;
$cursor = $users_->find();
foreach($cursor as $ff)
{
	print_r($ff);
}

$users_->deleteIndex("size");;

$cursor = $users_->find();
foreach($cursor as $ff)
{
	print_r($ff);
}



exit;






include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

	if($_REQUEST['action'] == 'accesscode' && $_REQUEST['method'] == 'post' && !empty($_REQUEST['key_id']) &&  !empty($_REQUEST['lock_id']) && !empty($_REQUEST['user_id']))
{
	
	
	
	if(isset($_REQUEST['end_dt']))
	{
		$future =  strtotime(date('d-m-Y H:i'));
		$old = strtotime(date($_REQUEST['end_dt']));
		if($old >= $future)
			{ }
		else
			{
				$response['status'] = 'false';
				$response['error'] = 'Invalid Date and Time...';
				exit(json_encode($response));
			}
	}
	
	$key_id = $_REQUEST['key_id'];
	$response['status'] = 'false';
	$response['error'] = 'Invalid User';
	
	$users_ = $app_data->users;
	$cursor = $users_->findOne(array('user_id' =>(int) $_REQUEST['user_id']));
	
	if(isset($cursor['user_id']))
	{
		if($cursor['role'] != 1)
		{
			if(date('D') == 'Sat' || date('D') == 'Sun')
			{
					$die = 1;
					$admin_approves = 'Yes';
					$com_approves = 'Yes';
					$settings = $app_data->settings;
					$cursor = $settings->findOne(array('company_id'=>(int)$cursor['company_id']));
					
					if(!empty($cursor['setting_id']))
					{
						$admin_approves = $cursor['super_admin_allow_saturday_sunday'];
						$com_approves = $cursor['allow_saturday_sunday'];
						if($admin_approves == 'No' || $com_approves == 'No')
						{
							$die = 0;
						}
					}
					else
					{
							$die = 0;
					}
					if($die == 0)
					{
						unset($response['error']);
						$response['msg'] = 'You are Not Allowed To use access code on '.date('l');
						exit(json_encode($response));
					}
			}
		}
		
	$response['error'] = 'Invalid Key Name';
	$keys_details = $app_data->keys;
	$key_detail = $keys_details->findOne(array('key_ID'=>(int)$key_id));
	
	if(isset($key_detail['key_ID']))
	{
		$response['status'] = 'false';
		unset($response['error']);
		
		$key_group_ids = json_decode($key_detail['key_group_id']);
		$KeyLockGroup = $app_data->KeyLockGroup;
		$KeyLockGroups = $KeyLockGroup->find();
		if($KeyLockGroups->count() > 0)
		{
			echo '<pre>';
			foreach($KeyLockGroups as $vals)
			{
				if(in_array( $vals['key_group_id'], $key_group_ids ))
				{
					
					
					
					
					
					
					
					
					if(in_array($cursor['role'],array(4,5)))
					{
						if( in_array($cursor['user_id'],  $vals['users']))
						{
							print_r($vals);
							$pairing_ids[] = $vals['keyLockGroup_ID'];
						}
					}
					else
					{
						$pairing_ids[] = $vals['keyLockGroup_ID'];
					}
					
					
					
					
				}
			}
		}
		
		 
		if(!empty($pairing_ids))
		{
			$pairing_ids = array_map('intval',$pairing_ids);
			$KeyLockGroups = $app_data->KeyLockGroup;
			$arg = array('keyLockGroup_ID' => array('$in'=> $pairing_ids ));
			$cursor = $KeyLockGroups->find( $arg );
			
			$die = 0;
			
			if($cursor->count() > 0)
			{
				foreach($cursor as $pairing_details)
				{
					$paymentDate = !empty($_REQUEST['end_dt']) ? strtotime(date('d-m-Y',strtotime($_REQUEST['end_dt']))) : strtotime(date('d-m-Y'));
					$paymentTime = !empty($_REQUEST['end_dt']) ? strtotime(date('H:i',strtotime($_REQUEST['end_dt']))) : strtotime(date('H:i'));
					// Start here
					$die = 1;					
					$contractDateBegin = strtotime( $pairing_details['date_from']);
					$contractDateEnd = strtotime( $pairing_details['date_to'] );
					$contractTimeBegin = strtotime( $pairing_details['time_from_hh'] . ':' . $pairing_details['time_from_mm']);
					$contractTimeEnd = strtotime( $pairing_details['time_to_hh'] . ':' . $pairing_details['time_to_mm'] );
					
					if ((($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)) && (($paymentTime >= $contractTimeBegin) && ($paymentTime <= $contractTimeEnd) ) )
					{
							$die = 0;
							break;
					} 
					// End here
				}
			}
		}
		else
		{
			unset($response['error']);
			// $response['msg'] = 'Key is Valid Die to Not Assinged to Any Pairing';
			$response['msg'] = 'No Access Control Exists...';
			exit(json_encode($response));
		}
		echo '<pre>';
		 print_r($pairing_ids); 
		 exit;
		if($die == 1)
		{
			$user_reg = $app_data->history_log;
			$start_time = date('d-m-Y H:i:s');
			$end_dt = trim($_REQUEST['end_dt']) == '' ? date("d-m-Y H:i:s", strtotime('+1 hour')) : $_REQUEST['end_dt'];
			$post = array(
				'history_id' => getNext_users_Sequence('history_log'),
				'user_id'     =>(int)$_REQUEST['user_id'],
				'lock_id'     =>(int) $_REQUEST['lock_id'],
				'key_id'     =>(int) $_REQUEST['key_id'],
				'start_dt'     => $start_time,
				'end_dt'     => $end_dt,
				'Status' => 'false',
				'access_code' => 'Request Occures in Invalid Date & Time.',
				'requested_time'  => $start_time,
				'timer'  => '',
				);
			
			if($user_reg->insert($post))
			{
				$response['msg'] = 'You are not allowed for access at this time 123456';
			}
			echo json_encode($response);
			exit;
		}
		
		// Get Lock and Key Details
		$locks = $app_data->locks;
		$cursor = $locks->find(array('lock_ID' =>(int) $_REQUEST['lock_id']));
		if($cursor->count() > 0)
		{
				$response['status'] = 'true';
			foreach($cursor as $company_detail)
			{
				$lock_name = $company_detail['lock_name'];
			}
		}
		$response['status'] = 'false';
		$keys = $app_data->keys;
		$cursor = $keys->find(array('key_ID' =>(int) $_REQUEST['key_id']));
		if($cursor->count() > 0)
		{
			$response['status'] = 'true';
			foreach($cursor as $keys)
			{
				$phone_number = $keys['key_phone_number'];
			}
		}
		
		
				// Main Logic for Access code Start
		
				if($response['status'] == 'true')
				{
					unset($response['msg']);
					$lock_name = str_replace(' ', '%20', $lock_name);
					// $response['accesscode'] = file_get_contents('http://app.weisslocks.com/api2?lockname=certis%201&mobile=6590293123');
					/*
					$app_date = isset($_REQUEST['end_dt']) && $_REQUEST['end_dt'] != '' ? $_REQUEST['end_dt'] : date('d-m-Y H:i');
					$year =  date('Y',strtotime($app_date));
					$month =  date('m',strtotime($app_date));
					$day =  date('d',strtotime($app_date));
					$hour =  date('H',strtotime($app_date));
					$minute =  date('i',strtotime($app_date));
					'http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number.'&year'.$year.'&month='.$month.'&day='.$day.'&hour='.$hour.'&minute='.$minute;
					*/
					// $app_date = isset($_REQUEST['end_dt']) && $_REQUEST['end_dt'] != '' ? $_REQUEST['end_dt'] : date('d-m-Y H:i');
					// $year =  date('Y',strtotime($app_date));
					// $month =  date('m',strtotime($app_date));
					// $day =  date('d',strtotime($app_date));
					// $hour =  date('H',strtotime($app_date));
					// $minute =  date('i',strtotime($app_date));
					
					$response['accesscode'] = file_get_contents('http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number);
					// $response['accesscode'] = file_get_contents('http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number.'&year'.$year.'&month='.$month.'&day='.$day.'&hour='.$hour.'&minute='.$minute);
					 $MAIN_response = $response['accesscode'];
					 $params = explode(':',$MAIN_response);
					// $response['status'] = isset($params[2]) ? 'false' : 'true';
					 $response['accesscode'] = $params[2];
					 $response['valid_on'] = $params[3] . ':' . $params[4] . ':' . $params[5];
					$end_dt = trim($_REQUEST['end_dt']) == '' ? date("d-m-Y H:i:s", strtotime('+1 hour')) : $_REQUEST['end_dt'];
					$user_reg = $app_data->history_log;
					$start_time = date('d-m-Y H:i:s');
					
					$response['status'] = is_null($params[2]) ? 'false' : 'true';
					$response['msg'] = is_null($params[2]) ? $MAIN_response : '';
					
					$start = new DateTime( $start_time );
					$end = new DateTime( $end_dt );
					$interval = $end->diff($start);
					$days = $interval->format('%d');
					$hours = 24 * $days + $interval->format('%h');
					
					$convert_to_mill = $hours.':'.$interval->format('%i').':00';
					$string = $convert_to_mill;
					$time   = explode(":", $string);

					$hour_mili   = $time[0] * 60 * 60 * 1000;
					$minute_mili = $time[1] * 60 * 1000;
					$sec_mili    = $time[2] * 1000;

					$result = $hour_mili + $minute_mili + $sec_mili;

					$post = array(
					'history_id' => getNext_users_Sequence('history_log'),
					'user_id'     =>(int)$_REQUEST['user_id'],
					'lock_id'     =>(int) $_REQUEST['lock_id'],
					'lock_name'     => $lock_name,
					'key_id'     =>(int) $_REQUEST['key_id'],
					'pairing_id'     =>(int) $_REQUEST['pairing_id'],
					'start_dt'     => $start_time,
					'end_dt'     => $end_dt,
					'Status' => $response['status'],
					'access_code' => $MAIN_response,
					'requested_time'  => $start_time,
					'timer'  => $result,
					);
					$user_reg->insert($post);
					$collection = new MongoCollection($app_data, 'history_log');
					$Reg_Query = array('_id' => $post['_id'] ) ;
					$cursor = $collection->find( $Reg_Query );
					
					if (strpos($response['accesscode'], 'Unauthorized') !== false || strpos($response['accesscode'], '500') !== false || strpos($response['accesscode'], 'foundError') !== false || strpos($response['accesscode'], 'not') !== false)
					{
						$response['status'] = 'false';
						$response['msg'] = $MAIN_response;
					}
					
					foreach($cursor as $history)
					{
						unset($history['_id']);
						$response['data'] = $history;
					}
				}
		
				// Main Logic for Access code End
		
		
		
		
		
		
		
		
		
		
		
	}
	}
}

exit;
	
// https://gist.github.com/odan/138dbd41a0c5ef43cbf529b03d814d7c









/*
https://en.wikipedia.org/wiki/Alexa_Internet
define( 'API_ACCESS_KEY', 'AAAAVOkhDvo:APA91bGRILek2Q_yUzhTAhgaoOaDPeuhpLrHJEBL58y66xgdGdai7U6MCpqwWOV231Qk98Wj6p_oJpbkua9omdqeLvaKJVox_elZHK84tMZbmuqiOCOUixTcHWyQkYNNIF2AmlIg1Pvh' );
$registrationIds = array( 'fqSwLMpUIWs:APA91bHNPw56Bna87Le49xIR43o4uKoWleeOeB0zA6elmqyBv9eeWDmep7i7FFNPtiXj2p2bPGQ_ei6NzxTqh5_DDP-3oDnDWPw6XJPtyPwv_VkeMzvfARG0CPoHnvQO16c7FfEFVuhu' );

$msg = array
(
	'title'		=> 'This is a title. title',
	'message' 	=> 'here is a message. message',
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
echo $result;


exit;*/

/*
function name: send_ios_notification
@param: deviceToken
@param: message
*/

/*
function send_ios_notification($deviceToken,$message)
{
	$passphrase = 'PushChat';
	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', 'development.pem');
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
	$fp = stream_socket_client(
		 'ssl://gateway.sandbox.push.apple.com:2195', $err,  // For development
		// 'ssl://gateway.push.apple.com:2195', $err, // for production
		$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

	if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

	$body['aps'] = array(
		'alert' => trim($message),
		'sound' => 'default'
	);
	
	$payload = json_encode($body);
	$msg = chr(0) . pack('n', 32) . pack('H*', trim($deviceToken)) . pack('n', strlen($payload)) . $payload;
	
	$result = fwrite($fp, $msg, strlen($msg));
	return $result;
	fclose($fp);
}

echo $deviceToken = '3bca645a81104e46c5c528dafed2a8b9eaadcb8aa8c29c62a5aa0608580ac099';
echo '<br/><br/><br/>Response : ';
$message = 'You are jjjjjjjjjjjjjjjjjjjjjjjjjjj Successfully.5555555555555555555555555';
$result = send_ios_notification($deviceToken,$message); 
echo $result;




https://gist.github.com/joashp/b2f6c7e24127f2798eb2


*/




		/*$passphrase = 'passphrase';
		$deviceToken = '3bca645a81104e46c5c528dafed2a8b9eaadcb8aa8c29c62a5aa0608580ac099';
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'development.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		$fp = stream_socket_client(
			'ssl://gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		$body['aps'] = array(
			'alert' => array(
			    'title' => 'Weiss Locks',
                'body' => 'gfdgfdhhhhhhhhhhhhhhhhhhhh999999999',
			 ),
			'sound' => 'default'
		);
		$payload = json_encode($body);
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		$result = fwrite($fp, $msg, strlen($msg));
		fclose($fp);
		echo $result;*/
		
		
		echo $deviceToken = '5426ec7c1f00c1eed0889f7a4ae7db54d0ac21cc3b8b4156045ec2ad00c9313e';
		echo '<br/><br/><br/><br/>';
		$ctx = stream_context_create();
		// ck.pem is your certificate file
		stream_context_set_option($ctx, 'ssl', 'local_cert', dirname(__FILE__) . '/production(1).pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		// Open a connection to the APNS server
		$fp = stream_socket_client(
			 'ssl://gateway.sandbox.push.apple.com:2195', $err,
			// 'gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);
		// Create the payload body
		$body['aps'] = array(
			'alert' => array(
			    'title' => 'iii5535',
                'body' => 'wsw11111111111111',
			 ),
			'sound' => 'default'
		);
		// Encode the payload as JSON
		$payload = json_encode($body);
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		
		// Close the connection to the server
		fclose($fp);
		if (!$result)
		{
			echo  'Message not delivered' . PHP_EOL;
		}
		else
		{
			echo 'Message successfully delivered' . PHP_EOL;
		}
		
		
		
?>