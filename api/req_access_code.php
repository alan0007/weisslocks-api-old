<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();
if($_REQUEST['action'] == 'time' && $_REQUEST['method'] == 'get')
{
	$response['status'] = 'true';
	$response['date'] = date('d-m-Y');
	$response['hour_minute'] = date('H') . '.' . date('i');
}
if($_REQUEST['action'] == 'validate_accesscode' && $_REQUEST['method'] == 'get')
{
	$settings = $app_data->settings;
	$cursor = $settings->findOne(array('super_user_id' =>1));
	$allow_saturday_sunday = $cursor['allow_saturday_sunday'];
	$response['status'] = 'true';
	$response['allow_saturday_sunday'] = $allow_saturday_sunday;
}

if($_REQUEST['action'] == 'accesscode' && $_REQUEST['method'] == 'get' && isset($_REQUEST['lock_id']) && $_REQUEST['lock_id'] != '' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '')
{
	$response['status'] = 'false';
	$response['msg'] = 'Invalid Parameters';
	
	$users = $app_data->users;
	$cursor = $users->findOne(array('user_id' =>(int) $_REQUEST['user_id']));
	if(!empty($cursor['user_id']) && $cursor['user_id'] != 1)
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
					$response['msg'] = 'You are Not Allowed To use access code on that Day...';
					exit(json_encode($response));
				}
		}
		
	// Check Current date is between Pairing's two date OR NOT..
	$KeyLockGroup = $app_data->KeyLockGroup;
	$cursor = $KeyLockGroup->findOne(array('keyLockGroup_ID'=>(int)$_REQUEST['pairing_id']));
	if(!empty($cursor['keyLockGroup_ID']))
	{
		$die = 0;
		
		if(isset($_REQUEST['end_dt']) && $_REQUEST['end_dt'] != '')
		{
			
			$future =  strtotime(date('d-m-Y H:i'));
			$old = strtotime(date($_REQUEST['end_dt']));
			if($old >= $future)
			{ }
			else
			{
			$die = 1;
			}
			
			$paymentDate = date('d-m-Y H:i',strtotime($_REQUEST['end_dt']));
			//$paymentDate=date('d-m-Y H:i', strtotime($paymentDate));;
			$contractDateBegin = date('d-m-Y H:i', strtotime( $cursor['date_from'] . ' ' . $cursor['time_from_hh'] . ':' . $cursor['time_from_mm']));
			$contractDateEnd = date('d-m-Y H:i', strtotime( $cursor['date_to'] . ' ' . $cursor['time_to_hh'] . ':' . $cursor['time_to_mm'] ));
			if (($paymentDate > $contractDateBegin) && ($paymentDate < $contractDateEnd))
			{ }  // Current Date is between 
			else
			{
				$die = 1;
			}
			$current_time = date('H:i',strtotime($_REQUEST['end_dt']));
			$date1 = DateTime::createFromFormat('H:i', $current_time);
			$date2 = DateTime::createFromFormat('H:i', $cursor['time_from_hh'] . ':' . $cursor['time_from_mm']);
			$date3 = DateTime::createFromFormat('H:i', $cursor['time_to_hh'] . ':' . $cursor['time_to_mm']);
			if ($date1 > $date2 && $date1 < $date3)
			{ } // Current Time is between 
			else
			{
				$die = 1;
			}
			
		}
		else
		{
		
			$paymentDate = date('d-m-Y H:i');
			$paymentDate=date('d-m-Y H:i', strtotime($paymentDate));;
			$contractDateBegin = date('d-m-Y H:i', strtotime( $cursor['date_from'] . ' ' . $cursor['time_from_hh'] . ':' . $cursor['time_from_mm']));
			$contractDateEnd = date('d-m-Y H:i', strtotime( $cursor['date_to'] . ' ' . $cursor['time_to_hh'] . ':' . $cursor['time_to_mm'] ));
			
			// $die = 0;
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
		
		}
	// ------------------------------------------------------------------------------------------------------
	
	//echo $die;exit;
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
				'pairing_id'     =>(int) $_REQUEST['pairing_id'],
				'start_dt'     => $start_time,
				'end_dt'     => $end_dt,
				'Status' => 'false',
				'access_code' => 'Request Occures in Invalid Date & Time.',
				'requested_time'  => $start_time,
				'timer'  => '',
				);
			
			if($user_reg->insert($post))
			{
				$response['msg'] = 'You are not allowed for access at this time';
			}
			echo json_encode($response);
			exit;
		}
		
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
	}
	}
}


//Currently in use
if($_REQUEST['action'] == 'accesscode' && $_REQUEST['method'] == 'post' && !empty($_REQUEST['key_id']) &&  !empty($_REQUEST['lock_id']) && !empty($_REQUEST['user_id']))
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