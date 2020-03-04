<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

//View List of Access Control Keys/Lock Group Pairing
if($_REQUEST['action'] == 'view' && $_REQUEST['method'] == 'access_locks_keys' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	
	if($_REQUEST['user_id'] == 1)
	{
	$collection = new MongoCollection($app_data, 'KeyLockGroup');
    $KeyLockGroup = $collection->find();
    if($KeyLockGroup->count() > 0) 
	{
		$response['status'] = 'true';
		$i=0;
		foreach($KeyLockGroup as $KeyLockGroups)
		{
				$pair_users_arr = array();
				$pair_users = $KeyLockGroups['users'];
				for($u=0;$u<=count($pair_users);$u++)
				{
					$users_d = new MongoCollection($app_data, 'users');
					$users_details = $users_d->findOne(array('user_id'=>(int)$pair_users[$u]));
					if(isset($users_details['user_id']))
					{
						unset($users_details['_id']);
						$pair_users_arr[] = $users_details;
					}
				}
			  $from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
			  $to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';
			  $pairing_name = !empty($KeyLockGroups['pairing_name']) ? $KeyLockGroups['pairing_name'] : '';
			  $response['data']['pairing'][] = array('paring_id'=>$KeyLockGroups['keyLockGroup_ID'],'paring_name'=>$pairing_name,
			  'date' => date("d.m.Y", strtotime( $KeyLockGroups['date_from'] )) . ' - ' . date("d.m.Y", strtotime( $KeyLockGroups['date_to'] )),
			  'start_date' => date("d.m.Y", strtotime( $KeyLockGroups['date_from'] )),
			  'end_date' => date("d.m.Y", strtotime( $KeyLockGroups['date_to'] )),
			  'time' => $KeyLockGroups['time_from_hh'].':'.$KeyLockGroups['time_from_mm'] . ' - ' . $KeyLockGroups['time_to_hh'] . ':' . $KeyLockGroups['time_to_mm'],
			  'users' => $pair_users_arr
			  );
			  $collection1 = new MongoCollection($app_data, 'lockgroup');
			  $lockgroup = $collection1->find(array('lock_group_ID'=>(int)$KeyLockGroups['lock_group_id']));
			  $response['data']['pairing'][$i]['lockgroup_status'] = 'false';
			  if($lockgroup->count() > 0) 
			  {
				  $response['data']['pairing'][$i]['lockgroup_status'] = 'true';
				  $j=0;
				  foreach($lockgroup as $lockgroups)
				  {
					  $response['data']['pairing'][$i]['lockgroup'] = array('lock_group_ID'=>$lockgroups['lock_group_ID'],'lock_group_name'=>$lockgroups['lock_group_name']);
					  $lock_ids = json_decode($lockgroups['lock_id']);
					  $response['data']['pairing'][$i]['lockgroup']['locks'] = array( );
					  for($l=0;$l<=count( $lock_ids );$l++)
					  {
						  $collection2 = new MongoCollection($app_data, 'locks');
						  $lock_id_details = $collection2->find(array('lock_ID'=>(int)$lock_ids[$l]));
						  if($lock_id_details->count() > 0) 
						  {
							  foreach($lock_id_details as $lock_id_detail)
							  {
								$response['data']['pairing'][$i]['lockgroup']['locks'][] = array('lock_ID'=> $lock_id_detail['lock_ID'],'lock_name'=> $lock_id_detail['lock_name'] );
							  }
						  }
					  }
					 $j++;
				  }
			 }

			  $response['data']['pairing'][$i]['keygroup_status'] = 'false';
			  $collection3 = new MongoCollection($app_data, 'keygroup');
			  $keygroup = $collection3->find(array('key_group_ID'=>(int)$KeyLockGroups['key_group_id']));
			  if($keygroup->count() > 0) 
			  {
				  $response['data']['pairing'][$i]['keygroup_status'] = 'true';
				  $k=0;
				  foreach($keygroup as $keygroups)
				  {
					  $pairing_name = !empty($KeyLockGroups['pairing_name']) ? $KeyLockGroups['pairing_name'] : '';
					  $response['data']['pairing'][$i]['keygroup'] = array('key_group_ID'=>$keygroups['key_group_ID'],'key_group_name'=>$pairing_name);
					    $key_ids = json_decode($keygroups['key_id']);
						$response['data']['pairing'][$i]['keygroup']['keys'] = array( );
					     for($key=0;$key<=count( $key_ids );$key++)
					     {
						    $collection4 = new MongoCollection($app_data, 'keys');
						    $key_id_details = $collection4->find(array('key_ID'=>(int)$key_ids[$key]));
						    if($key_id_details->count() > 0) 
						    {
							   foreach($key_id_details as $key_id_detail)
							   {
								   if($key_id_detail['status'] == 1)
								   {
									$response['data']['pairing'][$i]['keygroup']['keys'][] = array('key_ID'=> $key_id_detail['key_ID'],'key_name'=> $key_id_detail['key_name'] );
								   }
							   }
						 }
					     }
					  $k++;
				  }
			  }
			$i++;
		}
	}
	
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
				if(in_array($_REQUEST['user_id'],$users))
				{
					$com[] = $company['company_ID'];
				}
			}
		}
		
		
		
	$collection = new MongoCollection($app_data, 'KeyLockGroup');
	$KeyLockGroup = $collection->find();
	if($KeyLockGroup->count() > 0) 
	{
		$i=0;
		foreach($KeyLockGroup as $KeyLockGroups)
		{
			 if(in_array($KeyLockGroups['company_id'],$com))
			{
				$pair_users_arr = array();
				$pair_users = $KeyLockGroups['users'];
				for($u=0;$u<=count($pair_users);$u++)
				{
					$users_d = new MongoCollection($app_data, 'users');
					$users_details = $users_d->findOne(array('user_id'=>(int)$pair_users[$u]));
					if(isset($users_details['user_id']))
					{
						unset($users_details['_id']);
						//Added to show only relevant Info
						$pair_users_arr[] = $users_details;
						//$pair_users_arr[]['user_id'] = $users_details['user_id'];
					}
				}
				$from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
				$to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';
			  $response['status'] = 'true';
			  $response['data']['pairing'][] = array('paring_id'=>$KeyLockGroups['keyLockGroup_ID'],'paring_name'=>$KeyLockGroups['pairing_name'],
			  'date' => date("d.m.Y", strtotime( $KeyLockGroups['date_from'] )) . ' - ' . date("d.m.Y", strtotime( $KeyLockGroups['date_to'] )),
			  'start_date' => date("d.m.Y", strtotime( $KeyLockGroups['date_from'] )),
			  'end_date' => date("d.m.Y", strtotime( $KeyLockGroups['date_to'] )),
			  'time' => $KeyLockGroups['time_from_hh'].':'.$KeyLockGroups['time_from_mm'] .' - ' . $KeyLockGroups['time_to_hh'] . ':' . $KeyLockGroups['time_to_mm'],
			  'users' => $pair_users_arr
			  );
			  $collection1 = new MongoCollection($app_data, 'lockgroup');
			  $lockgroup = $collection1->find(array('lock_group_ID'=>(int)$KeyLockGroups['lock_group_id']));
			  $response['data']['pairing'][$i]['lockgroup_status'] = 'false';
			  if($lockgroup->count() > 0) 
			  {
				 
				  $j=0;
				  foreach($lockgroup as $lockgroups)
				  {
					  if(in_array($lockgroups['company_id'],$com))
					  {
						   $response['data']['pairing'][$i]['lockgroup_status'] = 'true';
					  $response['data']['pairing'][$i]['lockgroup'] = array('lock_group_ID'=>$lockgroups['lock_group_ID'],'lock_group_name'=>$lockgroups['lock_group_name']);
					  $lock_ids = json_decode($lockgroups['lock_id']);
					  $response['data']['pairing'][$i]['lockgroup']['locks'] = array( );
					  for($l=0;$l<=count( $lock_ids );$l++)
					  {
						  $collection2 = new MongoCollection($app_data, 'locks');
						  $lock_id_details = $collection2->find(array('lock_ID'=>(int)$lock_ids[$l]));
						  if($lock_id_details->count() > 0) 
						  {
							  foreach($lock_id_details as $lock_id_detail)
							  {
								$response['data']['pairing'][$i]['lockgroup']['locks'][] = array('lock_ID'=> $lock_id_detail['lock_ID'],'lock_name'=> $lock_id_detail['lock_name'] );
							  }
						  }
					  }
					 $j++;
				  }
				  }
			 }

			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			  $response['data']['pairing'][$i]['keygroup_status'] = 'false';
			  $collection3 = new MongoCollection($app_data, 'keygroup');
			  $keygroup = $collection3->find(array('key_group_ID'=>(int)$KeyLockGroups['key_group_id']));
			  if($keygroup->count() > 0) 
			  {
				  $k=0;
				  foreach($keygroup as $keygroups)
				  {
					   if(in_array($keygroups['company_id'],$com))
					   {
						   $response['data']['pairing'][$i]['keygroup_status'] = 'true';
					  $response['data']['pairing'][$i]['keygroup'] = array('key_group_ID'=>$keygroups['key_group_ID'],'key_group_name'=>$keygroups['key_group_name']);
					    $key_ids = json_decode($keygroups['key_id']);
						$response['data']['pairing'][$i]['keygroup']['keys'] = array( );
					     for($key=0;$key<=count( $key_ids );$key++)
					     {
						    $collection4 = new MongoCollection($app_data, 'keys');
						    $key_id_details = $collection4->find(array('key_ID'=>(int)$key_ids[$key]));
						    if($key_id_details->count() > 0) 
						    {
							   foreach($key_id_details as $key_id_detail)
							   {
								    if($key_id_detail['status'] == 1)
								   {
										$response['data']['pairing'][$i]['keygroup']['keys'][] = array('key_ID'=> $key_id_detail['key_ID'],'key_name'=> $key_id_detail['key_name'] );
								   }
							   }
						 }
					     }
					  $k++;
				  }
				  }
			  }
			$i++;
		}
		}
	}
		
		
		
		
		
	}
}

