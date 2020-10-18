<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();

$current_user = $_SESSION['user_id'];
$msg = '';

if(isset($_REQUEST['activate_key']) && $_REQUEST['activate_key'] != '')
{
	$user_ID = $_REQUEST['user_ID'];
	
	$collection = new MongoCollection($app_data, 'keys');
	$criteria = array('key_ID'=>(int) $_REQUEST['activate_key'] );
		
		$collection->update( $criteria ,array('$set' => array(
				'status'  => (int) 1, // old format
				'updated_by'  => (int) 1, // old format
//				'activated_on' => date('d F Y, H:i A') // old format
                'activated_on' => date('c') // new format
		)));
		
		//New format to make key reflect which user has activated the key at date and time
		if ( !array_key_exists('key_activated_user_id', $user_ID) ){
			$update = array(
				'$push' =>	array(
					'key_activated_user_id' => array('$each' => $user_ID),
					'key_activation' => array(
						'$each' => array(
							'user_id' => $_REQUEST['user_ID'],
							'status'  => (int) 1, // old format
							'updated_by'  => (int) $current_user, // old format
//							'activated_on' => date('d F Y, H:i A') // old format
                            'activated_on' => date('c') // new format
						)						
					)
				)
			);			
			$return = $collection->update($criteria, $update);
			if ($return === false) {
			   throw new \ErrorException('Unable to update collection');
			}
		}
		
}


