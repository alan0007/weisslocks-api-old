<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$locks = isset($_REQUEST['locks']) ? $_REQUEST['locks'] : array();
	if (isset($_REQUEST['locks'])){
		$locks = array_map('intval', $locks);
	}
	
	$lockgroupData = array();
	
	$lock_grp_user_id = isset($_REQUEST['lock_grp_user_id']) ? $_REQUEST['lock_grp_user_id'] : array();
	if (isset($_REQUEST['lock_grp_user_id'])){
		$lock_grp_user_id = array_map('intval', $lock_grp_user_id);
	}
	
	if($_REQUEST['lock_group_ID'] == 0)
	{
		$lockgroup = $app_data->lockgroup;
		$post = array(
				'lock_group_ID'  => getNext_users_Sequence('lock_group_id'),
				'lock_group_name' => $_REQUEST['lock_group_name'],
				//'lock_grp_user_id' => $_REQUEST['lock_grp_user_id'],
				'lock_grp_user_id' => $lock_grp_user_id,
				'company_id' => (int) $_REQUEST['company_id'],
				'lock_id' => json_encode($locks),
				'added_by'  => (int) $current_user
			);
		//$lockgroup->insert($post);
		if($lockgroup->insert($post)){
			$Reg_Query = array('_id' => $post['_id'] ) ;
			$lockgroupData = $lockgroup->findOne( $Reg_Query );
		}
	}
	else
	{
		$collection = new MongoCollection($app_data, 'lockgroup');
		$criteria = array('lock_group_ID'=>(int) $_REQUEST['lock_group_ID']);
		
		
		$lockData = $collection->findOne($criteria);
		//$previous_lock_ids = array_map('intval',json_decode($lockData['lock_id']));
		$previous_lock_ids = array_map('intval',$lockData['lock_id']);
		$lock_group_ids =  array_map('intval',$locks);
		$diff = array_values(array_diff($previous_lock_ids,$lock_group_ids));
		if(!empty($diff))
		{
			$integerIDs = array_map('intval', $diff );
			$keygroup = $app_data->locks;
			$arg = array('lock_ID' => array('$in'=> $integerIDs ));
			$keygroupData = $keygroup->find($arg);
			if($keygroupData->count() > 0)
			{
				foreach($keygroupData as $keygroupsDatas)
				{
					//Make Int Array List
					$lock_id_array = $keygroupsDatas['lock_group_id'];
					$lock_id_array = array_map('intval', $lock_id_array);
					
					//$key_id_update = json_decode($keygroupsDatas['lock_group_id']);
					$lock_id_update = $lock_id_array;
					
					if (false !== $key_exists = array_search( $_REQUEST['lock_group_ID'] ,  $lock_id_update )) 
					{
						unset($lock_id_update[$key_exists]);
						 $keygroup->update( array('lock_ID'=>(int) $keygroupsDatas['lock_ID']),
									 array('$set' => array(
											       //'lock_group_id' => json_encode($lock_id_update),
												   'lock_group_id' => $lock_id_update,
											 )
										 )
									 );
					}
				}
			}
		}
		
		$collection->update( $criteria ,array('$set' => array(
			       'lock_group_name' => $_REQUEST['lock_group_name'],
			       //'lock_grp_user_id' => $_REQUEST['lock_grp_user_id'],
				   'lock_grp_user_id' => $lock_grp_user_id,
			       'company_id' => (int) $_REQUEST['company_id'],
			       //'lock_id' => json_encode($locks),
				   'lock_id' => $locks,
			       'updated_by'  => (int) $current_user,
		)));
		 
		$lockgroupData = $collection->findOne( $criteria ); 
	}
	
	// Update lock group id into locks collection...
	if(!empty($lockgroupData)){
		$lock_group_ID = (int) $lockgroupData['lock_group_ID'];
		$lock_ids = array_map('intval',json_decode($lockgroupData['lock_id']));
		if(!empty($lock_ids)){
			$locks = $app_data->locks;
			$arg = array('lock_ID' => array('$in'=>$lock_ids));
			$locksData = $locks->find($arg);
			//$keysData = iterator_to_array($keysData);
			//print_r($keysData);
			if(!empty($locksData)){
				foreach($locksData as $val){
					//$lock_group_ids = array_map('intval',json_decode($val['lock_group_id']));
					$lock_group_ids = array_map('intval',$val['lock_group_id']);
					$lock_group_ids = !empty($lock_group_ids)? $lock_group_ids : array();
					if(empty($lock_group_ids) || !in_array($lock_group_ID, $lock_group_ids)){
						array_push($lock_group_ids,$lock_group_ID);
						$locks->update( array('lock_ID'=>(int) $val['lock_ID']),
									array('$set' => array(
											      //'lock_group_id' => json_encode($lock_group_ids),
												  'lock_group_id' => $lock_group_ids,
											)
										)
									);
					}		
				}
			}
		}
	}
	echo "<script>window.location='lockgroup.php?sucess=true'</script>";
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Lock Group</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="lock_group_ID" value="<?php echo isset($_REQUEST['lock_group_ID']) ? $_REQUEST['lock_group_ID'] : 0;  ?>" />
									<?php
									$locks_group_name = '';
									$lock_grp_user_id = 0;
									$company_id = 0; 									
									$lock_id = 0;
									if(isset($_REQUEST['lock_group_ID']))
									{
										$lockgroup = $app_data->lockgroup;
										$cursor = $lockgroup->find(array('lock_group_ID' =>(int) $_REQUEST['lock_group_ID']));
										 if($cursor->count() > 0)
										 {
											 foreach($cursor as $lockgroup)
											 { 
												$locks_group_name = $lockgroup['lock_group_name'];
												$lock_grp_user_id = $lockgroup['lock_grp_user_id'];
												$company_id = $lockgroup['company_id'];
												//$lock_id = json_decode($lockgroup['lock_id']);
												$lock_id = $lockgroup['lock_id'];
											 }
										 }
									}
									?>
										<div class="form-group">
                                            <label> Locks Group Name </label>
                                            <input class="form-control" name="lock_group_name" value="<?php echo $locks_group_name; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Owner ID (as Lock Group Admin)</label>
											<select name="lock_grp_user_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0) 
												{?>
											
											
											<?php if($_SESSION['role'] == 1) { 
														foreach($users as $user) { ?>
															<option <?php echo $lock_grp_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } 
										} else {
														foreach($users as $user) {
															if($user['user_id'] == $current_user) { ?>
															<option <?php echo $lock_grp_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } }
												} }
											?>
											</select>
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
                                            <label>Select Company</label>
											<select name="company_id" class="form-control">
											<?php
												
												$collection = new MongoCollection($app_data, 'company');
												$companies = $collection->find();
												if($companies->count() > 0) 
												{?>
														<?php foreach($companies as $comp) { 
														if(in_array($comp['company_ID'],$com))
															{  ?>
															<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
														<?php } }
												}
											?>
											</select>
                                        </div>
										<?php } ?>
										<div class="form-group">
                                            <label>Select Lock</label><br/>
											<?php if($_SESSION['role'] == 1) { ?>
											<?php
											$collection = new MongoCollection($app_data, 'locks');
												$locks = $collection->find();
												if($locks->count() > 0) 
												{
													foreach($locks as $lock) 
													{
														//$lock_group_ids = json_decode($lock['lock_group_id']);
														$lock_group_ids = $lock['lock_group_id'];
													?>
															<input <?php if(is_array($lock_id) && in_array($lock['lock_ID'],$lock_id) || in_array( $_REQUEST['lock_group_ID'], $lock_group_ids)) { echo 'checked'; } ?> type="checkbox" name="locks[]" value="<?php echo $lock['lock_ID']; ?>" />
															<?php echo $lock['lock_name'].'<br/>'; ?>
														<?php } } ?>
											
											
											<?php } else { ?>
											<?php
												$collection = new MongoCollection($app_data, 'locks');
												$locks = $collection->find();
												if($locks->count() > 0) 
												{
													foreach($locks as $lock) { 
														if(in_array($lock['company_id'],$com))
															{
																//$lock_group_ids = json_decode($lock['lock_group_id']);
																$lock_group_ids = $lock['lock_group_id'];
																?>
															<input <?php if(is_array($lock_id) && in_array($lock['lock_ID'],$lock_id) || in_array( $_REQUEST['lock_group_ID'], $lock_group_ids)) { echo 'checked'; } ?> type="checkbox" name="locks[]" value="<?php echo $lock['lock_ID']; ?>" />
															<?php echo $lock['lock_name'].'<br/>'; ?>
															<?php } } } ?>
											<?php } ?>
                                        </div>
										
										<!-- Show Key Group User ID -->
										<div class="form-group">
                                            <label>Lock Group User ID</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$search_query = array( 'company_id' => $company_id );
												$lockgroup_user = $collection->find( $search_query ) ;
												if($lockgroup_user->count() > 0) 
												{
													foreach($lockgroup_user as $lockgroup_user) {
														//$key_group_ids = json_decode($lockgroup_user['lock_group_id']);													
														//$lock_group_id = $lockgroup_user['lock_group_id'];
														$company_user_id = $lockgroup_user['user_id'];
														$username = $lockgroup_user['username'];
													?>
															
															<input <?php if(is_array($company_user_id) && in_array($company_user_id,$lock_grp_user_id) || in_array($company_user_id,$lock_grp_user_id)) { echo 'checked'; } ?> type="checkbox" name="lock_grp_user_id[]" value="<?php echo $company_user_id; ?>" />
															<?php echo $username.'<br/>'; ?>
													<?php 
													} 
												} ?>
                                        </div>
										
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