//View List of Locks
else if($_REQUEST['action'] == 'view' && $_REQUEST['method'] == 'locks' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	$response['lock_group_status'] = 'false';
	if($_REQUEST['user_id'] == 1) 
	{
		/*$locks_details = $app_data->locks;
		$cursor = $locks_details->find();
		if($cursor->count() > 0)
		{
			$k=0;
			$response['status'] = 'true';
			foreach($cursor as $locks_details)
			{
				$response['data'][] = $locks_details;
				$lock_group_ids = json_decode($locks_details['lock_group_id']);
				$response['data'][$k]['lock_groups'] = array();
				for($i=0;$i<=count($lock_group_ids);$i++)
				{
					$collection = new MongoCollection($app_data, 'lockgroup');
					$lockgroups = $collection->findOne(array('lock_group_ID'=>(int)$lock_group_ids[$i]));
					if(isset($lockgroups['lock_group_ID']))
					{
						$response['data'][$k]['lock_groups'][] = $lockgroups;
					}
				}
				$k++;
			}
		}*/
		
		$locks_details = $app_data->locks;
		$cursor = $locks_details->find();
		
		if($cursor->count() > 0)
		{
			$response['status'] = 'true';
			foreach($cursor as $locks_details)
			{
				$response['data'][] = $locks_details;
				$lock_group_ids = json_decode($locks_details['lock_group_id']);
				for($i=0;$i<count($lock_group_ids);$i++)
				{
					$locks_groups[] = (int)$lock_group_ids[$i];
				}
			}
		}
		if($locks_groups)
		{
				$collection = new MongoCollection($app_data, 'lockgroup');
				$lockgroups = $collection->find(array('lock_group_ID'=>array('$in'=> $locks_groups ) ));
				
				if($lockgroups->count() > 0)
				{
					$response['lock_group_status'] = 'true';
					foreach($lockgroups as $lockgroup)
					{
						$response['lock_group'][] = $lockgroup;
					}
				}
		}
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
				if(in_array($_REQUEST['user_id'],$users))
				{
					unset($company['_id']);
					$com[] = $company['company_ID'];
				}
			}
		}
		
		$locks_details = $app_data->locks;
		$cursor = $locks_details->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $locks_details)
			{
				if(in_array($locks_details['company_id'],$com))
				{
					$response['status'] = 'true';
					//Remove Id from showing
					unset($locks_details['_id']);
					$response['data'][] = $locks_details;
					$lock_group_ids = json_decode($locks_details['lock_group_id']);
					for($i=0;$i<count($lock_group_ids);$i++)
					{
							$locks_groups[] =(int) $lock_group_ids[$i];
					}
				}
			}
		}
		
		if($locks_groups)
		{
				$collection = new MongoCollection($app_data, 'lockgroup');
				$lockgroups = $collection->find(array('lock_group_ID'=>array('$in'=> $locks_groups ) ));
				if($lockgroups->count() > 0)
				{
					$response['lock_group_status'] = 'true';
					foreach($lockgroups as $lockgroup)
					{
						//Remove Id from showing
						unset($lockgroup['_id']);
						$response['lock_group'][] = $lockgroup;
					}
				}
		}
	}
}

