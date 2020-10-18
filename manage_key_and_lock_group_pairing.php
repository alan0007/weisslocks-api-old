<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$msg = '';
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$users = isset($_REQUEST['users']) ? $_REQUEST['users'] : array();
	if (isset($_REQUEST['users'])){
		$users = array_map('intval', $users);
	}
	
	/*
	$key_group_id = isset($_REQUEST['key_group_id']) ? $_REQUEST['key_group_id'] : array();
	if (isset($_REQUEST['key_group_id'])){
		$key_group_id = array_map('intval', $key_group_id);
	}
	
	$lock_group_id = isset($_REQUEST['lock_group_id']) ? $_REQUEST['lock_group_id'] : array();
	if (isset($_REQUEST['lock_group_id'])){
		$lock_group_id = array_map('intval', $lock_group_id);
	}
	*/
	
	if($_REQUEST['keyLockGroup_ID'] == 0)
	{
			$user_reg = $app_data->KeyLockGroup;
			$post = array(
				'keyLockGroup_ID' => getNext_users_Sequence('keyLockGroup_ID'),
				'pairing_name'  => $_REQUEST['pairing_name'],
				//'lock_group_id'  => $_REQUEST['lock_group_id'],
				'lock_group_id'  => (int) $_REQUEST['lock_group_id'],
				//'key_group_id'  => $_REQUEST['key_group_id'],
				'key_group_id'  => (int) $_REQUEST['key_group_id'],
				'company_id'  => (int) $_REQUEST['company_id'],
				'users'  => $users,
				'key_time_restricted'  => (int) $_REQUEST['key_time_restricted'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'time_from_hh'  => $_REQUEST['time_from_hh'],
				'time_from_mm'  => $_REQUEST['time_from_mm'],
				'time_to_hh'  => $_REQUEST['time_to_hh'],
				'time_to_mm'  => $_REQUEST['time_to_mm'],
				'lat'  => $_REQUEST['lat'],
				'long'  => $_REQUEST['long'],
				'radius'  => $_REQUEST['radius'],
				'added_by'  => (int) $current_user,
                'allowed_days'=> $_REQUEST['allowed_days']
				);
			$user_reg->insert($post);
	}
	else
	{
		$collection = new MongoCollection($app_data, 'KeyLockGroup');
		$criteria = array('keyLockGroup_ID'=>(int) $_REQUEST['keyLockGroup_ID']);
		
		$collection->update( $criteria ,array('$set' => array(
				'pairing_name'  => $_REQUEST['pairing_name'],
				//'lock_group_id'  => $_REQUEST['lock_group_id'],
				'lock_group_id'  => (int) $_REQUEST['lock_group_id'],
				//'key_group_id'  => $_REQUEST['key_group_id'],
				'key_group_id'  => (int) $_REQUEST['key_group_id'],
				//'company_id'  => $_REQUEST['company_id'],
				'company_id'  => (int) $_REQUEST['company_id'],
				'users'  => $users,
				'key_time_restricted'  => (int) $_REQUEST['key_time_restricted'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'time_from_hh'  => $_REQUEST['time_from_hh'],
				'time_from_mm'  => $_REQUEST['time_from_mm'],
				'time_to_hh'  => $_REQUEST['time_to_hh'],
				'time_to_mm'  => $_REQUEST['time_to_mm'],
				'lat'  => $_REQUEST['lat'],
				'long'  => $_REQUEST['long'],
				'radius'  => $_REQUEST['radius'],
				'updated_by'  => (int) $current_user,
                'allowed_days'=> $_REQUEST['allowed_days']
		)));
	}
	echo "<script>window.location='key_and_lock_group_pairing.php?sucess=true'</script>";
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><!--Key Group and Lock Group Pairing--> Access Control </h1>
                </div>
				<?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                <?php } ?>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="keyLockGroup_ID" value="<?php echo isset($_REQUEST['keyLockGroup_ID']) ? $_REQUEST['keyLockGroup_ID'] : 0;  ?>" />
									<?php
									$pairing_name = '';
									//$lock_group_id = '';
									//$key_group_id = '';
									//$company_id = '';
									$lock_group_id = 0;
									$key_group_id = 0;
									$company_id = 0;
									$key_time_restricted = 1;
									$date_from = '';
									$date_to = '';
									$time_from_hh = '';
									$time_from_mm = '';
									$lat = '';
									$long = '';
                                    $radius = '';
                                    $allowed_days = array();
									
									if(isset($_REQUEST['keyLockGroup_ID']))
									{
										$users = $app_data->KeyLockGroup;
										$cursor = $users->find(array('keyLockGroup_ID' =>(int) $_REQUEST['keyLockGroup_ID']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $KeyLockGroup)
											{
												$pairing_name = $KeyLockGroup['pairing_name'];
												$lock_group_id = $KeyLockGroup['lock_group_id'];
												$key_group_id = $KeyLockGroup['key_group_id'];
												$company_id = $KeyLockGroup['company_id'];
												$selected_users = $KeyLockGroup['users'];
												$key_time_restricted = $KeyLockGroup['key_time_restricted'];
												$date_from = $KeyLockGroup['date_from'];
												$date_to = $KeyLockGroup['date_to'];
												$time_from_hh = $KeyLockGroup['time_from_hh'];
												$time_from_mm = $KeyLockGroup['time_from_mm'];
												$time_to_hh = $KeyLockGroup['time_to_hh'];
												$time_to_mm = $KeyLockGroup['time_to_mm'];
												$lat = $KeyLockGroup['lat'];
												$long = $KeyLockGroup['long'];
												$radius = $KeyLockGroup['radius'];
                                                $users = $KeyLockGroup['users'];
                                                $allowed_days = $KeyLockGroup['allowed_days'];
											}
										}
									}
									?>
									<div class="form-group">
										<label> <!--Pairing--> Access Control Name </label>
										<input class="form-control" type="text" name="pairing_name" value="<?php echo $pairing_name; ?>">
									</div>
									<?php if($_SESSION['role'] == 1) { ?>
												<div class="form-group">
													<label>Lock Group ID : </label> 
													<select class="form-control" name="lock_group_id">
													<?php $lockgroup = $app_data->lockgroup;
														  $cursor = $lockgroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $lockgroup)
															  { ?>
																	<option <?php echo $lock_group_id == $lockgroup['lock_group_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $lockgroup['lock_group_ID']; ?>"> <?php echo $lockgroup['lock_group_name']; ?></option>
														  <?php } } ?>
													</select>
												</div>
									<?php } else {
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
													<label>Lock Group ID : </label>
													<select class="form-control" name="lock_group_id">
													<?php $lockgroup = $app_data->lockgroup;
														  $cursor = $lockgroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $lockgroup)
															  { 
															  if(in_array($lockgroup['company_id'],$com))
																  {
															  ?>
																	<option <?php echo $lock_group_id == $lockgroup['lock_group_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $lockgroup['lock_group_ID']; ?>"> <?php echo $lockgroup['lock_group_name']; ?></option>
																  <?php } } } ?>
													</select>
												</div>
									<?php } ?>
									<?php if($_SESSION['role'] == 1) { ?>
												<div class="form-group">
													<label>Key Group ID : </label>
													<select class="form-control" name="key_group_id">
													<?php $keygroup = $app_data->keygroup;
														  $cursor = $keygroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keygroup)
															  { ?>
																	<option <?php echo $key_group_id == $keygroup['key_group_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $keygroup['key_group_ID']; ?>"> <?php echo $keygroup['key_group_name']; ?></option>
														  <?php } } ?>
													</select>
												</div>
												<?php } else { ?>
												<div class="form-group">
													<label>Key Group ID : </label>
													<select class="form-control" name="key_group_id">
													<?php $keygroup = $app_data->keygroup;
														  $cursor = $keygroup->find();
														  if($cursor->count() > 0)
														  {
															  foreach($cursor as $keygroup)
															  {
																 if(in_array($keygroup['company_id'],$com))
																  {
															  ?>
																	<option <?php echo $key_group_id == $keygroup['key_group_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $keygroup['key_group_ID']; ?>"> <?php echo $keygroup['key_group_name']; ?></option>
														  <?php } } } ?>
													</select>
												</div>
												<?php } ?>
												<?php if($_SESSION['role'] == 1) { ?>
												<div class="form-group">
													<label>Select Company</label>
													<select name="company_id" class="form-control company_id" required>
														<option value="0"> Select Conpany </option>
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
													<select name="company_id" class="form-control company_id" required>
														<option value="0"> Select Conpany </option>
													<?php
														$collection = new MongoCollection($app_data, 'company');
														$companies = $collection->find();
														if($companies->count() > 0) 
														{?>
																<?php foreach($companies as $comp) {
																	if(in_array($comp['company_ID'],$com))
																	{ ?>
																	<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
																<?php } } } ?>
													</select>
												</div>
												<?php } ?>
												<div class="form-group">
													<label>Contractors/Staffs</label>
													<div class="users">
													<?php
													 $collection = new MongoCollection($app_data, 'company');
													 $cursor = $collection->findOne( array('company_ID'=>(int) $company_id ) );
													 if(isset($cursor['company_ID']))
													 {
														$users =  json_decode($cursor['user_id']);
														 for($i=0;$i<count($users);$i++)
														 {
															$collection1 = new MongoCollection($app_data, 'users');
															$cursor_users = $collection1->findOne( array('user_id'=>(int) $users[$i] ) );
															 if(isset($cursor_users['user_id']) && in_array($cursor_users['role'],array(4,5)))
															 { ?>
																 <input id="user_<?php echo $users[$i]; ?>" <?php echo in_array( $cursor_users['user_id'] , $selected_users) ? 'checked' : ''; ?> type="checkbox" name="users[]" value="<?php echo $cursor_users['user_id']; ?>" /> 
																 <label for="user_<?php echo $users[$i]; ?>"><?php echo $cursor_users['username']; ?></label><br/>
																 <?php  } } } ?>
													</div>
												</div>
												<div class="form-group">
													<label> Key Time Restricted </label>
													<select name="key_time_restricted" class="form-control">
														<option <?php echo $key_time_restricted == 1 ? 'selected="selected"' : ''; ?> value="1"> Yes </option>
														<option <?php echo $key_time_restricted == 0 ? 'selected="selected"' : ''; ?> value="0"> No </option>
													</select>
												</div>
												<script>
												  $( function() {
													$( "#date_from,#date_to" ).datepicker({
													  dateFormat: "dd-mm-yy"
													});
												  } );
												</script>
												<div class="form-group">
													<label> Date from  </label>
													<input class="form-control" type="text" name="date_from" id="date_from" value="<?php echo $date_from; ?>">
												</div>
												<div class="form-group">
													<label> Date To  </label>
													<input class="form-control" type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
												</div>
												<div class="form-group">
													<label> Time From  </label>
													<select class="form-control" name="time_from_hh">
													<?php  for($i=0;$i<=23;$i++) {    ?>
														<option <?php echo $time_from_hh == sprintf("%02d", $i) ? 'selected="selected"' : ''; ?> value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
													<?php } ?>
													</select>
													<select class="form-control" name="time_from_mm">
													<?php  for($i=0;$i<=59;$i++) {    ?>
														<option <?php echo $time_from_mm == sprintf("%02d", $i) ? 'selected="selected"' : ''; ?> value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
													<?php } ?>
													</select>
												</div>
												<div class="form-group">
													<label> Time To  </label>
													<select class="form-control" name="time_to_hh">
													<?php  for($i=0;$i<=23;$i++) { ?>
														<option <?php echo $time_to_hh == sprintf("%02d", $i) ? 'selected="selected"' : ''; ?> value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
													<?php } ?>
													</select>
													<select class="form-control" name="time_to_mm">
													<?php  for($i=0;$i<=59;$i++) { ?>
														<option <?php echo $time_to_mm == sprintf("%02d", $i) ? 'selected="selected"' : ''; ?> value="<?php echo sprintf("%02d", $i); ?>"><?php echo sprintf("%02d", $i); ?></option>
													<?php } ?> 
													</select>
												</div>
										<div class="form-group">
                                            <label> Location Longitude </label>
                                            <input class="form-control" name="long" value="<?php echo $long; ?>">
                                        </div>
										<div class="form-group">
                                            <label> Location Latitude </label>
                                            <input class="form-control" name="lat" value="<?php echo $lat; ?>">
                                        </div>
										
										<div class="form-group">
                                            <label> Detection Radius </label>
                                            <input class="form-control" name="radius" value="<?php echo $radius; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label> Allowed Days in Week </label><br/>
                                            <input id="monday" type="checkbox" name="allowed_days[]" value="1"
                                                <?php echo in_array( '1' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="monday">Monday</label><br/>

                                            <input id="tuesday" type="checkbox" name="allowed_days[]" value="2"
                                                <?php echo in_array( '2' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="tuesday">Tuesday</label><br/>

                                            <input id="wednesday" type="checkbox" name="allowed_days[]" value="3"
                                                <?php echo in_array( '3' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="wednesday">Wednesday</label><br/>

                                            <input id="thursday" type="checkbox" name="allowed_days[]" value="4"
                                                <?php echo in_array( '4' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="thursday">Thursday</label><br/>

                                            <input id="friday" type="checkbox" name="allowed_days[]" value="5"
                                                <?php echo in_array( '5' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="friday">Friday</label><br/>

                                            <input id="saturday" type="checkbox" name="allowed_days[]" value="6"
                                                <?php echo in_array( '6' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="saturday">Saturday</label><br/>

                                            <input id="sunday" type="checkbox" name="allowed_days[]" value="0"
                                                <?php echo in_array( '0' , $allowed_days ) ? 'checked="checked"' : ''; ?>
                                            />
                                            <label for="sunday">Sunday</label><br/>

                                        </div>

                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
<script>
	$(document).ready(function(event){
		$(".company_id").change(function(event)
		{
			$.ajax({
			 type: "GET",
			 dataType:'json',
			 url: '<?php echo SITE_URL; ?>api/users.php?action=get&view=staff_contractors_by_com&com_id='+this.value,
			 success: function(response){
				 var htm = '';
				 if(response.status == 'true')
				 {
					 $.each(response.data, function(index, element) {
						htm += '<input id="user_'+element.user_id+'" type="checkbox" name="users[]" value="'+element.user_id+'" /> <label for="user_'+element.user_id+'">' + element.user_fullname + '</label><br/>';
					 });
				 }
				 $('.users').html(htm);
			 }
			});
		});
	});
</script>