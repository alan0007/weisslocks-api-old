<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

//----------------------------------
//Permit Registration
//----------------------------------
// for Visitor, Contractor
if($_REQUEST['action'] == 'add' && isset($_REQUEST['user_name']) )
{
	
    /*if (!filter_var($_REQUEST['user_email'], FILTER_VALIDATE_EMAIL))
    {
		$response['status'] = 'false';
			$response['error'] = 'Invalid Email Address';
		exit(json_encode($response));
    }*/
    
	// Check for company
	/*
	$collection = new MongoCollection($app_data, 'company');
    //$C_Query = array( 'company_ref_id' => $_REQUEST['company_ref_id'] );
	$Reg_Query = array( '$or' => array( array('company_ref_id' => $_REQUEST['company_ref_id'] ), array('company_id'=>(int)$_REQUEST['company_id']) ) );
    $cursor = $collection->find( $Reg_Query );
    if($cursor->count() == 1)
    {
		foreach($cursor as $companies)
		{
			$company_ID = $companies['company_ID'];
		}
	}
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Invalid Company Ref. ID';
		exit(json_encode($response));
	}
	*/
	
	$collection = new MongoCollection($app_data, 'users');
    $Reg_Query = array( '$or' => array( array('username' => $_REQUEST['host_name'] ), array('email'=>$_REQUEST['host_email_phone']), array('phone_number'=>$_REQUEST['host_email_phone'])  ) );
    $cursor = $collection->find( $Reg_Query );
	if($cursor->count() == 1)
    {
		$response['status'] = 'true';
    }
	else{
		$response['status'] = 'false';
        $response['error'] = 'User Does Not Exists...';
	}
	
	//$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) 
	
    //$collection = new MongoCollection($app_data, 'users');
    //$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) );
    //$cursor = $collection->find( $Reg_Query );
	
	// For PTE
	$collection = new MongoCollection($app_data, 'permit_to_enter');
	
		$response['status'] = 'true';
		//$phone_number = isset($_REQUEST['user_phone']) ? $_REQUEST['user_phone'] : '';
		//$user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : 5;
		//$UDID_IOS = isset($_REQUEST['UDID_IOS']) ? $_REQUEST['UDID_IOS'] : '';
		
		$permit_to_enter = $app_data->permit_to_enter;
		
		$self_host = isset($_REQUEST['self_host']) ? $_REQUEST['self_host'] : 'No'; //Default is Not Self Host
		
		$post = array(
			'permit_id' => getNext_users_Sequence('permit_to_enter'),
			'user_id'     => $_REQUEST['user_id'],
			'username'     => $_REQUEST['user_name'], //request is user_name, key is username
			'visitor_company_name'     => $_REQUEST['visitor_company_name'],
			'company_id'     => $_REQUEST['company_id'],
			'company_ref_id'     => $_REQUEST['company_ref_id'],
			'role'     => $_REQUEST['role'],
			'location'   => $_REQUEST['location'],
			'host_name' => $_REQUEST['host_name'],
			'host_email_phone' => $_REQUEST['host_email_phone'],
			'date_from' => $_REQUEST['date_from'],
			'date_to' => $_REQUEST['date_to'],
			'time_from' => $_REQUEST['time_from'],
			'time_to' => $_REQUEST['time_to'],
			
			//For accompany Person No.1
			'pte_user_name_data' => $_REQUEST['pte_user_name_data'],
			'pte_user_email_data' => $_REQUEST['pte_user_email_data'],
			'pte_user_phone_data' => $_REQUEST['pte_user_phone_data'],
			'pte_company_name_data' => $_REQUEST['pte_company_name_data'],
			'pte_company_position_data' => $_REQUEST['pte_company_position_data'],
			'pte_user_message_data' => $_REQUEST['pte_user_message_data'],
			'pte_user_image_name_data' => $_REQUEST['pte_user_image_name_data'], //Image File Name
			
			//For NRIC & Phone Number validation Record
			'nric'  => $_REQUEST['nric'], //NRIC
			'foreigner_ic'  => $_REQUEST['foreigner_ic'], //Foreigner NRIC
			'phone_number_valid' => 0, //For Phone OTP	
			
			//For Self Host (Staff Only)
			'self_host' => $self_host, //Default is Not Self Host
			
			'registered_time'  => date('d F Y, H:i'),
			//Set to auto approve for now
			'approved' => 1,
			'subadmin_approved' => 0,
			'admin_approved' => 0
			
			);
			
		if($permit_to_enter->insert($post))
		{
			
			$Reg_Query = array('_id' => $post['_id'] ) ;
			$cursor = $collection->find( $Reg_Query ); 
			
			foreach($cursor as $ff)
			{
				unset($ff['_id']);
				$response['data'] = $ff;
				$permit_id = $ff['permit_id'];
				$user_id = $ff['user_id'];
				$username = $ff['username'];
				$location = $ff['location'];
				$host_name = $ff['host_name'];
				$host_email_phone = $ff['host_email_phone'];
				$date_from = $ff['date_from'];
				$date_to = $ff['date_to'];
				$time_from = $ff['time_from'];
				$time_to = $ff['time_to'];
				
			}
	
			/*$permit_id = $permit_id;
			$collection1 = new MongoCollection($app_data, 'company');
			$Reg_Query1 = array( 'company_ID'=>(int) $company_ID);
			$cursor1 = $collection1->find( $Reg_Query1 );
			if($cursor1->count() == 1)
			{
				foreach($cursor1 as $kk)
				{
					$user_ids = json_decode($kk['user_id']);
					$user_ids[] = $user_id;
					$collection2 = new MongoCollection($app_data, 'company');
					$criteria2 = array('company_ID'=>(int) $company_ID);
					 $collection2->update( $criteria2 ,array('$set' => array(
							 'user_id'  => json_encode($user_ids)
					 )));
				}
				
			}
			*/
		//Email in development
		/*
		
						// Mail to Admins Start
						$state = 'all';
						$collection = new MongoCollection($app_data, 'settings');
						$settings = $collection->find(array('company_id'=>(int)$company_ID));
						foreach($settings as $setting)
							{
								$state = $setting['send_notification'];
								$mailed_users = $setting['users'];
							}
						require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = 'smtp.gmail.com';
						$mail->Port = 587;
						$mail->SMTPSecure = 'tls';
						$mail->SMTPAuth = true;
						$mail->Username = "sendweisslocks@gmail.com";
						$mail->Password = "AppRegistration";
						$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
						if($state == 'all')
						{
							$collection5 = new MongoCollection($app_data, 'company');
							$company_users = $collection5->find(array('company_ID'=>(int)$company_ID));
							foreach($company_users as $company_user)
								{
									$mailed_users = $company_user['user_id'];
									$mail_user = json_decode($mailed_users);
									for($i=0;$i<=count($mail_user);$i++)
									{
										$collection_user = new MongoCollection($app_data, 'users');
										$permit_to_enter = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
										if($permit_to_enter->count() > 0) {
											foreach($permit_to_enter as $permit_to_enter)
											{
												if($user['role'] >= 1 && $user['role'] <= 3)
												{
													$mail->addAddress( $user['email'] );
													if($user['device_name'] == 1)
													{
														$device_id_admin = $user['device_id'];
														$passphrase = 'IOSPUSH';
														$deviceToken = $device_id_admin;
														$ctx = stream_context_create();
														stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
														stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
														$fp = stream_socket_client(
															'ssl://gateway.sandbox.push.apple.com:2195', $err,
															$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
														$body['aps'] = array(
															'alert' => array(
																'title' => 'Weiss Locks',
																'body' => 'New Registration Added',
															 ),
															'sound' => 'default'
														);
														$payload = json_encode($body);
														$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
														$result = fwrite($fp, $msg, strlen($msg));
														fclose($fp);
													}
													
													if($user['device_name'] == 2)
													{
														$device_id_admin = $user['device_id'];
														$registrationIds = array( $device_id_admin );
														$msg = array
														(
															'title'		=> 'Weiss Locks',
															'message' 	=> 'New Registration Added',
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
											}
										}
									}
								}
						}
						else if($state == 'Custom')
						{
							$mail_user = json_decode($mailed_users);
							$collection_user = new MongoCollection($app_data, 'users');
							for($i=0;$i<=count($mail_user);$i++)
							{
								$permit_to_enter = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
								if($permit_to_enter->count() > 0) {
									foreach($permit_to_enter as $permit_to_enter)
									{
										$mail->addAddress( $user['email'] );
										
										if($user['device_name'] == 1)
										{
											$passphrase = 'IOSPUSH';
											$deviceToken = $user['device_id'];
											$ctx = stream_context_create();
											stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
											stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
											$fp = stream_socket_client(
												'ssl://gateway.sandbox.push.apple.com:2195', $err,
												$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
											$body['aps'] = array(
												'alert' => array(
													'title' => 'Weiss Locks',
													'body' => 'New Registration Added',
												 ),
												'sound' => 'default'
											);
											$payload = json_encode($body);
											$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
											$result = fwrite($fp, $msg, strlen($msg));
											fclose($fp);
											
										}
										
										
										
										if($user['device_name'] == 2)
										{
											$device_id_admin = $user['device_id'];
											$registrationIds = array( $device_id_admin );
											$msg = array
											(
												'title'		=> 'Weiss Locks',
												'message' 	=> 'New Registration Added',
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
								}
							}
						}
						$mail->Subject = 'Weiss Locks - New Registration Added';
						$mail->msgHTML('
						Dear Candidate,
						<br/><br/>
						New User Registered In the System. Details are As Follows...
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Admins End
			
						// Mail to Users Start
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = 'smtp.gmail.com';
						$mail->Port = 587;
						$mail->SMTPSecure = 'tls';
						$mail->SMTPAuth = true;
						$mail->Username = "sendweisslocks@gmail.com";
						$mail->Password = "AppRegistration";
						$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
						$mail->addAddress( $_REQUEST['user_email'] );
						$mail->Subject = 'Weiss Locks - Successfully Registered';
						$mail->msgHTML('
						Dear Candidate,
						<br/><br/>
						You are successfully Registered.
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Users Email
						// Send Notifications Start
						
						// For IOS
						if($device_name == 1)
						{
							$passphrase = 'IOSPUSH';
							$deviceToken = $device_id;
							$ctx = stream_context_create();
							stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
							stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
							$fp = stream_socket_client(
								'ssl://gateway.sandbox.push.apple.com:2195', $err,
								$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
							$body['aps'] = array(
								'alert' => array(
									'title' => 'Weiss Locks',
									'body' => 'You Are Successfully Registered',
								 ),
								'sound' => 'default'
							);
							$payload = json_encode($body);
							$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
							$result = fwrite($fp, $msg, strlen($msg));
							fclose($fp);
						}
						
						// For Andriod
						if($device_name == 2)
						{
							$registrationIds = array( $device_id );
							$msg = array
							(
								'title'		=> 'Weiss Locks',
								'message' 	=> 'You Are Successfully Registered',
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
						
						*/
		}
	
}
// End Permit Registration for Visitor, Contractor