//View List of Keys
else if($_REQUEST['action'] == 'view' && $_REQUEST['method'] == 'keys' && $_REQUEST['user_id'] != '')
{
	$users_details = $app_data->users;
	$users_detail = $users_details->findOne(array('user_id'=>(int) $_REQUEST['user_id']));
	$response['status'] = 'false';
	$response['key_group_status'] = 'false';
	$key_groups = array();
	if(isset($users_detail['user_id']))
	{
		if($users_detail['role'] == 1) 
		{
			
			$keys_details = $app_data->keys;
			$cursor = $keys_details->find(array());
			if($cursor->count() > 0)
			{
				$response['status'] = 'true';
				foreach($cursor as $key_detail)
				{
					//Remove Id from showing
					unset($key_detail['_id']);
					$response['data'][] = $key_detail;
					$key_group_ids = json_decode($key_detail['key_group_id']);
					for($i=0;$i<count($key_group_ids);$i++)
					{
						$key_groups[] = (int)$key_group_ids[$i];
					}
				}
			}
			
			if($key_groups)
			{
				$keygroup = $app_data->keygroup;
				$arg = array('key_group_ID' => array('$in'=> $key_groups ));
				$keygroupData = $keygroup->find($arg);
				if(count($keygroupData) > 0)
				{
					$response['key_group_status'] = 'true';
					foreach($keygroupData as $groups)
					{
						//Remove Id from showing
						unset($groups['_id']);
						$response['key_group'][] = $groups;
					}
				}
			}
		}
		else if(  in_array($users_detail['role'],array(4,5)))
		{
			
				$keys = json_decode($users_detail['key_id']);
				for($i=0;$i<=count($keys);$i++)
				{
					$collection1 = new MongoCollection($app_data, 'keys');
					$key_details = $collection1->findOne(array('key_ID'=> (int) $keys[$i] ));
					if(isset($key_details['key_ID']))
					{
						if($key_details['status'])
						{
							$response['status'] = 'true';
							//Remove Id & Phone Number from showing
							unset($key_details['_id']);
							unset($key_details['key_phone_number']);
							$response['data'][] = $key_details;
							$response['data'][$i]['key_groups'] = array();
						}
					}
				}
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
					if(in_array($_REQUEST['user_id'],$users))
					{
						$response['status'] = 'true';
						//Remove Id from showing
						unset($company['_id']);
						$com[] = $company['company_ID'];
					}
				}
			}
			
			$keys_details = $app_data->keys;
			$cursor = $keys_details->find(array());
			if($cursor->count() > 0)
			{
				$response['status'] = 'true';
				foreach($cursor as $key_detail)
				{
					if(in_array($key_detail['company_id'],$com))
					{
						//Remove Id from showing
						unset($key_detail['_id']);
						unset($key_detail['key_phone_number']);
						$response['data'][] = $key_detail;
						$key_group_ids = json_decode($key_detail['key_group_id']);
						for($i=0;$i<count($key_group_ids);$i++)
						{
							$key_groups[] = (int)$key_group_ids[$i];
						}
					}
				}
			}
			if($key_groups)
			{
				$keygroup = $app_data->keygroup;
				$arg = array('key_group_ID' => array('$in'=> $key_groups ));
				$keygroupData = $keygroup->find($arg);
				if(count($keygroupData) > 0)
				{
					$response['key_group_status'] = 'true';
					foreach($keygroupData as $groups)
					{
						//Remove Id from showing
						unset($groups['_id']);
						$response['key_group'][] = $groups;
					}
				}
			}
		}
	}
	
}

