<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

//define('UPLOAD_PATH', '/permit_to_enter/');
/*-----------------------------------
//For Initial create collection only

$qrcode = $app_data->createCollection("qrcode");
$msgs = $qrcode->find();

foreach ($msgs as $msg) {
    echo $msg['msg']."\n";
}
*/

// View data
if($_REQUEST['action'] == 'view')
{
	$collection = new MongoCollection($app_data, 'qrcode');
	//$qrcode_reg = $app_data->qrcode;
	$cursor = $collection->find();
	if($cursor->count() > 0) { 
		$response['status'] = 'true';
		foreach($cursor as $qrcode)
			{
				$response['data'] = $qrcode;
				echo json_encode($response);

			}
	}
	else{
		$response['status'] = 'false';
	}

}

//Get Entry
else if($_REQUEST['action'] == 'access' && isset($_REQUEST['permit_id']) && $_REQUEST['permit_id'] != '' && 
isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' &&
isset($_REQUEST['company_ref_id']) && $_REQUEST['company_ref_id'] != '' ) 
{	
	$response['status'] = 'false';
	//Here
	$collection_permit = new MongoCollection($app_data, 'permit_to_enter');
	$permit_query = array( '$and' => array( array('permit_id' => (int)$_REQUEST['permit_id'] ), array('user_id'=>$_REQUEST['user_id']), array('company_ref_id'=>$_REQUEST['company_ref_id']) ) );
	$cursor_permit = $collection_permit->find( $permit_query );
	//If Permit is verified
	if($cursor_permit->count() > 0)
	{
		$collection = new MongoCollection($app_data, 'qrcode');
		//$qrcode_reg = $app_data->qrcode;
		$post = array(
			'qrcode_id' => getNext_users_Sequence('qrcode'),
			'permit_id'     => (int)$_REQUEST['permit_id'],
			'user_id'     => (int) $_REQUEST['user_id'],
			'user_name'     => $_REQUEST['user_name'],
			'role'  =>$_REQUEST['role'],
			'location'  =>$_REQUEST['location'],
			'access_in_out'   => $_REQUEST['access_in_out'],
			'access_time' => $_REQUEST['access_time'],
			'qrcode_time' => $_REQUEST['qrcode_time'],
			'valid_from'  => $_REQUEST['valid_from'],
			'valid_to'  => $_REQUEST['valid_to'],
			'company_ref_id'  => $_REQUEST['company_ref_id'],
			'count'  => 0,
			'time'  => date('d/m/Y H:i:s'),
			'token'  => $_REQUEST['token'],
			'visitor_company_name' => $user_company_name //For Visitor Only		
			);
		
		if ($collection->insert($post) ){
	
			$cursor = $collection->find();
			if($cursor->count() > 0) { 
				$response['status'] = 'true';
				foreach($cursor as $qrcode)
					{
						unset($qrcode['_id']);
						//This is encoded in json for qrcode reader to process
						$response['data'] = json_encode($qrcode);					
					}
			}
			else{
				$response['status'] = 'false';
			}
		}
	}
	else{
		$response['status'] = 'false';
	}
	//Encoded in json format again
	echo json_encode($response);
}


//Get Entry with limited try and time restricted
else if($_REQUEST['action'] == 'limited_access' && isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' ) 
{	
	$response['status'] = 'false';
	//Here
	$collection = new MongoCollection($app_data, 'qrcode');
	$Reg_Query = array( '$and' => array( array('permit_id' => $_REQUEST['permit_id'] ), array('user_id'=>(int)$_REQUEST['user_id']), array('token'=>$_REQUEST['token']) ) );
    $cursor = $collection->find( $Reg_Query );
	//If Already Have Entry
	if($cursor->count() > 0)
	{
		//$cursor->update( array('$inc' => array('count'=>1)) );
		//$collection->update( $cursor ,array('$inc' => array('count'=> -1))) );
		//$collection->update( $Reg_Query ,array(
		//	'$inc' => array(
		//			 'count'  => 1
		//	 )));
		foreach($cursor as $qrcode)
		{
			$count = $qrcode['count'];
		}
		
		$count = $count+1;
		
		//If not exceed 3 tries
		if ( $count <= 2 ){
		
			//Post Access with New Time
			//$qrcode_reg = $app_data->qrcode;
			$post = array(
				'qrcode_id' => getNext_users_Sequence('qrcode'),
				'permit_id'     => (int)$_REQUEST['permit_id'],
				'user_id'     => (int) $_REQUEST['user_id'],
				'user_name'     => $_REQUEST['user_name'],
				'role'  =>$_REQUEST['role'],
				'location'  =>$_REQUEST['location'],
				'access_in_out'   => $_REQUEST['access_in_out'],
				'access_time' => $_REQUEST['access_time'],
				'qrcode_time' => $_REQUEST['qrcode_time'],
				'valid_from'  => $_REQUEST['valid_from'],
				'valid_to'  => $_REQUEST['valid_to'],
				'company_ref_id'  => $_REQUEST['company_ref_id'],
				'count'  => $count,
				'time'  => date('d/m/Y H:i:s'),
				'token'  => $_REQUEST['token'],
				'visitor_company_name' => $user_company_name //For Visitor Only		
				);
				
			$collection->insert($post);	
			
			$cursor_2 = $collection->find(); // Temporarily find all qr code
			if($cursor_2->count() > 0) { 
				$response['status'] = 'true';
				foreach($cursor_2 as $qrcode2)
					{
						$response['data'] = json_encode($qrcode2);
					}
			}
			else{
				$response['status'] = 'false';
			}
		}
		else{
			$response['status'] = 'false';
			$response['error'] = 'limit_exceeded';
		}
		
	}
	//If No Entry
	else{
		$collection = new MongoCollection($app_data, 'qrcode');
		//$qrcode_reg = $app_data->qrcode;
		$post = array(
			'qrcode_id' => getNext_users_Sequence('qrcode'),
			'permit_id'     => (int)$_REQUEST['permit_id'],
			'user_id'     => (int) $_REQUEST['user_id'],
			'user_name'     => $_REQUEST['user_name'],
			'role'  =>$_REQUEST['role'],
			'location'  =>$_REQUEST['location'],
			'access_in_out'   => $_REQUEST['access_in_out'],
			'access_time' => $_REQUEST['access_time'],
			'qrcode_time' => $_REQUEST['qrcode_time'],
			'valid_from'  => $_REQUEST['valid_from'],
			'valid_to'  => $_REQUEST['valid_to'],
			'company_ref_id'  => $_REQUEST['company_ref_id'],
			'count'  => 0,
			'time'  => date('d/m/Y H:i:s'),
			'token'  => $_REQUEST['token'],
			'visitor_company_name' => $user_company_name //For Visitor Only		
			);
		$collection->insert($post);			
					
		$cursor = $collection->find();
		if($cursor->count() > 0) { 
			$response['status'] = 'true';
			foreach($cursor as $qrcode)
				{
					//This is encoded in json for qrcode reader to process
					$response['data'] = json_encode($qrcode);
				}
		}
		else{
			$response['status'] = 'false';
		}
	}
	//Encoded in json format again
	echo json_encode($response);
}




?>