if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$key_id = isset($_REQUEST['key_id']) ? $_REQUEST['key_id'] : array();
	if (isset($_REQUEST['key_id'])){
		$key_id = array_map('intval', $key_id);
	}
	$key_activated = isset($_REQUEST['key_activated']) ? $_REQUEST['key_activated'] : array();
	if (isset($_REQUEST['key_activated'])){
		$key_activated = array_map('intval', $key_activated);
	}
	$key_group_id = isset($_REQUEST['key_group_id']) ? $_REQUEST['key_group_id'] : array();
	if (isset($_REQUEST['key_group_id'])){
		$key_group_id = array_map('intval', $key_group_id);
	}
	$lock_group_id = isset($_REQUEST['lock_group_id']) ? $_REQUEST['lock_group_id'] : array();
	if (isset($_REQUEST['lock_group_id'])){
		$lock_group_id = array_map('intval', $lock_group_id);
	}
	$user_company = isset($_REQUEST['user_company']) ? $_REQUEST['user_company'] : array();
	$KeyLockGroup = isset($_REQUEST['KeyLockGroup']) ? $_REQUEST['KeyLockGroup'] : array();
	if (isset($_REQUEST['KeyLockGroup'])){
		$KeyLockGroup = array_map('intval', $KeyLockGroup);
	}
	
	if($_REQUEST['user_ID'] == 0)
	{
		$collection = new MongoCollection($app_data, 'users');
		$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['user_name'] ), array('email'=>$_REQUEST['user_email']) ) );
		$cursor = $collection->find( $Reg_Query );
		if($cursor->count() == 1)
		{
			$msg = 'User Already Exists...';
		}
	
		if (!filter_var($_REQUEST['user_email'], FILTER_VALIDATE_EMAIL))
		{
			$msg = 'Invalid Email Address';
		}
		
		if($msg == '')
		{
			$user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : 5;
			$user_password = isset($_REQUEST['password']) ? $_REQUEST['password'] : 'test';
			$user_reg = $app_data->users;
			
			$collection_com = new MongoCollection($app_data, 'company');
			$Reg_Query = array('company_ID' => (int) $_REQUEST['company_id']) ;
			$cursor_com = $collection_com->findOne( $Reg_Query ); 
			
			if(isset($cursor_com['user_id']))
			{
				$company_ref = $cursor_com['company_ref'];
			}
			
			$post = array(
				'user_id' => getNext_users_Sequence('weiss_locks_user'),
				'username'     => $_REQUEST['user_name'],
				'email'   => $_REQUEST['user_email'],
				'full_name' => $_REQUEST['full_name'],
				'password'  => md5( $user_password ),
				'phone_number'  => $_REQUEST['phone_number'],
                'country_code'  => $_REQUEST['country_code'],
				'approved'  =>(int) $_REQUEST['approved'],
				'role'  => (int) $user_role,
				'company_id'  =>(int) $_REQUEST['company_id'],
				'company_ref_id'  => $company_ref, //$_REQUEST['company_ref_id'],
				'user_company'  => $user_company, // format change on 2020-10-19
				'user_company_ref_id'  => $_REQUEST['user_company_ref_id'],
				//'key_group_id'  => json_encode($key_group_id), //Old Format
				'key_group_id'  => $key_group_id,
				//'key_id'  => json_encode($key_id), //Old Format
				'key_id'  => $key_id, 
				//'key_activated'  => json_encode($key_activated),
				//'lock_group_id'  => json_encode($lock_group_id), //Old Format
				'lock_group_id'  => $lock_group_id,
				//'KeyLockGroup'  => json_encode($KeyLockGroup),//Old Format
				'KeyLockGroup'  => $KeyLockGroup,
				
				'payment_id'  => $_REQUEST['payment_id'],
				'invoice_no'  => '',
				'cc_name'  => $_REQUEST['cc_name'],
				'cc_num'  => $_REQUEST['cc_num'],
				'cc_validity'  => $_REQUEST['cc_month'] . '/' . $_REQUEST['cc_year'],
//				'registered_time'  => date('d F Y, H:i A'),
                'registered_time'  => date('c'),
				// 'device_name'  => $_REQUEST['device_name'],
				'device_id'  => $_REQUEST['device_id'],
				'UDID_IOS'  => $_REQUEST['UDID_IOS'],
				'token'	=> $_REQUEST['token'],
				// Additional Field
				'company_position'  => $_REQUEST['company_position'],
				'user_registration_message'  => $_REQUEST['user_registration_message'],
				'user_registration_image_name' => $_REQUEST['user_registration_image_name'], //Image File Name
				'participant' => (int) $_REQUEST['participant'],
                'lock_server_username' => $_REQUEST['lock_server_username'],
                'lock_server_password' => $_REQUEST['lock_server_password'],
                'first_name' => $_REQUEST['first_name'],
                'last_name' => $_REQUEST['last_name'],
                'identification_last_4_digit' => $_REQUEST['identification_last_4_digit'],
                'department' => $_REQUEST['department']
            );
				
			if($user_reg->insert($post))
			{
				$Reg_Query = array('_id' => $post['_id'] ) ;
				$cursor = $collection->findOne( $Reg_Query ); 
				
				if(isset($cursor['user_id']))
				{
					$user_id = $cursor['user_id'];
					$collection1 = new MongoCollection($app_data, 'company');
					$Reg_Query1 = array( 'company_ID'=>(int) $_REQUEST['company_id']);
					$cursor1 = $collection1->find( $Reg_Query1 );
					if($cursor1->count() == 1)
					{
						foreach($cursor1 as $kk)
						{
							$user_ids = json_decode($kk['user_id']);
							 $user_ids[] = $user_id;
							 $collection2 = new MongoCollection($app_data, 'company');
							 $criteria2 = array('company_ID'=>(int) $_REQUEST['company_id']);
							 $collection2->update( $criteria2 ,array('$set' => array(
									'user_id'  => json_encode($user_ids)
							 )));
						}
					}
					/*
					if(in_array($user_role,array(4,5,6,7,8,9)))
					{
						require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
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
						You are Successfully Added By Administrator.
						<br/><br/>
						Name : '.$full_name.'<br/>
						User Name : '.$username.'<br/>
						');
						$mail->send();
						// Mail to Users Email
					}*/
					echo "<script>window.location='users.php?sucess=true'</script>";
				}
			}
			
		}
	}
	else
	{
		
		$collection = new MongoCollection($app_data, 'users');
		$Reg_Query =array('user_id'=>(int) $_REQUEST['user_ID']);
		
		$cursor = $collection->find( $Reg_Query );
		if($cursor->count() == 1)
		{
			foreach($cursor as $user)
			{
				$user_email = $user['email'];
			}
		}
		if($user_email != $_REQUEST['user_email'])
		{
			$collection = new MongoCollection($app_data, 'users');
			$Reg_Query =array('email'=>$_REQUEST['user_email']);
			$cursor = $collection->find( $Reg_Query );
			if($cursor->count() == 1)
			{
				$msg = 'Email Already Exists...';
			}
		}
		if($msg == '')
		{
			$collection = new MongoCollection($app_data, 'users');
			$criteria = array('user_id'=>(int) $_REQUEST['user_ID']);			
			
			$collection_com = new MongoCollection($app_data, 'company');
			$Reg_Query = array('company_ID' => (int) $_REQUEST['company_id']) ;
			$cursor_com = $collection_com->findOne( $Reg_Query ); 
			
			if(isset($cursor_com['user_id']))
			{
				$company_ref = $cursor_com['company_ref'];
			}
			// Update Other Stuffs
			$collection->update( $criteria ,array('$set' => array(
                'phone_number'  => $_REQUEST['phone_number'],
                'country_code'  => $_REQUEST['country_code'],
                'role'  => (int) $_REQUEST['role'],
                'full_name' => $_REQUEST['full_name'],
                'email'   => $_REQUEST['user_email'],
                'approved'  =>(int) $_REQUEST['approved'],
                'company_ref_id'  => $company_ref, // $_REQUEST['company_ref_id'],
                'user_company'  => $user_company, // format change on 2020-10-19
                'user_company_ref_id'  => $_REQUEST['user_company_ref_id'],
                'cc_name'  => $_REQUEST['cc_name'],
                'cc_num'  => $_REQUEST['cc_num'],
                'cc_validity'  => $_REQUEST['cc_month'] . '/' . $_REQUEST['cc_year'],
                //'key_id'  => json_encode($key_id), //Old format
                'key_id'  => $key_id,
                //'lock_group_id'  => json_encode($lock_group_id), //Old format
                'lock_group_id'  => $lock_group_id,
                //'KeyLockGroup'  => json_encode($KeyLockGroup), //Old format
                'KeyLockGroup'  => $KeyLockGroup,

                'company_id'  =>(int) $_REQUEST['company_id'],
                //'key_group_id'  => json_encode($key_group_id), //Old format
                'key_group_id'  => $key_group_id,
                //'key_activated'  => json_encode($key_activated),
                'key_activated'  => $key_activated,
                'payment_id'  => (int) $_REQUEST['payment_id'],
                'device_id'  => $_REQUEST['device_id'],
                'UDID_IOS'  => $_REQUEST['UDID_IOS'],
                'token'	=> $_REQUEST['token'],
                // Additional Field
                'company_position'  => $_REQUEST['company_position'],
                'user_registration_message'  => $_REQUEST['user_registration_message'],
                'user_registration_image_name' => $_REQUEST['user_registration_image_name'], //Image File Name
                'participant' => (int) $_REQUEST['participant'],
                'lock_server_username' => $_REQUEST['lock_server_username'],
                'lock_server_password' => $_REQUEST['lock_server_password'],
                'first_name' => $_REQUEST['first_name'],
                'last_name' => $_REQUEST['last_name'],
                'identification_last_4_digit' => $_REQUEST['identification_last_4_digit'],
                'department' => $_REQUEST['department']
			)));
			
			// Update Password if Exists
			if(isset($_REQUEST['password']) && $_REQUEST['password'] != '') {
			$collection->update( $criteria ,array('$set' => array(
					'password'  => md5($_REQUEST['password'])
			)));
			}
			
			if($_REQUEST['extra_com_ref_id'] != $_REQUEST['company_id'])
			{
				$collection = new MongoCollection($app_data, 'company');
				$Reg_Query = array('company_ID'=>(int) $_REQUEST['extra_com_ref_id']);
				$cursor = $collection->findOne( $Reg_Query );
				$user_ids = json_decode($cursor['user_id']);
				if (false !== $key = array_search($_REQUEST['user_ID'], $user_ids)) 
				{
					unset($user_ids[$key]);
				}
				$criteria = array('company_ID'=>(int)$_REQUEST['extra_com_ref_id']);
				$collection->update( $criteria ,array('$set' => array('user_id' => json_encode( $user_ids ) ) ) );
				$Reg_Query = array('company_ID'=>(int) $_REQUEST['company_id']);
				$cursor = $collection->findOne( $Reg_Query );
				$user_ids = json_decode($cursor['user_id']);
				if (false !== $key = array_search($_REQUEST['user_ID'], $user_ids)) 
				{
				}
				else { $user_ids[] = $_REQUEST['user_ID'];  }
				$criteria = array('company_ID'=>(int)$_REQUEST['company_id']);
				$collection->update( $criteria ,array('$set' => array('user_id' => json_encode( $user_ids ) ) ) );
				$collection = new MongoCollection($app_data, 'users');
				$criteria = array('user_id'=>(int)$_REQUEST['user_ID']);
				$collection->update( $criteria ,array('$set' => array( "company_id" =>(int) $_REQUEST['company_id'] ) ) );
			}
			
			
			
			
			echo "<script>window.location='users.php?sucess=true'</script>";
		}
	
	
	
	}
	
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Users</h1>
                </div>
				<?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                <?php } ?>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="user_ID" value="<?php echo isset($_REQUEST['user_ID']) ? $_REQUEST['user_ID'] : 0;  ?>" />
									<?php
									$user_id = 0;
									$phone_number = '';
                                    $country_code = '';
									$approved = '';
									$role = '';
									$username = '';
									$email = '';
									$full_name = '';
									$company_ref_id = '';
									$cc_name = '';
									$cc_num = '';
									$cc_validthru = '0/0';
									$key_id = array();
									$key_group_id = array();
									$lock_group_id = array();
									$KeyLockGroup_id = array();
									$device_id = '';
									$UDID_IOS = '';
									// Additional Field
									$company_position = '';
									$user_registration_message = '';
									$user_registration_image_name = '';
									$participant = 0;
                                    $lock_server_username = '';
                                    $lock_server_password = '';

                                    $first_name = '';
                                    $last_name = '';
                                    $identification_last_4_digit = '';
                                    $department = '';
									
									if(isset($_REQUEST['user_ID']))
									{
										$users = $app_data->users;
										$cursor = $users->find(array('user_id' =>(int) $_REQUEST['user_ID']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $user)
											{
												$user_id = (int) $user['user_id'];
												$username = $user['username'];
												$email = $user['email'];
												$phone_number = $user['phone_number'];
                                                $country_code = $user['country_code'];
												$approved = $user['approved'];
												$role = (int) $user['role'];
												$full_name = $user['full_name'];
												$company_ref_id = $user['company_ref_id'];
												$user_company = $user['user_company'];
												$user_company_ref_id = $user['user_company_ref_id'];
												$cc_name = $user['cc_name'];
												$cc_num = $user['cc_num'];
												$cc_validthru = $user['cc_validity'];
												$key_id = $user['key_id'];
												$lock_group_id = $user['lock_group_id'];
												$key_group_id = $user['key_group_id'];
												$company_id = (int) $user['company_id'];
												//$key_activated = $user['key_activated'];
												$payment_id = (int) $user['payment_id'];
												$device_name = $user['device_name'];
												$device_id = $user['device_id'];
												$UDID_IOS = $user['UDID_IOS'];
												$device_token = $user['token'];
												$KeyLockGroup_id = $user['KeyLockGroup'];
												//Additional Field
												$company_position = $user['company_position'];
												$user_registration_message = $user['user_registration_message'];
												$user_registration_image_name = $user['user_registration_image_name'];
												$participant = (int) $user['participant'];
                                                $lock_server_username = $user['lock_server_username'];
                                                $lock_server_password = $user['lock_server_password'];

                                                $first_name = $user['first_name'];
                                                $last_name = $user['last_name'];
                                                $identification_last_4_digit = $user['identification_last_4_digit'];
                                                $department = $user['department'];
											}
										}
									}
									?>
									
									
									
									<?php if($_SESSION['role'] == 1) { ?>
									
										<div class="form-group">
											<label>User ID : </label>
											<span> <?php echo $user_id; ?></span>
										</div>
										
									<?php } ?>
										
										<?php
											if(isset($_REQUEST['user_ID']) && $_REQUEST['user_ID'] != 0)
											{ ?>
												<div class="form-group">
													<label>Username : </label>
													<span> <?php echo $username; ?></span>
												</div>
												<div class="form-group">
													<label>Email : </label>
													<span> <?php // echo $email; ?></span>
													<input class="form-control" name="user_email" value="<?php echo $email; ?>">
												</div>
											<?php } else { ?>
												<div class="form-group">
													<label>Username : </label>
													<input class="form-control" name="user_name">
												</div>
												<div class="form-group">
													<label>Email : </label>
													<input class="form-control" name="user_email">
												</div>
											<?php } ?>
                                                <div class="form-group">
                                                    <label>Full Name : </label>
                                                    <input class="form-control" name="full_name" value="<?php echo $full_name; ?>" />
                                                </div>

                                                <div class="form-group">
                                                    <label>First Name : </label>
                                                    <input class="form-control" name="first_name" value="<?php echo $first_name; ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Last Name : </label>
                                                    <input class="form-control" name="last_name" value="<?php echo $last_name; ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Identification Last 4 Digit : </label>
                                                    <input class="form-control" name="identification_number" value="<?php echo $identification_last_4_digit; ?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Department : </label>
                                                    <input class="form-control" name="department" value="<?php echo $department; ?>" />
                                                </div>


												
												<?php if($_SESSION['role'] == 3) { ?>
												<?php $role = !isset($_REQUEST['user_ID']) ? 5 : $role; ?>
													<?php if(in_array($role,array(4,5,6,7,8,9))) { ?>
													<div class="form-group">
														<label>Role : </label>
														<select name="role" class="form-control">
															<?php //<!-- <option <?php echo $role == 7 ? 'selected="selected"' : ''; ?><?php // value="7"> <!-- Visitor --> Visitor </option> ?>
															<option <?php echo $role == 6 ? 'selected="selected"' : ''; ?> value="7"> <!-- Visitor --> Visitor </option>
															<option <?php echo $role == 6 ? 'selected="selected"' : ''; ?> value="6"> <!--SubAdmin--> SubAdmin </option>
															<option <?php echo $role == 5 ? 'selected="selected"' : ''; ?> value="5"> <!-- Key Holder -User (User of Company)-->Staff </option>
															<option <?php echo $role == 4 ? 'selected="selected"' : ''; ?> value="4"> <!--User Company(Company)--> Contractor </option>																											
														</select>
													</div>
													<?php } else { ?>
														<input type="hidden" name="role" value="<?php echo $role; ?>" />
													<?php } ?>
													
												<?php }
												else if($_SESSION['role'] == 2) { ?>
												<?php $role = !isset($_REQUEST['user_ID']) ? 5 : $role; ?>
												<?php  if(in_array($role,array(3,4,5,6,7,8,9))) { ?>
												<div class="form-group">
													<label>Role : </label>
													<select name="role" class="form-control">
														<!--<option value="2"> Owner - Admin Company </option>-->
														<option <?php echo $role == 7 ? 'selected="selected"' : ''; ?> value="7"> <!-- Visitor --> Visitor </option>
														<option <?php echo $role == 6 ? 'selected="selected"' : ''; ?> value="6"> <!--SubAdmin--> SubAdmin </option>
														<option <?php echo $role == 5 ? 'selected="selected"' : ''; ?> value="5"> <!-- Key Holder -User (User of Company)-->Staff </option>
														<option <?php echo $role == 4 ? 'selected="selected"' : ''; ?> value="4"> <!--User Company(Company)--> Contractor </option>
														<option <?php echo $role == 3 ? 'selected="selected"' : ''; ?> value="3"> Admin (Adminstrative from Admin Company) </option>
													</select>
												</div>
												<?php } else { ?>
													<input type="hidden" name="role" value="<?php echo $role; ?>" />
												<?php } ?>
												<?php } else { ?>
												<div class="form-group">
													<label>Role : </label>
													<select name="role" class="form-control">
														<option <?php echo $role == 7 ? 'selected="selected"' : ''; ?> value="7"> <!-- Visitor --> Visitor </option>
														<option <?php echo $role == 6 ? 'selected="selected"' : ''; ?> value="6"> <!--SubAdmin--> SubAdmin </option>
														<option <?php echo $role == 5 ? 'selected="selected"' : ''; ?> value="5"> <!--Key Holder -User (User of Company)--> Staff </option>
														<option <?php echo $role == 1 ? 'selected="selected"' : ''; ?> value="1">Super Admin</option>
														<option <?php echo $role == 2 ? 'selected="selected"' : ''; ?> value="2"> Owner - Admin Company </option>
														<option <?php echo $role == 3 ? 'selected="selected"' : ''; ?> value="3"> Admin (Adminstrative from Admin Company) </option>
														<option <?php echo $role == 4 ? 'selected="selected"' : ''; ?> value="4"> <!--User Company(Company)--> Contractor </option>
													</select>
												</div>
												<?php } ?>
												<?php if($_SESSION['role'] != 1) 
												{
													$users = $app_data->users;
													$cursor = $users->find(array('user_id' =>(int)$current_user));
													if($cursor->count() > 0)
													{
														foreach($cursor as $user)
														{
															$selected_company_id = $user['company_id'];
														}
													} ?>
													<div class="form-group">
														<label><!-- Select Company--> Attached Company</label>
														<select name="company_id" class="form-control">
														<?php
															$collection = new MongoCollection($app_data, 'company');
															$companies = $collection->find();
															if($companies->count() > 0) 
															{?>
																	<?php foreach($companies as $comp) { if($comp['company_ID'] == $selected_company_id) { ?>
																		<option value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
															<?php } } }	?>
														</select>
													</div>
													<?php 
												} else { ?>
													<div class="form-group">
														<label><!-- Select Company--> Attached Company</label>
														<select name="company_id" class="form-control">
															<option value="0">Please Select Company</option>
														<?php
															$collection = new MongoCollection($app_data, 'company');
															$companies = $collection->find();
															if($companies->count() > 0) 
															{?>
																	<?php foreach($companies as $comp) { ?>
																		<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
																	<?php }
															}
														?>
														</select>
													</div>
													<input type="hidden" name="extra_com_ref_id" value="<?php echo $company_id; ?>" />
												<?php } ?>
												<div class="form-group">
													<label> <!-- Company Ref. ID--> Attached Company Ref. ID: </label>
													<br/>
													<?php
															$collection = new MongoCollection($app_data, 'company');
															$companies = $collection->find();
															if($companies->count() > 0) 
															{
																	foreach($companies as $comp) { 
																		if($company_id == $comp['company_ID'])
																		{
																			echo $comp['company_ref'];
																		}
																		
																	 }
															}
														?>
													<input type="hidden" class="form-control" name="company_ref_id" value="<?php echo $company_ref_id; ?>" />
												</div>
												
												<!-- Additional Field -->
												<div class="form-group">
													<label>Company Position : </label>
													<input class="form-control" name="company_position" value="<?php echo $company_position; ?>" />
												</div>
												<div class="form-group">
													<label>Registration Message : </label>
													<input class="form-control" name="user_registration_message" value="<?php echo $user_registration_message; ?>" />
												</div>
												<div class="form-group">
													<label>Registration Image : </label>
													<span><?php echo $user_registration_image_name; ?></span>
													<img src="<?php echo "api/uploads/" . $user_registration_image_name; ?>" height="500"/>
												</div>
												<!-- End Additional Field -->
												
												<div class="form-group">
													<label>User Company (Contractor Company) : </label><br/>
													<?php
													$user_company_id = json_decode($user_company);
													$collection = new MongoCollection($app_data, 'company');
													$companies = $collection->find();
													foreach($companies as $company)
													{
														if(count($company['contracted_name']) > 0)
														{
															for($i=0;$i<=count( $company['contracted_name']) ; $i++)
															{
																if($company['contracted_name'][$i] != '') {
																?>
																<input <?php echo in_array($company['contracted_ref_id'][$i],$user_company_id) ? 'checked="checked"' : ''; ?> type="checkbox" name="user_company[]" value="<?php echo $company['contracted_ref_id'][$i]; ?>" />
																<?php echo $company['contracted_name'][$i] . ' ( Ref ID : ' . $company['contracted_ref_id'][$i] . ' ) <br/>'; ?>
															<?php  } } } } ?>
													<!-- <input type="text" class="form-control" name="user_company" value="<?php echo $user_company; ?>"/>-->
													<input type="hidden" name="user_company_ref_id" value="0" />
												</div>
												<!--
												<div class="form-group">
													<label>User Company Ref. ID(Contractor Company) : </label>
													<input type="text" class="form-control" name="user_company_ref_id" value="<?php echo $user_company_ref_id; ?>"/>
												</div>
												-->
												<div class="form-group">
													<label>Password : </label>
													<input type="text" class="form-control" name="password" />
												</div>
												<div class="form-group">
													<label>Status : </label>
													<select name="approved" class="form-control">
														<option <?php echo $approved == 0 ? 'selected="selected"' : ''; ?> value="0">Pending</option>
														<option <?php echo $approved == 2 ? 'selected="selected"' : ''; ?> value="2">Disapprove</option>
														<option <?php echo $approved == 1 ? 'selected="selected"' : ''; ?> value="1"> Approve </option>
													</select>
												</div>
												<!-- added for Alarm Reporting -->
												<div class="form-group">
													<label>Participant : </label>
													<select name="participant" class="form-control">
														<option <?php echo $participant == 0 ? 'selected="selected"' : ''; ?> value="0">No</option>
														<option <?php echo $participant == 1 ? 'selected="selected"' : ''; ?> value="1"> Yes </option>
													</select>
												</div>
												
												<div class="form-group">
													<label>keys : </label><br/>
													<table class="table table-striped table-bordered table-hover" id="dataTables-example">
														<tr>
															<td>Select</td>
															<td>User</td>
															<td>Status</td>
															<td>Action</td>
														</tr>
													<?php
													//$key_ids = json_decode($key_id);
													$key_ids = $key_id;
													$keys = $app_data->keys;
													$cursor = $keys->find();
													 if($cursor->count() > 0)
														  {
															  foreach($cursor as $keys)
															  { ?>
																<tr>
																	<td>
																		<input <?php echo in_array($keys['key_ID'],$key_ids) ? 'checked' : ''; ?> type="checkbox" name="key_id[]" value="<?php echo $keys['key_ID']; ?>" />
																		<?php echo $keys['key_name']; ?>
																	</td>
																	<td>
																	<?php 
																		if($keys['status'] == 1 && $_SESSION['role'] == 1)
																		  {
																				$user_details = $app_data->users;
																				$cursor = $user_details->find(array('user_id' =>(int) $keys['updated_by']));
																				if($cursor->count() > 0)
																				{
																					foreach($cursor as $user_detail)
																					{
																						print_r($user_detail['full_name']);
																					}
																					
																				} else { echo 'User Not in List'; }
																		  }
																	?>
																	</td>
																	<td><?php echo $keys['status'] == 0 ? 'Deactivated' : 'Activated'; ?></td>
																	<td>
																		<?php
																		if($keys['status'] == 0 && $_SESSION['role'] == 1)
																		  {
																			  echo '<a class="btn btn-lg btn-primary btn-block" href="manageusers.php?user_ID='.$_REQUEST['user_ID'].'&activate_key='.$keys['key_ID'].'">Activate</a>';
																		  }
																		?>
																	</td>
																</tr>
															  <?php } } ?>
															  </table>
												</div>
												<!--<div class="form-group">
													<label> Key Activated(Key ID) : </label><br/>
													<?php 
													// $key_activateds = json_decode($key_activated);
													// $keys = $app_data->keys;
														  // $cursor = $keys->find();
														  // if($cursor->count() > 0)
														  // {
															  // foreach($cursor as $keys)
															  // { ?>
															  <input <?php //echo in_array($keys['key_ID'],$key_activateds) ? 'checked' : ''; ?> type="checkbox" name="key_activated[]" value="<?php //echo $keys['key_ID']; ?>" />
															  <?php //echo $keys['key_name']. '<br/>'; ?>
													<?php //} } ?>
												</div>-->
												<div class="form-group">
													<label>Key Group ID : </label><br/>
													<?php
													//$key_group_ids = json_decode($key_group_id);
													$key_group_ids = $key_group_id;
													$keygroup = $app_data->keygroup;
														  $cursor = $keygroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keygroup)
															  { ?>
															  <input <?php echo in_array($keygroup['key_group_ID'],$key_group_ids) ? 'checked' : ''; ?> type="checkbox" name="key_group_id[]" value="<?php echo $keygroup['key_group_ID']; ?>" />
															  <?php echo $keygroup['key_group_name']. ' ('. $keygroup['key_group_ID'] . ')' . '<br/>'; ?>
															  <?php } } ?> 
												</div>
												<div class="form-group">
													<label>Lock Group ID : </label> <br/>
													<?php
													//$lock_group_ids = json_decode($lock_group_id);
													$lock_group_ids = $lock_group_id;
													$lockgroup = $app_data->lockgroup;
														  $cursor = $lockgroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $lockgroup)
															  { ?> 
																	<input <?php echo in_array($lockgroup['lock_group_ID'],$lock_group_ids) ? 'checked' : ''; ?> type="checkbox" name="lock_group_id[]" value="<?php echo $lockgroup['lock_group_ID']; ?>" />
																	<?php echo $lockgroup['lock_group_name'] . ' ('. $lockgroup['lock_group_ID'] . ')' . '<br/>'; ?>
													<?php } } ?>
												</div>
												<div class="form-group">
													<label>Key Group and Lock Group Pairing : </label> <br/>
													<?php
													//$KeyLockGroup_ids = json_decode($KeyLockGroup_id);
													$KeyLockGroup_ids = $KeyLockGroup_id;
													$KeyLockGroup = $app_data->KeyLockGroup;
														  $cursor = $KeyLockGroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $KeyLockGroup)
															  {
															  ?> 
																	<input class="merge_button" <?php echo in_array($KeyLockGroup['keyLockGroup_ID'],$KeyLockGroup_ids) ? 'checked' : ''; ?> type="checkbox" name="KeyLockGroup[]" value="<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>" />
																	<?php echo $KeyLockGroup['keyLockGroup_ID'] . ' - ' . $KeyLockGroup['pairing_name'];
																	echo ' (Date : ' . $KeyLockGroup['date_from'] . ' - ' . $KeyLockGroup['date_to'].' Time: ' . $KeyLockGroup['time_from_hh'] .':' .  $KeyLockGroup['time_from_mm'] . '-'  . $KeyLockGroup['time_to_hh'] . ':' . $KeyLockGroup['time_to_mm'].')<br/>';
																	?>
													<?php } } ?>
												</div>
												<script>
													$(document).ready(function(event){
														$(".merge_button").change(function(event){
															// alert($(this).val());
														});
													});
												</script>
												<div class="form-group">
													<label> Payment ID : </label> 
													<select class="form-control" name="payment_id">
														<option value="0"> Select Payment </option>
													<?php
													$payment = $app_data->payment;
														  $cursor = $payment->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $payment)
															  { ?> 
																	<option <?php echo $payment_id == $payment['payment_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $payment['payment_ID']; ?>"> <?php echo 'Payment ID : '. $payment['payment_ID'] . ' & Amt:' . $payment['amount']; ?></option>
														  <?php } } ?>
													</select>
												</div>
												<div class="form-group">
													<label>Credit card Name : </label>
													<input class="form-control" name="cc_name" value="<?php echo $cc_name; ?>" />
												</div>
												<div class="form-group">
													<label>Credit Card Number : </label>
													<input class="form-control" name="cc_num" value="<?php echo $cc_num; ?>" />
												</div>
												<div class="form-group">
													<label>Credit Card ValidThru (in MM/YYYY format) : </label>
													<br/>
													<?php 
													$validity = explode('/',$cc_validthru);
													?>
													<select class="form-control" name="cc_month">
													<?php for($i=1;$i<=12;$i++) { ?>
														<option <?php echo $validity[0] == $i ? 'selected="selected"' : ''; ?> value="<?php echo sprintf("%02d", $i); ?>"> <?php echo sprintf("%02d", $i); ?></option>
													<?php } ?>
													</select>
													<select class="form-control" name="cc_year">
													<?php for($i=1975;$i<=2050;$i++) { ?>
														<option <?php echo $validity[1] == $i ? 'selected="selected"' : ''; ?> value="<?php echo $i; ?>"> <?php echo $i; ?></option>
													<?php } ?>
													</select>
												</div>
												<div class="form-group"> 
													<label> Firebase /APN Device ID : </label>
													<input class="form-control" name="device_id" value="<?php echo $device_id; ?>" />
												</div>
												<div class="form-group"> 
													<label> IOS & Android Device UDID : </label>
													<input class="form-control" name="UDID_IOS" value="<?php echo $UDID_IOS; ?>" />
												</div>
												<div class="form-group"> 
													<label> Device Token : </label>
													<input class="form-control" name="UDID_IOS" value="<?php echo $device_token; ?>" />
												</div>

                                        <h3>Lock Server Details</h3>
                                        <div class="form-group">
                                            <label> Lock Server Username : </label>
                                            <input class="form-control" name="lock_server_username" value="<?php echo $lock_server_username; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label> Lock Server Password : </label>
                                            <input class="form-control" name="lock_server_password" value="<?php echo $lock_server_password; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label>Country Code : </label>
                                            <input class="form-control" name="country_code" value="<?php echo $country_code; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label>Phone Number : </label>
                                            <input class="form-control" name="phone_number" value="<?php echo $phone_number; ?>" />
                                        </div>

                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
