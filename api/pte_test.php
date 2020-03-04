<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

/*
	$collection = new MongoCollection($app_data, 'users');
	$cursor = $collection->find();
	if($cursor->count() > 0)
	{
		$response['status'] = 'true';
		
		foreach($cursor as $kk)
		{
			$no_uploaded_image = 'no_uploaded_image.png';
			$user_id = (int) $kk['user_id'];
			$user_registration_image_name = $kk['user_registration_image_name'];
			
			//Save as no image uploaded.jpg if no image uploaded
			if ( $user_registration_image_name == null || $user_registration_image_name == ""){
				$_REQUEST['user_registration_image_name'] = "no_uploaded_image.png";
			}
			$collection2 = new MongoCollection($app_data, 'users');
			$criteria2 = array('user_id'=>(int) $user_id);
			$collection2->update( $criteria2 ,array('$set' => array(
				'user_registration_image_name'  => $no_uploaded_image
			)));
			$cursor2 = $collection2->find($criteria2);
			if($cursor2->count() == 1)
			{
				foreach($cursor2 as $kk2)
				{
					$response = $kk2;
				}
			}

			
		}
		
	}
	else{
		$response['status'] = 'false';
	}
	echo json_encode($response);
*/

//$Connection = new MongoClient( ); 
//$app_data = $Connection->selectDB('testing');


//$collection = $app_data->createCollection("test_logger2");
//$collection = new MongoCollection($app_data, 'permit_to_enter');

//for ($i = 0; $i < 5; $i++) {
//    $collection->insert(array("level" => WARN, "msg" => "sample log message #$i", "ts" => new MongoDate()));
//}

//$msgs = $collection->find();

//foreach ($msgs as $msg) {
//    echo $msg['msg']."\n";
//}

//Test insert data into new collection
//$log = $app_data->createCollection("test_logger2");
/*
for ($i = 0; $i < 5; $i++) {
    $log->insert(array("level" => WARN, "msg" => "sample log message #$i", "ts" => new MongoDate()));
}

$msgs = $log->find();

foreach ($msgs as $msg) {
    echo $msg['msg']."\n";
}
*/

//Test User Data
/*
$collection = new MongoCollection($app_data, 'users');
$Reg_Query = array( '$or' => array( array('username' => 'alanstaff' ), array('email'=>$_REQUEST['alanchin0210@gmail.com']) ) );
if($cursor->count() == 1)
    {
		$response['status'] = 'false';
        $response['error'] = 'User Already Exists...';
    }
	else
    {
		$response['status'] = 'true';
		$response['error'] = 'User Does Not Exists...';
	}
	echo json_encode($response);
	*/

//Test Company collection
/*	
$collection = new MongoCollection($app_data, 'company');
    $C_Query = array( 'company_ref' => "hdb" );
    $cursor = $collection->find( $C_Query );
    if($cursor->count() == 1)
    {
		$response['status'] = 'true';
		$response['response'] = $C_Query;
	}
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Invalid Company Ref. ID';
		exit(json_encode($response));
	}
	echo json_encode($response);
*/	
	
/*include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

$collection = new MongoCollection($app_data, 'permit_to_enter');

 idx = db.getCollectionNames().indexOf("permit_to_enter");
 
 echo idx;
 
if ($collection == null){
	$collection = $db->command(array(
		"create" => $name,
		"capped" => $options["capped"],
		"size" => $options["size"],
		"max" => $options["max"],
		"autoIndexId" => $options["autoIndexId"],
	));
}
*/

	
/*include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

$collection = new MongoCollection($app_data, 'permit_to_enter');

 idx = db.getCollectionNames().indexOf("permit_to_enter");
 
 echo idx;
 
if ($collection == null){
	$collection = $db->command(array(
		"create" => $name,
		"capped" => $options["capped"],
		"size" => $options["size"],
		"max" => $options["max"],
		"autoIndexId" => $options["autoIndexId"],
	));
}
*/

//---------------
//Example Only
//---------------