// For Staff Only - added for Self Host Registration (Without Host Approval) 
//- Incomplete
if($_REQUEST['action'] == 'add_staff' && isset($_REQUEST['user_name']) )
{
	
    /*if (!filter_var($_REQUEST['user_email'], FILTER_VALIDATE_EMAIL))
    {
		$response['status'] = 'false';
			$response['error'] = 'Invalid Email Address';
		exit(json_encode($response));
    }*/
    
	// Check for company
	/*
	$collection = new MongoCollection($app_data, 'company');
    //$C_Query = array( 'company_ref_id' => $_REQUEST['company_ref_id'] );
	$Reg_Query = array( '$or' => array( array('company_ref_id' => $_REQUEST['company_ref_id'] ), array('company_id'=>(int)$_REQUEST['company_id']) ) );
    $cursor = $collection->find( $Reg_Query );
    if($cursor->count() == 1)
    {
		foreach($cursor as $companies)
		{
			$company_ID = $companies['company_ID'];
		}
	}
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Invalid Company Ref. ID';
		exit(json_encode($response));
	}
	*/
	
	$collection = new MongoCollection($app_data, 'users');
    $Reg_Query = array( '$or' => array( array('username' => $_REQUEST['host_name'] ), array('email'=>$_REQUEST['host_email_phone']), array('phone_number'=>$_REQUEST['host_email_phone'])  ) );
    $cursor = $collection->find( $Reg_Query );
	if($cursor->count() == 1)
    {
		$response['status'] = 'true';
    }
	else{
		$response['status'] = 'false';
        $response['error'] = 'User Does Not Exists...';
	}
	
	//$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) 
	
    //$collection = new MongoCollection($app_data, 'users');
    //$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) );
    //$cursor = $collection->find( $Reg_Query );
	
	// For PTE
	$collection = new MongoCollection($app_data, 'permit_to_enter');
	
		$response['status'] = 'true';
		//$phone_number = isset($_REQUEST['user_phone']) ? $_REQUEST['user_phone'] : '';
		//$user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : 5;
		//$UDID_IOS = isset($_REQUEST['UDID_IOS']) ? $_REQUEST['UDID_IOS'] : '';
		
		$permit_to_enter = $app_data->permit_to_enter;
		$post = array(
			'permit_id' => getNext_users_Sequence('permit_to_enter'),
			'user_id'     => $_REQUEST['user_id'],
			'username'     => $_REQUEST['user_name'], //request is user_name, key is username
			'visitor_company_name'     => $_REQUEST['visitor_company_name'],
			'company_id'     => $_REQUEST['company_id'],
			'company_ref_id'     => $_REQUEST['company_ref_id'],
			'role'     => $_REQUEST['role'],
			'location'   => $_REQUEST['location'],
			'host_name' => $_REQUEST['host_name'],
			'host_email_phone' => $_REQUEST['host_email_phone'],
			'date_from' => $_REQUEST['date_from'],
			'date_to' => $_REQUEST['date_to'],
			'time_from' => $_REQUEST['time_from'],
			'time_to' => $_REQUEST['time_to'],
			
			//For accompany Person No.1
			'pte_user_name_data' => $_REQUEST['pte_user_name_data'],
			'pte_user_email_data' => $_REQUEST['pte_user_email_data'],
			'pte_user_phone_data' => $_REQUEST['pte_user_phone_data'],
			'pte_company_name_data' => $_REQUEST['pte_company_name_data'],
			'pte_company_position_data' => $_REQUEST['pte_company_position_data'],
			'pte_user_message_data' => $_REQUEST['pte_user_message_data'],
			'pte_user_image_name_data' => $_REQUEST['pte_user_image_name_data'], //Image File Name
			
			//For NRIC & Phone Number validation Record
			'nric'  => $_REQUEST['nric'], //NRIC
			'foreigner_ic'  => $_REQUEST['foreigner_ic'], //Foreigner NRIC
			'phone_number_valid' => 0, //For Phone OTP	
			
			//For Self Host (Staff Only)
			'self_host' => 'No',	
			
			'registered_time'  => date('d F Y, H:i'),
			'approved' => 0,
			'subadmin_approved' => 0,
			'admin_approved' => 0
			
			);
			
		if($permit_to_enter->insert($post))
		{
			
			$Reg_Query = array('_id' => $post['_id'] ) ;
			$cursor = $collection->find( $Reg_Query ); 
			
			foreach($cursor as $ff)
			{
				unset($ff['_id']);
				$response['data'] = $ff;
				$permit_id = $ff['permit_id'];
				$user_id = $ff['user_id'];
				$username = $ff['username'];
				$location = $ff['location'];
				$host_name = $ff['host_name'];
				$host_email_phone = $ff['host_email_phone'];
				$date_from = $ff['date_from'];
				$date_to = $ff['date_to'];
				$time_from = $ff['time_from'];
				$time_to = $ff['time_to'];
				
			}
	
			/*$permit_id = $permit_id;
			$collection1 = new MongoCollection($app_data, 'company');
			$Reg_Query1 = array( 'company_ID'=>(int) $company_ID);
			$cursor1 = $collection1->find( $Reg_Query1 );
			if($cursor1->count() == 1)
			{
				foreach($cursor1 as $kk)
				{
					$user_ids = json_decode($kk['user_id']);
					$user_ids[] = $user_id;
					$collection2 = new MongoCollection($app_data, 'company');
					$criteria2 = array('company_ID'=>(int) $company_ID);
					 $collection2->update( $criteria2 ,array('$set' => array(
							 'user_id'  => json_encode($user_ids)
					 )));
				}
				
			}
			*/
		//Email in development
		/*
		
						// Mail to Admins Start
						$state = 'all';
						$collection = new MongoCollection($app_data, 'settings');
						$settings = $collection->find(array('company_id'=>(int)$company_ID));
						foreach($settings as $setting)
							{
								$state = $setting['send_notification'];
								$mailed_users = $setting['users'];
							}
						require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = 'smtp.gmail.com';
						$mail->Port = 587;
						$mail->SMTPSecure = 'tls';
						$mail->SMTPAuth = true;
						$mail->Username = "sendweisslocks@gmail.com";
						$mail->Password = "AppRegistration";
						$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
						if($state == 'all')
						{
							$collection5 = new MongoCollection($app_data, 'company');
							$company_users = $collection5->find(array('company_ID'=>(int)$company_ID));
							foreach($company_users as $company_user)
								{
									$mailed_users = $company_user['user_id'];
									$mail_user = json_decode($mailed_users);
									for($i=0;$i<=count($mail_user);$i++)
									{
										$collection_user = new MongoCollection($app_data, 'users');
										$permit_to_enter = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
										if($permit_to_enter->count() > 0) {
											foreach($permit_to_enter as $permit_to_enter)
											{
												if($user['role'] >= 1 && $user['role'] <= 3)
												{
													$mail->addAddress( $user['email'] );
													if($user['device_name'] == 1)
													{
														$device_id_admin = $user['device_id'];
														$passphrase = 'IOSPUSH';
														$deviceToken = $device_id_admin;
														$ctx = stream_context_create();
														stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
														stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
														$fp = stream_socket_client(
															'ssl://gateway.sandbox.push.apple.com:2195', $err,
															$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
														$body['aps'] = array(
															'alert' => array(
																'title' => 'Weiss Locks',
																'body' => 'New Registration Added',
															 ),
															'sound' => 'default'
														);
														$payload = json_encode($body);
														$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
														$result = fwrite($fp, $msg, strlen($msg));
														fclose($fp);
													}
													
													if($user['device_name'] == 2)
													{
														$device_id_admin = $user['device_id'];
														$registrationIds = array( $device_id_admin );
														$msg = array
														(
															'title'		=> 'Weiss Locks',
															'message' 	=> 'New Registration Added',
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
											}
										}
									}
								}
						}
						else if($state == 'Custom')
						{
							$mail_user = json_decode($mailed_users);
							$collection_user = new MongoCollection($app_data, 'users');
							for($i=0;$i<=count($mail_user);$i++)
							{
								$permit_to_enter = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
								if($permit_to_enter->count() > 0) {
									foreach($permit_to_enter as $permit_to_enter)
									{
										$mail->addAddress( $user['email'] );
										
										if($user['device_name'] == 1)
										{
											$passphrase = 'IOSPUSH';
											$deviceToken = $user['device_id'];
											$ctx = stream_context_create();
											stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
											stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
											$fp = stream_socket_client(
												'ssl://gateway.sandbox.push.apple.com:2195', $err,
												$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
											$body['aps'] = array(
												'alert' => array(
													'title' => 'Weiss Locks',
													'body' => 'New Registration Added',
												 ),
												'sound' => 'default'
											);
											$payload = json_encode($body);
											$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
											$result = fwrite($fp, $msg, strlen($msg));
											fclose($fp);
											
										}
										
										
										
										if($user['device_name'] == 2)
										{
											$device_id_admin = $user['device_id'];
											$registrationIds = array( $device_id_admin );
											$msg = array
											(
												'title'		=> 'Weiss Locks',
												'message' 	=> 'New Registration Added',
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
								}
							}
						}
						$mail->Subject = 'Weiss Locks - New Registration Added';
						$mail->msgHTML('
						Dear Candidate,
						<br/><br/>
						New User Registered In the System. Details are As Follows...
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Admins End
			
						// Mail to Users Start
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = 'smtp.gmail.com';
						$mail->Port = 587;
						$mail->SMTPSecure = 'tls';
						$mail->SMTPAuth = true;
						$mail->Username = "sendweisslocks@gmail.com";
						$mail->Password = "AppRegistration";
						$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
						$mail->addAddress( $_REQUEST['user_email'] );
						$mail->Subject = 'Weiss Locks - Successfully Registered';
						$mail->msgHTML('
						Dear Candidate,
						<br/><br/>
						You are successfully Registered.
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Users Email
						// Send Notifications Start
						
						// For IOS
						if($device_name == 1)
						{
							$passphrase = 'IOSPUSH';
							$deviceToken = $device_id;
							$ctx = stream_context_create();
							stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
							stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
							$fp = stream_socket_client(
								'ssl://gateway.sandbox.push.apple.com:2195', $err,
								$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
							$body['aps'] = array(
								'alert' => array(
									'title' => 'Weiss Locks',
									'body' => 'You Are Successfully Registered',
								 ),
								'sound' => 'default'
							);
							$payload = json_encode($body);
							$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
							$result = fwrite($fp, $msg, strlen($msg));
							fclose($fp);
						}
						
						// For Andriod
						if($device_name == 2)
						{
							$registrationIds = array( $device_id );
							$msg = array
							(
								'title'		=> 'Weiss Locks',
								'message' 	=> 'You Are Successfully Registered',
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
						
						*/
		}
	
}

