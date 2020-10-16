<?php
include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/uploads/');

//Update password after registration
if(isset($_REQUEST['user']) && isset($_REQUEST['password']) && isset($_REQUEST['company_id']))
{
    $collection = new MongoCollection($app_data, 'users');
    $criteria = array('username'=>$_REQUEST['user']);
    $response['status'] = 'false';
    $response['error'] = 'Invalid User';
    $cursor = $collection->find($criteria);
    if($cursor->count() == 1)
    {
        foreach($cursor as $users)
        {
            $approved = $users['approved'];
            $users_id = $users['user_id'];
        }
        if($approved == 1)
        {
            unset($response['error']);
            $criteria = array('user_id'=>(int)$users_id);
            $collection->update( $criteria ,array('$set' => array('password' => md5($_REQUEST['password']) ) ) );
            $response['status'] = 'true';
            $cursor_success = $collection->findOne($criteria);

            $cursor_success['company_name'] = '';
            $cursor_success['contracted_company_name'] = '';

            if(isset($cursor_success['user_id']))
            {

                if( !empty( $cursor_success['company_id'] ) &&  $cursor_success['company_id'] != 0 )
                {
                    $collection_com = new MongoCollection($app_data, 'company');
                    $coms = $collection_com->findOne(array('company_ID'=>(int)$cursor_success['company_id']));
                    if(isset($coms['company_ID']))
                    {
                        $cursor_success['company_name'] = $coms['company_name'];
                        $cursor_success['company_ref_id'] = $coms['company_ref'];

                        for($k = 0 ; $k <= count($coms['contracted_name']) ; $k++)
                        {
                            if (false !== $key = array_search( json_decode($cursor_success['user_company'])[0] , $coms['contracted_ref_id'] ))
                            {
                                $cursor_success['contracted_company_name'] = $coms['contracted_name'][$key];
                            }
                        }
                    }
                }

                unset($cursor_success['_id']);
                //Not showing entire user data
                //$response['data'] = $cursor_success;
                //Only show required details
                $response['data']['user_id'] = $cursor_success['user_id'];
                $response['data']['username'] = $cursor_success['username'];
                $response['data']['email'] = $cursor_success['email'];
                $response['data']['role'] = $cursor_success['role'];
                $response['data']['registered_time'] = $cursor_success['registered_time'];
                $response['data']['device_id'] = $cursor_success['device_id'];
                $response['data']['UDID_IOS'] = $cursor_success['UDID_IOS'];
            }
        } else {$response['error'] = 'You are not Approved Yet...';}
    }
}