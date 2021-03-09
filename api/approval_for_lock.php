<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

//date_default_timezone_set('Asia/Singapore');
$datetime = new DateTime();
$datetime->format(DateTime::ATOM);

//----------------------------------
// Approval for Locks
//----------------------------------
// Add a new set of lock that needs to be approved
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add'
    && isset($_REQUEST['company_id'])
    && isset($_REQUEST['lock_id'])
    && isset($_REQUEST['require_admin_approval'])
    && isset($_REQUEST['require_subadmin_approval']))
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Lock';

    //Create collection
    $approval_for_lock = $app_data->approval_for_lock;
    $post = array(
        'approval_for_lock_id'  => getNext_users_Sequence('approval_for_lock'),
        'company_id'  => (int) $_REQUEST['company_id'],
        'lock_id'  => (int) $_REQUEST['lock_id'],
        'require_admin_approval'  => (Boolean) $_REQUEST['require_admin_approval'],
        'require_subadmin_approval'  => (Boolean) $_REQUEST['require_subadmin_approval'],
        'require_second_approval' => (Boolean) $_REQUEST['require_second_approval']
    );
    if($approval_for_lock->insert($post)){
        $Reg_Query = array('_id' => $post['_id'] );
        $locksData = $approval_for_lock->findOne( $Reg_Query );

        $response['status'] = 'true';
        unset($response['error']);
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);