// For Kiosk Registration Only
if($_REQUEST['action'] == 'add_kiosk' && isset($_REQUEST['user_name']) )
{
	
    /*if (!filter_var($_REQUEST['user_email'], FILTER_VALIDATE_EMAIL))
    {
		$response['status'] = 'false';
			$response['error'] = 'Invalid Email Address';
		exit(json_encode($response));
    }*/
    
	// Check for company
	/*
	$collection = new MongoCollection($app_data, 'company');
    //$C_Query = array( 'company_ref_id' => $_REQUEST['company_ref_id'] );
	$Reg_Query = array( '$or' => array( array('company_ref_id' => $_REQUEST['company_ref_id'] ), array('company_id'=>(int)$_REQUEST['company_id']) ) );
    $cursor = $collection->find( $Reg_Query );
    if($cursor->count() == 1)
    {
		foreach($cursor as $companies)
		{
			$company_ID = $companies['company_ID'];
		}
	}
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Invalid Company Ref. ID';
		exit(json_encode($response));
	}
	*/
	
	$collection = new MongoCollection($app_data, 'users');
    $Reg_Query = array( '$or' => array( array('username' => $_REQUEST['host_name'] ), array('email'=>$_REQUEST['host_email_phone']), array('phone_number'=>$_REQUEST['host_email_phone'])  ) );
    $cursor = $collection->find( $Reg_Query );
	if($cursor->count() == 1)
    {
		$response['status'] = 'true';
    }
	else{
		$response['status'] = 'false';
        $response['error'] = 'User Does Not Exists...';
	}
	
	//$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) 
	
    //$collection = new MongoCollection($app_data, 'users');
    //$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) );
    //$cursor = $collection->find( $Reg_Query );
	
	// For PTE
	$collection = new MongoCollection($app_data, 'permit_to_enter');
	
		$response['status'] = 'true';
		//$phone_number = isset($_REQUEST['user_phone']) ? $_REQUEST['user_phone'] : '';
		//$user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : 5;
		//$UDID_IOS = isset($_REQUEST['UDID_IOS']) ? $_REQUEST['UDID_IOS'] : '';
		
		$permit_to_enter = $app_data->permit_to_enter;
		$post = array(
			'permit_id' => getNext_users_Sequence('permit_to_enter'),
			'user_id'     => $_REQUEST['user_id'],
			'username'     => $_REQUEST['user_name'], //request is user_name, key is username
			'visitor_company_name'     => $_REQUEST['visitor_company_name'],
			'company_id'     => $_REQUEST['company_id'],
			'company_ref_id'     => $_REQUEST['company_ref_id'],
			'role'     => $_REQUEST['role'],
			'location'   => $_REQUEST['location'],
			'host_name' => $_REQUEST['host_name'],
			'host_email_phone' => $_REQUEST['host_email_phone'],
			'date_from' => $_REQUEST['date_from'],
			'date_to' => $_REQUEST['date_to'],
			'time_from' => $_REQUEST['time_from'],
			'time_to' => $_REQUEST['time_to'],
			
			//For accompany Person No.1
			'pte_user_name_data' => $_REQUEST['pte_user_name_data'],
			'pte_user_email_data' => $_REQUEST['pte_user_email_data'],
			'pte_user_phone_data' => $_REQUEST['pte_user_phone_data'],
			'pte_company_name_data' => $_REQUEST['pte_company_name_data'],
			'pte_company_position_data' => $_REQUEST['pte_company_position_data'],
			'pte_user_message_data' => $_REQUEST['pte_user_message_data'],
			'pte_user_image_name_data' => $_REQUEST['pte_user_image_name_data'], //Image File Name
			
			//For NRIC & Phone Number validation Record
			'nric'  => $_REQUEST['nric'], //NRIC
			'foreigner_ic'  => $_REQUEST['foreigner_ic'], //Foreigner NRIC
			'phone_number_valid' => 0, //For Phone OTP

			//For Self Host (Staff Only)
			'self_host' => 'No',			
			
			'registered_time'  => date('d F Y, H:i'),
			'approved' => 0,
			'subadmin_approved' => 0,
			'admin_approved' => 0,
			
			//For Visitor
			'visitor_name'  => $_REQUEST['visitor_name'], //NRIC
			'visitor_residence'  => $_REQUEST['visitor_residence'],
			'visitor_nric'  => $_REQUEST['visitor_nric'],
			'visitor_foreigner_ic'  => $_REQUEST['visitor_foreigner_ic'],
			'visitor_email'  => $_REQUEST['visitor_email'],
			'visitor_company_name'  => $_REQUEST['visitor_company_name'],
			'visitor_company_position'  => $_REQUEST['visitor_company_position'],
			'visitor_phone_number'  => $_REQUEST['visitor_phone_number'],
			'visitor_image_name'  => $_REQUEST['visitor_image_name']
			);
			
		if($permit_to_enter->insert($post))
		{
			
			$Reg_Query = array('_id' => $post['_id'] ) ;
			$cursor = $collection->find( $Reg_Query ); 
			
			foreach($cursor as $ff)
			{
				unset($ff['_id']);
				$response['data'] = $ff;
				$permit_id = $ff['permit_id'];
				$user_id = $ff['user_id'];
				$username = $ff['username'];
				$location = $ff['location'];
				$host_name = $ff['host_name'];
				$host_email_phone = $ff['host_email_phone'];
				$date_from = $ff['date_from'];
				$date_to = $ff['date_to'];
				$time_from = $ff['time_from'];
				$time_to = $ff['time_to'];
				
			}
	
			/*$permit_id = $permit_id;
			$collection1 = new MongoCollection($app_data, 'company');
			$Reg_Query1 = array( 'company_ID'=>(int) $company_ID);
			$cursor1 = $collection1->find( $Reg_Query1 );
			if($cursor1->count() == 1)
			{
				foreach($cursor1 as $kk)
				{
					$user_ids = json_decode($kk['user_id']);
					$user_ids[] = $user_id;
					$collection2 = new MongoCollection($app_data, 'company');
					$criteria2 = array('company_ID'=>(int) $company_ID);
					 $collection2->update( $criteria2 ,array('$set' => array(
							 'user_id'  => json_encode($user_ids)
					 )));
				}
				
			}
			*/
		//Email in development
		/*
		
						// Mail to Admins Start
						$state = 'all';
						$collection = new MongoCollection($app_data, 'settings');
						$settings = $collection->find(array('company_id'=>(int)$company_ID));
						foreach($settings as $setting)
							{
								$state = $setting['send_notification'];
								$mailed_users = $setting['users'];
							}
						require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = 'smtp.gmail.com';
						$mail->Port = 587;
						$mail->SMTPSecure = 'tls';
						$mail->SMTPAuth = true;
						$mail->Username = "sendweisslocks@gmail.com";
						$mail->Password = "AppRegistration";
						$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
						if($state == 'all')
						{
							$collection5 = new MongoCollection($app_data, 'company');
							$company_users = $collection5->find(array('company_ID'=>(int)$company_ID));
							foreach($company_users as $company_user)
								{
									$mailed_users = $company_user['user_id'];
									$mail_user = json_decode($mailed_users);
									for($i=0;$i<=count($mail_user);$i++)
									{
										$collection_user = new MongoCollection($app_data, 'users');
										$permit_to_enter = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
										if($permit_to_enter->count() > 0) {
											foreach($permit_to_enter as $permit_to_enter)
											{
												if($user['role'] >= 1 && $user['role'] <= 3)
												{
													$mail->addAddress( $user['email'] );
													if($user['device_name'] == 1)
													{
														$device_id_admin = $user['device_id'];
														$passphrase = 'IOSPUSH';
														$deviceToken = $device_id_admin;
														$ctx = stream_context_create();
														stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
														stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
														$fp = stream_socket_client(
															'ssl://gateway.sandbox.push.apple.com:2195', $err,
															$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
														$body['aps'] = array(
															'alert' => array(
																'title' => 'Weiss Locks',
																'body' => 'New Registration Added',
															 ),
															'sound' => 'default'
														);
														$payload = json_encode($body);
														$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
														$result = fwrite($fp, $msg, strlen($msg));
														fclose($fp);
													}
													
													if($user['device_name'] == 2)
													{
														$device_id_admin = $user['device_id'];
														$registrationIds = array( $device_id_admin );
														$msg = array
														(
															'title'		=> 'Weiss Locks',
															'message' 	=> 'New Registration Added',
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
											}
										}
									}
								}
						}
						else if($state == 'Custom')
						{
							$mail_user = json_decode($mailed_users);
							$collection_user = new MongoCollection($app_data, 'users');
							for($i=0;$i<=count($mail_user);$i++)
							{
								$permit_to_enter = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
								if($permit_to_enter->count() > 0) {
									foreach($permit_to_enter as $permit_to_enter)
									{
										$mail->addAddress( $user['email'] );
										
										if($user['device_name'] == 1)
										{
											$passphrase = 'IOSPUSH';
											$deviceToken = $user['device_id'];
											$ctx = stream_context_create();
											stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
											stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
											$fp = stream_socket_client(
												'ssl://gateway.sandbox.push.apple.com:2195', $err,
												$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
											$body['aps'] = array(
												'alert' => array(
													'title' => 'Weiss Locks',
													'body' => 'New Registration Added',
												 ),
												'sound' => 'default'
											);
											$payload = json_encode($body);
											$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
											$result = fwrite($fp, $msg, strlen($msg));
											fclose($fp);
											
										}
										
										
										
										if($user['device_name'] == 2)
										{
											$device_id_admin = $user['device_id'];
											$registrationIds = array( $device_id_admin );
											$msg = array
											(
												'title'		=> 'Weiss Locks',
												'message' 	=> 'New Registration Added',
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
								}
							}
						}
						$mail->Subject = 'Weiss Locks - New Registration Added';
						$mail->msgHTML('
						Dear Candidate,
						<br/><br/>
						New User Registered In the System. Details are As Follows...
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Admins End
			
						// Mail to Users Start
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = 'smtp.gmail.com';
						$mail->Port = 587;
						$mail->SMTPSecure = 'tls';
						$mail->SMTPAuth = true;
						$mail->Username = "sendweisslocks@gmail.com";
						$mail->Password = "AppRegistration";
						$mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
						$mail->addAddress( $_REQUEST['user_email'] );
						$mail->Subject = 'Weiss Locks - Successfully Registered';
						$mail->msgHTML('
						Dear Candidate,
						<br/><br/>
						You are successfully Registered.
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Users Email
						// Send Notifications Start
						
						// For IOS
						if($device_name == 1)
						{
							$passphrase = 'IOSPUSH';
							$deviceToken = $device_id;
							$ctx = stream_context_create();
							stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
							stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
							$fp = stream_socket_client(
								'ssl://gateway.sandbox.push.apple.com:2195', $err,
								$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
							$body['aps'] = array(
								'alert' => array(
									'title' => 'Weiss Locks',
									'body' => 'You Are Successfully Registered',
								 ),
								'sound' => 'default'
							);
							$payload = json_encode($body);
							$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
							$result = fwrite($fp, $msg, strlen($msg));
							fclose($fp);
						}
						
						// For Andriod
						if($device_name == 2)
						{
							$registrationIds = array( $device_id );
							$msg = array
							(
								'title'		=> 'Weiss Locks',
								'message' 	=> 'You Are Successfully Registered',
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
						
						*/
		}
	
}

