<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

//-----------------------------------
//For Initial create collection only
/*
$qrcode = $app_data->createCollection("beacon");
$msgs = $qrcode->find();

if($msgs->count() > 0) { 
	foreach ($msgs as $msg) {
		//echo $msg['msg']."\n";
		$response['data'] = $msg;
		echo json_encode($response);
	}
}
else{
	$response['status'] = 'false';
	echo json_encode($response);
}
*/

if($_REQUEST['action'] == 'view_by_superadmin_and_no_one_else')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';

		foreach($cursor as $beacon)
			{
				unset($beacon['_id']);
				unset($beacon['beacon_geo_location']);
				unset($beacon['beacon_type']);
				$response['data'][] = $beacon;
			}
	}
	else{
		$response['status'] = 'false';
	}
	//var_dump(json_decode($response));
	echo json_encode($response);
}

//View Beacon by SP Only
else if($_REQUEST['action'] == 'view')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$criteria = array('company_id'=>12);
	$cursor = $collection->find( $criteria );
	//$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $beacon)
			{
				unset($beacon['_id']);
				unset($beacon['beacon_geo_location']);
				unset($beacon['beacon_type']);
				$response['data'][] = $beacon;
				

			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'No Beacon Found';
	}
	
	
	echo json_encode($response);
}
//View All Beacon
else if($_REQUEST['action'] == 'view_all_beacons')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $beacon)
			{
				unset($beacon['_id']);
				unset($beacon['beacon_geo_location']);
				unset($beacon['beacon_type']);
				$response['data'][] = $beacon;
				

			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'No Beacon Found';
	}
	
	
	echo json_encode($response);
}
//View Copany Specific Beacon
else if($_REQUEST['action'] == 'view_beacon' && isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$criteria = array('company_id'=>(int)$_REQUEST['company_id']);
	$cursor = $collection->find( $criteria );
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $beacon)
			{
				unset($beacon['_id']);
				unset($beacon['beacon_geo_location']);
				unset($beacon['beacon_type']);
				$response['data'][] = $beacon;
				

			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'No Beacon Found';
	}
	
	
	echo json_encode($response);
}

else if($_REQUEST['action'] == 'view_name_only')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $beacon)
			{
				unset($response['status']);
				unset($beacon['_id']);
				unset($beacon['beacon_geo_location']);
				unset($beacon['beacon_type']);
				$response['data'][] = $beacon['beacon_name'];
				//$response['data']['beacon_location_name'] = $beacon['beacon_location_name'];
				

			}
	}
	else{
		$response['status'] = 'false';
	}
	
	echo json_encode($response);
	
}

//Update Beacon Only
else if($_REQUEST['action'] == 'update' && isset($_REQUEST['beacon_name']) && $_REQUEST['beacon_name'] != '' && isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' ) 
{
	$collection = new MongoCollection($app_data, 'beacon');	
	//$criteria = array('eddystone_uid'=>$_REQUEST['eddystone_uid']);
	//$Reg_Query = array( '$and' => array( array('user_id' => $_REQUEST['user_id'] ), array('company_id'=>$_REQUEST['company_id']) ) );
	//$cursor_pte = $collection->find( $Reg_Query );
	
	$post = array(
		'beacon_id' => getNext_users_Sequence('beacon_id'),
		'beacon_name'  =>$_REQUEST['beacon_name'],
		'beacon_color'  =>$_REQUEST['beacon_color'],
		'beacon_type'     => $_REQUEST['beacon_type'],
		'beacon_geo_location'     => $_REQUEST['beacon_geo_location'],
		'eddystone_uid'  => $_REQUEST['eddystone_uid'],
		'eddystone_namespace_id'   => $_REQUEST['eddystone_namespace_id'],
		'eddystone_instance_id'   => $_REQUEST['eddystone_instance_id'],
		'company_id'   => (int)$_REQUEST['company_id'],
		'beacon_location_id'   => $_REQUEST['beacon_location_id'],
		'beacon_location_name'   => $_REQUEST['beacon_location_name'],
		'battery_lifetime'   => $_REQUEST['battery_lifetime'],
		'battery_lifetime_end' => $_REQUEST['battery_lifetime_end'],
		'iBeacon_UUID' => $_REQUEST['iBeacon_UUID'],
		'iBeacon_major' => $_REQUEST['iBeacon_major'],
		'iBeacon_minor' => $_REQUEST['iBeacon_minor'],
		'location_id' => (int)$_REQUEST['location_id'],
		'building_id' => (int)$_REQUEST['building_id']
		);
/*
	$Reg_Query = array( '$or' => array( array('username' => $_REQUEST['host_name'] ), array('email'=>$_REQUEST['host_email_phone']), array('phone_number'=>$_REQUEST['host_email_phone'])  ) );
    $cursor = $collection->find( $Reg_Query );
	if($cursor->count() == 1)
    {
		$response['status'] = 'true';
		unset($permit_to_enter['_id']);
		
		
		
    }
	else{
		$response['status'] = 'false';
        $response['error'] = 'User Does Not Exists...';
	}
	
	$post = array(
		'beacon_id' => getNext_users_Sequence('beacon'),
		'beacon_address'  =>$_REQUEST['beacon_address'],
		'beacon_location'  =>$_REQUEST['beacon_location'],
		'user_id'     => (int) $_REQUEST['user_id'],
		'user_name'     => (int) $_REQUEST['user_name'],
		'company_ref_id'  => $_REQUEST['company_ref_id'],
		'access_in_out'   => $_REQUEST['acess_in_out'],
		'access_time'  => date('d/m/Y H:i')
		);
*/

	if ($collection->insert($post) ){
	
		$cursor_2 = $collection->find();
		if($cursor_2->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor_2 as $beacon)
				{
					$response['data'] = $beacon;
					echo json_encode($response);
				}
		}
		else{
			$response['status'] = 'false';
		}
	
	}
				
}


		
?>