//View List of Lock Groups
else if($_REQUEST['action'] == 'lock_grouping' && $_REQUEST['method'] == 'view' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1)
	{
		$collection = new MongoCollection($app_data, 'company');
		$companies = $collection->find();
		if($companies->count() > 0) { $response['status'] = 'true';
			foreach($companies as $company)
			{
				unset($company['_id']);
				//$response['company_data'][] = $company;
				$response['company_data']['company_ID'] = $company['company_ID'];
				$response['company_data']['company_ref'] = $company['company_ref'];
			}
		}
		$collection = new MongoCollection($app_data, 'locks');
		$locks = $collection->find();
		if($locks->count() > 0) { $response['status'] = 'true';
			foreach($locks as $company)
			{
				unset($company['_id']);
				$response['locks_data'][] = $company;
			}
		}
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
				if(in_array($_REQUEST['user_id'],$users))
				{
					$response['status'] = 'true';
					//Remove ID from Showing
					unset($company['_id']);
					//$response['company_data'][] = $company;
					$response['company_data']['company_ID'] = $company['company_ID'];
					$response['company_data']['company_ref'] = $company['company_ref'];
					
					$com[] = $company['company_ID'];
				}
			}
		}
		$locks = $app_data->locks;
		$cursor = $locks->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $lock)
			{ 
				if(in_array($lock['company_id'],$com))
				{
					$response['status'] = 'true';
					unset($lock['_id']);
					$response['locks_data'][] = $lock;
				}
			}
		}
		
		
		$lg = $app_data->lockgroup;
		$cursor = $lg->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $lockgroup)
			{
				if(in_array($lockgroup['company_id'],$com))
				{
					$response['status'] = 'true';
					unset($lockgroup['_id']);
					$response['lockgroups_data'][] = $lockgroup;
				}
			}
		}
			
	}
}

//View List of Key Groups
else if($_REQUEST['action'] == 'key_grouping' && $_REQUEST['method'] == 'view' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1)
	{
		$collection = new MongoCollection($app_data, 'company');
		$companies = $collection->find();
		if($companies->count() > 0) { $response['status'] = 'true';
			foreach($companies as $company)
			{
				unset($company['_id']);
				//$response['company_data'][] = $company;
				$response['company_data']['company_ID'] = $company['company_ID'];
				$response['company_data']['company_ref'] = $company['company_ref'];
			}
		}
		$collection = new MongoCollection($app_data, 'keys');
		$keys = $collection->find();
		if($keys->count() > 0) { $response['status'] = 'true';
			foreach($keys as $key)
			{
				unset($key['_id']);
				unset($key['key_phone_number']);
				$response['keys_data'][] = $key;
			}
		}
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
				if(in_array($_REQUEST['user_id'],$users))
				{
					unset($company['_id']);
					unset($company['user_id']);
					$response['status'] = 'true';
					
					//$response['company_data'][] = $company;
					$response['company_data']['company_ID'] = $company['company_ID'];
					$response['company_data']['company_ref'] = $company['company_ref'];
					
					$com[] = $company['company_ID'];
				}
			}
		}
		
		$keys = $app_data->keys;
		$cursor = $keys->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $keys)
			{
				if(in_array($keys['company_id'],$com))
				{
					unset($keys['_id']);
					unset($keys['key_phone_number']);
					$response['keys_data'][] = $keys;
				}
			}
		}
					
		$kg = $app_data->keygroup;
		$cursor = $kg->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $keygroup)
			{
				if(in_array($keygroup['company_id'],$com))
				{
					$response['status'] = 'true';
					unset($keygroup['_id']);
					$response['keygroup_data'][] = $keygroup;
				}
			}
		}
		
	}
}

//Add Lock Group
else if($_REQUEST['action'] == 'lock_grouping' && $_REQUEST['method'] == 'add' && isset($_REQUEST['lock_group_name']) && isset($_REQUEST['company_id']) && isset($_REQUEST['lock_id']))
{
	if ( isset($_REQUEST['user_id']) ){
		$lock_grp_user_id = $_REQUEST['user_id'];
	}
	else{
		$lock_grp_user_id = 1;
	}
	
	//Map Lock_id into array
	$lock_id = isset($_REQUEST['lock_id']) ? $_REQUEST['lock_id'] : array();
	if (isset($_REQUEST['lock_id'])){
		$lock_id = array_map('intval', $lock_id);
	}
	
	
		$response['status'] = 'false';
		$lockgroup = $app_data->lockgroup;
		$post = array(
			'lock_group_ID'  => getNext_users_Sequence('lock_group_id'),
			'lock_group_name' => $_REQUEST['lock_group_name'],
			'lock_grp_user_id' => $lock_grp_user_id,
			'company_id' => (int) $_REQUEST['company_id'],
			//'lock_id' =>  json_encode( explode( ',' , $_REQUEST['lock_id'] ) ) 
			'lock_id' => $lock_id
		);
		if($lockgroup->insert($post))
		{
			$response['status'] = 'true';
			$response['msg'] = 'Lock Group Added Successfully';
		}
}
// Add Key Group 
else if($_REQUEST['action'] == 'key_grouping' && $_REQUEST['method'] == 'add' && isset($_REQUEST['key_group_name']) && isset($_REQUEST['company_id']) && isset($_REQUEST['key_id']))
{
	if ( isset($_REQUEST['user_id']) ){
		$key_grp_user_id = $_REQUEST['user_id'];
	}
	else{
		$key_grp_user_id = 1;
	}
	
	//Map Key_id into array
	$key_id = isset($_REQUEST['key_id']) ? $_REQUEST['key_id'] : array();
	if (isset($_REQUEST['key_id'])){
		$key_id = array_map('intval', $key_id);
	}
	
		$response['status'] = 'false';
		$keygroup = $app_data->keygroup;
		$post = array(
				'key_group_ID'  => getNext_users_Sequence('key_group_ID'),
				'key_group_name' => $_REQUEST['key_group_name'],
				'key_grp_user_id' => $user_id,
				'company_id' => (int) $_REQUEST['company_id'],
				//'key_id' => json_encode(explode(',',$_REQUEST['key_id']))
				'key_id' => $key_id
		);
		if($keygroup->insert($post))
		{
			$response['status'] = 'true';
			$response['msg'] = 'Key Group Added Successfully';
		}
}

