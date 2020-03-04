<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();
if($_REQUEST['action'] == 'time' && $_REQUEST['method'] == 'get')
{
	$response['status'] = 'true';
	$response['date'] = date('d-m-Y');
	$response['hour_minute'] = date('H') . '.' . date('i');
}
if($_REQUEST['action'] == 'validate_bluetooth' && $_REQUEST['method'] == 'get')
{
	$settings = $app_data->settings;
	$cursor = $settings->findOne(array('super_user_id' =>1));
	$allow_saturday_sunday = $cursor['allow_saturday_sunday'];
	$response['status'] = 'true';
	$response['allow_saturday_sunday'] = $allow_saturday_sunday;
}

//Check Access Control
if($_REQUEST['action'] == 'view_access_control' && 
isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	$response['error'] = 'Invalid Parameters';
	
	$collection = $app_data->users;
	//$user_details = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
	
	if(isset($user_details['user_id']))

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
					if(in_array($user['role'],array(4,5,6,7,8))  && $user['company_id'] == $user_details['company_id'] && $user['user_id'] == $_REQUEST['user_id'] )
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
						unset($response['error']);
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

//Get Permit Info
if($_REQUEST['action'] == 'view_permit' && 
isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '')
{
	$response['status'] = 'false';
	if($_REQUEST['user_id'] == 1)
	{
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		$permit_to_enter = $collection->find();
		
		if($permit_to_enter->count() > 0) { 
			$response['status'] = 'true';
			foreach($permit_to_enter as $permit_to_enter)
			{
				$permit_id = $permit_to_enter['permit_id'];	
				
					//$pair_users_arr = array();
					//$pair_users = $access_permit['user_id'];
					
					/*
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
					*/
					//$from_ap_pn = $KeyLockGroups['time_from_hh'] >= 12 ? 'PM' : 'AM';
					//$to_ap_pn = $KeyLockGroups['time_to_hh'] >= 12 ? 'PM' : 'AM';

					/*
					$response['data']['pairing'][] = array(
						'permit_id'=>$permit_id,
						'user_id'=>$permit_to_enter['user_id'],
						'username'=>$permit_to_enter['username'],
						'company_id'=>$permit_to_enter['company_id'],
						'company_ref_id'=>$permit_to_enter['company_ref_id'],
						'role'=>$permit_to_enter['role'],
						'location'=>$permit_to_enter['location'],
						'host_name'=>$permit_to_enter['host_name'],
						'host_email_phone'=>$permit_to_enter['host_email_phone'],
						'date_from'=>$permit_to_enter['date_from'],
						'date_to'=>$permit_to_enter['date_to'],
						'time_from'=>$permit_to_enter['time_from'],
						'time_to'=>$permit_to_enter['time_to'],
						// Accompany Person Data
						'pte_user_name_data'=>$permit_to_enter['pte_user_name_data'],
						'pte_user_email_data'=>$permit_to_enter['pte_user_email_data'],
						'pte_user_phone_data'=>$permit_to_enter['pte_user_phone_data'],
						'pte_company_name_data'=>$permit_to_enter['pte_company_name_data'],
						'pte_company_position_data'=>$permit_to_enter['pte_company_position_data'],
						'pte_user_message_data'=>$permit_to_enter['pte_user_message_data'],
						'pte_user_image_name_data'=>$permit_to_enter['pte_user_image_name_data'],
						
						
						'registered_time'=>$permit_to_enter['registered_time'],
						'approved'=>$permit_to_enter['approved'],
						'subadmin_approved'=>$permit_to_enter['subadmin_approved'],
						'admin_approved'=>$permit_to_enter['admin_approved']
					*/
				
				if($permit_to_enter['permit_id'] == $_REQUEST['permit_id'])
				{
					$response['current_permit'][] = $permit_to_enter;
				}
				if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
				{
					
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
					echo $since_start->days.' days total -- ';
					echo $since_start->y.' years -- ';
					echo $since_start->m.' months -- ';
					echo $since_start->d.' days -- ';
					echo $since_start->h.' hours -- ';
					echo $since_start->i.' minutes -- ';
					echo $since_start->s.' seconds---';
					echo '<br>';*/
					
					if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
					{
						$permit_to_enter['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$permit_to_enter['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
						//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
					}
					else { $permit_to_enter['duration'] = '--/--'; }
					$response['data'][] = $permit_to_enter;
				}
			}
		}
    }
	else
	{
		$collection = new MongoCollection($app_data, 'permit_to_enter');
		//$C_Query = array( 'company_id' => $company_ID );
		//$cursor_pte = $collection->find(array('company_id'=> $C_Query));
		$criteria_pte = array(	
			'$and' => array( 
				array( 'company_id'=> $_REQUEST['company_id'] ),
				//array( 'company_id'=> $demo_pa_company_id ),
				array( 'user_id' => $_REQUEST['user_id'] )
			)
		);	
		$cursor_pte = $collection->find($criteria_pte);
		
		//$cursor_pte = $collection->find();
		
		if($cursor_pte->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_pte as $permit_to_enter)
			{
				unset($permit_to_enter['_id']);
				$permit_id = $permit_to_enter['permit_id'];	
								
				if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
				{
					//calculation of Duration
					$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
					$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
					
					/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
					echo $since_start->days.' days total -- ';
					echo $since_start->y.' years -- ';
					echo $since_start->m.' months -- ';
					echo $since_start->d.' days -- ';
					echo $since_start->h.' hours -- ';
					echo $since_start->i.' minutes -- ';
					echo $since_start->s.' seconds---';
					echo '<br>';*/
					
					if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
					{
						$permit_to_enter['duration'] = 'NOW';
						//echo 'Now<br><br>';
					}
					else if($since_start->d == 0 && $since_start->h >= 0)
					{
						$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
						//echo 'Before 1 day<br><br>';
					}
					else if($since_start->days == 1)
					{
						$permit_to_enter['duration'] = 'Yesterday';
						//echo 'Yesterday<br><br>';
					}
					else if($since_start->days >= 2)
					{
						$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
						//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
					}
					else { 
						$permit_to_enter['duration'] = '--/--'; 
					}
					//End Duration Calculation
					
					//Show Data
					//$response['data'][] = $permit_to_enter;					
					$response['data']['permit_id'] = $permit_to_enter['permit_id'];
					$response['data']['user_id'] = $permit_to_enter['user_id'];
					$response['data']['date_from'] = $permit_to_enter['date_from'];
					$response['data']['date_to'] = $permit_to_enter['date_to'];
					$response['data']['time_from'] = $permit_to_enter['time_from'];
					$response['data']['time_to'] = $permit_to_enter['time_to'];
					$response['data']['registered_time'] = $permit_to_enter['registered_time'];
					$response['data']['approved'] = $permit_to_enter['approved'];
					$response['data']['subadmin_approved'] = $permit_to_enter['subadmin_approved'];
					$response['data']['admin_approved'] = $permit_to_enter['admin_approved'];
					$response['data']['token'] = $permit_to_enter['token'];
					$response['data']['duration'] = $permit_to_enter['duration'];
				}			
				
			}
		}
		else
		{
			$response['status'] = 'false';
			$response['error'] = 'Invalid Company ID';
			exit(json_encode($response));
		}		
	}
	
	
}

//List Locks under Permit
if($_REQUEST['action'] == 'list_bluetooth_lock' && 
isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '')
{
	$user_id = $_REQUEST['user_id'];
	
	$collection = $app_data->users;
	$user_details = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
	
	$response['status'] = 'false';
	$response['error'] = 'Invalid Parameters';
	
	$collection = $app_data->users;
	$user_details = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
	
	if(isset($user_details['user_id']))
	{
		if($user_details['role'] == 1)
		{
			
		}
		else
		{
			$com = array(); //Company details array
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
						$com['company_ID'] = $company['company_ID']; //Put in company details array
					}
				}
			}
			
			//Lock Group Not used here
			
//			$lg = $app_data->lockgroup;
			//$cursor = $lg->find();
//			$cursor = $lg->find();
//			if($cursor->count() > 0)
//			{
//				foreach($cursor as $lockgroup)
//				{
//					if(in_array($lockgroup['company_id'],$com))
//					{
						//$response['status'] = 'true';
//						unset($lockgroup['_id']);
//						$response['lockgroups_data'][] = $lockgroup;
//					}
//				}
//			}
			
			
			$lock_group_id = array();
			
			$collection = new MongoCollection($app_data, 'users');
			$users = $collection->find( array('user_id'=>(int)$_REQUEST['user_id']) );
			if($users->count() > 0) { 
				//$response['status'] = 'true';
				foreach($users as $user)
				{
					if(in_array($user['role'],array(4,5,6,7,8))  && $user['company_id'] == $user_details['company_id'])
					{
						unset($user['_id']);
						//$response['user'][] = $user; //Cannot reveal all data
						$user_id = $user['user_id'];
						$username = $user['username'];
						$lock_group_id = $user['lock_group_id'];
						
						$com['users'] = $user_id; //Put in company details array
					}
					
				}
			}
			
			//Added by Alan 2018-02-25
			$collectionGroup = new MongoCollection($app_data, 'KeyLockGroup');
			//$criteriaGroup = array('keyLockGroup_ID'=>(int) $_REQUEST['pairing_id']);
			$accessControl = $collectionGroup->find();
			if($accessControl->count() > 0) { 
				//$response['status'] = 'true';
				foreach($accessControl as $accessControl)
				{
					$i=0;
					if(in_array($accessControl['company_id'],$com)) //If User company and User ID is correct
					{
						//$response['status'] = 'true';
						unset($accessControl['_id']);
						$access_date_from = $accessControl['date_from'];
						$access_date_to = $accessControl['date_to'];
						$access_time_from_hh = $accessControl['time_from_hh'];
						$access_time_from_mm = $accessControl['time_from_mm'];
						$access_time_to_hh = $accessControl['time_to_hh'];
						$access_time_to_mm = $accessControl['time_to_mm'];
						$lock_group_id = $accessControl['lock_group_id'];
						
						//$response['access_control'][] = $accessControl;
						$response['access_control'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
						$response['access_control'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
						$response['access_control'][$i]['pairing_name'] = $accessControl['pairing_name'];
						$response['access_control'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
						$response['access_control'][$i]['key_group_id'] = $accessControl['key_group_id'];
						$response['access_control'][$i]['company_id'] = $accessControl['company_id'];
						$response['access_control'][$i]['users'] = $accessControl['users'];
						$response['access_control'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
						$response['access_control'][$i]['date_from'] = $accessControl['date_from'];
						$response['access_control'][$i]['date_to'] = $accessControl['date_to'];
						$response['access_control'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
						$response['access_control'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
						$response['access_control'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
						$response['access_control'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
						$response['access_control'][$i]['lat'] = $accessControl['lat'];
						$response['access_control'][$i]['long'] = $accessControl['long'];
						$response['access_control'][$i]['radius'] = $accessControl['radious']; //TODO: change in DB
						$response['access_control'][$i]['added_by'] = $accessControl['added_by'];

						//unset($accessControl['keyLockGroup_ID']);
						//unset($accessControl['pairing_name']);
						
						$i++;
					}
				}
			}
			
			$collection = new MongoCollection($app_data, 'permit_to_enter');
			//$C_Query = array( 'company_id' => $company_ID );
			//$cursor_pte = $collection->find(array('company_id'=> $C_Query));
			$criteria_pte = array(	
				'$and' => array( 
					array( 'company_id'=> $_REQUEST['company_id'] ),
					//array( 'company_id'=> $demo_pa_company_id ),
					array( 'user_id' => $_REQUEST['user_id'] )
				)
			);	
			$cursor_pte = $collection->find($criteria_pte);
			
			//$cursor_pte = $collection->find();
			
			if($cursor_pte->count() > 0) { 
				//$response['status'] = 'true';
				$c = 0;
				foreach($cursor_pte as $permit_to_enter)
				{
					unset($permit_to_enter['_id']);
					$permit_id = $permit_to_enter['permit_id'];	
									
					if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
					{
						//calculation of Duration
						$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
						$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
						
						//echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
						//echo $since_start->days.' days total -- ';
						//echo $since_start->y.' years -- ';
						//echo $since_start->m.' months -- ';
						//echo $since_start->d.' days -- ';
						//echo $since_start->h.' hours -- ';
						//echo $since_start->i.' minutes -- ';
						//echo $since_start->s.' seconds---';
						//echo '<br>';
						
						if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
						{
							$permit_to_enter['duration'] = 'NOW';
							//echo 'Now<br><br>';
						}
						else if($since_start->d == 0 && $since_start->h >= 0)
						{
							$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
							//echo 'Before 1 day<br><br>';
						}
						else if($since_start->days == 1)
						{
							$permit_to_enter['duration'] = 'Yesterday';
							//echo 'Yesterday<br><br>';
						}
						else if($since_start->days >= 2)
						{
							$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
							//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
						}
						else { 
							$permit_to_enter['duration'] = '--/--'; 
						}
						//End Duration Calculation
						
						$permit_date_from = $permit_to_enter['date_from'];
						$permit_date_to = $permit_to_enter['date_to'];
						$permit_time_from = $permit_to_enter['time_from'];
						$permit_time_to = $permit_to_enter['time_to'];
						//Show Data
						//$response['data'][] = $permit_to_enter;					
						$response['permit'][$c]['permit_id'] = $permit_to_enter['permit_id'];
						$response['permit'][$c]['user_id'] = $permit_to_enter['user_id'];
						$response['permit'][$c]['date_from'] = $permit_to_enter['date_from'];
						$response['permit'][$c]['date_to'] = $permit_to_enter['date_to'];
						$response['permit'][$c]['time_from'] = $permit_to_enter['time_from'];
						$response['permit'][$c]['time_to'] = $permit_to_enter['time_to'];
						$response['permit'][$c]['registered_time'] = $permit_to_enter['registered_time'];
						$response['permit'][$c]['approved'] = $permit_to_enter['approved'];
						$response['permit'][$c]['subadmin_approved'] = $permit_to_enter['subadmin_approved'];
						$response['permit'][$c]['admin_approved'] = $permit_to_enter['admin_approved'];
						$response['permit'][$c]['token'] = $permit_to_enter['token'];
						$response['permit'][$c]['duration'] = $permit_to_enter['duration'];
						
						$c++;
					}			
					
				}
			}
			else
			{
				$response['status'] = 'false';
				$response['error'] = 'Invalid Company ID';
				exit(json_encode($response));
			}
			
			//Show Time Now
			$date_time_now = date("Y-m-d h:i:sa");
			//$date_now = date("d-m-Y");
			$date_now = date("d-m-Y");
			//$time_now = date("H:i:s");
			$time_now = date("H:i:s");
			$response['date_now'] = $date_now;
			$response['time_now'] = $time_now;
			
			//Access Control Date & Time
			//
			//$access_date_from = $accessControl['date_from'];//01-02-2019
			//$access_date_to = $accessControl['date_to'];//01-10-2019
			//$access_time_from_hh = $accessControl['time_from_hh'];//00 - 24 hour format
			//$access_time_from_mm = $accessControl['time_from_mm'];//00 - 60 minute format
			//$access_time_to_hh = $accessControl['time_to_hh'];//00 - 24 hour format
			//$access_time_to_mm = $accessControl['time_to_mm'];//00 - 60 minute format
			//
			//Permit Date & Time
			//
			//$permit_date_from = $permit_to_enter['date_from']; //31/1/2019
			//$permit_date_to = $permit_to_enter['date_to']; //30/4/2019
			//$permit_time_from = $permit_to_enter['time_from'];//20:50  - 24 hour format
			//$permit_time_to = $permit_to_enter['time_to'];//20:50 - 24 hour format
			//
						
			//Process Allowed Lock Access
			
			//Convert Time to m/d/Y H:i:s due to php reading d/m/Y as American Time
			$year_format = 'Y';
			$date_format = 'd/m/Y';
			$time_format = 'd/m/Y H:i:s';
			$american_date_format = 'm/d/Y';
			$american_time_format = 'm/d/Y H:i:s';
			//$time_before_format = DateTime::createFromFormat($time_format, $date_from);
			//$time_after_format =  $time_before_format->format('m/d/Y H:i:s');
			
			//Permit Date Conversion
			//Replace - with / if old date format is used
			$permit_date_from = str_replace("-","/",$permit_date_from);
			$permit_date_to = str_replace("-","/",$permit_date_to);
			
			$permit_date_from_before_format = DateTime::createFromFormat($date_format, $permit_date_from);
			$permit_date_from_after_format =  $permit_date_from_before_format->format('d-m-Y');
			$permit_date_from_compare =  strtotime($permit_date_from_after_format);
			$permit_date_to_before_format = DateTime::createFromFormat($date_format, $permit_date_to);
			$permit_date_to_after_format =  $permit_date_to_before_format->format('d-m-Y');
			$permit_date_to_compare =  strtotime($permit_date_to_after_format);			
			
			$response['permit_date_from'] = $permit_date_from_after_format;
			$response['permit_date_to'] = $permit_date_to_after_format;
			$response['permit_time_from'] = $permit_time_from;
			$response['permit_time_to'] = $permit_time_to;
			
			//Access Time Conversion
			$access_time_from = $access_time_from_hh . ":" . $access_time_from_mm . ":00";
			$access_time_to = $access_time_to_hh . ":" . $access_time_to_mm . ":00";
			//$response['access_time_from'] = $access_time_from;
			//$response['access_time_to'] = $access_time_to;
			
			//Test Time Allowed
			//
//			if ( time() >= strtotime($access_time_from) && time() <= strtotime($access_time_to) ){
//				$response['permit_time_allowed'] = 'yes';
//			}
//			else{
//				$response['permit_time_allowed'] = 'no';
//			}
//
			
			//if ( $date_now >= $permit_date_from_before_format && $date_now <= $permit_date_to_before_format){
			if ( date() >= strtotime($permit_date_from_compare) && date() <= strtotime($permit_date_to_compare) ){
				unset($response['error']);
				$response['permit_date_allowed'] = 'yes';
				
				
				if ( time() >= strtotime($permit_time_from) && time() <= strtotime($permit_time_to) ){
					$response['permit_time_allowed'] = 'yes';
					
					$collection_locks = new MongoCollection($app_data, 'locks');					
					$cursor_locks = $collection_locks->find( array( 'lock_group_id' => $lock_group_id) ); // Find using lock group id
					if($cursor_locks->count() > 0) { 
						$response['status'] = 'true';
						$x=0;
						foreach($cursor_locks as $locks)
						{
							unset($locks['_id']);
							if (in_array($locks['company_id'],$com)){								
								//Show Data
								//$response['locks'][$x] = $locks;
								$response['locks'][$x]['lock_id'] = $locks['lock_ID'];
								$response['locks'][$x]['serial_number'] = $locks['serial_number'];
								$response['locks'][$x]['company_id'] = $locks['company_id'];
								$response['locks'][$x]['lock_name'] = $locks['lock_name'];
								$response['locks'][$x]['lock_group_id'] = $locks['lock_group_id'];
								$response['locks'][$x]['log_number'] = $locks['log_number'];
								$response['locks'][$x]['site_id'] = $locks['site_id'];
							}
							$x++;
						}
					}
					
				}
				else{
					$response['permit_time_allowed'] = 'no';
				}				
			}
			else{
				$response['permit_date_allowed'] = 'no';
			}
			
			
			//echo $time_after_format;
			//$detection_time = date('d/m/Y H:i:s',strtotime($alarm_time));
			//
//			$exit_building_start_time = date('m/d/Y H:i:s',strtotime($alarm_time));
//			$exit_building_start_time = date('m/d/Y H:i:s',strtotime($time_after_format));
//			$exit_building_start_time_before_format = DateTime::createFromFormat($american_time_format, $exit_building_start_time);
//			$exit_building_start_time_date_first =  $exit_building_start_time_before_format->format('d/m/Y H:i:s');
//			$exit_building_start_time = date('m/d/Y H:i:s', $exit_building_start_time_raw); 
			//
			
		}
	}
	
	
	
}
	
//Open Locks under Permit
if($_REQUEST['action'] == 'open_bluetooth_lock' && 
isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
isset($_REQUEST['lock_id']) && $_REQUEST['lock_id'] != '')
{
	$user_id = $_REQUEST['user_id'];
	$lock_id = $_REQUEST['lock_id'];
	$response['open_lock'] = 'no';	
	
	$collection = $app_data->users;
	$user_details = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
	
	$response['status'] = 'false';
	$response['error'] = 'Invalid Parameters';
	
	$collection = $app_data->users;
	$user_details = $collection->findOne(array('user_id'=>(int)$_REQUEST['user_id']));
	
	if(isset($user_details['user_id']))
	{
		if($user_details['role'] == 1)
		{
			
		}
		else
		{
			$com = array(); //Company details array
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
						$com['company_ID'] = $company['company_ID']; //Put in company details array
					}
				}
			}
			
			//Lock Group Not used here
			/*
			$lg = $app_data->lockgroup;
			//$cursor = $lg->find();
			$cursor = $lg->find();
			if($cursor->count() > 0)
			{
				foreach($cursor as $lockgroup)
				{
					if(in_array($lockgroup['company_id'],$com))
					{
						//$response['status'] = 'true';
						unset($lockgroup['_id']);
						$response['lockgroups_data'][] = $lockgroup;
					}
				}
			}
			*/
			
			$lock_group_id = array();
			
			$collection = new MongoCollection($app_data, 'users');
			$users = $collection->find( array('user_id'=>(int)$_REQUEST['user_id']) );
			if($users->count() > 0) { 
				//$response['status'] = 'true';
				foreach($users as $user)
				{
					if(in_array($user['role'],array(4,5,6,7,8))  && $user['company_id'] == $user_details['company_id'])
					{
						unset($user['_id']);
						//$response['user'][] = $user; //Cannot reveal all data
						$user_id = $user['user_id'];
						$username = $user['username'];
						$lock_group_id = $user['lock_group_id'];
						
						$com['users'] = $user_id; //Put in company details array
					}
					
				}
			}
			
			//Added by Alan 2018-02-25
			$collectionGroup = new MongoCollection($app_data, 'KeyLockGroup');
			//$criteriaGroup = array('keyLockGroup_ID'=>(int) $_REQUEST['pairing_id']);
			$accessControl = $collectionGroup->find();
			if($accessControl->count() > 0) { 
				//$response['status'] = 'true';
				foreach($accessControl as $accessControl)
				{
					$i=0;
					if(in_array($accessControl['company_id'],$com)) //If User company and User ID is correct
					{
						//$response['status'] = 'true';
						unset($accessControl['_id']);
						$access_date_from = $accessControl['date_from'];
						$access_date_to = $accessControl['date_to'];
						$access_time_from_hh = $accessControl['time_from_hh'];
						$access_time_from_mm = $accessControl['time_from_mm'];
						$access_time_to_hh = $accessControl['time_to_hh'];
						$access_time_to_mm = $accessControl['time_to_mm'];
						$lock_group_id = $accessControl['lock_group_id'];
						
						//$response['access_control'][] = $accessControl;
						/*
						$response['access_control'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
						$response['access_control'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
						$response['access_control'][$i]['pairing_name'] = $accessControl['pairing_name'];
						$response['access_control'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
						$response['access_control'][$i]['key_group_id'] = $accessControl['key_group_id'];
						$response['access_control'][$i]['company_id'] = $accessControl['company_id'];
						$response['access_control'][$i]['users'] = $accessControl['users'];
						$response['access_control'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
						$response['access_control'][$i]['date_from'] = $accessControl['date_from'];
						$response['access_control'][$i]['date_to'] = $accessControl['date_to'];
						$response['access_control'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
						$response['access_control'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
						$response['access_control'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
						$response['access_control'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
						$response['access_control'][$i]['lat'] = $accessControl['lat'];
						$response['access_control'][$i]['long'] = $accessControl['long'];
						$response['access_control'][$i]['radius'] = $accessControl['radious']; //TODO: change in DB
						$response['access_control'][$i]['added_by'] = $accessControl['added_by'];
						

						//unset($accessControl['keyLockGroup_ID']);
						//unset($accessControl['pairing_name']);
						
						$i++;
						*/
					}
				}
			}
			
			$collection = new MongoCollection($app_data, 'permit_to_enter');
			//$C_Query = array( 'company_id' => $company_ID );
			//$cursor_pte = $collection->find(array('company_id'=> $C_Query));
			$criteria_pte = array(	
				'$and' => array( 
					array( 'company_id'=> $_REQUEST['company_id'] ),
					//array( 'company_id'=> $demo_pa_company_id ),
					array( 'user_id' => $_REQUEST['user_id'] )
				)
			);	
			$cursor_pte = $collection->find($criteria_pte);
			
			//$cursor_pte = $collection->find();
			
			if($cursor_pte->count() > 0) { 
				//$response['status'] = 'true';
				$c = 0;
				foreach($cursor_pte as $permit_to_enter)
				{
					unset($permit_to_enter['_id']);
					$permit_id = $permit_to_enter['permit_id'];	
									
					if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
					{
						//calculation of Duration
						$start_date = new DateTime( date('d-m-Y H:i',strtotime( $permit_to_enter['registered_time'] )) );
						$since_start = $start_date->diff(new DateTime( date('d-m-Y H:i') ));
						
						/*echo date('d F Y, H:i') . '---' . $permit_to_enter['registered_time'] . '-----';
						echo $since_start->days.' days total -- ';
						echo $since_start->y.' years -- ';
						echo $since_start->m.' months -- ';
						echo $since_start->d.' days -- ';
						echo $since_start->h.' hours -- ';
						echo $since_start->i.' minutes -- ';
						echo $since_start->s.' seconds---';
						echo '<br>';*/
						
						if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
						{
							$permit_to_enter['duration'] = 'NOW';
							//echo 'Now<br><br>';
						}
						else if($since_start->d == 0 && $since_start->h >= 0)
						{
							$permit_to_enter['duration'] = date('H:i', strtotime( $permit_to_enter['registered_time']));
							//echo 'Before 1 day<br><br>';
						}
						else if($since_start->days == 1)
						{
							$permit_to_enter['duration'] = 'Yesterday';
							//echo 'Yesterday<br><br>';
						}
						else if($since_start->days >= 2)
						{
							$permit_to_enter['duration'] = date('d/m', strtotime( $permit_to_enter['registered_time']));
							//echo  date('d/m', strtotime( $permit_to_enter['registered_time'])) . ' <br><br>';
						}
						else { 
							$permit_to_enter['duration'] = '--/--'; 
						}
						//End Duration Calculation
						
						$permit_date_from = $permit_to_enter['date_from'];
						$permit_date_to = $permit_to_enter['date_to'];
						$permit_time_from = $permit_to_enter['time_from'];
						$permit_time_to = $permit_to_enter['time_to'];
						//Show Data
						//$response['data'][] = $permit_to_enter;
						/*
						$response['permit'][$c]['permit_id'] = $permit_to_enter['permit_id'];
						$response['permit'][$c]['user_id'] = $permit_to_enter['user_id'];
						$response['permit'][$c]['date_from'] = $permit_to_enter['date_from'];
						$response['permit'][$c]['date_to'] = $permit_to_enter['date_to'];
						$response['permit'][$c]['time_from'] = $permit_to_enter['time_from'];
						$response['permit'][$c]['time_to'] = $permit_to_enter['time_to'];
						$response['permit'][$c]['registered_time'] = $permit_to_enter['registered_time'];
						$response['permit'][$c]['approved'] = $permit_to_enter['approved'];
						$response['permit'][$c]['subadmin_approved'] = $permit_to_enter['subadmin_approved'];
						$response['permit'][$c]['admin_approved'] = $permit_to_enter['admin_approved'];
						$response['permit'][$c]['token'] = $permit_to_enter['token'];
						$response['permit'][$c]['duration'] = $permit_to_enter['duration'];
						
						
						$c++;
						*/
					}			
					
				}
			}
			else
			{
				$response['status'] = 'false';
				$response['error'] = 'Invalid Company ID';
				exit(json_encode($response));
			}
			
			//Show Time Now
			$date_time_now = date("Y-m-d h:i:sa");
			//$date_now = date("d-m-Y");
			$date_now = date("d-m-Y");
			//$time_now = date("H:i:s");
			$time_now = date("H:i:s");
			$response['date_now'] = $date_now;
			$response['time_now'] = $time_now;
			
			//Access Control Date & Time
			/*
			$access_date_from = $accessControl['date_from'];//01-02-2019
			$access_date_to = $accessControl['date_to'];//01-10-2019
			$access_time_from_hh = $accessControl['time_from_hh'];//00 - 24 hour format
			$access_time_from_mm = $accessControl['time_from_mm'];//00 - 60 minute format
			$access_time_to_hh = $accessControl['time_to_hh'];//00 - 24 hour format
			$access_time_to_mm = $accessControl['time_to_mm'];//00 - 60 minute format
			*/
			//Permit Date & Time
			/*
			$permit_date_from = $permit_to_enter['date_from']; //31/1/2019
			$permit_date_to = $permit_to_enter['date_to']; //30/4/2019
			$permit_time_from = $permit_to_enter['time_from'];//20:50  - 24 hour format
			$permit_time_to = $permit_to_enter['time_to'];//20:50 - 24 hour format
			*/
						
			//Process Allowed Lock Access
			
			//Convert Time to m/d/Y H:i:s due to php reading d/m/Y as American Time
			$year_format = 'Y';
			$date_format = 'd/m/Y';
			$time_format = 'd/m/Y H:i:s';
			$american_date_format = 'm/d/Y';
			$american_time_format = 'm/d/Y H:i:s';
			//$time_before_format = DateTime::createFromFormat($time_format, $date_from);
			//$time_after_format =  $time_before_format->format('m/d/Y H:i:s');
			
			//Permit Date Conversion
			$permit_date_from_before_format = DateTime::createFromFormat($date_format, $permit_date_from);
			$permit_date_from_after_format =  $permit_date_from_before_format->format('d-m-Y');
			$permit_date_from_compare =  strtotime($permit_date_from_after_format);
			$permit_date_to_before_format = DateTime::createFromFormat($date_format, $permit_date_to);
			$permit_date_to_after_format =  $permit_date_to_before_format->format('d-m-Y');
			$permit_date_to_compare =  strtotime($permit_date_to_after_format);			
			
			$response['permit_date_from'] = $permit_date_from_after_format;
			$response['permit_date_to'] = $permit_date_to_after_format;
			$response['permit_time_from'] = $permit_time_from;
			$response['permit_time_to'] = $permit_time_to;
			
			//Access Time Conversion
			$access_time_from = $access_time_from_hh . ":" . $access_time_from_mm . ":00";
			$access_time_to = $access_time_to_hh . ":" . $access_time_to_mm . ":00";
			//$response['access_time_from'] = $access_time_from;
			//$response['access_time_to'] = $access_time_to;
			
			//Test Time Allowed
			/*
			if ( time() >= strtotime($access_time_from) && time() <= strtotime($access_time_to) ){
				$response['permit_time_allowed'] = 'yes';
			}
			else{
				$response['permit_time_allowed'] = 'no';
			}
			*/
			
			//if ( $date_now >= $permit_date_from_before_format && $date_now <= $permit_date_to_before_format){
			if ( date() >= strtotime($permit_date_from_compare) && date() <= strtotime($permit_date_to_compare) ){
				unset($response['error']);
				$response['permit_date_allowed'] = 'yes';
				
				
				if ( time() >= strtotime($permit_time_from) && time() <= strtotime($permit_time_to) ){
					$response['permit_time_allowed'] = 'yes';
					
					$collection_locks = new MongoCollection($app_data, 'locks');					
					$cursor_locks = $collection_locks->find( array( 'lock_group_id' => $lock_group_id) ); // Find using lock group id
					if($cursor_locks->count() > 0) { 						
						foreach($cursor_locks as $locks)
						{
							unset($locks['_id']);
							if (in_array($locks['company_id'],$com)){
								
								//Check if lock id is here
								if ( $lock_id == $locks['lock_ID']){
									$response['status'] = 'true';
									$response['open_lock'] = 'yes';
									//Show Data
									//$response['locks'][$i] = $locks;
									$response['locks']['lock_id'] = $locks['lock_ID'];
									$response['locks']['serial_number'] = $locks['serial_number'];
									$response['locks']['company_id'] = $locks['company_id'];
									$response['locks']['lock_name'] = $locks['lock_name'];
									$response['locks']['lock_group_id'] = $locks['lock_group_id'];
									$response['locks']['log_number'] = $locks['log_number'];
									$response['locks']['site_id'] = $locks['site_id'];									
								}
							}
						}
					}
					
				}
				else{
					$response['permit_time_allowed'] = 'no';
				}				
			}
			else{
				$response['permit_date_allowed'] = 'no';
			}
			
			
			//echo $time_after_format;
			//$detection_time = date('d/m/Y H:i:s',strtotime($alarm_time));
/*
			//$exit_building_start_time = date('m/d/Y H:i:s',strtotime($alarm_time));
			$exit_building_start_time = date('m/d/Y H:i:s',strtotime($time_after_format));
			$exit_building_start_time_before_format = DateTime::createFromFormat($american_time_format, $exit_building_start_time);
			$exit_building_start_time_date_first =  $exit_building_start_time_before_format->format('d/m/Y H:i:s');
			//$exit_building_start_time = date('m/d/Y H:i:s', $exit_building_start_time_raw); 
*/
			
			if($die == 1)
			{
				$user_reg = $app_data->bluetoothlock_history_log;
				$start_time = date('d-m-Y H:i:s');
				$end_dt = trim($_REQUEST['end_dt']) == '' ? date("d-m-Y H:i:s", strtotime('+1 hour')) : $_REQUEST['end_dt'];
				$post = array(
					'history_id' => getNext_users_Sequence('history_log'),
					'user_id'     =>(int)$_REQUEST['user_id'],
					'lock_id'     =>(int) $_REQUEST['lock_id'],
					'key_id'     =>(int) $_REQUEST['key_id'],
					'start_dt'     => $start_time,
					'end_dt'     => $end_dt,
					'Status' => 'false',
					'access_code' => 'Request Occures in Invalid Date & Time.',
					'requested_time'  => $start_time,
					'timer'  => '',
					);
				
				if($user_reg->insert($post))
				{
					$response['msg'] = 'You are not allowed for access at this time'; // 123456
				}

			}
		}
	}
}


//Currently NOT in use
if($_REQUEST['action'] == 'bluetooth_access_old' && !empty($_REQUEST['lock_id']) && !empty($_REQUEST['user_id']) && !empty($_REQUEST['NOT_USED']))
{
	if(isset($_REQUEST['end_dt']))
	{
		$future =  strtotime(date('d-m-Y H:i'));
		$old = strtotime(date($_REQUEST['end_dt']));
		if($old >= $future)
			{ }
		else
			{
				$response['status'] = 'false';
				$response['error'] = 'Invalid Date and Time...';
				exit(json_encode($response));
			}
	}
	
	$key_id = $_REQUEST['key_id'];
	$response['status'] = 'false';
	$response['error'] = 'Invalid User';
	
	$users_ = $app_data->users;
	$cursor = $users_->findOne(array('user_id' =>(int) $_REQUEST['user_id']));
	
	if(isset($cursor['user_id']))
	{
		if($cursor['role'] != 1)
		{
			if(date('D') == 'Sat' || date('D') == 'Sun')
			{
					$die = 1;
					$admin_approves = 'Yes';
					$com_approves = 'Yes';
					$settings = $app_data->settings;
					$cursor = $settings->findOne(array('company_id'=>(int)$cursor['company_id']));
					
					if(!empty($cursor['setting_id']))
					{
						$admin_approves = $cursor['super_admin_allow_saturday_sunday'];
						$com_approves = $cursor['allow_saturday_sunday'];
						if($admin_approves == 'No' || $com_approves == 'No')
						{
							$die = 0;
						}
					}
					else
					{
							$die = 0;
					}
					if($die == 0)
					{
						unset($response['error']);
						$response['msg'] = 'You are Not Allowed To use access code on '.date('l');
						exit(json_encode($response));
					}
			}
		}
		
	$response['error'] = 'Invalid Key Name';
	$keys_details = $app_data->keys;
	$key_detail = $keys_details->findOne(array('key_ID'=>(int)$key_id));
	
	if(isset($key_detail['key_ID']))
	{
		$response['status'] = 'false';
		unset($response['error']);
		
		$key_group_ids = json_decode($key_detail['key_group_id']);
		$KeyLockGroup = $app_data->KeyLockGroup;
		$KeyLockGroups = $KeyLockGroup->find();
		if($KeyLockGroups->count() > 0)
		{
			foreach($KeyLockGroups as $vals)
			{
				if(in_array( $vals['key_group_id'], $key_group_ids ))
				{
					// $pairing_ids[] = $vals['keyLockGroup_ID'];
					if(in_array($cursor['role'],array(4,5)))
					{
						if( in_array($cursor['user_id'],  $vals['users']))
						{
							$pairing_ids[] = $vals['keyLockGroup_ID'];
						}
					}
					else
					{
						$pairing_ids[] = $vals['keyLockGroup_ID'];
					}
				}
			}
		}
		
		// echo '<pre>';
		// print_r($pairing_ids);
		// exit;
		if(!empty($pairing_ids))
		{
			$pairing_ids = array_map('intval',$pairing_ids);
			$KeyLockGroups = $app_data->KeyLockGroup;
			$arg = array('keyLockGroup_ID' => array('$in'=> $pairing_ids ));
			$cursor = $KeyLockGroups->find( $arg );
			
			$die = 0;
			
			if($cursor->count() > 0)
			{
				foreach($cursor as $pairing_details)
				{
					$paymentDate = !empty($_REQUEST['end_dt']) ? strtotime(date('d-m-Y',strtotime($_REQUEST['end_dt']))) : strtotime(date('d-m-Y'));
					$paymentTime = !empty($_REQUEST['end_dt']) ? strtotime(date('H:i',strtotime($_REQUEST['end_dt']))) : strtotime(date('H:i'));
					// Start here
					$die = 1;					
					$contractDateBegin = strtotime( $pairing_details['date_from']);
					$contractDateEnd = strtotime( $pairing_details['date_to'] );
					$contractTimeBegin = strtotime( $pairing_details['time_from_hh'] . ':' . $pairing_details['time_from_mm']);
					$contractTimeEnd = strtotime( $pairing_details['time_to_hh'] . ':' . $pairing_details['time_to_mm'] );
					
					if ((($paymentDate >= $contractDateBegin) && ($paymentDate <= $contractDateEnd)) && (($paymentTime >= $contractTimeBegin) && ($paymentTime <= $contractTimeEnd) ) )
					{
							$die = 0;
							break;
					} 
					// End here
				}
			}
		}
		else
		{
			unset($response['error']);
			$response['msg'] = 'You are Unauthorized to Access Access Code...';
			exit(json_encode($response));
		}
		
		if($die == 1)
		{
			$user_reg = $app_data->history_log;
			$start_time = date('d-m-Y H:i:s');
			$end_dt = trim($_REQUEST['end_dt']) == '' ? date("d-m-Y H:i:s", strtotime('+1 hour')) : $_REQUEST['end_dt'];
			$post = array(
				'history_id' => getNext_users_Sequence('history_log'),
				'user_id'     =>(int)$_REQUEST['user_id'],
				'lock_id'     =>(int) $_REQUEST['lock_id'],
				'key_id'     =>(int) $_REQUEST['key_id'],
				'start_dt'     => $start_time,
				'end_dt'     => $end_dt,
				'Status' => 'false',
				'access_code' => 'Request Occures in Invalid Date & Time.',
				'requested_time'  => $start_time,
				'timer'  => '',
				);
			
			if($user_reg->insert($post))
			{
				$response['msg'] = 'You are not allowed for access at this time'; // 123456
			}
			echo json_encode($response);
			exit;
		}
		
		// Get Lock and Key Details
		$locks = $app_data->locks;
		$cursor = $locks->find(array('lock_ID' =>(int) $_REQUEST['lock_id']));
		if($cursor->count() > 0)
		{
				$response['status'] = 'true';
			foreach($cursor as $company_detail)
			{
				$lock_name = $company_detail['lock_name'];
			}
		}
		$response['status'] = 'false';
		$keys = $app_data->keys;
		$cursor = $keys->find(array('key_ID' =>(int) $_REQUEST['key_id']));
		if($cursor->count() > 0)
		{
			$response['status'] = 'true';
			foreach($cursor as $keys)
			{
				$phone_number = $keys['key_phone_number'];
			}
		}
		
		
				// Main Logic for Access code Start
		
				if($response['status'] == 'true')
				{
					unset($response['msg']);
					$lock_name = str_replace(' ', '%20', $lock_name);
					// $response['accesscode'] = file_get_contents('http://app.weisslocks.com/api2?lockname=certis%201&mobile=6590293123');
					/*
					$app_date = isset($_REQUEST['end_dt']) && $_REQUEST['end_dt'] != '' ? $_REQUEST['end_dt'] : date('d-m-Y H:i');
					$year =  date('Y',strtotime($app_date));
					$month =  date('m',strtotime($app_date));
					$day =  date('d',strtotime($app_date));
					$hour =  date('H',strtotime($app_date));
					$minute =  date('i',strtotime($app_date));
					'http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number.'&year'.$year.'&month='.$month.'&day='.$day.'&hour='.$hour.'&minute='.$minute;
					*/
					// $app_date = isset($_REQUEST['end_dt']) && $_REQUEST['end_dt'] != '' ? $_REQUEST['end_dt'] : date('d-m-Y H:i');
					// $year =  date('Y',strtotime($app_date));
					// $month =  date('m',strtotime($app_date));
					// $day =  date('d',strtotime($app_date));
					// $hour =  date('H',strtotime($app_date));
					// $minute =  date('i',strtotime($app_date));
					
					$response['accesscode'] = file_get_contents('http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number);
					// $response['accesscode'] = file_get_contents('http://app.weisslocks.com/api2?lockname='.$lock_name.'&mobile='.$phone_number.'&year'.$year.'&month='.$month.'&day='.$day.'&hour='.$hour.'&minute='.$minute);
					 $MAIN_response = $response['accesscode'];
					 $params = explode(':',$MAIN_response);
					// $response['status'] = isset($params[2]) ? 'false' : 'true';
					 $response['accesscode'] = $params[2];
					 $response['valid_on'] = $params[3] . ':' . $params[4] . ':' . $params[5];
					$end_dt = trim($_REQUEST['end_dt']) == '' ? date("d-m-Y H:i:s", strtotime('+1 hour')) : $_REQUEST['end_dt'];
					$user_reg = $app_data->history_log;
					$start_time = date('d-m-Y H:i:s');
					
					$response['status'] = is_null($params[2]) ? 'false' : 'true';
					$response['msg'] = is_null($params[2]) ? $MAIN_response : '';
					
					$start = new DateTime( $start_time );
					$end = new DateTime( $end_dt );
					$interval = $end->diff($start);
					$days = $interval->format('%d');
					$hours = 24 * $days + $interval->format('%h');
					
					$convert_to_mill = $hours.':'.$interval->format('%i').':00';
					$string = $convert_to_mill;
					$time   = explode(":", $string);

					$hour_mili   = $time[0] * 60 * 60 * 1000;
					$minute_mili = $time[1] * 60 * 1000;
					$sec_mili    = $time[2] * 1000;

					$result = $hour_mili + $minute_mili + $sec_mili;

					$post = array(
					'history_id' => getNext_users_Sequence('history_log'),
					'user_id'     =>(int)$_REQUEST['user_id'],
					'lock_id'     =>(int) $_REQUEST['lock_id'],
					'lock_name'     => $lock_name,
					'key_id'     =>(int) $_REQUEST['key_id'],
					'pairing_id'     =>(int) $_REQUEST['pairing_id'],
					'start_dt'     => $start_time,
					'end_dt'     => $end_dt,
					'Status' => $response['status'],
					'access_code' => $MAIN_response,
					'requested_time'  => $start_time,
					'timer'  => $result,
					);
					$user_reg->insert($post);
					$collection = new MongoCollection($app_data, 'history_log');
					$Reg_Query = array('_id' => $post['_id'] ) ;
					$cursor = $collection->find( $Reg_Query );
					
					if (strpos($response['accesscode'], 'Unauthorized') !== false || strpos($response['accesscode'], '500') !== false || strpos($response['accesscode'], 'foundError') !== false || strpos($response['accesscode'], 'not') !== false)
					{
						$response['status'] = 'false';
						$response['msg'] = $MAIN_response;
					}
					
					foreach($cursor as $history)
					{
						unset($history['_id']);
						$response['data'] = $history;
					}
				}
		
				// Main Logic for Access code End
		
		
		
		
		
		
		
		
		
		
		
	}
	}
}

if($_REQUEST['action'] == 'accesscode' && $_REQUEST['method'] == 'history' && !empty($_REQUEST['lock_id']) && !empty($_REQUEST['user_id']))
{
	$response['status'] = 'false';
	$response['error'] = 'Invalid Lock';
	$locks = $app_data->locks;
	$cursor = $locks->findOne(array('lock_ID'=>(int) $_REQUEST['lock_id'] ));
	
	if(!empty($cursor['lock_ID']))
	{
		unset($response['error']);
		$history_log_history = $app_data->history_log;
		$history_log_histories = $history_log_history->find(array('lock_id'=>(int)$_REQUEST['lock_id'],'user_id'=>(int)$_REQUEST['user_id']));
		if($history_log_histories->count() > 0)
		{
			$response['status'] = 'true';
			foreach($history_log_histories as $history)
			{
				// $history['ghfghfhfg'] = $history['access_code'];
				//Remove ID from showing
				unset($history['_id']);
				$params = explode(':', $history['access_code'] );
				
				$history['code_status'] = is_null($params[2]) ? 'false' : 'true';
				$history['code'] = (int) str_replace('\n','',$params[2]);
				
				$history['code_validity'] = is_null($params[3]) ? 'false' : 'true';
				$history['validity'] = $params[3]  . ':' . $params[4] . ':' . str_replace('None','',$params[5]);
				
				$response['data'][] = $history;
			}
		}
	}
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>