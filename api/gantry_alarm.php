<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
include(dirname(__FILE__).'/controller/SmsController.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

//define('UPLOAD_PATH', '/permit_to_enter/');
/*-----------------------------------
//For Initial create collection only

$qrcode = $app_data->createCollection("qrcode");
$msgs = $qrcode->find();

foreach ($msgs as $msg) {
    echo $msg['msg']."\n";
}
*/
// Controller Functions
$SmsController = new Sms365Controller;

$date_now = date('Y-m-d H:i:s');
$datetime_now = new DateTime();
$datetime_now = $datetime_now->format(DateTime::ATOM);

$response['datetime_now']=$datetime_now;

if($_REQUEST['action'] == 'test'){
	$response['status'] = 'false';
	$open_close = $_REQUEST['open_close'];
	$location = $_REQUEST['location'];
	if ($location == null){ 
		$location = "substation";
	}
	if ($open_close == null){ 
		$open_close = "close";
	}
	
	$collection_access = new MongoCollection($app_data, 'gantry_access');
	
	$criteria = array(
		'location' => $location,
		'open_close' => $open_close
	);
	
	$cursor_last_access = $collection_access->find($criteria)->sort( array('gantry_access_id' => -1) )->limit(1);
	//$cursor_last_access = $cursor_last_access->sort( array('gantry_access_id' => -1) )->limit(1);
	
	if($cursor_last_access->count() >= 1)
	{
		$response['status'] = 'true';
		foreach ( $cursor_last_access as $last_access)
		{
			unset($last_access['_id']);
			$last_acess_time = $last_access['time'];
			
			$response['last_access'] = $last_access;
		}
		
		$date = new DateTime( $last_acess_time );
		$date2 = new DateTime( $datetime_now );

		$diffInSeconds = $date2->getTimestamp() - $date->getTimestamp();

		$response['time_diff'] = $diffInSeconds;
		
		if ( $diffInSeconds > 2){
			$response['update_access'] = 'true';
		}
		else{
			$response['update_access'] = 'false';
		}
		
	}
	
	/*
	$collection_access = new MongoCollection($app_data, 'gantry_access');
	//$data_gantry_access = $app_data->gantry_access;
	
	//Insert access
	$post = array(
		'gantry_access_id' => getNext_users_Sequence('gantry_access_id'),			
		'location'  => $location,
		'open_close'     => $open_close,
		'time'  => $date_now
		);
	
	if ($collection_access->insert($post) ){
		$cursor = $collection_access->find(); // Temporarily find all access
		if($cursor->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor as $access)
				{
					unset($access['_id']);
					//This is encoded in json for qrcode reader to process
					$access_id = $access['gantry_access_id'];
					$response['data'] = json_encode($access);					
				}	
		}
		else{
			$response['status'] = 'false';
			$response['access_record'] = 'not found';
		}
	}
	*/
	/*
	$phone_number_365_2 = '6588688828'; // To Alan
				
	//$phone_number_twilio_list = array();
	$phone_number_365_list = array();
	
	$phone_number_365_list = array($phone_number_365_2);
	
	$SmsController->access_id = $access_id;
	$SmsController->open_close = $open_close;
	$SmsController->location = $location;
	
	//Set Phone number list to null for now
	$SmsController->phone_number_list=null;
	
	//SMS Gateway send
	$i =0;
	//$SmsController->sendSMSTwilio();
	foreach ( $phone_number_365_list as $phone_number){
		$SmsController->phone_number=$phone_number;
		$SmsController->sendSMS365();
		//$response['sms'][$i]['sms_url'] = $SmsController->sms_url;
		$response['sms'][$i]['sms_message_sent'] = $SmsController->sms_message_sent;
		$response['sms'][$i]['sms_message_result'] = $SmsController->sms_message_result;
		
		$i++;
	}
	*/
}

// Gantry Alarm
else if( $_REQUEST['action'] == 'gantry_access' && isset($_REQUEST['open_close']) && isset($_REQUEST['location']) ){
	$response['status'] = 'false';
	$open_close = (string) $_REQUEST['open_close'];
	$location = (string) $_REQUEST['location'];
	
	//$last_acess_time = null;
	
	//Check for last entry	
	$collection_access = new MongoCollection($app_data, 'gantry_access');
	
	$criteria = array(
		'location' => $location,
		'open_close' => $open_close
	);
	
	$cursor_last_access = $collection_access->find($criteria)->sort( array('gantry_access_id' => -1) )->limit(1);
	
	if($cursor_last_access->count() >= 1)
	{
		foreach ( $cursor_last_access as $last_access)
		{
			unset($last_access['_id']);
			$last_acess_time = $last_access['time'];
			
			$response['last_access'] = $last_access;
		}
		
		$date = new DateTime( $last_acess_time );
		$date2 = new DateTime( $datetime_now );

		$diffInSeconds = $date2->getTimestamp() - $date->getTimestamp();

		$response['time_diff'] = $diffInSeconds;
	}
	
	if ( $last_acess_time == null ){
		$diffInSeconds = 100;
	}
	
	$response['time_diff'] = $diffInSeconds;
		
	//Update Access if more than 2 seconds
	if ( $diffInSeconds > 2){
		$response['update_access'] = 'true';
		
		$collection_access_new = new MongoCollection($app_data, 'gantry_access');
		
		//Insert access
		$post = array(
			'gantry_access_id' => getNext_users_Sequence('gantry_access_id'),			
			'location'  => $location,
			'open_close'     => $open_close,
			'time'  => $datetime_now
			);
		
		if ($collection_access_new->insert($post) ){
			
			$response['status'] = 'true';
			
			$collection_access_latest = new MongoCollection($app_data, 'gantry_access');
			$criteria_now = array(
				'location' => $location,
				'open_close' => $open_close,
				'time' => $datetime_now
			);
			
			$cursor_last_update = $collection_access_latest->find($criteria_now)->sort( array('gantry_access_id' => -1) )->limit(1);
			
			if($cursor_last_update->count() > 0) { 
				foreach($cursor_last_update as $access)
					{
						unset($access['_id']);
						//This is encoded in json for qrcode reader to process
						$access_id = $access['gantry_access_id'];
						$response['new_data'] = json_encode($access);					
					}
			}
			else{
				$response['new_data'] = 'false';
			}

			//---- SMS
			//$phone_number_twilio_2 = '+6588688828'; // To Alan

			//$phone_number_365_1 = '+6599999999'; //To Others
			$phone_number_365_2 = '6588688828'; // To Alan
			
			//$phone_number_twilio_list = array();
			$phone_number_365_list = array();
			
			$phone_number_365_list = array($phone_number_365_2);
			
			$SmsController->access_id = $access_id;
			$SmsController->open_close = $open_close;
			$SmsController->location = $location;
			
			//Set Phone number list to null for now
			$SmsController->phone_number_list=null;
			
			//SMS Gateway send
			$i =0;
			//$SmsController->sendSMSTwilio();
			foreach ( $phone_number_365_list as $phone_number){
				$SmsController->phone_number=$phone_number;
				$SmsController->sendSMS365();
				$response['sms'][$i]['phone_number'] = $phone_number;
				//$response['sms'][$i]['sms_url'] = $SmsController->sms_url;
				$response['sms'][$i]['sms_message_sent'] = $SmsController->sms_message_sent;
				$response['sms'][$i]['sms_message_result'] = $SmsController->sms_message_result;
				
				$i++;
			}
		}
		else{
			$response['error'] = 'Error Inserting data';
		}
	}
	else{
		$response['update_access'] = 'false';
	}
}

/*
//Get Entry
if($_REQUEST['action'] == 'access' && isset($_REQUEST['permit_id']) && $_REQUEST['permit_id'] != '' && 
isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
isset($_REQUEST['company_ref_id']) && $_REQUEST['company_ref_id'] != '' ) 
{	
	$response['status'] = 'false';
	//Here
	$collection_permit = new MongoCollection($app_data, 'permit_to_enter');
	$permit_query = array( '$and' => array( array('permit_id' => (int)$_REQUEST['permit_id'] ), array('user_id'=>$_REQUEST['user_id']), array('company_ref_id'=>$_REQUEST['company_ref_id']) ) );
	$cursor_permit = $collection_permit->find( $permit_query );
	//If Permit is verified
	if($cursor_permit->count() > 0)
	{
		$collection = new MongoCollection($app_data, 'qrcode');
		//$qrcode_reg = $app_data->qrcode;
		$post = array(
			'qrcode_id' => getNext_users_Sequence('qrcode'),
			'permit_id'     => (int)$_REQUEST['permit_id'],
			'user_id'     => (int) $_REQUEST['user_id'],
			'user_name'     => $_REQUEST['user_name'],
			'role'  =>$_REQUEST['role'],
			'location'  =>$_REQUEST['location'],
			'access_in_out'   => $_REQUEST['access_in_out'],
			'access_time' => $_REQUEST['access_time'],
			'qrcode_time' => $_REQUEST['qrcode_time'],
			'valid_from'  => $_REQUEST['valid_from'],
			'valid_to'  => $_REQUEST['valid_to'],
			'company_ref_id'  => $_REQUEST['company_ref_id'],
			'count'  => 0,
			'time'  => date('d/m/Y H:i:s'),
			'token'  => $_REQUEST['token'],
			'visitor_company_name' => $user_company_name //For Visitor Only		
			);
		
		if ($collection->insert($post) ){
	
			$cursor = $collection->find();
			if($cursor->count() > 0) { 
				$response['status'] = 'true';
				foreach($cursor as $qrcode)
					{
						unset($qrcode['_id']);
						//This is encoded in json for qrcode reader to process
						$response['data'] = json_encode($qrcode);					
					}
			}
			else{
				$response['status'] = 'false';
			}
		}
	}
	else{
		$response['status'] = 'false';
	}
	//Encoded in json format again
	echo json_encode($response);
}


//Get Entry with limited try and time restricted
else if($_REQUEST['action'] == 'limited_access' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' ) 
{	
	$response['status'] = 'false';
	//Here
	$collection = new MongoCollection($app_data, 'qrcode');
	$Reg_Query = array( '$and' => array( array('permit_id' => $_REQUEST['permit_id'] ), array('user_id'=>(int)$_REQUEST['user_id']), array('token'=>$_REQUEST['token']) ) );
    $cursor = $collection->find( $Reg_Query );
	//If Already Have Entry
	if($cursor->count() > 0)
	{
		//$cursor->update( array('$inc' => array('count'=>1)) );
		//$collection->update( $cursor ,array('$inc' => array('count'=> -1))) );
		//$collection->update( $Reg_Query ,array(
		//	'$inc' => array(
		//			 'count'  => 1
		//	 )));
		foreach($cursor as $qrcode)
		{
			$count = $qrcode['count'];
		}
		
		$count = $count+1;
		
		//If not exceed 3 tries
		if ( $count <= 2 ){
		
			//Post Access with New Time
			//$qrcode_reg = $app_data->qrcode;
			$post = array(
				'qrcode_id' => getNext_users_Sequence('qrcode'),
				'permit_id'     => (int)$_REQUEST['permit_id'],
				'user_id'     => (int) $_REQUEST['user_id'],
				'user_name'     => $_REQUEST['user_name'],
				'role'  =>$_REQUEST['role'],
				'location'  =>$_REQUEST['location'],
				'access_in_out'   => $_REQUEST['access_in_out'],
				'access_time' => $_REQUEST['access_time'],
				'qrcode_time' => $_REQUEST['qrcode_time'],
				'valid_from'  => $_REQUEST['valid_from'],
				'valid_to'  => $_REQUEST['valid_to'],
				'company_ref_id'  => $_REQUEST['company_ref_id'],
				'count'  => $count,
				'time'  => date('d/m/Y H:i:s'),
				'token'  => $_REQUEST['token'],
				'visitor_company_name' => $user_company_name //For Visitor Only		
				);
				
			$collection->insert($post);	
			
			$cursor_2 = $collection->find(); // Temporarily find all qr code
			if($cursor_2->count() > 0) { 
				$response['status'] = 'true';
				foreach($cursor_2 as $qrcode2)
					{
						$response['data'] = json_encode($qrcode2);
					}
			}
			else{
				$response['status'] = 'false';
			}
		}
		else{
			$response['status'] = 'false';
			$response['error'] = 'limit_exceeded';
		}
		
	}
	//If No Entry
	else{
		$collection = new MongoCollection($app_data, 'qrcode');
		//$qrcode_reg = $app_data->qrcode;
		$post = array(
			'qrcode_id' => getNext_users_Sequence('qrcode'),
			'permit_id'     => (int)$_REQUEST['permit_id'],
			'user_id'     => (int) $_REQUEST['user_id'],
			'user_name'     => $_REQUEST['user_name'],
			'role'  =>$_REQUEST['role'],
			'location'  =>$_REQUEST['location'],
			'access_in_out'   => $_REQUEST['access_in_out'],
			'access_time' => $_REQUEST['access_time'],
			'qrcode_time' => $_REQUEST['qrcode_time'],
			'valid_from'  => $_REQUEST['valid_from'],
			'valid_to'  => $_REQUEST['valid_to'],
			'company_ref_id'  => $_REQUEST['company_ref_id'],
			'count'  => 0,
			'time'  => date('d/m/Y H:i:s'),
			'token'  => $_REQUEST['token'],
			'visitor_company_name' => $user_company_name //For Visitor Only		
			);
		$collection->insert($post);			
					
		$cursor = $collection->find();
		if($cursor->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor as $qrcode)
				{
					//This is encoded in json for qrcode reader to process
					$response['data'] = json_encode($qrcode);
				}
		}
		else{
			$response['status'] = 'false';
		}
	}
	//Encoded in json format again
	echo json_encode($response);
}
*/


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);


?>