<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

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
			  $from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
			  $to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';
			  $pairing_name = !empty($KeyLockGroups['pairing_name']) ? $KeyLockGroups['pairing_name'] : '';
			  $response['data']['pairing'][] = array('paring_id'=>$KeyLockGroups['keyLockGroup_ID'],'paring_name'=>$pairing_name,
			  'date' => date("d.m.Y", strtotime( $KeyLockGroups['date_from'] )) . ' - ' . date("d.m.Y", strtotime( $KeyLockGroups['date_to'] )),
			  'time' => $KeyLockGroups['time_from_hh'].':'.$KeyLockGroups['time_from_mm'] . ' - ' . $KeyLockGroups['time_to_hh'] . ':' . $KeyLockGroups['time_to_mm']
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
				$from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
				$to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';
			  $response['status'] = 'true';
			  $response['data']['pairing'][] = array('paring_id'=>$KeyLockGroups['keyLockGroup_ID'],'paring_name'=>$KeyLockGroups['pairing_name'],
			  'date' => date("d.m.Y", strtotime( $KeyLockGroups['date_from'] )) . ' - ' . date("d.m.Y", strtotime( $KeyLockGroups['date_to'] )),
			  'time' => $KeyLockGroups['time_from_hh'].':'.$KeyLockGroups['time_from_mm'] .' - ' . $KeyLockGroups['time_to_hh'] . ':' . $KeyLockGroups['time_to_mm']
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

else if($_REQUEST['action'] == 'view' && $_REQUEST['method'] == 'locks' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1) 
	{
		$locks_details = $app_data->locks;
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
					$com[] = $company['company_ID'];
				}
			}
		}
		
		$locks_details = $app_data->locks;
		$cursor = $locks_details->find();
		if($cursor->count() > 0)
		{
			$k=0;
			foreach($cursor as $locks_details)
			{
				if(in_array($locks_details['company_id'],$com))
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
			}
		}
	}
}
else if($_REQUEST['action'] == 'view' && $_REQUEST['method'] == 'keys' && $_REQUEST['user_id'] != '')
{
	$users_details = $app_data->users;
	$users_detail = $users_details->findOne(array('user_id'=>(int) $_REQUEST['user_id']));
	$response['status'] = 'false';
	if(isset($users_detail['user_id']))
	{
		if($users_detail['role'] == 1) 
		{
			$keys_details = $app_data->keys;
			$cursor = $keys_details->find(array());
			if($cursor->count() > 0)
			{
				$response['status'] = 'true';
				$k = 0;
				foreach($cursor as $key_detail)
				{
					$response['data'][] = $key_detail;
					$key_group_ids = json_decode($key_detail['key_group_id']);
					$response['data'][$k]['key_groups'] = array();
					for($i=0;$i<=count($key_group_ids);$i++)
					{
						$collection = new MongoCollection($app_data, 'keygroup');
						$keygroups = $collection->findOne(array('key_group_ID'=>(int)$key_group_ids[$i]));
						if(isset($keygroups['key_group_ID']))
						{
							$response['data'][$k]['key_groups'][] = $keygroups;
						}
					}
					$k++;
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
						$com[] = $company['company_ID'];
					}
				}
			}
			
			$keys_details = $app_data->keys;
			$cursor = $keys_details->find(array());
			if($cursor->count() > 0)
			{
				$response['status'] = 'true';
				$k = 0;
				foreach($cursor as $key_detail)
				{
					if(in_array($key_detail['company_id'],$com))
					{
						$response['data'][] = $key_detail;
						$key_group_ids = json_decode($key_detail['key_group_id']);
						$response['data'][$k]['key_groups'] = array();
						for($i=0;$i<=count($key_group_ids);$i++)
						{
							$collection = new MongoCollection($app_data, 'keygroup');
							$keygroups = $collection->findOne(array('key_group_ID'=>(int)$key_group_ids[$i]));
							if(isset($keygroups['key_group_ID']))
							{
								$response['data'][$k]['key_groups'][] = $keygroups;
							}
						}
						$k++;
					}
				}
			}
		}
	}
	
	
	
	
}
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
				$response['company_data'][] = $company;
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
					
					$response['company_data'][] = $company;
					
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
	}
}
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
				$response['company_data'][] = $company;
			}
		}
		$collection = new MongoCollection($app_data, 'keys');
		$keys = $collection->find();
		if($keys->count() > 0) { $response['status'] = 'true';
			foreach($keys as $key)
			{
				unset($key['_id']);
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
					$response['status'] = 'true';
					
					$response['company_data'][] = $company;
					
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
					$response['keys_data'][] = $keys;
				}
			}
		}
	}
}
else if($_REQUEST['action'] == 'lock_grouping' && $_REQUEST['method'] == 'add' && isset($_REQUEST['lock_group_name']) && isset($_REQUEST['company_id']) && isset($_REQUEST['lock_id']))
{
		$response['status'] = 'false';
		$lockgroup = $app_data->lockgroup;
		$post = array(
			'lock_group_ID'  => getNext_users_Sequence('lock_group_id'),
			'lock_group_name' => $_REQUEST['lock_group_name'],
			'lock_grp_user_id' => '1',
			'company_id' => $_REQUEST['company_id'],
			'lock_id' =>  json_encode( explode( ',' , $_REQUEST['lock_id'] ) )  
		);
		if($lockgroup->insert($post))
		{
			$response['status'] = 'true';
			$response['msg'] = 'Lock Group Added Successfully';
		}
}
// Key Group Add
else if($_REQUEST['action'] == 'key_grouping' && $_REQUEST['method'] == 'add' && isset($_REQUEST['key_group_name']) && isset($_REQUEST['company_id']) && isset($_REQUEST['lock_id']))
{
		$response['status'] = 'false';
		$keygroup = $app_data->keygroup;
		$post = array(
				'key_group_ID'  => getNext_users_Sequence('key_group_ID'),
				'key_group_name' => $_REQUEST['key_group_name'],
				'key_grp_user_id' => "1",
				'company_id' => $_REQUEST['company_id'],
				'key_id' => json_encode(explode(',',$_REQUEST['lock_id']))
		);
		if($keygroup->insert($post))
		{
			$response['status'] = 'true';
			$response['msg'] = 'Key Group Added Successfully';
		}
}
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

