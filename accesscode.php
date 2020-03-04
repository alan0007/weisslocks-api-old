<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$response = '';
$msg = '';

$role = $_SESSION['role'];
$current_user = $_SESSION['user_id'];

if(isset($_REQUEST['delete']) && $_REQUEST['delete'] != '')
{
	//$collection = new MongoCollection($app_data, 'history_log');
	//$collection->remove( array( 'history_id' =>(int) $_REQUEST['delete'] ) );
}

if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Send')
{ 
	$KeyLockGroup = $app_data->KeyLockGroup;
	$cursor = $KeyLockGroup->findOne(array('keyLockGroup_ID'=>(int)$_REQUEST['pairing_id']));
	if(!empty($cursor['keyLockGroup_ID']))
	{
		$paymentDate = date('d-m-Y H:i');
		$paymentDate=date('d-m-Y H:i', strtotime($paymentDate));;
		$contractDateBegin = date('d-m-Y H:i', strtotime( $cursor['date_from'] . ' ' . $cursor['time_from_hh'] . ':' . $cursor['time_from_mm']));
		$contractDateEnd = date('d-m-Y H:i', strtotime( $cursor['date_to'] . ' ' . $cursor['time_to_hh'] . ':' . $cursor['time_to_mm'] ));
		
		$die = 0;
		$current_time = date('H:i');
		$date1 = DateTime::createFromFormat('H:i', $current_time);
		$date2 = DateTime::createFromFormat('H:i', $cursor['time_from_hh'] . ':' . $cursor['time_from_mm']);
		$date3 = DateTime::createFromFormat('H:i', $cursor['time_to_hh'] . ':' . $cursor['time_to_mm']);
		
		if ($date1 > $date2 && $date1 < $date3)
		{ } // Current Time is between 
		else
		{
			$die = 1;
		}
		
		if (($paymentDate > $contractDateBegin) && ($paymentDate < $contractDateEnd))
		{ }  // Current Date is between 
		else
		{
			$die = 1;
		}
		
		if($die == 1)
		{
			$user_reg = $app_data->history_log;
			$start_time = date('d-m-Y H:i:s');
			$end_dt = $_REQUEST['date_from'] . ' ' . $_REQUEST['time_hh'] . '' . $_REQUEST['time_mm'];
			$post = array(
				'history_id' => getNext_users_Sequence('history_log'),
				'user_id'     =>(int)$_REQUEST['user_id'],
				'lock_id'     =>(int) $_REQUEST['lock_id'],
				'key_id'     =>(int) $_REQUEST['key_id'],
				'pairing_id'     =>(int) $_REQUEST['pairing_id'],
				'start_dt'     => $start_time,
				'end_dt'     => $end_dt,
				'Status' => 'false',
				'access_code' => 'Request Occures in Invalid Date & Time.',
				'requested_time'  => $start_time,
				'timer'  => '',
				);
			$user_reg->insert($post);
		}
	}// delete
	
	$future =  strtotime(date('d-m-Y H:i:s'));
	$old = strtotime(date($_REQUEST['date_from'].' '.$_REQUEST['time_hh'].':'.$_REQUEST['time_mm']));
	if($old >= $future && $die == 0)
	{
	$locks = $app_data->locks;
	$cursor = $locks->find(array('lock_ID' =>(int) $_REQUEST['lock_id']));
	if($cursor->count() > 0)
	{
		foreach($cursor as $company_detail)
		{
			$lock_name = $company_detail['lock_name'];
		}
	}
	
	$keys = $app_data->keys;
	$cursor = $keys->find(array('key_ID' =>(int) $_REQUEST['key_id']));
	if($cursor->count() > 0)
	{
		foreach($cursor as $keys)
		{
			$phone_number = $keys['key_phone_number'];
		}
	}
	
	$lock_name = str_replace(' ', '%20', $lock_name);
	$url = 'http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number;
	$response = file_get_contents($url);
	
		$start = new DateTime( $start_time );
		$end = new DateTime( $end_dt );
		$interval = $end->diff($start);
		$days = $interval->format('%d');
		$hours = 24 * $days + $interval->format('%h');
		$start_time = date('d-m-Y H:i:s');
		$end_dt = $_REQUEST['date_from'] . ' ' . $_REQUEST['time_hh'] . '' . $_REQUEST['time_mm'];
		$user_reg = $app_data->history_log;
		$post = array(
			'history_id' => getNext_users_Sequence('history_log'),
			'user_id'     =>(int)$_REQUEST['user_id'],
			'lock_id'     =>(int) $_REQUEST['lock_id'],
			'key_id'     =>(int) $_REQUEST['key_id'],
			'pairing_id'     =>(int) $_REQUEST['pairing_id'],
			'start_dt'     => $start_time,
			'end_dt'     => $end_dt,
			'Status' => 'true',
			'access_code' => $response,
			'requested_time'  => $start_time,
			'timer'  => $hours.':'.$interval->format('%i'),
	    );
		$user_reg->insert($post);
	}
	else
	{
		$msg = 'Invalid Date and Time you have Selected...';
	}
	
	
	if($die == 1)
	{
		$msg = 'You are not allowed for access at this time For Your Pairing Selection.';
	}
	
	
	
}
?>
<style>
    .navwrap li{
        list-style: none;
        display: inline;
    }
    .pag-selected {
        font-weight: bold;
        text-decoration: underline;
    }
    .navwrap a{
        color:black;
    }
