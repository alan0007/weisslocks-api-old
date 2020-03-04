<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$lock_group_id = isset($_REQUEST['lock_group_id']) ? $_REQUEST['lock_group_id'] : array();
	
	if($_REQUEST['lock_ID'] == 0)
	{
		$user_company = $app_data->locks;
		$post = array(
				'lock_ID'  => getNext_users_Sequence('locks'),
				'company_id'  => $_REQUEST['company_id'],
				'lock_name'  => $_REQUEST['lock_name'],
				'serial_number'  => $_REQUEST['serial_number'],
				'lock_group_id'  => json_encode($lock_group_id),
				'linked_keys'  => $_REQUEST['linked_keys'],
				'lock_area'  => $_REQUEST['lock_area'],
				'lock_address'  => $_REQUEST['lock_address'],
				'lock_loc_unit'  => $_REQUEST['lock_loc_unit'],
				'lock_post_code'  => $_REQUEST['lock_post_code'],
				'lock_plate_num'  => $_REQUEST['lock_plate_num'],
				'added_by'  => (int) $current_user
			);
		$user_company->insert($post);
	}
	else
	{
		$collection = new MongoCollection($app_data, 'locks');
		$criteria = array('lock_ID'=>(int) $_REQUEST['lock_ID']);
		$collection->update( $criteria ,array('$set' => array(
				'company_id'  => $_REQUEST['company_id'],
				'lock_name'  => $_REQUEST['lock_name'],
				'serial_number'  => $_REQUEST['serial_number'],
				'lock_group_id'  => json_encode($lock_group_id),
				'linked_keys'  => $_REQUEST['linked_keys'],
				'lock_area'  => $_REQUEST['lock_area'],
				'lock_address'  => $_REQUEST['lock_address'],
				'lock_loc_unit'  => $_REQUEST['lock_loc_unit'],
				'lock_post_code'  => $_REQUEST['lock_post_code'],
				'lock_plate_num'  => $_REQUEST['lock_plate_num'],
				'updated_by'  => (int) $current_user
		)));
	}
	echo "<script>window.location='locks.php?sucess=true'</script>"; 
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Locks</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="lock_ID" value="<?php echo isset($_REQUEST['lock_ID']) ? $_REQUEST['lock_ID'] : 0;  ?>" />
									<?php
									$company_id = 0;
									$lock_name = '';
									$serial_number = '';
									$lock_group_id = 0;
									$linked_keys = '';
									$lock_area = '';
									$lock_address = '';
									$lock_loc_unit = '';
									$lock_post_code = '';
									$lock_plate_num = '';
									if(isset($_REQUEST['lock_ID']))
									{
										$locks_details = $app_data->locks;
										$cursor = $locks_details->find(array('lock_ID' =>(int) $_REQUEST['lock_ID']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $locks_detail)
											{
												$company_id = $locks_detail['company_id'];
												$lock_name = $locks_detail['lock_name'];
												$serial_number = $locks_detail['serial_number'];
												$lock_group_id = $locks_detail['lock_group_id'];
												$linked_keys = $locks_detail['linked_keys'];
												$lock_area = $locks_detail['lock_area'];
												$lock_address = $locks_detail['lock_address'];
												$lock_loc_unit = $locks_detail['lock_loc_unit'];
												$lock_post_code = $locks_detail['lock_post_code'];
												$lock_plate_num = $locks_detail['lock_plate_num'];
											}
										}
									}
									?>
										<div class="form-group">
                                            <label>Lock ID : </label>
                                            <?php echo isset($_REQUEST['lock_ID']) ? $_REQUEST['lock_ID'] : 0; ?>
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
											<select name="company_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'company');
												$companies = $collection->find();
												if($companies->count() > 0) 
												{
														foreach($companies as $comp) {
															if(in_array($comp['company_ID'],$com))
															{ ?>
															<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
														<?php } } } ?>
											</select>
                                        </div>
										<?php } ?>
										
										
										<div class="form-group">
                                            <label>Lock Name</label>
                                            <input class="form-control" name="lock_name" value="<?php echo $lock_name; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Serial Number</label>
                                            <input class="form-control" name="serial_number" value="<?php echo $serial_number; ?>">
                                        </div>
										
										
										
										
										
                                        <div class="form-group">
                                            <label>Lock GroupID </label><br/>
											
											
											<?php if($_SESSION['role'] == 1) { ?>
											
											<?php
												$lock_group_ids = json_decode($lock_group_id);
												$collection = new MongoCollection($app_data, 'lockgroup');
												$lockgroups = $collection->find();
												if($lockgroups->count() > 0) 
												{?>
														<?php foreach($lockgroups as $lockgroup) { ?>
															<input <?php echo in_array($lockgroup['lock_group_ID'],$lock_group_ids) ? 'checked' : ''; ?> type="checkbox" name="lock_group_id[]" value="<?php echo $lockgroup['lock_group_ID']; ?>" />
															<?php echo $lockgroup['lock_group_name'] . '<br/>'; ?>
															
														<?php }
												}
											?>
											
											<?php } else { ?>
											
											<?php
												$lock_group_ids = json_decode($lock_group_id);
												$collection = new MongoCollection($app_data, 'lockgroup');
												$lockgroups = $collection->find();
												if($lockgroups->count() > 0) 
												{
													foreach($lockgroups as $lockgroup) {
															if(in_array($lockgroup['company_id'],$com))
															{ ?>
															<input <?php echo in_array($lockgroup['lock_group_ID'],$lock_group_ids) ? 'checked' : ''; ?> type="checkbox" name="lock_group_id[]" value="<?php echo $lockgroup['lock_group_ID']; ?>" />
															<?php echo $lockgroup['lock_group_name'].'<br/>'; ?>
															<?php } } } ?>
											<?php } ?>
											
                                        </div>
                                        <!--<div class="form-group">
                                            <label>Linked Keys</label>
                                            <input class="form-control" name="linked_keys" value="<?php echo $linked_keys; ?>">
                                        </div>-->
										<div class="form-group">
                                            <label>Locks Area </label>
											<select name="lock_area" class="form-control">
												<option <?php echo $lock_area == 'North' ? 'selected="selected"' : ''; ?> value="North">North</option>
												<option <?php echo $lock_area == 'North East' ? 'selected="selected"' : ''; ?> value="North East">North East</option>
												<option <?php echo $lock_area == 'Central' ? 'selected="selected"' : ''; ?> value="Central">Central</option>
												<option <?php echo $lock_area == 'East' ? 'selected="selected"' : ''; ?> value="East">East</option>
												<option  <?php echo $lock_area == 'West' ? 'selected="selected"' : ''; ?>value="West">West</option>
											</select>
                                        </div>
										<div class="form-group">
                                            <label>Locks Address</label>
                                            <input class="form-control" name="lock_address" value="<?php echo $lock_address; ?>">
                                        </div>
										<div class="form-group">
                                            <label>Locks Location Unit Number</label>
                                            <input class="form-control" name="lock_loc_unit" value="<?php echo $lock_loc_unit; ?>">
                                        </div>
										<div class="form-group">
                                            <label>Locks Post Code</label>
                                            <input class="form-control" name="lock_post_code" value="<?php echo $lock_post_code; ?>">
                                        </div>
										<div class="form-group">
                                            <label>Locks License Plate Number (If locks is installed in vehicles)</label>
                                            <input class="form-control" name="lock_plate_num" value="<?php echo $lock_plate_num; ?>">
                                        </div>
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