//Activate Key - Serial Number
else if($_REQUEST['action'] == 'activate' && $_REQUEST['method'] == 'keys' && $_REQUEST['serial_key'] != '' && $_REQUEST['key_id'] != '' && $_REQUEST['user_id'] != '')
{
		$response['status'] = 'false';
		$response['msg'] = 'Invalid Key';
		$collection = new MongoCollection($app_data, 'keys');
		$keys = $collection->find(array('key_ID'=>(int) $_REQUEST['key_id'] ));
		if($keys->count() > 0) { $response['status'] = 'true';
		foreach($keys as $key)
		{
			if($key['key_serial_number'] == $_REQUEST['serial_key'])
			{
				$collection = new MongoCollection($app_data, 'keys');
				$criteria = array('key_ID'=>(int) $key['key_ID']);
				$collection->update( $criteria ,array('$set' => array('status' =>(int) 1,'activated_on' => date('d F Y, H:i'),'key_user_id' => $_REQUEST['user_id'] ) ) );
				$response['msg'] = 'Serial Key Successfully Activated';
			}
			else
			{
				 $response['status'] = 'false';
				$response['msg'] = 'Wrong Serial Key';
			}
		}
		}
}

//List of Access Control Key/Lock Group Pairing
else if($_REQUEST['action'] == 'key_lock_paring' && $_REQUEST['method'] == 'view' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	
	
	
	$collection = $app_data->users;
	$user_details = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
	
	if(isset($user_details['user_id']))
	{
		if($user_details['role'] == 1)
		{
		
			$collection = new MongoCollection($app_data, 'lockgroup');
			$lockgroup = $collection->find();
			if($lockgroup->count() > 0) { $response['status'] = 'true';
				foreach($lockgroup as $lockgroups)
				{
					unset($lockgroups['_id']);
					$response['lockgroups_data'][] = $lockgroups;
				}
			}
			$collection = new MongoCollection($app_data, 'keygroup');
			$keygroup = $collection->find();
			if($keygroup->count() > 0) { $response['status'] = 'true';
				foreach($keygroup as $keygroups)
				{
					unset($keygroups['_id']);
					$response['keygroup_data'][] = $keygroups;
				}
			}
			
			$collection = new MongoCollection($app_data, 'users');
			$users = $collection->find();
			if($users->count() > 0) { $response['status'] = 'true';
				foreach($users as $user)
				{
					if(in_array($user['role'],array(4,5)))
					{
						unset($user['_id']);
						$response['user'][] = $user;
					}
				}
			}
		
			$collectionGroup = new MongoCollection($app_data, 'KeyLockGroup');
			//$criteriaGroup = array('keyLockGroup_ID'=>(int) $_REQUEST['pairing_id']);
			$group = $collectionGroup->find();
			if($group->count() > 0) { 
				$response['status'] = 'true';
				foreach($group as $group)
				{
						unset($group['_id']);
						$response['KeyLockGroup'][] = $group;
				}
			}
			
		}
		else
		{
			$com = array();
			$collection1 = new MongoCollection($app_data, 'company');
			//$companies = $collection1->find();
			$companies = $collection1->find(  );
			if($companies->count() > 0) 
			{
				foreach($companies as $company)
				{
					$users = json_decode($company['user_id']);
					if(in_array($_REQUEST['user_id'],$users))
					{
						$com[] = $company['company_ID'];
					}
				}
			}
			
			$lg = $app_data->lockgroup;
			//$cursor = $lg->find();
			$cursor = $lg->find(  );
			if($cursor->count() > 0)
			{
				foreach($cursor as $lockgroup)
				{
					if(in_array($lockgroup['company_id'],$com))
					{
						$response['status'] = 'true';
						unset($lockgroup['_id']);
						$response['lockgroups_data'][] = $lockgroup;
					}
				}
			}
			
			$kg = $app_data->keygroup;
			$cursor = $kg->find();
			if($cursor->count() > 0)
			{
				foreach($cursor as $keygroup)
				{
					if(in_array($keygroup['company_id'],$com))
					{
						$response['status'] = 'true';
						unset($keygroup['_id']);
						$response['keygroup_data'][] = $keygroup;
					}
				}
			}
			
			$collection = new MongoCollection($app_data, 'users');
			$users = $collection->find();
			if($users->count() > 0) { $response['status'] = 'true';
				foreach($users as $user)
				{
					if(in_array($user['role'],array(4,5))  && $user['company_id'] == $user_details['company_id'])
					{
						unset($user['_id']);
						//$response['user'][] = $user; //Cannot revealing all data
						$response['user']['user_id'] = $user['user_id'];
						$response['user']['username'] = $user['username'];
						$response['user']['key_group_id'] = $user['key_group_id'];
						$response['user']['key_id'] = $user['key_id'];
						$response['user']['key_activated'] = $user['key_activated'];
						$response['user']['lock_group_id'] = $user['lock_group_id'];
					}
				}
			}
			
			//Added by Alan 2018-10-18
			$collectionGroup = new MongoCollection($app_data, 'KeyLockGroup');
			//$criteriaGroup = array('keyLockGroup_ID'=>(int) $_REQUEST['pairing_id']);
			$accessControl = $collectionGroup->find();
			if($accessControl->count() > 0) { 
				$response['status'] = 'true';
				foreach($accessControl as $accessControl)
				{
					if(in_array($accessControl['company_id'],$com))
					{
						$response['status'] = 'true';
						unset($accessControl['_id']);
						//$response['access_control'][] = $accessControl;
						$response['access_control']['access_control_ID'] = $accessControl['keyLockGroup_ID'];
						$response['access_control']['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
						$response['access_control']['pairing_name'] = $accessControl['pairing_name'];
						$response['access_control']['lock_group_id'] = $accessControl['lock_group_id'];
						$response['access_control']['key_group_id'] = $accessControl['key_group_id'];
						$response['access_control']['company_id'] = $accessControl['company_id'];
						$response['access_control']['users'] = $accessControl['users'];
						$response['access_control']['key_time_restricted'] = $accessControl['key_time_restricted'];
						$response['access_control']['date_from'] = $accessControl['date_from'];
						$response['access_control']['date_to'] = $accessControl['date_to'];
						$response['access_control']['time_from_hh'] = $accessControl['time_from_hh'];
						$response['access_control']['time_from_mm'] = $accessControl['time_from_mm'];
						$response['access_control']['time_to_hh'] = $accessControl['time_to_hh'];
						$response['access_control']['time_to_mm'] = $accessControl['time_to_mm'];
						$response['access_control']['lat'] = $accessControl['lat'];
						$response['access_control']['long'] = $accessControl['long'];
						$response['access_control']['radius'] = $accessControl['radious']; //TODO: change in DB
						$response['access_control']['added_by'] = $accessControl['added_by'];

						//unset($accessControl['keyLockGroup_ID']);
						//unset($accessControl['pairing_name']);
					}
				}
			}
		}
	}
}

//Add Access Control Key/Lock Group Pairing
else if($_REQUEST['action'] == 'key_lock_paring' && $_REQUEST['method'] == 'add' && $_REQUEST['lock_group_id'] != '' && $_REQUEST['key_group_id'] != ''
		&& $_REQUEST['date_from'] != '' && $_REQUEST['date_to'] != '' 
		&& $_REQUEST['time_from'] != '' && $_REQUEST['time_to'] != '' && $_REQUEST['pairing_name'] != '' && $_REQUEST['company_id'] != '' && $_REQUEST['users'] != ''  )
{
	$response['status'] = 'false';
	
	$users = isset($_REQUEST['users']) ? $_REQUEST['users'] : array();
	if (isset($_REQUEST['users'])){
		$users = array_map('intval', $users);
	}
	
			$user_reg = $app_data->KeyLockGroup;
			$post = array(
				'keyLockGroup_ID' => getNext_users_Sequence('keyLockGroup_ID'),
				'pairing_name'  => $_REQUEST['pairing_name'],
				'lock_group_id'  => (int) $_REQUEST['lock_group_id'],
				'key_group_id'  => (int) $_REQUEST['key_group_id'],
				'company_id'  => (int) $_REQUEST['company_id'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'time_from_hh'  => date('H', strtotime($_REQUEST['time_from'])),  
				'time_from_mm'  => date('i', strtotime($_REQUEST['time_from'])),  
				'time_to_hh'  => date('H', strtotime($_REQUEST['time_to'])),
				'time_to_mm'  => date('i', strtotime($_REQUEST['time_to'])),
				//'users'=>  explode(',',$_REQUEST['users'])
				'users'=>  $users
				);
			if($user_reg->insert($post))
			{
				$response['status'] = 'true';
				$response['msg'] = 'Key Group and Lock Group Pairing Added Successfully';
				
			}
}

//Get Keys in Key Group - INCOMPLETE
else if($_REQUEST['action'] == 'keys_of_keygrp' && $_REQUEST['method'] == 'get' && $_REQUEST['keygrp_id'] != '')
{
	$response['status'] = 'false';
	$response['data'] = array();
    $collection = new MongoCollection($app_data, 'keygroup');
    $keygroups = $collection->find(array('key_group_ID'=> (int) $_REQUEST['keygrp_id'] ));
    if($keygroups->count() > 0) { $response['status'] = 'true';
    foreach($keygroups as $keygroup)
    {
		if($_REQUEST['role'] == 1)
		{
			$key_ids = json_decode($keygroup['key_id']);
			//$key_ids = $keygroup['key_id'];
			for($i=0;$i<=count($key_ids);$i++)
			{
				$collection_key = new MongoCollection($app_data, 'keys');
				$keys = $collection_key->find(array('key_ID'=> (int) $key_ids[$i] ));
				if($keys->count() > 0) { $response['status'] = 'true';
					foreach($keys as $key)
					{
						$response['data'][] = $key;
					}
				}
			}
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
					if(in_array($_REQUEST['user_id'],$users))
					{
						$com[] = $company['company_ID'];
					}
				}
			}
			
			if(in_array($keygroup['company_id'],$com))
			{
				$key_ids = json_decode($keygroup['key_id']);
				for($i=0;$i<=count($key_ids);$i++)
				{
					$collection_key = new MongoCollection($app_data, 'keys');
					$keys = $collection_key->find(array('key_ID'=> (int) $key_ids[$i] ));
					if($keys->count() > 0) { $response['status'] = 'true';
						foreach($keys as $key)
						{
							if($key['status'] == 1)
							{
								$response['data'][] = $key;
							}
						}
					}
				}
			}
		}
    }
    }
}
//Get Locks in Lock Group - INCOMPLETE
else if($_REQUEST['action'] == 'locks_of_lockgrp' && $_REQUEST['method'] == 'get' && $_REQUEST['lockgrp_id'] != '')
{
	$response['status'] = 'false';
	$response['data'] = array();
    $collection = new MongoCollection($app_data, 'lockgroup');
    $keygroups = $collection->find(array('lock_group_ID'=> (int) $_REQUEST['lockgrp_id'] ));
    if($keygroups->count() > 0) { $response['status'] = 'true';
    foreach($keygroups as $keygroup)
    {
		if($_REQUEST['role'] == 1)
		{
			$key_ids = json_decode($keygroup['lock_id']);
			for($i=0;$i<=count($key_ids);$i++)
			{
				 $collection_key = new MongoCollection($app_data, 'locks');
				 $keys = $collection_key->find(array('lock_ID'=> (int) $key_ids[$i] ));
				 if($keys->count() > 0) { $response['status'] = 'true';
					 foreach($keys as $key)
					 {
						 $response['data'][] = $key;
					 }
				 }
			}
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
					if(in_array($_REQUEST['user_id'],$users))
					{
						$com[] = $company['company_ID'];
					}
				}
			}
			
			if(in_array($keygroup['company_id'] ,$com))
			{
				$key_ids = json_decode($keygroup['lock_id']);
				for($i=0;$i<=count($key_ids);$i++)
				{
					 $collection_locks = new MongoCollection($app_data, 'locks');
					 $locks = $collection_locks->find(array('lock_ID'=> (int) $key_ids[$i] ));
					 if($locks->count() > 0) { $response['status'] = 'true';
						 foreach($locks as $lock)
						 {
								$response['data'][] = $lock;
						 }
					 }
				}
			}
		}
    }
    }
}

