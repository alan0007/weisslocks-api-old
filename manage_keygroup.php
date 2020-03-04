<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$keys = isset($_REQUEST['keys']) ? $_REQUEST['keys'] : array();
	if ( isset($_REQUEST['keys']) ){
		$keys = array_map('intval', $keys);
	}
	$keygroupData = array();
	
	$key_grp_user_id = isset($_REQUEST['key_grp_user_id']) ? $_REQUEST['key_grp_user_id'] : array();
	if (isset($_REQUEST['key_grp_user_id'])){
		$key_grp_user_id = array_map('intval', $key_grp_user_id);
	}
	
	if($_REQUEST['key_group_ID'] == 0)
	{
		$keygroup = $app_data->keygroup;
		$post = array(
				'key_group_ID'  => getNext_users_Sequence('key_group_ID'),
				'key_group_name' => $_REQUEST['key_group_name'],
				'key_grp_user_id' => $key_grp_user_id,
				'company_id' => (int) $_REQUEST['company_id'],
				//'key_id' => json_encode($keys),
				'key_id' => $keys,
				'added_by'  => (int) $current_user
			);
		//$keygroup->insert($post);
		if($keygroup->insert($post)){
			$Reg_Query = array('_id' => $post['_id'] ) ;
			$keygroupData = $keygroup->findOne( $Reg_Query );
		}
	}
	else
	{
		$collection = new MongoCollection($app_data, 'keygroup');
		$criteria = array('key_group_ID'=>(int) $_REQUEST['key_group_ID']);
		
		$keygroupData = $collection->findOne($criteria);
		//$previous_key_ids = array_map('intval',json_decode($keygroupData['key_id']));
		$previous_key_ids = array_map( 'intval',$keygroupData['key_id'] );
		$key_ids =  array_map('intval',$keys);
		$diff = array_values(array_diff($previous_key_ids,$key_ids));
		
		if(!empty($diff))
		{
			$integerIDs = array_map('intval', $diff );
			$keygroup = $app_data->keys;
			$arg = array('key_ID' => array('$in'=> $integerIDs ));
			$keygroupData = $keygroup->find($arg);
			if($keygroupData->count() > 0)
			{
				foreach($keygroupData as $keygroupsDatas)
				{
					//Make Int Array List
					$key_id_array = $keygroupsDatas['key_group_id'];
					$key_id_array = array_map('intval', $key_id_array);
					
					//$key_id_update = json_decode($key_id_array);
					$key_id_update = $key_id_array;
					
					if (false !== $key_exists = array_search( $_REQUEST['key_group_ID'] ,  $key_id_update )) 
					{
						unset($key_id_update[$key_exists]);
						 $keygroup->update( array('key_ID'=> $keygroupsDatas['key_ID']),
									 array('$set' => array(
											       //'key_group_id' => json_encode($key_id_update),
												   'key_group_id' => $key_id_update,
											 )
										 )
									 );
					}
				}
			}
		}
		
		
		
		$collection->update( $criteria ,array('$set' => array(
			       'key_group_name' => $_REQUEST['key_group_name'],
			       //'key_grp_user_id' => (int) $_REQUEST['key_grp_user_id'],
				   'key_grp_user_id' => $key_grp_user_id,
			       'company_id' => (int) $_REQUEST['company_id'],
			       //'key_id' => json_encode($keys),
				   'key_id' => $keys,
			       'updated_by'  => (int) $current_user,
		)));
		
		$keygroupData = $collection->findOne( $criteria );
	}
	
	// Update Key group id into keys collection...
	if(!empty($keygroupData)){
		$key_group_ID = (int) $keygroupData['key_group_ID'];
		$key_ids = array_map('intval',json_decode($keygroupData['key_id']));
		if(!empty($key_ids)){
			$keys = $app_data->keys;
			$arg = array('key_ID' => array('$in'=>$key_ids));
			$keysData = $keys->find($arg);
			//$keysData = iterator_to_array($keysData);
			//print_r($keysData);
			if(!empty($keysData)){
				foreach($keysData as $val){
					//$key_group_ids = array_map('intval',json_decode($val['key_group_id']));
					$key_group_ids = array_map( 'intval',$val['key_group_id'] );
					$key_group_ids = !empty($key_group_ids)? $key_group_ids : array();
					if(empty($key_group_ids) || !in_array($key_group_ID, $key_group_ids)){
						array_push($key_group_ids,$key_group_ID);
						$keys->update( array('key_ID'=>(int) $val['key_ID']),
									array('$set' => array(
											      //'key_group_id' => json_encode($key_group_ids),
												  'key_group_id' => $key_group_ids,
											)
										)
									);
					}		
				}
			}
		}
	}
	echo "<script>window.location='keygroup.php?sucess=true'</script>";
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Key Group</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="key_group_ID" value="<?php echo isset($_REQUEST['key_group_ID']) ? $_REQUEST['key_group_ID'] : 0;  ?>" />
									<?php
									$keys_group_name = '';
									//$key_grp_user_id = 0;
									$key_grp_user_id = 0;
									$company_id = 0; 
									$key_id = 0;
									
									if(isset($_REQUEST['key_group_ID']))
									{
										$keygroup = $app_data->keygroup;
										$cursor = $keygroup->find(array('key_group_ID' =>(int) $_REQUEST['key_group_ID']));
										 if($cursor->count() > 0)
										 {
											 foreach($cursor as $keygroup)
											 {
												$key_group_name = $keygroup['key_group_name'];
												$key_grp_user_id = $keygroup['key_grp_user_id'];
												$company_id = $keygroup['company_id'];
												//$key_id = json_decode($keygroup['key_id']);
												$key_id = $keygroup['key_id'];
											 }
										 }
									}
									?>
										<div class="form-group">
                                            <label> Key Group Name </label>
                                            <input class="form-control" name="key_group_name" value="<?php echo $key_group_name; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Owner ID (as Lock Group Admin)</label>
											<select name="key_grp_user_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0)
												{
													if($_SESSION['role'] == 1) { ?>
														<?php foreach($users as $user) { ?>
															<option <?php echo $key_grp_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } ?>
													<?php } else { ?>
														<?php foreach($users as $user) { 
														if($user['user_id'] == $current_user) {?>
															<option <?php echo $key_grp_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } } ?>
														<?php } } ?>
											</select>
                                        </div>
										<div class="form-group">
                                            <label>Select Company</label>
											
											
											
											
											<?php if($_SESSION['role'] == 1) { ?>
											
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
											<select name="company_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'company');
												$companies = $collection->find();
												if($companies->count() > 0) 
												{?>
														<?php foreach($companies as $comp) { 
														if(in_array($comp['company_ID'],$com))
															{?>
															<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
														<?php } }
												}
											?>
											</select>
											<?php } ?>
                                        </div>
										<?php if($_SESSION['role'] == 1) { ?>
										<div class="form-group">
                                            <label>Select Key</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'keys');
												$keys = $collection->find();
												if($keys->count() > 0) 
												{
													foreach($keys as $key) {
														//$key_group_ids = json_decode($key['key_group_id']);
														$key_group_ids = $key['key_group_id'];
													?>
															<input <?php if(is_array($key_id) && in_array($key['key_group_id'],$key_id) || in_array($_REQUEST['key_group_ID'],$key_group_ids)) { echo 'checked'; } ?> type="checkbox" name="keys[]" value="<?php echo $key['key_ID']; ?>" />
															<?php echo $key['key_name'].'<br/>'; ?>
														<?php } } ?>
                                        </div>
										<?php } else { ?>
										<div class="form-group">
                                            <label>Select Key</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'keys');
												$keys = $collection->find();
												if($keys->count() > 0) 
												{
													foreach($keys as $key) {
													if(in_array($key['company_id'],$com))
															{
																//$key_group_ids = json_decode($key['key_group_id']);
																$key_group_ids = $key['key_group_id'];
													?>
															<input <?php if(is_array($key_id) && in_array($key['key_ID'],$key_id) || in_array($_REQUEST['key_group_ID'],$key_group_ids)) { echo 'checked'; } ?> type="checkbox" name="keys[]" value="<?php echo $key['key_ID']; ?>" />
															<?php echo $key['key_name'].'<br/>'; ?>
															<?php } } } ?>
                                        </div>
										<?php } ?>
										
										<!-- Show Key Group User ID -->
										<div class="form-group">
                                            <label>Key Group User ID</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$search_query = array( 'company_id' => $company_id );
												$keygroup_user = $collection->find( $search_query ) ;
												if($keygroup_user->count() > 0) 
												{
													foreach($keygroup_user as $keygroup_user) {
														//$key_group_ids = json_decode($keygroup_user['key_group_id']);													
														$company_user_id = $keygroup_user['user_id'];
														$username = $keygroup_user['username'];
													?>
															
															<input <?php if(is_array($company_user_id) && in_array($company_user_id,$key_grp_user_id) || in_array($company_user_id,$key_grp_user_id)) { echo 'checked'; } ?> type="checkbox" name="key_grp_user_id[]" value="<?php echo $company_user_id; ?>" />
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