</style>

<?php include("header.php");?>

<body>

    <div id="wrapper">

       <?php include("menu.php");?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Access Code</h1>
                </div>
				<div class="col-lg-12">
				<?php 
					echo '<pre>';
					if($response != '')
					{
							echo '<h4>Access Code Response : </h4><br/>';
							echo $response;
					}
					if($response == '' && isset($_REQUEST['lock_id']))
					{
						echo '<h4>Access Code Response : </h4><br/>';
						echo 'Something went wrong';
					}
					if($msg != '')
					{
						echo '<h4>'.$msg.'</h4>';
					}
					echo '</pre>';
				?>
				</div>
				
				<div class="col-lg-6">
				<form method="post">
				<div class="form-group">
						<label>Pairing ( Key & Lock Group Pairing )</label>
						<select name="pairing_id" class="form-control pairing_selection" required>
							<option value="">Select Pairing</option>
						<?php
							$collection = new MongoCollection($app_data, 'KeyLockGroup');
							$KeyLockGroups = $collection->find();
							if($KeyLockGroups->count() > 0) 
							{
							foreach($KeyLockGroups as $KeyLockGroup) 
							{
								if($role == 1) 
								{
								?>	
									<option value="<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>"> <?php echo $KeyLockGroup['pairing_name']; ?> </option>
									<?php 
								
								} 
							else
							{
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
									
									if(in_array($KeyLockGroup['company_id'],$com))
									{ ?>
										<option value="<?php echo $KeyLockGroup['keyLockGroup_ID']; ?>"> <?php echo $KeyLockGroup['pairing_name']; ?> </option>
										<?php
									}
							} } } ?>
						</select>
					</div>
					
					<div class="form-group">
						<label>User</label>
						<select name="user_id" class="form-control">
						<?php
							$collection = new MongoCollection($app_data, 'users');
							$users = $collection->find();
							if($users->count() > 0) 
							{
							foreach($users as $user) 
							{
								if($role == 1) { ?>
									<option value="<?php echo $user['user_id']; ?>"> <?php echo $user['full_name']; ?> </option>
								<?php
								}
								else
								{
									if($user['user_id'] == $current_user)
									{ ?>
								<option value="<?php echo $user['user_id']; ?>"> <?php echo $user['full_name']; ?> </option>
								<?php } }
							}
							} ?>
						</select>
					</div>
					
					<?php if($role == 1) { ?>
					
					<div class="form-group">
							<label>Key Group </label>
							<select name="key_group_id" class="form-control key_group_id" required>
							<option value="">Select Key Group</option>
							
							</select>
                    </div>
					
					<?php } else { ?>
					<div class="form-group">
							<label>Key Group </label>
							<select name="key_group_id" class="form-control key_group_id" required>
							<option value="">Select Key Group</option>
							</select>
                    </div>
					<?php } ?>
					 <div class="form-group">
							<label>Key </label>
							<select name="key_id" id="key_ids" class="form-control" required>
							<?php
								//$collection = new MongoCollection($app_data, 'keys');
								//$keys = $collection->find();
								//if($keys->count() > 0) {?>
										<?php //foreach($keys as $key) { ?>
											<!--<option value="<?php //echo $key['key_ID']; ?>"> <?php //echo $key['key_name']; ?> </option>-->
										<?php //} } ?>
							</select>
                    </div>
					<?php if($role == 1) { ?>
					
					 <div class="form-group">
						<label>Lock GroupID </label><br/>
						<select name="lock_group_id" id="lock_group_id" class="form-control" required>
						<option value=""> Select Lock Group </option>
						</select>
					</div>
					<?php } else { ?>
					
					  <div class="form-group">
						<label>Lock GroupID </label><br/>
						<select name="lock_group_id" id="lock_group_id" class="form-control" required>
						<option value=""> Select Lock Group </option>
						</select>
					</div>
					 <?php } ?>
					<div class="form-group">
						<label>Select Lock</label>
						<select name="lock_id" class="form-control" id="lock_ids" required>
						<?php
							// $collection = new MongoCollection($app_data, 'locks');
							// $locks = $collection->find();
							// if($locks->count() > 0) 
							// {
								// foreach($locks as $lock) { ?>
										<!--<option value="<?php echo $lock['lock_ID']; ?>"> <?php echo $lock['lock_name']; ?> </option>-->
									<?php // } } ?>
						</select>
					</div>
					<script>
					  $( function() {
						$( "#date_from" ).datepicker({
						  dateFormat: "dd-mm-yy"
						});
					  } );
					</script>
					<div class="form-group">
						<label> Date from  </label>
						<input class="form-control" type="text" name="date_from" id="date_from" value="<?php echo date('d-m-Y'); ?>">
					</div>
					<div class="form-group">
						<label> Time from  </label>
						<select class="form-control" name="time_hh">
							<?php  for($i=0;$i<=23;$i++) {    ?>
								<option value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
							<?php } ?>
						</select>
						<select class="form-control" name="time_mm">
							<?php  for($i=0;$i<=59;$i++) { ?>
								<option value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
							<?php } ?>
						</select>
					</div>
					<input type="submit" class="btn btn-default" value="Send" name="process">
					</form>
				</div>
                <div class="col-lg-12">
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>User Name</th>
											<th>Access Code</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
									  
									  
									 if($role == 1) 
									 {
										$users = $app_data->users;
									  $cursor = $users->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $user)
										  {
										  ?>
											 <tr>
												<th><?php echo $user['full_name']; ?></th>
												<th>
												
												
												<table class="table table-bordered table-hover" id="dataTables-example">
													<tr>
														<td>Lock Name</td>
														<td>Requested Date/Time</td>
														<td>Access Code Response</td>
													</tr>
												<?php
												$user_id = $user['user_id'];
												$collection_histry = new MongoCollection($app_data, 'history_log');
												$cursor = $collection_histry->find(array('user_id'=>$user_id))->sort(array("history_id" => -1));
												$j=0;
												foreach($cursor as $history)
												{
												$j++;
													$his_locks = $app_data->locks;
													$cursor_locks = $his_locks->find(array('lock_ID'=>$history['lock_id']));
													foreach($cursor_locks as $lokcs)
													{
														?>
														<tr>
															<td><?php echo $lokcs['lock_name']; ?></td>
															<td><?php echo $history['requested_time']; ?></td>
															<td><?php echo $history['access_code']; ?></td>
														</tr>
														
														<?php 
														//echo $j .'. Lock : '.$lokcs['lock_name'] . ' ==> Access Code : ' . $history['access_code'];
														?>
														<!-- <a onclick="return confirm('Are you sure?')" href="accesscode.php?delete=<?php echo $history['history_id']; ?>">Delete</a>-->
														<?php
														//echo '  -- Requested Time : '.$history['requested_time'];
														//echo '<hr/>';
													}
												}
												?>
												
												</table>
												
												</th>
												
											</tr>
									  <?php } } 
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="5">No Users Founds </td></tr>';
										}
									 }
									 else
									 {
										$users = $app_data->users;
									  $cursor = $users->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $user)
										  {
											  if($user['user_id'] == $current_user)
											  {
										  ?>
											 <tr>
												<th><?php echo $user['full_name']; ?></th>
												<th><?php
												$user_id = $user['user_id'];
												$collection_histry = new MongoCollection($app_data, 'history_log');
												$cursor = $collection_histry->find(array('user_id'=>$user_id))->sort(array("history_id" => -1));
												$j=0;
												foreach($cursor as $history)
												{
												$j++;
													$his_locks = $app_data->locks;
													$cursor_locks = $his_locks->find(array('lock_ID'=>$history['lock_id']));
													foreach($cursor_locks as $lokcs)
													{ ?>
														<tr>
															<td><?php echo $lokcs['lock_name']; ?></td>
															<td><?php echo $history['requested_time']; ?></td>
															<td><?php echo $history['access_code']; ?></td>
														</tr>
														<?php 
														//echo $j .'. Lock : '.$lokcs['lock_name'] . ' ==> Access Code : ' . $history['access_code'];
														?>
														<!-- <a onclick="return confirm('Are you sure?')" href="accesscode.php?delete=<?php echo $history['history_id']; ?>">Delete</a>-->
														<?php
														//echo '  -- Requested Time : '.$history['requested_time'];
														//echo '<hr/>';
													}
												}
												?></th>
											</tr>
									 <?php } } } } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php include("footer.php");?>