//----------------------------------
//view
//----------------------------------
//Default for SP: http://app.weisslocks.com/api/permit_to_enter.php?action=view&user_id=331&company_id=25
else if( $_REQUEST['action'] == 'view' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && isset($_REQUEST['company_id']) )
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1)
	{
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		$permit_to_enter = $collection->find();
		
		if($permit_to_enter->count() > 0) { 
			$response['status'] = 'true';
			foreach($permit_to_enter as $permit_to_enter)
			{
				$permit_id = $permit_to_enter['permit_id'];	
				
					//$pair_users_arr = array();
					//$pair_users = $access_permit['user_id'];
					
					/*
					for($u=0;$u<=count($pair_users);$u++)
					{
						$users_d = new MongoCollection($app_data, 'users');
						$users_details = $users_d->findOne(array('user_id'=>(int)$pair_users[$u]));
						if(isset($users_details['user_id']))
						{
							unset($users_details['_id']);
							$pair_users_arr[] = $users_details;
						}
					}
					*/
					//$from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
					//$to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';

					/*
					$response['data']['pairing'][] = array(
						'permit_id'=>$permit_id,
						'user_id'=>$permit_to_enter['user_id'],
						'username'=>$permit_to_enter['username'],
						'company_id'=>$permit_to_enter['company_id'],
						'company_ref_id'=>$permit_to_enter['company_ref_id'],
						'role'=>$permit_to_enter['role'],
						'location'=>$permit_to_enter['location'],
						'host_name'=>$permit_to_enter['host_name'],
						'host_email_phone'=>$permit_to_enter['host_email_phone'],
						'date_from'=>$permit_to_enter['date_from'],
						'date_to'=>$permit_to_enter['date_to'],
						'time_from'=>$permit_to_enter['time_from'],
						'time_to'=>$permit_to_enter['time_to'],
						// Accompany Person Data
						'pte_user_name_data'=>$permit_to_enter['pte_user_name_data'],
						'pte_user_email_data'=>$permit_to_enter['pte_user_email_data'],
						'pte_user_phone_data'=>$permit_to_enter['pte_user_phone_data'],
						'pte_company_name_data'=>$permit_to_enter['pte_company_name_data'],
						'pte_company_position_data'=>$permit_to_enter['pte_company_position_data'],
						'pte_user_message_data'=>$permit_to_enter['pte_user_message_data'],
						'pte_user_image_name_data'=>$permit_to_enter['pte_user_image_name_data'],
						
						
						'registered_time'=>$permit_to_enter['registered_time'],
						'approved'=>$permit_to_enter['approved'],
						'subadmin_approved'=>$permit_to_enter['subadmin_approved'],
						'admin_approved'=>$permit_to_enter['admin_approved']
					*/
				
				if($permit_to_enter['permit_id'] == $_REQUEST['permit_id'])
				{
					$response['current_permit'][] = $permit_to_enter;
				}
				if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
				{
					
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
					echo $since_start->days.' days total -- ';
					echo $since_start->y.' years -- ';
					echo $since_start->m.' months -- ';
					echo $since_start->d.' days -- ';
					echo $since_start->h.' hours -- ';
					echo $since_start->i.' minutes -- ';
					echo $since_start->s.' seconds---';
					echo '<br>';*/
					
					if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
					{
						$permit_to_enter['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$permit_to_enter['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
						//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
					}
					else { $permit_to_enter['duration'] = '--/--'; }
					$response['data'][] = $permit_to_enter;
				}
			}
		}
    }
	else
	{
		//331
		
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		//$C_Query = array( 'company_id' => $company_ID );
		//$cursor_pte = $collection->find(array('company_id'=> $C_Query));
		$cursor_pte = $collection->find(array( 'company_id'=> $_REQUEST['company_id'] ));
		
		//$cursor_pte = $collection->find();
		
		if($cursor_pte->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_pte as $permit_to_enter)
			{
				unset($permit_to_enter['_id']);
				$permit_id = $permit_to_enter['permit_id'];	
				
					//$pair_users_arr = array();
					//$pair_users = $access_permit['user_id'];
					
					/*
					for($u=0;$u<=count($pair_users);$u++)
					{
						$users_d = new MongoCollection($app_data, 'users');
						$users_details = $users_d->findOne(array('user_id'=>(int)$pair_users[$u]));
						if(isset($users_details['user_id']))
						{
							unset($users_details['_id']);
							$pair_users_arr[] = $users_details;
						}
					}
					*/
					//$from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
					//$to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';

					/*
					$response['data']['pairing'][] = array(
						'permit_id'=>$permit_id,
						'user_id'=>$permit_to_enter['user_id'],
						'username'=>$permit_to_enter['username'],
						'company_id'=>$permit_to_enter['company_id'],
						'company_ref_id'=>$permit_to_enter['company_ref_id'],
						'role'=>$permit_to_enter['role'],
						'location'=>$permit_to_enter['location'],
						'host_name'=>$permit_to_enter['host_name'],
						'host_email_phone'=>$permit_to_enter['host_email_phone'],
						'date_from'=>$permit_to_enter['date_from'],
						'date_to'=>$permit_to_enter['date_to'],
						'time_from'=>$permit_to_enter['time_from'],
						'time_to'=>$permit_to_enter['time_to'],
						// Accompany Person Data
						'pte_user_name_data'=>$permit_to_enter['pte_user_name_data'],
						'pte_user_email_data'=>$permit_to_enter['pte_user_email_data'],
						'pte_user_phone_data'=>$permit_to_enter['pte_user_phone_data'],
						'pte_company_name_data'=>$permit_to_enter['pte_company_name_data'],
						'pte_company_position_data'=>$permit_to_enter['pte_company_position_data'],
						'pte_user_message_data'=>$permit_to_enter['pte_user_message_data'],
						'pte_user_image_name_data'=>$permit_to_enter['pte_user_image_name_data'],
						
						
						'registered_time'=>$permit_to_enter['registered_time'],
						'approved'=>$permit_to_enter['approved'],
						'subadmin_approved'=>$permit_to_enter['subadmin_approved'],
						'admin_approved'=>$permit_to_enter['admin_approved']
					*/
					
					//$ids = json_decode($permit_to_enter['user_id']);
				
				//if($permit_to_enter['user_id'] == $_REQUEST['user_id'])
				//{
				//	$response['current_permit'][] = $permit_to_enter;
				//}
				
				if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
				{
					
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
					echo $since_start->days.' days total -- ';
					echo $since_start->y.' years -- ';
					echo $since_start->m.' months -- ';
					echo $since_start->d.' days -- ';
					echo $since_start->h.' hours -- ';
					echo $since_start->i.' minutes -- ';
					echo $since_start->s.' seconds---';
					echo '<br>';*/
					
					if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
					{
						$permit_to_enter['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$permit_to_enter['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
						//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
					}
					else { $permit_to_enter['duration'] = '--/--'; }
					$response['data'][] = $permit_to_enter;
				}			
				
			}
		}
		else
		{
			$response['status'] = 'false';
			$response['error'] = 'Invalid Company ID';
			exit(json_encode($response));
		}		
	}
}

