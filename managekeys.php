<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$key_group_id = isset($_REQUEST['key_group_id']) ? $_REQUEST['key_group_id'] : array();
	if (isset($_REQUEST['key_group_id'])){
		$key_group_id = array_map('intval', $key_group_id);
	}
	$keysData = array();
	
	$key_user_id = isset($_REQUEST['key_user_id']) ? $_REQUEST['key_user_id'] : array();
	if (isset($_REQUEST['key_user_id'])){
		$key_user_id = array_map('intval', $key_user_id);
	}
	
	
	if($_REQUEST['key_ID'] == 0)
	{
		$keys = $app_data->keys;
		$post = array(
				'key_ID'  => getNext_users_Sequence('keys'),
				'lock_id'  => (int) $_REQUEST['lock_id'],
				'key_name'  => $_REQUEST['key_name'],
				'key_phone_number'  => $_REQUEST['key_phone_number'],
				'key_serial_number'  => $_REQUEST['key_serial_number'],
				//'key_group_id'  => json_encode($key_group_id),
				'key_group_id'  => $key_group_id,
				'company_id'  => (int) $_REQUEST['company_id'],
				'key_user_id'  => $key_user_id,
				'status'  => (int) 0,
				'added_by'  => (int) $current_user,
				'activated_on' => ''
			);
		//$keys->insert($post);
		if($keys->insert($post)){
			$Reg_Query = array('_id' => $post['_id'] ) ;
			$keysData = $keys->findOne( $Reg_Query );
		}
	}else{
		$collection = new MongoCollection($app_data, 'keys');
		$criteria = array('key_ID'=>(int) $_REQUEST['key_ID']);
		
		
		$keyData = $collection->findOne($criteria);
		$previous_key_ids = array_map('intval',json_decode($keyData['key_group_id']));
		$key_group_ids =  array_map('intval',$key_group_id);
		$diff = array_values(array_diff($previous_key_ids,$key_group_ids));
		if(!empty($diff))
		{
			$integerIDs = array_map('intval', $diff );
			$keygroup = $app_data->keygroup;
			$arg = array('key_group_ID' => array('$in'=> $integerIDs ));
			$keygroupData = $keygroup->find($arg);
			if($keygroupData->count() > 0)
			{
				foreach($keygroupData as $keygroupsDatas)
				{
					$key_id_update = json_decode($keygroupsDatas['key_id']);
					//$key_id_update = $keygroupsDatas['key_id'];
					if (false !== $key_exists = array_search( $_REQUEST['key_ID'] ,  $key_id_update )) 
					{
						unset($key_id_update[$key_exists]);
						$keygroup->update( 
							array(
								'key_group_ID'=>(int) $keygroupsDatas['key_group_ID']),
								array('$set' => array(
									'key_id' => json_encode( $key_id_update ),
								)
							)
						 );
					}
					
					
					
				}
			}
		}
		
		
		
		$collection->update( $criteria ,array('$set' => array(
				'lock_id'  => (int) $_REQUEST['lock_id'],
				'key_name'  => $_REQUEST['key_name'],
				'key_phone_number'  => $_REQUEST['key_phone_number'],
				'key_serial_number'  => $_REQUEST['key_serial_number'],
				//'key_group_id'  => json_encode($key_group_id),
				'key_group_id'  => $key_group_id,
				'company_id'  => (int) $_REQUEST['company_id'],
				//'key_user_id'  => $current_user,
				//'status'  => $_REQUEST['status']
				'updated_by'  => (int) $current_user,
		)));
		
		$keysData = $collection->findOne( $criteria );
	}
	
	// Update Key id into keygroup collection...
	if(!empty($keysData)){
		$key_ID = (int) $keysData['key_ID'];
		$key_group_ids = array_map('intval',json_decode($keysData['key_group_id']));
		if(!empty($key_group_ids)){
			$keygroup = $app_data->keygroup;
			$arg = array('key_group_ID' => array('$in'=>$key_group_ids));
			$keygroupData = $keygroup->find($arg);
			//$keygroupData = iterator_to_array($keygroupData);
			//print_r($keygroupData);
			if(!empty($keygroupData)){
				foreach($keygroupData as $val){
					$key_ids = array_map('intval',json_decode( $val['key_id']) );
					$key_ids = !empty($key_ids)? $key_ids : array();
					if(empty($key_ids) || !in_array($key_ID, $key_ids)){
						array_push($key_ids,$key_ID);
						$keygroup->update( array('key_group_ID'=>(int) $val['key_group_ID']),
									array('$set' => array(
											      'key_id' => json_encode($key_ids),
												  
											)
										)
									);
					}		
				}
			}
		}
	}
	echo "<script>window.location='keys.php?sucess=true'</script>"; 
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Keys</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="key_ID" value="<?php echo isset($_REQUEST['key_ID']) ? $_REQUEST['key_ID'] : 0;  ?>" />
									<?php
									$lock_id = 0;
									$key_name = '';
									$key_serial_number = '';
									$key_group_id = array();
									$company_id = 0;
									$key_user_id = array();
									$status = 0;
									$key_phone_number = '0';
									if(isset($_REQUEST['key_ID']))
									{
										$keys_details = $app_data->keys;
										$cursor = $keys_details->find(array('key_ID' =>(int) $_REQUEST['key_ID']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $keys_detail)
											{
												$lock_id = $keys_detail['lock_id'];
												$key_name = $keys_detail['key_name'];
												$key_serial_number = $keys_detail['key_serial_number'];
												$key_group_id = $keys_detail['key_group_id'];												
												$company_id = (int) $keys_detail['company_id'];
												$key_user_id = $keys_detail['key_user_id'];
												$status = $keys_detail['status'];
												$key_phone_number = $keys_detail['key_phone_number'];
												
												// For checking key in key group only
												/*
												$arrayCount = sizeof($key_group_id);
												for ($i=0;$i<=$arrayCount;$i++){
													echo $key_group_id[$i];
												}
												*/
												// For user in key_user_id only
												
												$arrayCount = sizeof($key_user_id);
												for ($i=0;$i<=$arrayCount;$i++){
													echo $key_user_id[$i];
												}
												
											}
										}
									}
									?>
									<?php if($_SESSION['role'] == 1) { ?>
										<div class="form-group">
                                            <label>Select Locks</label>
											<select name="lock_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'locks');
												$locks = $collection->find();
												if($locks->count() > 0) 
												{?>
														<?php foreach($locks as $lock) { ?>
															<option <?php echo $lock_id == $lock['lock_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $lock['lock_ID']; ?>"> <?php echo $lock['lock_name']; ?> </option>
														<?php }
												}
											?>
											</select>
                                        </div>
									<?php } else { ?>
										<?php 
											$com = array();
											$collection1 = new MongoCollection($app_data, 'company');
												$companies = $collection1->find();
												if($companies->count() > 0) 
												{
													foreach($companies as $company)
													{
														$users = json_decode($company['user_id']);
														if(in_array($current_user,$users))
														{
															$com[] = $company['company_ID'];
														}
													}
												}
											?>
										<div class="form-group">
                                            <label>Select Locks</label>
											<select name="lock_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'locks');
												$locks = $collection->find();
												if($locks->count() > 0) 
												{?>
														<?php foreach($locks as $lock) 
														{
														if(in_array($lock['company_id'],$com))
															{
														?>
															<option <?php echo $lock_id == $lock['lock_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $lock['lock_ID']; ?>"> <?php echo $lock['lock_name']; ?> </option>
														<?php } }
												}
											?>
											</select>
                                        </div>
									<?php } ?>
										<div class="form-group">
                                            <label>Key Name</label>
                                            <input class="form-control" name="key_name" value="<?php echo $key_name; ?>">
                                        </div>
										
										
										<?php if($_SESSION['role'] == 1) { ?>
										
										<div class="form-group">
                                            <label>Phone Number</label>
                                            <input id="quantity" class="form-control" name="key_phone_number" value="<?php echo $key_phone_number; ?>">
                                        </div>
										<script>
										$(document).ready(function () {
										  $("#quantity").keypress(function (e) {
											 if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
													   return false;
											}
										   });
										});
										</script>
										<?php } ?>
                                        <div class="form-group">
                                            <label>Serial Number</label>
                                            <input class="form-control" name="key_serial_number" value="<?php echo $key_serial_number; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Key GroupID </label>
											<br/>
											<?php if($_SESSION['role'] == 1) { ?>
											
												<?php
												//$key_group_ids = json_decode($key_group_id);
												$key_group_ids = $key_group_id;
												$collection = new MongoCollection($app_data, 'keygroup');
												$keygroups = $collection->find();
												if($keygroups->count() > 0) 
												{
													foreach($keygroups as $keygroup) { ?>
														<input <?php echo in_array( $keygroup['key_group_ID'] , $key_group_ids ) ? 'checked="checked"' : ''; ?> type="checkbox" name="key_group_id[]" value="<?php echo $keygroup['key_group_ID']; ?>" />
														<?php echo $keygroup['key_group_name'] . '<br/>';
													} } ?>
													
											<?php } else { ?>
											
											<?php
												//$key_group_ids = json_decode($key_group_id);
												$key_group_ids = $key_group_id;
												$collection = new MongoCollection($app_data, 'keygroup');
												$keygroups = $collection->find();
												if($keygroups->count() > 0) 
												{
													foreach($keygroups as $keygroup) {
														if(in_array($keygroup['company_id'],$com))
															{ ?>
														<input <?php echo in_array( $keygroup['key_group_ID'] , $key_group_ids ) ? 'checked="checked"' : ''; ?> type="checkbox" name="key_group_id[]" value="<?php echo $keygroup['key_group_ID']; ?>" />
														<?php echo $keygroup['key_group_name'] . '<br/>';
														} } } ?>
											<?php } ?>
													
                                        </div>
										
										<?php if($_SESSION['role'] == 1) { ?>
                                        <div class="form-group">
                                            <label>Select Company</label>
											<select name="company_id" class="form-control">
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
										<?php } else { ?>
										<div class="form-group">
                                            <label>Select Company</label>
											<select name="company_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'company');
												$companies = $collection->find();
												if($companies->count() > 0) 
												{?>
														<?php foreach($companies as $comp) { 
														
														if(in_array($comp['company_ID'],$com))
															{
														?>
															<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
														<?php } }
												}
											?>
											</select>
                                        </div>
										
										<?php } ?>
										
										<div class="form-group">
                                            <label>Key Activated By User</label>
											<br/>
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0) 
												{
													foreach($users as $user) 
													{
														 if($key_user_id == $user['user_id'])
														 {
															 echo $user['full_name'] . ' ( Username : '.$user['username'].' )';
														 }
													}
												}
											?>
											<!--<select name="key_user_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0) 
												{?>
														<?php foreach($users as $user) { ?>
															<option <?php echo $key_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } } ?>
											</select>-->
                                        </div>
										<div class="form-group">
                                            <label>Status</label>
											<br/>
											<?php echo $status == 0 ? 'Deactivated' : 'Activated'; ?>
											<!--<select name="status" class="form-control">
												<option <?php echo $status == 0 ? 'selected="selected"' : ''; ?> value="0"> Deactivate </option>
												<option <?php echo $status == 1 ? 'selected="selected"' : ''; ?> value="1"> Activate </option>
											</select>-->
                                        </div>
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