//Update Access Control Key/Lock Group Pairing
else if($_REQUEST['action'] == 'key_lock_paring' && $_REQUEST['method'] == 'update' && $_REQUEST['lock_group_id'] != '' && $_REQUEST['key_group_id'] != ''
		&& $_REQUEST['date_from'] != '' && $_REQUEST['date_to'] != '' 
		&& $_REQUEST['time_from'] != '' && $_REQUEST['time_to'] != '' && $_REQUEST['pairing_name'] != ''  && $_REQUEST['pairing_id'] != '')
{
	$response['status'] = 'false';
	
	$users = isset($_REQUEST['users']) ? $_REQUEST['users'] : array();
	if (isset($_REQUEST['users'])){
		$users = array_map('intval', $users);
	}
	
	$collection = new MongoCollection($app_data, 'KeyLockGroup');
	$criteria = array('keyLockGroup_ID'=>(int) $_REQUEST['pairing_id']);
	if($collection->update( $criteria ,array('$set' => 
		array(
				'pairing_name'  => $_REQUEST['pairing_name'],
				'lock_group_id'  => (int) $_REQUEST['lock_group_id'],
				'key_group_id'  => (int) $_REQUEST['key_group_id'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'time_from_hh'  => date('H', strtotime($_REQUEST['time_from'])),
				'time_from_mm'  => date('i', strtotime($_REQUEST['time_from'])),  
				'time_to_hh'  => date('H', strtotime($_REQUEST['time_to'])),
				'time_to_mm'  => date('i', strtotime($_REQUEST['time_to'])),
				//'users'=>  explode(',',$_REQUEST['users'])
				'users'=>  $users
			))))
			{
				$response['status'] = 'true';
				$response['msg'] = 'Key Group and Lock Group Pairing Updated Successfully';
			}
	
}

//Get Access Control Key/Lock Pairing Groups
else if($_REQUEST['action'] == 'get_pairing_groups' && $_REQUEST['method'] == 'get' && $_REQUEST['pairing_id'] != '')
{
		$response['status'] = 'false';
		$response['lockgroups'] = array();
		$response['keygroups'] = array();
		
		if($_REQUEST['role'] == 1)
		{
			$collection = new MongoCollection($app_data, 'KeyLockGroup');
			$KeyLockGroups = $collection->findOne(array('keyLockGroup_ID'=> (int) $_REQUEST['pairing_id'] ));
			if(isset($KeyLockGroups['keyLockGroup_ID'])) { $response['status'] = 'true';
				if($KeyLockGroups['lock_group_id'] != '')
				{
					$collection1 = new MongoCollection($app_data, 'lockgroup');
					$lockgroups = $collection1->findOne(array('lock_group_ID'=> (int) $KeyLockGroups['lock_group_id'] ));
					if(isset($lockgroups['lock_group_ID']))
					{
						$response['lockgroups'][] = $lockgroups;
					}
				}
				if($KeyLockGroups['key_group_id'] != '')
				{
					$collection2 = new MongoCollection($app_data, 'keygroup');
					$keygroups = $collection2->findOne(array('key_group_ID'=> (int) $KeyLockGroups['key_group_id'] ));
					if(isset($keygroups['key_group_ID']))
					{
						$response['keygroups'][] = $keygroups;
					}
				}
			}
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
					if(in_array($_REQUEST['user_id'],$users))
					{
						$com[] = $company['company_ID'];
					}
				}
			}
			
			$collection = new MongoCollection($app_data, 'KeyLockGroup');
			$KeyLockGroups = $collection->findOne(array('keyLockGroup_ID'=> (int) $_REQUEST['pairing_id'] ));
			if(isset($KeyLockGroups['keyLockGroup_ID'])) 
			{
				if(in_array($KeyLockGroups['company_id'],$com))
				{
					$response['status'] = 'true';
					unset($KeyLockGroups['_id']);
					$response['access_control']['access_control_ID'] = $KeyLockGroups['keyLockGroup_ID'];
					$response['access_control']['keyLockGroup_ID'] = $KeyLockGroups['keyLockGroup_ID'];
					$response['access_control']['pairing_name'] = $KeyLockGroups['pairing_name'];
					$response['access_control']['lock_group_id'] = $KeyLockGroups['lock_group_id'];
					$response['access_control']['key_group_id'] = $KeyLockGroups['key_group_id'];
					$response['access_control']['company_id'] = $KeyLockGroups['company_id'];
					$response['access_control']['users'] = $KeyLockGroups['users'];
					$response['access_control']['key_time_restricted'] = $KeyLockGroups['key_time_restricted'];
					$response['access_control']['date_from'] = $KeyLockGroups['date_from'];
					$response['access_control']['date_to'] = $KeyLockGroups['date_to'];
					$response['access_control']['time_from_hh'] = $KeyLockGroups['time_from_hh'];
					$response['access_control']['time_from_mm'] = $KeyLockGroups['time_from_mm'];
					$response['access_control']['time_to_hh'] = $KeyLockGroups['time_to_hh'];
					$response['access_control']['time_to_mm'] = $KeyLockGroups['time_to_mm'];
					$response['access_control']['lat'] = $KeyLockGroups['lat'];
					$response['access_control']['long'] = $KeyLockGroups['long'];
					$response['access_control']['radius'] = $KeyLockGroups['radious']; //TODO: change in DB
					$response['access_control']['added_by'] = $KeyLockGroups['added_by'];
					
					if($KeyLockGroups['lock_group_id'] != '')
					{
						$collection1 = new MongoCollection($app_data, 'lockgroup');
						$lockgroups = $collection1->findOne(array('lock_group_ID'=> (int) $KeyLockGroups['lock_group_id'] ));
						if(isset($lockgroups['lock_group_ID']))
						{
							unset($lockgroups['_id']);
							$response['lockgroups'][] = $lockgroups;
						}
					}
					if($KeyLockGroups['key_group_id'] != '')
					{
						$collection2 = new MongoCollection($app_data, 'keygroup');
						$keygroups = $collection2->findOne(array('key_group_ID'=> (int) $KeyLockGroups['key_group_id'] ));
						if(isset($keygroups['key_group_ID']))
						{
							unset($keygroups['_id']);
							$response['keygroups'][] = $keygroups;
						}
					}				
					
				}
			}
		}
}

