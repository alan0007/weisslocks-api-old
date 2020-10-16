<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$lock_group_id = isset($_REQUEST['lock_group_id']) ? $_REQUEST['lock_group_id'] : array();
	if (isset($_REQUEST['lock_group_id'])){
		$lock_group_id = array_map('intval', $lock_group_id);
	}
	$locksData = array();
	if($_REQUEST['lock_ID'] == 0)
	{
		$locks = $app_data->locks;
		$post = array(
            'lock_ID'  => getNext_users_Sequence('locks'),
            'company_id'  => (int) $_REQUEST['company_id'],
            'lock_name'  => $_REQUEST['lock_name'],
            'serial_number'  => $_REQUEST['serial_number'],
            'log_number'  => $_REQUEST['log_number'],
            //'lock_group_id'  => json_encode($lock_group_id),
            'lock_group_id'  => $lock_group_id,
            'linked_keys'  => $_REQUEST['linked_keys'],
            'lock_area'  => $_REQUEST['lock_area'],
            'lock_address'  => $_REQUEST['lock_address'],
            'lock_loc_unit'  => $_REQUEST['lock_loc_unit'],
            'lock_post_code'  => $_REQUEST['lock_post_code'],
            'lock_plate_num'  => $_REQUEST['lock_plate_num'],
            'site_id'  => $_REQUEST['site_id'],
            'added_by'  => (int) $current_user,
            'lock_type'  => $_REQUEST['lock_type'],
            'lock_model'  => $_REQUEST['lock_model'],
            'lock_mechanism'  => $_REQUEST['lock_mechanism'],
            'brand'  => $_REQUEST['brand'],
            'entrance_visibility'  => $_REQUEST['entrance_visibility'],
            // Added 2020-10-15
            'display_name'  => $_REQUEST['display_name'],
        );
		//$locks->insert($post);
		if($locks->insert($post)){
			$Reg_Query = array('_id' => $post['_id'] );
			$locksData = $locks->findOne( $Reg_Query );
		}
	}
	else
	{
		$collection = new MongoCollection($app_data, 'locks');
		$criteria = array('lock_ID'=>(int) $_REQUEST['lock_ID']);
		
		$lockData = $collection->findOne($criteria);
		$previous_lock_ids = array_map('intval',json_decode($lockData['lock_group_id']));
		$lock_group_ids =  array_map('intval',$lock_group_id);
		$diff = array_values(array_diff($previous_lock_ids,$lock_group_ids));
		if(!empty($diff))
		{
			$integerIDs = array_map('intval', $diff );
			$keygroup = $app_data->lockgroup;
			$arg = array('lock_group_ID' => array('$in'=> $integerIDs ));
			$keygroupData = $keygroup->find($arg);
			if($keygroupData->count() > 0)
			{
				foreach($keygroupData as $keygroupsDatas)
				{
					$key_id_update = json_decode($keygroupsDatas['lock_id']);
					if (false !== $key_exists = array_search( $_REQUEST['lock_ID'] ,  $key_id_update )) 
					{
						unset($key_id_update[$key_exists]);
						$keygroup->update( 
							array('lock_group_ID'=>(int) $keygroupsDatas['lock_group_ID']),
								array('$set' => array(
									'lock_id' => json_encode($key_id_update),
								)
							)
						);
									 
					}
				}
			}
		}
		
		
		$collection->update( $criteria ,array('$set' => array(
            'company_id'  => (int) $_REQUEST['company_id'],
            'lock_name'  => $_REQUEST['lock_name'],
            'serial_number'  => $_REQUEST['serial_number'],
            'log_number'  => $_REQUEST['log_number'],
            //'lock_group_id'  => json_encode($lock_group_id),
            'lock_group_id'  => $lock_group_id,
            'linked_keys'  => $_REQUEST['linked_keys'],
            'lock_area'  => $_REQUEST['lock_area'],
            'lock_address'  => $_REQUEST['lock_address'],
            'lock_loc_unit'  => $_REQUEST['lock_loc_unit'],
            'lock_post_code'  => $_REQUEST['lock_post_code'],
            'lock_plate_num'  => $_REQUEST['lock_plate_num'],
            'site_id'  => $_REQUEST['site_id'],
            'updated_by'  => (int) $current_user,
            'lock_type'  => $_REQUEST['lock_type'],
            'lock_model'  => $_REQUEST['lock_model'],
            'lock_mechanism'  => $_REQUEST['lock_mechanism'],
            'brand'  => $_REQUEST['brand'],
            'entrance_visibility'  => $_REQUEST['entrance_visibility'],
            'display_name'  => $_REQUEST['display_name']
		)));
		$locksData = $collection->findOne( $criteria );
	}
	
	// Update lock id into lockgroup collection...
	if(!empty($locksData)){
		$lock_ID = (int) $locksData['lock_ID'];
		$lock_group_ids = array_map('intval',json_decode($locksData['lock_group_id']));
		if(!empty($lock_group_ids)){
			$lockgroup = $app_data->lockgroup;
			$arg = array('lock_group_ID' => array('$in'=>$lock_group_ids));
			$lockgroupData = $lockgroup->find($arg);
			//$keygroupData = iterator_to_array($keygroupData);
			//print_r($keygroupData);
			if(!empty($lockgroupData)){
				foreach($lockgroupData as $val){
					$lock_ids = array_map('intval',json_decode($val['lock_id']));
					$lock_ids = !empty($lock_ids)? $lock_ids : array();
					if(empty($lock_ids) || !in_array($lock_ID, $lock_ids)){
						array_push($lock_ids,$lock_ID);
						$lockgroup->update( array('lock_group_ID'=>(int) $val['lock_group_ID']),
									array('$set' => array(
											      'lock_id' => json_encode($lock_ids),
											)
										)
									);
					}		
				}
			}
		}
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
									$log_number = '';
									$lock_group_id = array();
									$linked_keys = array();
									$lock_area = '';
									$lock_address = '';
									$lock_loc_unit = '';
									$lock_post_code = '';
									$lock_plate_num = '';
                                    $lock_type = '';
                                    $lock_model = '';
                                    $lock_mechanism = '';
                                    $brand = '';
                                    $entrance_visibility = '';
                                    $display_name = '';
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
												$log_number = $locks_detail['log_number'];
												$lock_group_id = $locks_detail['lock_group_id'];
												$linked_keys = $locks_detail['linked_keys'];
												$lock_area = $locks_detail['lock_area'];
												$lock_address = $locks_detail['lock_address'];
												$lock_loc_unit = $locks_detail['lock_loc_unit'];
												$lock_post_code = $locks_detail['lock_post_code'];
												$lock_plate_num = $locks_detail['lock_plate_num'];
                                                $lock_type = $locks_detail['lock_type'];
                                                $lock_model = $locks_detail['lock_model'];
                                                $lock_mechanism = $locks_detail['lock_mechanism'];
                                                $brand = $locks_detail['brand'];
                                                $entrance_visibility =  $locks_detail['entrance_visibility'];
												$site_id = $locks_detail['site_id'];

												$display_name = '';
                                                if (isset($locks_detail['display_name'])){
                                                    $display_name = $locks_detail['display_name'];
                                                }


												// For checking key in key group only
												/*
												$arrayCount = sizeof($lock_group_id);
												for ($i=0;$i<=$arrayCount;$i++){
													echo $lock_group_id[$i];
												}
												*/
												
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
                                            <label>Display Name</label>
                                            <input class="form-control" name="display_name" value="<?php echo $display_name; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Lock Type</label>
                                            <input class="form-control" name="lock_type" value="<?php echo $lock_type; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Lock Model</label>
                                            <input class="form-control" name="lock_model" value="<?php echo $lock_model; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Lock Mechanism</label>
                                            <input class="form-control" name="lock_mechanism" value="<?php echo $lock_mechanism; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Entrance Visibility</label>
                                            <select name="entrance_visibility" class="form-control">
                                                <option <?php echo $entrance_visibility == 'Hidden' ? 'selected="selected"' : ''; ?> value="Hidden">Hidden</option>
                                                <option <?php echo $entrance_visibility == 'Covered' ? 'selected="selected"' : ''; ?> value="Covered">Covered</option>
                                                <option <?php echo $entrance_visibility == 'Visible' ? 'selected="selected"' : ''; ?> value="Visible">Visible</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Lock Brand</label>
                                            <input class="form-control" name="brand" value="<?php echo $brand; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Serial Number</label>
                                            <input class="form-control" name="serial_number" value="<?php echo $serial_number; ?>">
                                        </div>
										
										<!-- Added for Bluetooth Lock -->
										<div class="form-group">
                                            <label>Log Number</label>
                                            <input class="form-control" name="log_number" value="<?php echo $log_number; ?>">
                                        </div>
										
										
										
                                        <div class="form-group">
                                            <label>Lock GroupID </label><br/>
											
											
											<?php if($_SESSION['role'] == 1) { ?>
											
											<?php
												//$lock_group_ids = json_decode($lock_group_id);
												$lock_group_ids = $lock_group_id;
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
												//$lock_group_ids = json_decode($lock_group_id);
												$lock_group_ids = $lock_group_id;
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
										
										<!-- Added Bluetooth Site ID -->
										<div class="form-group">
                                            <label>Bluetooth Site ID (If locks is bluetooth lock)</label>
                                            <input class="form-control" name="site_id" value="<?php echo $site_id; ?>">
                                        </div>
										
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
