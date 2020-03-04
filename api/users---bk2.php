<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/uploads/');


if($_REQUEST['action'] == 'add' && isset($_REQUEST['user_name']) && isset($_REQUEST['user_email']) && isset($_REQUEST['user_role']))
{
    if (!filter_var($_REQUEST['user_email'], FILTER_VALIDATE_EMAIL))
    {
		$response['status'] = 'false';
			$response['error'] = 'Invalid Email Address';
		exit(json_encode($response));
    }
    
	$collection = new MongoCollection($app_data, 'company');
    $C_Query = array( 'company_ref' => $_REQUEST['company_ref_id'] );
    $cursor = $collection->find( $C_Query );
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
	
    $collection = new MongoCollection($app_data, 'users');
    $Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) );
    $cursor = $collection->find( $Reg_Query );
    if($cursor->count() == 1)
    {
		$response['status'] = 'false';
        $response['error'] = 'User Already Exists...';
    }
    else
    {
		$response['status'] = 'true';
		$phone_number = isset($_REQUEST['user_phone']) ? $_REQUEST['user_phone'] : '';
		$user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : 5;
		$UDID_IOS = isset($_REQUEST['UDID_IOS']) ? $_REQUEST['UDID_IOS'] : '';
		
		//Added for PTE Visitor
		$user_company_name = isset($_REQUEST['user_company_name']) ? $_REQUEST['user_company_name'] : '';
		
		$user_reg = $app_data->users;
		$post = array(
			'user_id' => getNext_users_Sequence('weiss_locks_user'),
			'username'     => $_REQUEST['user_name'],
			'email'   => $_REQUEST['user_email'],
			'full_name' => $_REQUEST['full_name'],
			'password'  => md5('test'),
			'phone_number'  => $phone_number,
			'approved'  => 0,
			'role'  => $user_role,
			'company_id'  => (int) $company_ID,
			'key_group_id'  => '',
			'key_id'  => '',
			'key_activated'  => '',
			'lock_group_id'  => '',
			'payment_id'  => '',
			'invoice_no'  => '',
			'cc_name'  => '',
			'cc_num'  => '',
			'cc_validity'  => '',
			'registered_time'  => date('d F Y, H:i'),
			'device_name'  => $_REQUEST['device_name'],
			'device_id'  => $_REQUEST['device_id'],
			'company_ref_id'  => $_REQUEST['company_ref_id'],
			'UDID_IOS'  => $UDID_IOS,
			// Additional Field
			'user_company_name'  => $user_company_name,
			'company_position'  => $_REQUEST['company_position'],
			'user_registration_message'  => $_REQUEST['user_registration_message'],
			'user_registration_image_name' => $_REQUEST['user_registration_image_name'] //Image File Name
			);
		if($user_reg->insert($post))
		{
			
		$Reg_Query = array('_id' => $post['_id'] ) ;
		$cursor = $collection->find( $Reg_Query ); 
		
		foreach($cursor as $ff)
		{
			unset($ff['_id']);
			$response['data'] = $ff;
			$user_id = $ff['user_id'];
			$username = $ff['username'];
			$full_name = $ff['full_name'];
			$device_id = $ff['device_id'];
			$device_name = $ff['device_name'];
		}
	
		$user_id = $user_id;
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
										$users = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
										if($users->count() > 0) {
											foreach($users as $user)
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
								$users = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
								if($users->count() > 0) {
									foreach($users as $user)
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
		}
	}
}
else if($_REQUEST['action'] == 'view' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1)
	{
		$collection = new MongoCollection($app_data, 'users');
		$users = $collection->find();
		
		if($users->count() > 0) { $response['status'] = 'true';
			foreach($users as $user)
			{
				unset($user['_id']);
				$user['company_name'] = '';
				if( !empty( $user['company_id'] ) &&  $user['company_id'] != 0 )
				{
					$collection_com = new MongoCollection($app_data, 'company');
					$coms = $collection_com->findOne(array('company_ID'=>(int)$user['company_id']));
					if(isset($coms['company_ID']))
					{
						$user['company_name'] = $coms['company_name'];
						$user['company_ref_id'] = $coms['company_ref'];
					}
				}
				
				if($user['user_id'] == $_REQUEST['user_id'])
				{
					$response['current_user'][] = $user;
				}
				if($user['user_id'] != $_REQUEST['user_id'])
				{
					
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $user['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $user['registered_time'] . '-----';
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
						$user['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$user['duration'] = date('H:i', strtotime( $user['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$user['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$user['duration'] = date('d/m', strtotime( $user['registered_time']));
						//echo  date('d/m', strtotime( $user['registered_time'])) . ' <br><br>';
					}
					else { $user['duration'] = '--/--'; }
					$response['data'][] = $user;
				}
			}
		}
    }
	else
	{
		$users = $app_data->company;
		$cursor = $users->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $com)
			{
				$ids = json_decode($com['user_id']);
				if(in_array($_REQUEST['user_id'], $ids))
				{
					for($i=0;$i<=count($ids);$i++)
					{
						$users_l_1 = $app_data->users;
						$cursor_u = $users_l_1->find(array('user_id'=>(int)$ids[$i]));
						if($cursor_u->count())
						{
							$response['status'] = 'true';
							foreach($cursor_u as $uu)
							{
								$uu['company_name'] = '';
								if( !empty( $uu['company_id'] ) &&  $uu['company_id'] != 0 )
								{
									$collection_com = new MongoCollection($app_data, 'company');
									$coms = $collection_com->findOne(array('company_ID'=>(int)$uu['company_id']));
									if(isset($coms['company_ID']))
									{
										$uu['company_name'] = $coms['company_name'];
										$uu['company_ref_id'] = $coms['company_ref'];
									}
								}
								unset($user['_id']);
								if($uu['user_id'] == $_REQUEST['user_id'])
								{
									$response['current_user'][] = $uu;
								}
								if($uu['user_id'] != $_REQUEST['user_id'])
								{
									if(in_array($uu['role'],array(4,5)))
									{
										$start_date = new DateTime( date('d-m-Y H:i',strtotime( $uu['registered_time'] )) );
										$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
										
										/*echo date('d F Y, H:i') . '---' . $uu['registered_time'] . '-----';
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
											$uu['duration'] = 'NOW';
											//echo 'Now<br><br>';
										}
										else if($since_start->d == 0 && $since_start->h >= 0)
										{
											$uu['duration'] = date('H:i', strtotime( $uu['registered_time']));
											//echo 'Before 1 day<br><br>';
										}
										else if($since_start->days == 1)
										{
											$uu['duration'] = 'Yesterday';
											//echo 'Yesterday<br><br>';
										}
										else if($since_start->days >= 2)
										{
											$uu['duration'] = date('d/m', strtotime( $uu['registered_time']));
											//echo  date('d/m', strtotime( $uu['registered_time'])) . ' <br><br>';
										}
										else { $uu['duration'] = '--/--'; }
										$response['data'][] = $uu;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
else if($_REQUEST['action'] == 'check_approve' && isset($_REQUEST['user']))
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
else if($_REQUEST['action'] == 'update_password' && isset($_REQUEST['user']) && isset($_REQUEST['password']))
{
    $collection = new MongoCollection($app_data, 'users');
	$criteria = array('username'=>$_REQUEST['user']);
	$response['status'] = 'false';
	$response['error'] = 'Invalid User';
	$cursor = $collection->find($criteria);
	if($cursor->count() == 1)
	{
		foreach($cursor as $users)
		{
			$approved = $users['approved'];
			$users_id = $users['user_id'];
		}
		if($approved == 1)
		{
			unset($response['error']);
			$criteria = array('user_id'=>(int)$users_id);
			$collection->update( $criteria ,array('$set' => array('password' => md5($_REQUEST['password']) ) ) );
			$response['status'] = 'true';
			$cursor_success = $collection->findOne($criteria);
			
			$cursor_success['company_name'] = '';
			$cursor_success['contracted_company_name'] = '';
			
			if(isset($cursor_success['user_id']))
			{
				
				if( !empty( $cursor_success['company_id'] ) &&  $cursor_success['company_id'] != 0 )
				{
					$collection_com = new MongoCollection($app_data, 'company');
					$coms = $collection_com->findOne(array('company_ID'=>(int)$cursor_success['company_id']));
					if(isset($coms['company_ID']))
					{
						$cursor_success['company_name'] = $coms['company_name'];
						$cursor_success['company_ref_id'] = $coms['company_ref'];
						
						 for($k = 0 ; $k <= count($coms['contracted_name']) ; $k++)
						 {
							 if (false !== $key = array_search( json_decode($cursor_success['user_company'])[0] , $coms['contracted_ref_id'] )) 
							 {
								 $cursor_success['contracted_company_name'] = $coms['contracted_name'][$key];
							 }
						 }
					}
				}
				
				$response['data'] = $cursor_success;
			}
		} else {$response['error'] = 'You are not Approved Yet...';}
	}
}
else if($_REQUEST['action'] == 'login' && isset($_REQUEST['username']) && isset($_REQUEST['password']))
{
	$response['status'] = 'false';
	$response['error'] = 'Invalid Credentials';
	$collection = new MongoCollection($app_data, 'users');
	$Login_Query = array('username' => $_REQUEST['username'], 'password' => md5($_REQUEST['password']));
	$cursor = $collection->findOne( $Login_Query );
	if(isset($cursor['user_id']))
	{
			if(in_array($cursor['role'],array(2,3)))
			{
					$criteria_device = array('user_id'=>(int) $cursor['user_id'] );
					
					if( $cursor['UDID_IOS'] == '' )
						{
							$collection->update( $criteria_device ,array('$set' => array(
								'UDID_IOS'  => $_REQUEST['UDID_IOS'],
							)));
						}
						else
						{
							if($cursor['UDID_IOS'] != $_REQUEST['UDID_IOS'])
							{
								exit( json_encode($response));
							}
						}
			}
			$response['status'] = 'true';
			unset($response['error']);
			$collection = new MongoCollection($app_data, 'users');
			$criteria = array('user_id'=>(int) $cursor['user_id'] );
			$Security_token = Security_token();
			$collection->update( $criteria ,array('$set' => array(
				'last_login'  => date('H:i A,d F Y'),
				'token' => $Security_token
			)));
			$cursor = $collection->findOne( $Login_Query );
			$cursor['company_name'] = '';
			$cursor['contracted_company_name'] = '';
			if( !empty( $cursor['company_id'] ) &&  $cursor['company_id'] != 0 )
			{
				$collection_com = new MongoCollection($app_data, 'company');
				$coms = $collection_com->findOne(array('company_ID'=>(int)$cursor['company_id']));
				if(isset($coms['company_ID']))
				{
					$cursor['company_name'] = $coms['company_name'];
					$cursor['company_ref_id'] = $coms['company_ref'];
					
					for($k = 0 ; $k <= count($coms['contracted_name']) ; $k++)
					{
						if (false !== $key = array_search( json_decode($cursor['user_company'])[0] , $coms['contracted_ref_id'] )) 
						{
							$cursor['contracted_company_name'] = $coms['contracted_name'][$key];
						}
					}
				}
			}
		    $response['data'] = $cursor;
	}
}

else if($_REQUEST['action'] == 'approve' && isset($_REQUEST['user']) && isset($_REQUEST['status']))
{
	$response['status'] = 'false';
	$response['msg'] = 'User Not Found';
	$collection = new MongoCollection($app_data, 'users');
    $users = $collection->find(array('user_id'=>(int)$_REQUEST['user']));
    if($users->count() > 0) { $response['status'] = 'true'; unset($response['msg']);
		foreach($users as $user)
		{
			$user_emailTosend = $user['email'];
			$username = $user['username'];
			$device_id = $user['device_id'];
			$device_name = $user['device_name'];
			$company_position  = $user['company_position'];
			$user_registration_message  = $user'user_registration_message'];
			$user_registration_image_name = $user['user_registration_image_name'];
		}
	}
	if($response['status'] == 'true')
	{
		if(in_array($_REQUEST['status'],array(0,1,2)))
		{
			$collection = new MongoCollection($app_data, 'users');
			$criteria = array('user_id'=>(int) $_REQUEST['user']);
			$collection->update( $criteria ,array('$set' => array('approved' =>(int) $_REQUEST['status'],'token' => time().rand() ) ) );
			$response['status'] = 'true';
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
					$mail->addAddress( $user_emailTosend );
                    $mail->Subject = 'Weiss Locks - Successfully Approved';
					
					$users = $collection->find(array('user_id'=>(int)$_REQUEST['user']));
					if($users->count() > 0) {
						foreach($users as $user)
						{
							$token = $user['token'];
						}
					}
                    $mail->msgHTML('
					Dear Candidate,
					<br/><br/>
					You are Approved By Administrator.
					<br/><br/>
					Username : '.$username.'
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
							'message' 	=> 'You Are Successfully Approved',
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

else if($_REQUEST['action'] == 'profile_show' && isset($_REQUEST['user']))
{
		$response['status'] = 'false';
		$collection = new MongoCollection($app_data, 'users');
		$Profile_Query = array('user_id' =>(int) $_REQUEST['user']);
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

else if($_REQUEST['action'] == 'profile_upd' && isset($_REQUEST['user']) && isset($_REQUEST['phone_number']))
{
		$collection = new MongoCollection($app_data, 'users');
		$criteria = array('user_id'=>(int) $_REQUEST['user']);
		$collection->update( $criteria ,array('$set' => array('phone_number' => $_REQUEST['phone_number'])));
		if(isset($_REQUEST['password']) && $_REQUEST['password'] != '')
		{
			$collection->update( $criteria ,array('$set' => array('password'=> md5($_REQUEST['password']))));
		}
		$response['status'] = 'true';
		$Profile_Query = array('user_id' =>(int) $_REQUEST['user']);
		$cursor = $collection->find( $Profile_Query );
		if($cursor->count() == 1)
		{
			foreach ( $cursor as $pf_details)
			{
				unset($pf_details['_id']);
				$response['data'] = $pf_details;
			}
		}
}


else if($_REQUEST['action'] == 'get' && $_REQUEST['view'] == 'staff_contractors_by_com' &&  $_REQUEST['com_id'] != '')
{
	$response['status'] = 'false';
	 $collection = new MongoCollection($app_data, 'company');
	 $cursor = $collection->findOne( array('company_ID'=>(int) $_REQUEST['com_id'] ) );
	 if(isset($cursor['company_ID']))
	 {
		$users =  json_decode($cursor['user_id']);
		 for($i=0;$i<count($users);$i++)
		 {
			$collection1 = new MongoCollection($app_data, 'users');
			$cursor_users = $collection1->findOne( array('user_id'=>(int) $users[$i] ) );
			 if(isset($cursor_users['user_id']) && in_array($cursor_users['role'],array(4,5)))
			 {
				 $response['status'] = 'true';
				 $response['data'][] = array('user_id'=>$cursor_users['user_id'],'user_fullname'=>$cursor_users['username']);
			 }
		 }
	 }
}

else if($_REQUEST['action'] == 'post' && $_REQUEST['user_id'] != '' &&  $_REQUEST['device_id'] != '')
{
		$collection2 = new MongoCollection($app_data, 'users');
		$criteria2 = array('user_id'=>(int) $_REQUEST['user_id']);
		$collection2->update( $criteria2 ,array('$set' => array(
			'device_id'  => $_REQUEST['device_id']
		)));
		$response['status'] = 'true';
}

else if($_REQUEST['action'] == 'get' && $_REQUEST['method'] == 'device' && $_REQUEST['UDID'] != '')
{
		$response['status'] = 'false';
		$response['error'] = 'User Not Found';
		$collection1 = new MongoCollection($app_data, 'users');
		$cursor_users = $collection1->findOne( array('UDID_IOS'=>$_REQUEST['UDID']));
		if(isset($cursor_users['user_id']))
		{
			unset($response['error']);
			$response['status'] = 'true';
			$response['data'] = $cursor_users;
		}
		
}


echo json_encode($response);
?>