//Customise Key Settings
else if($_REQUEST['action'] == 'customized_keys_locks' && $_REQUEST['method'] == 'get' && $_REQUEST['user_id'] != '')
{
	$response['keys_status'] = 'false';
	$collection = new MongoCollection($app_data, 'users');
	$users = $collection->findOne(array('user_id'=> (int) $_REQUEST['user_id'] ));
	if(isset($users['user_id'])) 
	{
		if($users['role'] == 1)
		{
				$collection1 = new MongoCollection($app_data, 'keys');
				$key_details = $collection1->find();
				if($key_details->count() > 0)
				{
					foreach($key_details as $key_detail)
					{
							$response['keys_status'] = 'true';
							$response['keys'][] = $key_detail;
					}
				}
		}
		else
		{
			$keys = json_decode($users['key_id']);
			for($i=0;$i<=count($keys);$i++)
			{
				$collection1 = new MongoCollection($app_data, 'keys');
				$key_details = $collection1->findOne(array('key_ID'=> (int) $keys[$i] ));
				if(isset($key_details['key_ID']))
				{
					if($key_details['status'])
					{
						$response['keys_status'] = 'true';
						$response['keys'][] = $key_details;
					}
				}
			}
		}
		
	}
	
	$response['locks_status'] = 'false';
	if(isset($users['user_id']) && $users['role'] == 1)
	{
		$locks_details = $app_data->locks;
		$cursor = $locks_details->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $locks)
			{
					$response['locks_status'] = 'true';
					$response['locks'][] = $locks;
			}
		}
	}
	else
	{
		
		$com = array();
		$collection2 = new MongoCollection($app_data, 'company');
		$companies = $collection2->find();
		if($companies->count() > 0) 
		{
			foreach($companies as $company)
			{
				$users = json_decode($company['user_id']);
				if(in_array($_REQUEST['user_id'],$users))
				{
					$com[] = $company['company_ID'];
				}
			}
		}
		
		$locks_details = $app_data->locks;
		$cursor = $locks_details->find();
		if($cursor->count() > 0)
		{
			foreach($cursor as $locks)
			{
				if(in_array($locks['company_id'],$com))
				{
					$response['locks_status'] = 'true';
					$response['locks'][] = $locks;
				}
			}
		}
	}
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>