//----------------------------------
// Check for Approval
//----------------------------------
else if($_REQUEST['action'] == 'check_approve' && isset($_REQUEST['permit_id']))
{
	$collection = new MongoCollection($app_data, 'permit_to_enter');
	$check = $collection->find(array('permit_id'=>(int) $_REQUEST['permit_id']));
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

//----------------------------------
// PTE Approve Permit
//----------------------------------
else if($_REQUEST['action'] == 'approve' && isset($_REQUEST['user_id']) && isset($_REQUEST['permit_id']) && isset($_REQUEST['status']))
{
	$response['status'] = 'false';
	$response['msg'] = 'User Not Found';
	$collection_user = new MongoCollection($app_data, 'users');
    $users = $collection_user->find(array('user_id'=>(int)$_REQUEST['user_id']));
    if($users->count() > 0) 
	{ 
		$response['status'] = 'true'; 
		unset($response['msg']);
		
		foreach($users as $user)
		{
			$permit_to_enter_emailTosend = $user['email'];
			$username = $user['username'];
			$device_id = $user['device_id'];
			$device_name = $user['device_name'];
		}
	}
	if($response['status'] == 'true')
	{
		if(in_array($_REQUEST['status'],array(0,1,2)))
		{
			$collection_permit = new MongoCollection($app_data, 'permit_to_enter');
			$criteria = array('permit_id'=>(int) $_REQUEST['permit_id']);
			$collection_permit->update( $criteria ,array('$set' => array('approved' =>(int) $_REQUEST['status'],'token' => time().rand() ) ) );
			$response['status'] = 'true';
			
			$collection = new MongoCollection($app_data, 'users');
			$criteria = array('user_id'=>(int) $_REQUEST['user_id']);
			if($_REQUEST['status'] == 1)
			{
					require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
					$mail = new PHPMailer;
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Port = 587;
                    $mail->SMTPSecure = 'tls';
                    $mail->SMTPAuth = true;
					$mail->Username = "sendweisslocks@gmail.com";
                    $mail->Password = "AppRegistration";
                    $mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
                    // $mail->addAddress('archirayan5@gmail.com');
					$mail->addAddress( $permit_to_enter_emailTosend );
                    $mail->Subject = 'Weiss Locks - Permit To Enter Approved';
					
					$permit_to_enter = $collection_permit->find(array('permit_id'=>(int)$_REQUEST['permit_id']));
					if($permit_to_enter->count() > 0) {
						foreach($permit_to_enter as $permit_to_enter)
						{
							$token = $permit_to_enter['token'];
						}
					}
                    $mail->msgHTML('
					Dear Candidate,
					<br/><br/>
					Your Permit is approved by adminstrator.
					<br/><br/>
					Please proceed to Request for Entry Code
					<br/><br/>
					Your Username : '.$username.'
					');
					// <a href="http://app.weisslocks.com/setpassword.php?token='.$token.'"> Click here to Set Password </a>
                    $mail->send();
					
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
								'title' => 'Weiss Locks - Permit To Enter',
								'body' => 'Your permit is Successfully Approved. You can request for access now.',
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
							'title'		=> 'Weiss Locks - Permit To Enter', 
							'message' 	=> 'Your permit is Successfully Approved. You can request for access now.',
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
		}
	}
}

//----------------------------------

//----------------------------------
else if($_REQUEST['action'] == 'profile_show' && isset($_REQUEST['permit']))
{
		$response['status'] = 'false';
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		$Profile_Query = array('permit_id' =>(int) $_REQUEST['permit']);
		$cursor = $collection->find( $Profile_Query );
		if($cursor->count() == 1)
		{
			$response['status'] = 'true';
			foreach ( $cursor as $pf_details)
			{
				unset($pf_details['_id']);
				$response['data'] = $pf_details;
			}
		}
}

//----------------------------------

//----------------------------------
else if($_REQUEST['action'] == 'get' && $_REQUEST['view'] == 'staff_contractors_by_com' &&  $_REQUEST['com_id'] != '')
{
	$response['status'] = 'false';
	 $collection = new MongoCollection($app_data, 'company');
	 $cursor = $collection->findOne( array('company_ID'=>(int) $_REQUEST['com_id'] ) );
	 if(isset($cursor['company_ID']))
	 {
		$permit_to_enter =  json_decode($cursor['user_id']);
		 for($i=0;$i<count($permit_to_enter);$i++)
		 {
			$collection1 = new MongoCollection($app_data, 'permit_to_enter');
			$cursor_users = $collection1->findOne( array('permit_id'=>(int) $permit_to_enter[$i] ) );
			 if(isset($cursor_users['permit_id']) && in_array($cursor_users['role'],array(4,5)))
			 {
				 $response['status'] = 'true';
				 $response['data'][] = array('permit_id'=>$cursor_users['permit_id'],'user_fullname'=>$cursor_users['username']);
			 }
		 }
	 }
}

//----------------------------------

//----------------------------------
else if($_REQUEST['action'] == 'post' && $_REQUEST['permit_id'] != '' &&  $_REQUEST['device_id'] != '')
{
		$collection2 = new MongoCollection($app_data, 'permit_to_enter');
		$criteria2 = array('permit_id'=>(int) $_REQUEST['permit_id']);
		$collection2->update( $criteria2 ,array('$set' => array(
			'device_id'  => $_REQUEST['device_id']
		)));
		$response['status'] = 'true';
}

//----------------------------------
// For individual permit access
//----------------------------------
else if( $_REQUEST['action'] == 'choose_permit' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' )
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1) // For SuperAdmin Only
	{
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		//$criteria = array('user_id'=>$_REQUEST['user_id']);
		$permit_to_enter = $collection->find();
		
		if($permit_to_enter->count() > 0) { 
			$response['status'] = 'true';
			foreach($permit_to_enter as $permit_to_enter)
			{
				$permit_id = $permit_to_enter['permit_id'];	
				
					//$pair_users_arr = array();
					//$pair_users = $access_permit['user_id'];
					
					/*
					for($u=0;$u<=count($pair_users);$u++)
					{
						$users_d = new MongoCollection($app_data, 'users');
						$users_details = $users_d->findOne(array('user_id'=>(int)$pair_users[$u]));
						if(isset($users_details['user_id']))
						{
							unset($users_details['_id']);
							$pair_users_arr[] = $users_details;
						}
					}
					*/
					//$from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
					//$to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';

					/*
					$response['data']['pairing'][] = array(
						'permit_id'=>$permit_id,
						'user_id'=>$permit_to_enter['user_id'],
						'username'=>$permit_to_enter['username'],
						'company_id'=>$permit_to_enter['company_id'],
						'company_ref_id'=>$permit_to_enter['company_ref_id'],
						'role'=>$permit_to_enter['role'],
						'location'=>$permit_to_enter['location'],
						'host_name'=>$permit_to_enter['host_name'],
						'host_email_phone'=>$permit_to_enter['host_email_phone'],
						'date_from'=>$permit_to_enter['date_from'],
						'date_to'=>$permit_to_enter['date_to'],
						'time_from'=>$permit_to_enter['time_from'],
						'time_to'=>$permit_to_enter['time_to'],
						// Accompany Person Data
						'pte_user_name_data'=>$permit_to_enter['pte_user_name_data'],
						'pte_user_email_data'=>$permit_to_enter['pte_user_email_data'],
						'pte_user_phone_data'=>$permit_to_enter['pte_user_phone_data'],
						'pte_company_name_data'=>$permit_to_enter['pte_company_name_data'],
						'pte_company_position_data'=>$permit_to_enter['pte_company_position_data'],
						'pte_user_message_data'=>$permit_to_enter['pte_user_message_data'],
						'pte_user_image_name_data'=>$permit_to_enter['pte_user_image_name_data'],
						
						
						'registered_time'=>$permit_to_enter['registered_time'],
						'approved'=>$permit_to_enter['approved'],
						'subadmin_approved'=>$permit_to_enter['subadmin_approved'],
						'admin_approved'=>$permit_to_enter['admin_approved']
					*/
				
				if($permit_to_enter['permit_id'] == $_REQUEST['permit_id'])
				{
					$response['current_permit'][] = $permit_to_enter;
				}
				if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
				{
					
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
					echo $since_start->days.' days total -- ';
					echo $since_start->y.' years -- ';
					echo $since_start->m.' months -- ';
					echo $since_start->d.' days -- ';
					echo $since_start->h.' hours -- ';
					echo $since_start->i.' minutes -- ';
					echo $since_start->s.' seconds---';
					echo '<br>';*/
					
					if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
					{
						$permit_to_enter['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$permit_to_enter['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
						//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
					}
					else { $permit_to_enter['duration'] = '--/--'; }
					$response['data'][] = $permit_to_enter;
				}
			}
		}
    }
	
	else // For all Other Users
	{
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		$Reg_Query = array( '$and' => array( array('user_id' => $_REQUEST['user_id'] ), array('company_id'=>$_REQUEST['company_id']) ) );
		//$cursor_pte = $collection->find(array( 'company_id'=> $_REQUEST['company_id'] ));
		$cursor_pte = $collection->find( $Reg_Query );
		
		if($cursor_pte->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_pte as $permit_to_enter)
			{
				unset($permit_to_enter['_id']);
				$permit_id = $permit_to_enter['permit_id'];	
				
				if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
				{
					
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
					echo $since_start->days.' days total -- ';
					echo $since_start->y.' years -- ';
					echo $since_start->m.' months -- ';
					echo $since_start->d.' days -- ';
					echo $since_start->h.' hours -- ';
					echo $since_start->i.' minutes -- ';
					echo $since_start->s.' seconds---';
					echo '<br>';*/
					
					if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
					{
						$permit_to_enter['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$permit_to_enter['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
						//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
					}
					else { $permit_to_enter['duration'] = '--/--'; }
					$response['data'][] = $permit_to_enter;
				}			
				
			}
		}
		else
		{
			$response['status'] = 'false';
			$response['error'] = 'Invalid Company ID';
			exit(json_encode($response));
		}	
	}
}
// End For individual permit access

echo json_encode($response);
?>