/*
// Create new collection
// No Restriction
$db->createCollection("something");

// With Restriction
$log = $db->createCollection(
    "logger",
    array(
        'capped' => true,
        'size' => 10*1024,
        'max' => 10
    )
);

//Drop/Delete collection
$collection = $mongo->my_db->articles;
$response = $collection->drop();
print_r($response);
//Mongo Command: 
//delete collection
db.collection_name.drop()
//delete 1 row in collection
db.collection.deleteOne()

//End Example
*/
		
//Create New Databases
/*
$collection_new = $app_data->createCollection("building");
$collection = new MongoCollection($app_data, 'building');

    $cursor = $collection->find();
    if($cursor->count() == 0)
    {
		$response['status'] = 'true';
		$response['response'] = 'Empty Data in DB';
	}
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Invalid DB';
		exit(json_encode($response));
	}
	echo json_encode($response);
*/

//----------------------------------------------	
//Update Building Name [company_building]
//----------------------------------------------
//Manual Input - Complete
/*
//$collection_building = new MongoCollection($app_data, 'company_building');
$response['status'] = 'false';
$data_building = $app_data->company_building;
$post = array(
			'building_id' => getNext_users_Sequence('building_id'),
			'company_ID'     => '25',
			'building_name'     => 'IOS Company HQ',
			'building_address'     => '2 Kallang Sector, Singapore 349277',
			'location_id'     => '',
			'location_name'     => ''				
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}
*/
/*
//Web Input (Incomplete)
$response['status'] = 'false';
$data_building = $app_data->company_building;
$post = array(
			'location_id' => getNext_users_Sequence('location_id'),
			'building_id'     => 1,
			'location_name'     => 'Level 1 Meeting Area',
			'beacon_location_id'     => '',
			'building_entrance'     => ''			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}

*/
//MongoDB Insert
/*
db.company_building.insertOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/
//MongoDB Update
/*
db.company_building.updateOne(
   { "building_name" : "SP Group HQ" },
   { $set: { "company_ID": 12 } }
);
db.company_building.updateOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": 25 } }
);
*/
//Mongo Delete
/*
   db.company_building.deleteOne(
       { "location_name" : "Level 1 Meeting Area" }
   );
*/

//----------------------------------------------
//Update Location [company_location]
//----------------------------------------------
//Manual Input - Complete
/*
$response['status'] = 'false';
$data_building = $app_data->company_location;
$post = array(
			'location_id' => getNext_users_Sequence('location_id'),
			'building_id'     => 1,
			'location_name'     => 'Fire Evacuation Area',
			'beacon_location_id'     => '',
			'building_entrance'     => 'yes'			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}
*/
/*
//Web Input
$response['status'] = 'false';
$data_building = $app_data->company_location;
$post = array(
			'location_id' => getNext_users_Sequence('location_id'),
			'building_id'     => 1,
			'location_name'     => 'Level 1 Meeting Area',
			'beacon_location_id'     => '',
			'building_entrance'     => ''			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}

*/
//MongoDB Insert
/*
db.company_building.insertOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/
//MongoDB Update
/*
db.company_building.updateOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/


//----------------------------------------------
//Update Beacon Location [beacon_location]
//----------------------------------------------
//Manual Input - Complete
/*
$response['status'] = 'false';
$data_building = $app_data->beacon_location;
$post = array(
			'beacon_location_id' => getNext_users_Sequence('beacon_location_id'),
			'location_id'     => 16,
			'beacon_location_name'     => 'AA 8',
			'beacon_id'     => '',
			'beacon_enter_exit'     => 'yes'			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}
*/
/*
//Web Input
$response['status'] = 'false';
$data_building = $app_data->beacon_location;
$post = array(
			'location_id' => getNext_users_Sequence('location_id'),
			'building_id'     => 1,
			'location_name'     => 'Level 1 Meeting Area',
			'beacon_location_id'     => '',
			'building_entrance'     => ''			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}

*/
//MongoDB Insert
/*
db.company_building.insertOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/
//MongoDB Update
/*
db.company_building.updateOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/

//----------------------------------------------
//Update Beacon Location [beacon]
//----------------------------------------------
//Manual Input - Complete
/*
$response['status'] = 'false';
$data_building = $app_data->beacon;
$post = array(
			'beacon_id' => getNext_users_Sequence('beacon_id'),
			'beacon_name'     => '25',
			'beacon_color'     => 'IOS Company HQ',
			'beacon_type'     => '2 Kallang Sector, Singapore 349277',
			'beacon_geo_location'     => '',
			'eddystone_uid'     => '',
			'eddystone_namespace_id'     => '',
			'eddystone_instance_id'     => '',
			'company_id'     => ,
			'beacon_location_id'     => '',
			'beacon_location_name'     => '',
			'battery_lifetime'     => '',
			'battery_lifetime_end'     => '',		
			'iBeacon_UUID' => '',
			'iBeacon_major' => '',
			'iBeacon_minor' => '',
			'location_id' => '',
			'building_id' => ''

			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}
*/
/*
//Web Input
$response['status'] = 'false';
$data_building = $app_data->beacon_location;
$post = array(
			'location_id' => getNext_users_Sequence('location_id'),
			'building_id'     => 1,
			'location_name'     => 'Level 1 Meeting Area',
			'beacon_location_id'     => '',
			'building_entrance'     => ''			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}

*/
//MongoDB Insert
/*
db.company_building.insertOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/
//MongoDB Update
/*
db.company_building.updateOne(
   { "building_name" : "IOS Company HQ" },
   { $set: { "company_ID": "25" } }
);
*/

