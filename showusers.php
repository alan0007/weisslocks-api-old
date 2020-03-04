<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"> Show Users</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="user_ID" value="<?php echo isset($_REQUEST['user_ID']) ? $_REQUEST['user_ID'] : 0;  ?>" />
									<?php
									$user_id = 0;
									$phone_number = '';
									$approved = '';
									$role = '';
									$username = '';
									$email = '';
									$full_name = '';
									$company_ref_id = '';
									$cc_name = '';
									$cc_num = '';
									$cc_validthru = '0/0';
									$key_id = '';
									$lock_group_id = 0;
									$device_id = '';
									
									if(isset($_REQUEST['user_ID']))
									{
										$users = $app_data->users;
										$cursor = $users->find(array('user_id' =>(int) $_REQUEST['user_ID']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $user)
											{
												$user_id = $user['user_id'];
												$username = $user['username'];
												$email = $user['email'];
												$phone_number = $user['phone_number'];
												$approved = $user['approved'];
												$role = $user['role'];
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
												$company_id = $user['company_id'];
												$key_activated = $user['key_activated'];
												$payment_id = $user['payment_id'];
												$device_name = $user['device_name'];
												$device_id = $user['device_id'];
											}
										}
									}
									?>
											<div class="form-group">
													<label>Username : </label>
													<span> <?php echo $username; ?></span>
												</div>
												<div class="form-group">
													<label>Email : </label>
													<span> <?php echo $email; ?></span>
												</div>
												<div class="form-group">
													<label>Full Name : </label>
													<span> <?php echo $full_name; ?></span>
												</div>
												<div class="form-group">
													<label>Phone Number : </label>
													<span> <?php echo $phone_number; ?></span>
												</div>
												<div class="form-group">
													<label><!-- Select Company--> Attached Company : </label>
													<?php
														$collection = new MongoCollection($app_data, 'company');
														$companies = $collection->find();
														if($companies->count() > 0) 
														{?>
																<?php foreach($companies as $comp) {
																	if($company_id == $comp['company_ID']) 
																	{
																	echo $comp['company_name'];
																	$company_ref = $comp['company_ref'];
														} } } ?>
												</div>
												<div class="form-group">
													<label> <!-- Company Ref. ID--> Attached Company Ref. ID: </label>
													<span> <?php echo $company_ref_id; // echo $company_ref;?></span>
												</div>
												<div class="form-group">
													<label>User Company (Contractor Company) : </label><br/>
													<span><?php //echo $user_company; 
													$user_companies = json_decode($user_company);
													$collection = new MongoCollection($app_data, 'company');
													for($i=0;$i<count($user_companies);$i++)
													{
														$companies = $collection->find();
														foreach($companies as $company)
														{
															if (false !== $key = array_search($user_companies[$i], $company['contracted_ref_id'])) 
															{
																echo $i+1 .'. '. $company['contracted_name'][$key].' (Ref ID : '.$user_companies[$i].' ) <br/>';
															}
														}
													}
													?></span>
												</div>
												<!-- <div class="form-group">
													<label>User Company Ref. ID(Contractor Company) : </label>
													<span> <?php echo $user_company_ref_id; ?></span>
												</div>-->
												<div class="form-group">
													<label>Status : </label>
													<span><?php  echo $approved == 2 ? 'Disapproved' : ($approved == 1 ? 'Approved' : 'Pending'); ?></span>
												</div>
												<div class="form-group">
													<label>Role : </label>
													<?php 
													echo $role == 1 ? 'Super Admin' : ( $role == 2 ? 'Owner - Admin Company' : ( $role == 3 ? 'Admin (Adminstrative from Admin Company)' : ( $role == 4 ? 'User Company(Company)' : ( $role == 5 ? 'Key Holder -User (User of Company)' : '' ) ) ) );
													?>
												</div>
												<div class="form-group">
													<label>keys : </label><br/>
													<?php
													$key_ids = json_decode($key_id);
													$keys = $app_data->keys;
													$cursor = $keys->find();
													 if($cursor->count() > 0)
														  {
															  foreach($cursor as $keys)
															  {
																  if(in_array($keys['key_ID'],$key_ids))
																  { 
																	echo $keys['key_name'] . '<br/>';
															} } } ?>
													</div>
												<div class="form-group">
													<label> Key Activated(Key ID) : </label><br/>
													<?php 
													$key_activateds = json_decode($key_activated);
													$keys = $app_data->keys;
														  $cursor = $keys->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keys)
															  { 
																  if(in_array($keys['key_ID'],$key_activateds))
																  {
																	echo $keys['key_name']. '<br/>';
													} } } ?>
												</div>
												<div class="form-group">
													<label>Key Group ID : </label><br/>
													<?php
													$key_group_ids = json_decode($key_group_id);
													$keygroup = $app_data->keygroup;
														  $cursor = $keygroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keygroup)
															  {
																  if(in_array($keygroup['key_group_ID'],$key_group_ids))
																  {
																	echo $keygroup['key_group_name']. '<br/>';
															  } } } ?> 
												</div>
												<div class="form-group">
													<label>Lock Group ID : </label> <br/>
													<?php
													$lock_group_ids = json_decode($lock_group_id);
													$lockgroup = $app_data->lockgroup;
														  $cursor = $lockgroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $lockgroup)
															  { 
															  
															  if(in_array($lockgroup['lock_group_ID'],$lock_group_ids))
															  {
																	echo $lockgroup['lock_group_name']. '<br/>';
															  }}} ?>
												</div>
												<div class="form-group">
													<label> Payment ID : </label> 
													<?php
													$payment = $app_data->payment;
														  $cursor = $payment->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $payment)
															  {
																  if($payment_id == $payment['payment_ID'])
																  { ?> 
																	<?php echo 'Payment ID : '. $payment['payment_ID'] . ' & Amt:' . $payment['amount']; ?>
														  <?php } } } ?>
													
												</div>
												<div class="form-group">
													<label>Credit card Name : </label>
													<span> <?php echo $cc_name; ?></span>
												</div>
												<div class="form-group">
													<label>Credit Card Number : </label>
													<span> <?php echo $cc_num; ?></span>
												</div>
												<div class="form-group">
													<label>Credit Card ValidThru (in MM/YYYY format) : </label>
													<br/>
													<?php  echo $cc_validthru; ?>
												</div>
												<div class="form-group"> 
													<label> Device ID : </label>
													<span> <?php echo $device_id; ?></span>
												</div>
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