else if($_REQUEST['action'] == 'key_lock_paring' && $_REQUEST['method'] == 'view' && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1)
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

else if($_REQUEST['action'] == 'key_lock_paring' && $_REQUEST['method'] == 'add' && $_REQUEST['lock_group_id'] != '' && $_REQUEST['key_group_id'] != ''
		&& $_REQUEST['date_from'] != '' && $_REQUEST['date_to'] != '' 
		&& $_REQUEST['time_from'] != '' && $_REQUEST['time_to'] != '' && $_REQUEST['pairing_name'] != '' )
{
	$response['status'] = 'false';
			$user_reg = $app_data->KeyLockGroup;
			$post = array(
				'keyLockGroup_ID' => getNext_users_Sequence('keyLockGroup_ID'),
				'pairing_name'  => $_REQUEST['pairing_name'],
				'lock_group_id'  => $_REQUEST['lock_group_id'],
				'key_group_id'  => $_REQUEST['key_group_id'],
				'company_id'  => $_REQUEST['company_id'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'time_from_hh'  => explode(':',$_REQUEST['time_from'])[0],
				'time_from_mm'  => explode(':',$_REQUEST['time_from'])[1],
				'time_to_hh'  => explode(':',$_REQUEST['time_to'])[0],
				'time_to_mm'  => explode(':',$_REQUEST['time_to'])[1]
				);
			if($user_reg->insert($post))
			{
				$response['status'] = 'true';
				$response['msg'] = 'Key Group and Lock Group Pairing Added Successfully';
				
			}
}
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

else if($_REQUEST['action'] == 'key_lock_paring' && $_REQUEST['method'] == 'update' && $_REQUEST['lock_group_id'] != '' && $_REQUEST['key_group_id'] != ''
		&& $_REQUEST['date_from'] != '' && $_REQUEST['date_to'] != '' 
		&& $_REQUEST['time_from'] != '' && $_REQUEST['time_to'] != '' && $_REQUEST['pairing_name'] != ''  && $_REQUEST['pairing_id'] != '')
{
	$response['status'] = 'false';
	$collection = new MongoCollection($app_data, 'KeyLockGroup');
	$criteria = array('keyLockGroup_ID'=>(int) $_REQUEST['pairing_id']);
	if($collection->update( $criteria ,array('$set' => 
		array(
				'pairing_name'  => $_REQUEST['pairing_name'],
				'lock_group_id'  => $_REQUEST['lock_group_id'],
				'key_group_id'  => $_REQUEST['key_group_id'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'time_from_hh'  => explode(':',$_REQUEST['time_from'])[0],
				'time_from_mm'  => explode(':',$_REQUEST['time_from'])[1],
				'time_to_hh'  => explode(':',$_REQUEST['time_to'])[0],
				'time_to_mm'  => explode(':',$_REQUEST['time_to'])[1]
			))))
			{
				$response['status'] = 'true';
				$response['msg'] = 'Key Group and Lock Group Pairing Updated Successfully';
			}
	
}
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
		}
}

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


echo json_encode($response);
?>