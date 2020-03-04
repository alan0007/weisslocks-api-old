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
.
ib   f($msgs->count() > 0) { 
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
// Check User Location
if($_REQUEST['action'] == 'view_user_location_by_superadmin_and_no_one_else')
{
	$collection = new MongoCollection($app_data, 'user_location');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $user_location)
			{
				unset($user_location['_id']);
				$response['data'] = $user_location;
				echo json_encode($response) . "<br/>";
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'User Location Not Found';
		echo json_encode($response) . "<br/>" ;
	}

}

// Check User Location Name Only
if($_REQUEST['action'] == 'list_all_user_location')
{
	$collection = new MongoCollection($app_data, 'user_location');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		//$response['status'] = 'true';
		foreach($cursor as $user_location)
			{
				//unset($user_location['_id']);
				$user_id = $user_location['user_id'];
				
					$collection_user = new MongoCollection($app_data, 'users');		
					$criteria_user = array('user_id'=>(int)$user_id);
					$cursor_user = $collection_user->findOne( $criteria_user );
					$response['Username'] = $cursor_user['username'];
				
				$response['Beacon Name'] = $user_location['beacon_name'];
				$response['Time'] = $user_location['location_time'];
				echo json_encode($response) . "<br/>";
			}
	}
	else{
		//$response['status'] = 'false';
		$response['error'] = 'User Location Not Found';
		echo json_encode($response) . "<br/>" ;
	}

}

// Check Beacon List
else if($_REQUEST['action'] == 'view_beacon_by_superadmin_and_no_one_else')
{
	$collection = new MongoCollection($app_data, 'beacon');
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $beacon)
			{
				unset($beacon['_id']);
				$response['data'] = $beacon;
				
			}
	}
	else{
		$response['status'] = 'false';
		$response['error'] = 'Beacon Not Found';
	}
	
	echo json_encode($response) . "<br/>" ;
}

//Update User Location with Eddystone Check
else if(
	$_REQUEST['action'] == 'update_location_eddystone' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' && 
	isset($_REQUEST['eddystone_uid']) && $_REQUEST['eddystone_uid'] != '' && 
	isset($_REQUEST['eddystone_namespace_id']) && $_REQUEST['eddystone_namespace_id'] != '' && 
	isset($_REQUEST['eddystone_instance_id']) && $_REQUEST['eddystone_instance_id'] != '' 
	) 
{
	$response['status'] = 'false';
	// Only User Location Tracking & Fire Alarm System time format has second
	date_default_timezone_set('Asia/Singapore');
	$time_now = date('d/m/Y H:i:s');

	
	// Search for beacon name		
	$collection_beacon = new MongoCollection($app_data, 'beacon');		
	//$criteria = array('eddystone_uid'=>$_REQUEST['eddystone_uid']);
	$beacon_query = array( '$and' => array( array('eddystone_uid' => $_REQUEST['eddystone_uid'] ), array('eddystone_namespace_id'=>$_REQUEST['eddystone_namespace_id']), array('eddystone_instance_id'=>$_REQUEST['eddystone_instance_id']) ) );
	$cursor_beacon = $collection_beacon->find( $beacon_query );
	if($cursor_beacon->count() > 0) { 
		foreach($cursor_beacon as $beacon)
			{
				//$response['status'] = 'true';
				//$response['data'] = $beacon;
				$beacon_id = $beacon['beacon_id'];
				$beacon_name = $beacon['beacon_name'];
				$company_id = $beacon['company_id'];
				$beacon_location_id = (int) $beacon['beacon_location_id'];
				$location_id = $beacon['location_id'];
				$building_id = $beacon['building_id'];
				//echo json_encode($response);			
	
			}
	}
	else{
		$response['error'] = 'Beacon Not Found';
		exit(json_encode($response));
		//echo json_encode($response);
	}
	
	// Check time (Optional)
	
	
	// Start User Location Update
	$collection_user_location = new MongoCollection($app_data, 'user_location');
	// Filter Location Update - TODO filter
	//$beacon_query = array( '$and' => array( array('eddystone_uid' => $_REQUEST['eddystone_uid'] ), array('eddystone_namespace_id'=>$_REQUEST['eddystone_namespace_id']), array('eddystone_instance_id'=>$_REQUEST['eddystone_instance_id']) ) );
	//$cursor_beacon = $collection_beacon->find( $beacon_query );

	$post = array(
		'user_location_id' 			=> getNext_users_Sequence('user_location_id'),
		'user_id'  					=> (int)$_REQUEST['user_id'],
		'company_id'  				=> (int) $_REQUEST['company_id'],
		'beacon_id'     			=> (int) $beacon_id,
		'beacon_name'     			=> $beacon_name,
		'eddystone_uid'  			=> $_REQUEST['eddystone_uid'],
		'eddystone_namespace_id'   	=> $_REQUEST['eddystone_namespace_id'],
		'eddystone_instance_id'   	=> $_REQUEST['eddystone_instance_id'],
		'iBeacon_UUID'				=> '',
		'iBeacon_major'				=> '',
		'iBeacon_minor'				=> '',
		'location_time'  			=> date('d/m/Y H:i:s')
	);
	if($collection_user_location->insert($post))
	{
		$response['status'] = 'true';
		unset ($response['error']);
		unset ($post['_id']);
		$response['data'] = $post;
		$response['data']['location_id'] = $location_id;
		$response['data']['building_id'] = $building_id;
	}
	else{
		$response['error'] = 'Database Error - Cannot Insert Data';
	}
	echo json_encode($response);
	
}
//End User Location with Eddystone Update