<script>
$( document ).ready(function() 
{
	$( ".pairing_selection" ).change(function() {
		$.ajax({
		 type: "GET",
		 dataType:'json',
		 url: 'http://app.weisslocks.com/api/locks_keys.php?action=get_pairing_groups&method=get&pairing_id='+this.value+'&user_id=<?php echo $current_user; ?>&role=<?php echo $role; ?>',
		 success: function(response){
			  $.each(response, function(index, element) {
				if(index == 'lockgroups')
				{
						var htm = '<option value="">Select Lock Group</option>';
						$.each(element, function(index_, element_) 
						{
							htm += '<option value="'+element_.lock_group_ID+'">'+element_.lock_group_name+'</option>';
						});
						$('#lock_group_id').html(htm);
				}
				if(index == 'keygroups')
				{
						var htm = '<option value="">Select Key Group</option>';
						$.each(element, function(index_, element_) 
						{
							htm += '<option value="'+element_.key_group_ID+'">'+element_.key_group_name+'</option>';
						});
						$('.key_group_id').html(htm);
				}
			});
		 }
		});
	});
	
	
	
	
	
	
	$( ".key_group_id" ).change(function() {
		$.ajax({
		 type: "GET",
		 dataType:'json',
		 url: 'http://app.weisslocks.com/api/locks_keys.php?action=keys_of_keygrp&method=get&keygrp_id='+this.value+'&user_id=<?php echo $current_user; ?>&role=<?php echo $role; ?>',
		 success: function(response){
			  $.each(response, function(index, element) {
				if(index == 'data')
				{
						var htm = '';
						$.each(element, function(index_, element_) 
						{
							htm += '<option value="'+element_.key_ID+'">'+element_.key_name+'</option>';
					});
					$('#key_ids').html(htm);
				}
			});
		 }
		});
	});
	
	
	
	
	$( "#lock_group_id" ).change(function() {
		$.ajax({
		 type: "GET",
		 dataType:'json',
		 url: 'http://app.weisslocks.com/api/locks_keys.php?action=locks_of_lockgrp&method=get&lockgrp_id='+this.value+'&user_id=<?php echo $current_user; ?>&role=<?php echo $role; ?>',
		 success: function(response){
			   $.each(response, function(index, element) {
				 if(index == 'data')
				 {
						 var htm = '';
						 $.each(element, function(index_, element_) 
						 {
							 htm += '<option value="'+element_.lock_ID+'">'+element_.lock_name+'</option>';
					 });
					 $('#lock_ids').html(htm);
				 }
			 });
		 }
		});
	});
});
</script>