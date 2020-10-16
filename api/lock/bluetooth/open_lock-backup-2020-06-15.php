<?php
include(dirname(dirname(dirname(dirname(__FILE__)))) .'/configurations/config.php');

// Check Error
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Datetime in ISO format
//date_default_timezone_set('Asia/Singapore');
$datetime_now = date("c");

$response = array();

//Open Locks under Permit
// Updated 2020-03-04
if(isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' &&
isset($_REQUEST['permit_id']) && $_REQUEST['permit_id'] != '' &&
isset($_REQUEST['lock_id']) && $_REQUEST['lock_id'] != '')
{
	$user_id = $_REQUEST['user_id'];
	$lock_id = $_REQUEST['lock_id'];

    $response['open_lock'] = false;

    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';

    $response['user_id'] = $_REQUEST['user_id'];
    $response['company_id'] = $_REQUEST['company_id'];
    $response['permit_id'] = $_REQUEST['permit_id'];

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
			$companies = $collection1->find();
			if($companies->count() > 0) 
			{
				foreach($companies as $company)
				{
				    //Find if user is in company
					$users = json_decode($company['user_id']);
					$user_list = Array();
                    array_push($user_list, (int)$company['user_id']);
					if(in_array($_REQUEST['user_id'],$user_list))
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

            $collection_pte = new MongoCollection($app_data, 'permit_to_enter');
            //$C_Query = array( 'company_id' => $company_ID );
            //$cursor_pte = $collection->find(array('company_id'=> $C_Query));
            $criteria_pte = array(
                '$and' => array(
                    array( 'company_id'=> (String) $_REQUEST['company_id'] ),
                    //array( 'company_id'=> $demo_pa_company_id ),
                    //array( 'permit_id' => (int) $_REQUEST['permit_id'] ),
                    array( 'user_id' => (string) $_REQUEST['user_id'] )
                )
            );

            $cursor_pte = $collection_pte->find($criteria_pte);
            //$cursor_pte = $collection->find();

            if($cursor_pte->count() > 0) {
                //$response['status'] = 'true';
                $response['permit_found'] = true;
				$c = 0;
				foreach($cursor_pte as $permit_to_enter)
				{
					unset($permit_to_enter['_id']);
					$permit_id = $permit_to_enter['permit_id'];
                    $response['permit_id'][$c] = $permit_to_enter['permit_id'];

					if($permit_to_enter['permit_id'] != $_REQUEST['permit_id'])
					{
						$permit_date_from = $permit_to_enter['date_from'];
						$permit_date_to = $permit_to_enter['date_to'];
						$permit_time_from = $permit_to_enter['time_from'];
						$permit_time_to = $permit_to_enter['time_to'];
						//Show Data
						//$response['data'][] = $permit_to_enter;

//						$response['permit'][$c] = $permit_to_enter;
//						$response['permit'][$c]['user_id'] = $permit_to_enter['user_id'];
//						$response['permit'][$c]['date_from'] = $permit_to_enter['date_from'];
//						$response['permit'][$c]['date_to'] = $permit_to_enter['date_to'];
//						$response['permit'][$c]['time_from'] = $permit_to_enter['time_from'];
//						$response['permit'][$c]['time_to'] = $permit_to_enter['time_to'];
//						$response['permit'][$c]['registered_time'] = $permit_to_enter['registered_time'];
//						$response['permit'][$c]['approved'] = $permit_to_enter['approved'];
//						$response['permit'][$c]['subadmin_approved'] = $permit_to_enter['subadmin_approved'];
//						$response['permit'][$c]['admin_approved'] = $permit_to_enter['admin_approved'];
//						$response['permit'][$c]['token'] = $permit_to_enter['token'];
//						$response['permit'][$c]['duration'] = $permit_to_enter['duration'];
//
//						$c++;

					}
					else if ($permit_to_enter['permit_id'] == $_REQUEST['permit_id']){
                        $permit_date_from = $permit_to_enter['date_from'];
                        $permit_date_to = $permit_to_enter['date_to'];
                        $permit_time_from = $permit_to_enter['time_from'];
                        $permit_time_to = $permit_to_enter['time_to'];
                    }
					
				}
			}
			else
			{
                $response['permit_found'] = false;
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
			$permit_date_from_compare =  strtotime($permit_date_from_after_format); // ready for comparison
			$permit_date_to_before_format = DateTime::createFromFormat($date_format, $permit_date_to);
			$permit_date_to_after_format =  $permit_date_to_before_format->format('d-m-Y');
			$permit_date_to_compare =  strtotime($permit_date_to_after_format);	// ready for comparison

            //$permit_time_from_before_format = DateTime::createFromFormat($date_format, $permit_time_from);
            //$permit_time_from_after_format =  $permit_time_from_before_format->format('d-m-Y');
            //$permit_time_from_compare =  strtotime($permit_time_from_after_format); // ready for comparison
            //$permit_time_to_before_format = DateTime::createFromFormat($date_format, $permit_time_to);
            //$permit_time_to_after_format =  $permit_time_to_before_format->format('d-m-Y');
            //$permit_time_to_compare =  strtotime($permit_time_to_after_format); // ready for comparison

			$response['permit_date_from'] = $permit_date_from_after_format;
			$response['permit_date_to'] = $permit_date_to_after_format;
			$response['permit_time_from'] = $permit_time_from;
			$response['permit_time_to'] = $permit_time_to;

            $response['permit_date_allowed_status'] = false;
            $response['permit_time_allowed_status'] = false;

			//if ( $date_now >= $permit_date_from_before_format && $date_now <= $permit_date_to_before_format){
			if ( strtotime($date_now) >= $permit_date_from_compare && strtotime($date_now) <= $permit_date_to_compare ){
				unset($response['error']);
                $response['permit_date_allowed_status'] = true;
				$response['permit_date_allowed'] = 'yes';
				
				if ( strtotime($time_now) >= strtotime($permit_time_from) && strtotime($time_now) <= strtotime($permit_time_to) ){
                    $response['permit_time_allowed_status'] = true;
				    $response['permit_time_allowed'] = 'yes';

                    $response['status'] = 'false';
                    $response['open_lock'] = false;

                    $collection_locks = $app_data->locks;
                    $locks = $collection_locks->findOne(array('lock_ID'=>(int)$_REQUEST['lock_id']));
                    if(isset($locks)){
                        // Added to check if lock needs approval
                        $collection_approval_for_lock = $app_data->approval_for_lock;
                        $approval_for_lock = $collection_approval_for_lock->findOne(array('lock_id'=>(int)$locks['lock_ID']));
                        //Check result of approval for lock
                        //$response['approval_for_lock'] = $approval_for_lock;

                        if(isset($approval_for_lock)) {
                            $response['locks']['approval']['require_admin_approval'] = $approval_for_lock['require_admin_approval'];
                            $response['locks']['approval']['require_subadmin_approval'] = $approval_for_lock['require_subadmin_approval'];
                            $response['locks']['approval']['status'] = 'pending';

                            if ($approval_for_lock['require_admin_approval'] == true){
                                // If subadmin is required
                                if ($approval_for_lock['require_subadmin_approval'] == false){
                                    $response['open_lock'] = false;

//                                    // Check Request for Approval
//                                    $collection_last_approved = $app_data->approval_request_for_lock;
//                                    $criteria_last_approved = array(
//                                        '$and' => array(
//                                            array( 'company_id'=> (int)$_REQUEST['company_id'] ),
//                                            array( 'user_id'=> (int)$_REQUEST['user_id'] ),
//                                            array( 'lock_id' => (int)$_REQUEST['lock_id'] ),
//                                            array( 'permit_id' => (int)$_REQUEST['permit_id'] ),
//                                            array( 'admin_approved' => true )
//                                        )
//                                    );
//                                    //$cursor_approval_request_for_lock = $collection_approval_request_for_lock->find($criteria_approval_request_for_lock)->sort(array('approval_request_for_lock_id'=>-1));
//
//                                    //$criteria_approval_request_for_lock = array( 'lock_id' => (int)$_REQUEST['lock_id'] );
//                                    $cursor_last_approved = $collection_last_approved->find($criteria_last_approved);
//                                    $cursor_last_approved->sort(array('approval_request_for_lock_id' => -1))->limit(1);
//                                    foreach ($cursor_last_approved as $last_approved){
//                                        unset($last_approved['_id']);
//                                        $response['locks']['approval']['last_approved'][] = $last_approved;
//                                        $last_approved_approval_request_for_lock_id = $last_approved['approval_request_for_lock_id'];
//                                        $last_approved_admin_approved = $last_approved['admin_approved'];
//                                        $last_approved_admin_approved_on = $last_approved['admin_approved_on'];
//                                        $last_approved_valid_until = $last_approved['valid_until'];
//                                    }

                                    $collection_last_request = $app_data->approval_request_for_lock;
                                    $criteria_last_request = array(
                                        '$and' => array(
                                            array( 'company_id'=> (int)$_REQUEST['company_id'] ),
                                            array( 'user_id'=> (int)$_REQUEST['user_id'] ),
                                            array( 'lock_id' => (int)$_REQUEST['lock_id'] ),
                                            array( 'permit_id' => (int)$_REQUEST['permit_id'] )
                                        )
                                    );
                                    $cursor_last_request = $collection_last_request->find($criteria_last_request);
                                    $cursor_last_request->sort(array('approval_request_for_lock_id' => -1))->limit(1);
                                    foreach ($cursor_last_request as $last_request){
                                        unset($last_request['_id']);
                                        $response['locks']['approval']['last_approved'][] = $last_request;
                                        $last_request_approval_request_for_lock_id = $last_request['approval_request_for_lock_id'];
                                        $last_request_created_timestamp = $last_request['created_timestamp'];
                                        $last_request_admin_approved = $last_request['admin_approved'];
                                        $last_request_admin_approved_on = $last_request['admin_approved_on'];
//                                        $last_request_valid_from = $last_request['valid_from'];
                                        $last_request_valid_until = $last_request['valid_until'];
                                    }

                                    // Check Valid
                                    $response['locks']['approval']['timestamp'] = $datetime_now;
                                    //Check if last request is approved or not
                                    if ( $last_request_admin_approved == true ){
                                        // Can only rquest open lock after approval and before end of validity
                                        if ( strtotime($datetime_now) >= strtotime($last_request_admin_approved_on) && strtotime($datetime_now) <= strtotime($last_request_valid_until) ) {
                                            $response['locks']['approval']['valid'] = true;
                                            $response['locks']['approval']['new_request'] = false;

                                            $response['open_lock'] = true;
                                            $response['status'] = 'true';
                                        }
                                        else{
                                            $response['locks']['approval']['valid'] = false;
                                            $response['locks']['approval']['new_request'] = true;
                                        }
                                    } else{ // Check last request
                                        $response['locks']['approval']['valid'] = false;
                                        $starttimestamp = strtotime($last_request_created_timestamp);
                                        $endtimestamp  = strtotime($datetime_now);
                                        $time_difference = abs($endtimestamp - $starttimestamp)/60;
                                        $new_request_limit = 10;
                                        $response['locks']['approval']['last_request_timelapse_in_minutes'] = $time_difference;
                                        $response['locks']['approval']['new_request_limit_in_minutes'] = $new_request_limit;
                                        if ( $time_difference >= $new_request_limit ){
                                            $response['locks']['approval']['allow_new_request'] = true;
                                        } else {
                                            $response['locks']['approval']['allow_new_request'] = false;
                                            $response['locks']['approval']['allow_new_request_in_minutes'] = $time_difference;
                                        }

                                    }
                                    // Send Notification to Admin
                                }
                                else if ($approval_for_lock['require_subadmin_approval'] == true) {
                                    $response['open_lock'] = false;
                                    // Subadmin Process
                                }
                            }
                            else if ($approval_for_lock['require_admin_approval'] == false){
                                $response['open_lock'] = true;
                                $response['status'] = 'true';
                            }
                        }
                        else { // No admin approval needed
                            $response['locks']['approval']['require_admin_approval'] = null;
                            $response['locks']['approval']['require_subadmin_approval'] = null;
                            $response['status'] = 'true';
                            $response['open_lock'] = true;
                        }

                        //Show Lock Data
                        //$response['locks'][$i] = $locks;
                        $response['locks']['lock_id'] = $locks['lock_ID'];
                        $response['locks']['serial_number'] = $locks['serial_number'];
                        $response['locks']['company_id'] = $locks['company_id'];
                        $response['locks']['lock_name'] = $locks['lock_name'];
                        $response['locks']['lock_group_id'] = $locks['lock_group_id'];
                        $response['locks']['log_number'] = $locks['log_number'];
                        $response['locks']['site_id'] = $locks['site_id'];

                    }
                    else{
                        $response['status'] = 'false';
                        $response['open_lock'] = false;
                    }
				}
				else{
					$response['permit_time_allowed'] = 'no';
				}				
			}
			else{
                $response['permit_date_allowed_status'] = false;
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
            $die = 0;

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


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