//Update User Location with iBeacon Check
else if(
	$_REQUEST['action'] == 'update_location_ibeacon' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' && 
	isset($_REQUEST['company_id']) && $_REQUEST['company_id'] != '' && 
	isset($_REQUEST['iBeacon_UUID']) && $_REQUEST['iBeacon_UUID'] != '' && 
	isset($_REQUEST['iBeacon_major']) && $_REQUEST['iBeacon_major'] != '' && 
	isset($_REQUEST['iBeacon_minor']) && $_REQUEST['iBeacon_minor'] != '' 
	) 
{
	$response['status'] = 'false';
	// Only User Location Tracking & Fire Alarm System time format has second
	$time_now = date('d/m/Y H:i:s');

	
	// Search for beacon name		
	$collection_beacon = new MongoCollection($app_data, 'beacon');		
	//$criteria = array('iBeacon_UUID'=>$_REQUEST['iBeacon_UUID']);
	$beacon_query = array( '$and' => array( array('iBeacon_UUID' => $_REQUEST['iBeacon_UUID'] ), array('iBeacon_major'=>$_REQUEST['iBeacon_major']), array('iBeacon_minor'=>$_REQUEST['iBeacon_minor']) ) );
	$cursor_beacon = $collection_beacon->find( $beacon_query );
	if($cursor_beacon->count() > 0) { 
		foreach($cursor_beacon as $beacon)
			{
				//$response['status'] = 'true';
				//$response['data'] = $beacon;
				$beacon_id = $beacon['beacon_id'];
				$beacon_name = $beacon['beacon_name'];
				$company_id = $beacon['company_id'];
				//echo json_encode($response);
			}
	}
	else{
		$response['error'] = 'Beacon Not Found';
		exit(json_encode($response));
		//echo json_encode($response);
	}
	
	// Check time (Optional)
	
	
	// Start User Location Update
	$collection_user_location = new MongoCollection($app_data, 'user_location');

	$post = array(
		'user_location_id' 			=> getNext_users_Sequence('user_location_id'),
		'user_id'  					=> (int)$_REQUEST['user_id'],
		'company_id'  				=> (int) $_REQUEST['company_id'],
		'beacon_id'     			=> (int) $beacon_id,
		'beacon_name'     			=> $beacon_name,
		'eddystone_uid'  			=> '',
		'eddystone_namespace_id'   	=> '',
		'eddystone_instance_id'   	=> '',
		'iBeacon_UUID'				=> $_REQUEST['iBeacon_UUID'],
		'iBeacon_major'				=> $_REQUEST['iBeacon_major'],
		'iBeacon_minor'				=> $_REQUEST['iBeacon_minor'],
		'location_time'  			=> date('d/m/Y H:i:s')
	);
	if($collection_user_location->insert($post))
	{
		$response['status'] = 'true';
		unset ($response['error']);
		unset ($post['_id']);
		$response['data'] = $post;
	}
	else{
		$response['error'] = 'Database Error - Cannot Insert Data';
	}
	echo json_encode($response);
	
}
//End User Location with Beacon Update


		
?>