//Copy To Test Code Below

/*
db.beacon.updateOne(
   { "beacon_id" : 3 },
   { $set: { "beacon_location_id": 3, "beacon_location_name": "Level 1 Left Lobby" } }
);
*/



/*
$response['status'] = 'false';
$data_building = $app_data->beacon;
$post = array(
			'beacon_id' => getNext_users_Sequence('beacon_id'),
			'beacon_name'     => 'PA Assembly',
			'beacon_color'     => 'White Coconut Puff',
			'beacon_type'     => 'Proximity Beacon',
			'beacon_geo_location'     => 'Unknown',
			'eddystone_uid'     => 'edd1ebeac04e5defa017-eb4577b4975c',
			'eddystone_namespace_id'     => 'edd1ebeac04e5defa017',
			'eddystone_instance_id'     => 'eb4577b4975c',
			'iBeacon_UUID' => '',
			'iBeacon_major' => '',
			'iBeacon_minor' => '',
			'company_id'     => 27,
			'building_id' => 3,
			'building_name' => 'PA Office',
			'location_id' => 17,
			'location_name' => 'PA Office',
			'beacon_location_id'     => 22,
			'beacon_location_name'     => 'PA Office',
			'battery_lifetime'     => '19 months',
			'battery_lifetime_end'     => '06/2020'

			
			);
if($data_building->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}
*/


/// 2019-07-03

$collection_new = $app_data->createCollection("gantry_access");
$collection = new MongoCollection($app_data, 'gantry_access');

    $cursor = $collection->find();
    if($cursor->count() == 0)
    {
		$response['status'] = 'true';
		$response['response'] = 'Empty Data in DB';
	}
	else
	{
		$response['status'] = 'false';
		$response['error'] = 'Invalid DB';
		exit(json_encode($response));
	}
	echo json_encode($response);
	
/*
$response['status'] = 'false';
$data_gantry_access = $app_data->gantry_access;
$post = array(
			'gantry_access_id' => getNext_users_Sequence('building_id'),
			'location'     => 'gantry',
			'access_in_out'     => 'in',
			'access_time'     => '02/07/2019 17:46:50',
			'company_ref_id' : 'sp',
			'alarm'     => '0'				
			);
if($data_gantry_access->insert($post))
{
	$response['status'] = 'true';
	$response['data'] = $post;
	echo json_encode($response);
}
*/	

?>