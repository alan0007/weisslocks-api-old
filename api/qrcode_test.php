<?php
//Below Code is fully working on live database
/*
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';


$collection = new MongoCollection($app_data, 'qrcode');

$qrcode_reg = $app_data->qrcode;
$post = array(
	'qrcode_id' => getNext_users_Sequence('qrcode'),
	'user_id'     => 331,
	'user_name'     => "spvisitor3",
	'permit_id'     => "16",
	'role'  =>"7",
	'location'  =>"meeting area",
	'access_in_out'   => "in",
	'access_time' => "29/1/2018 18:58:07",
	'valid_from'  => "",
	'valid_to'  => "",
	'company_ref_id'  => "sp",
	'count'  => 0,
	'time'  => date('d/m/Y H:m'),
	'token'  => "12345678",
	'visitor_company_name' => "" //For Visitor Only		
	);
$qrcode_reg->insert($post);			
			
$cursor = $collection->find();
if($cursor->count() > 0) { 
	$response['status'] = 'true';
	foreach($cursor as $qrcode)
		{
			$response['data'] = $qrcode;
		}
}
else{
	$response['status'] = 'false';
}
echo json_encode($response);
*/
//End live database

/*
//Below Code is working on test database
$Connection = new MongoClient( ); 
$app_data = $Connection->selectDB('testing');

$qrcode = $app_data->createCollection("qrcode");


for ($i = 0; $i < 5; $i++) {
    $qrcode->insert(array("level" => WARN, "msg" => "sample log message #$i", "ts" => new MongoDate()));
}

$msgs = $qrcode->find();

foreach ($msgs as $msg) {
    echo $msg['msg']."\n";
}

$qrcode->deleteIndex("level");;

$cursor = $qrcode->find();
foreach($cursor as $ff)
{
	print_r($ff);
}
*/
?>