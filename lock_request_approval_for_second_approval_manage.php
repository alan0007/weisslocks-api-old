<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(dirname(__FILE__).'/configurations/config.php');
require dirname(__FILE__).'/common/config/Database.php';
require dirname(__FILE__).'/common/config/Constant.php';
require dirname(__FILE__).'/common/config/Utility.php';
require dirname(__FILE__).'/api/modules/v1/user/controllers/UserController.php';
require dirname(__FILE__).'/api/modules/v1/organization/controllers/CompanyController.php';
require dirname(__FILE__).'/api/modules/v1/lock/controllers/LockController.php';
require dirname(__FILE__).'/api/modules/v1/lock/controllers/LockRequestApprovalForSecondApprovalController.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSecondApprovalController;
use common\config\Constant;
use common\config\Database;
use common\config\Utility;


checklogin();
$current_user = $_SESSION['user_id'];

// Start
$Database = new Database();
$Constant = new Constant();
$Utility = new Utility();
$UserController = new UserController($Database);
$CompanyController = new CompanyController($Database);
$LockBluetoothController = new LockController($Database);
$LockRequestApprovalForSecondApprovalController = new LockRequestApprovalForSecondApprovalController($Database);

// Delete Record
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'record' && $_REQUEST['id'] != '')
{
    $delete = $LockRequestApprovalForSecondApprovalController->actionDeleteById($_REQUEST['id'] );
    if ($delete === TRUE){
        $msg = "Deleted Sucessfully!!";
        exit();
    }
    else{
        $msg = "Deleted Failed!!";
        exit();
    }
}

// Start Process
$admin_details = $UserController->actionGetOneById($current_user);
$admin_role = (int)$admin_details['role'];

// Declare variable
$company_name = NULL;

// Check is admin
if ( $_SESSION['role'] == 1 ){
    $is_superadmin = TRUE;
}
else{
    $is_superadmin = FALSE;
}

// Verify user's company id
$company_found = $CompanyController->actionGetOneById($admin_details['company_id']);
if(isset($company_found))
{
    $company_id = $company_found['company_ID'];
    $company_name = $company_found['company_name'];
}
else if( $is_superadmin = TRUE ){
    $company_id = 0;
}
else
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Company ID';
    exit(json_encode($response, JSON_PRETTY_PRINT));
}

if( (int)$admin_details['company_id'] == (int)$_REQUEST['company_id'] && in_array($admin_role,array(2,3))){
    $is_admin = TRUE;
}
else{
    $is_admin = FALSE;
}

if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
    $day_of_week = isset($_REQUEST['day_of_week']) ? $_REQUEST['day_of_week'] : array();
    if ( isset($_REQUEST['day_of_week']) ){
        $day_of_week = array_map('intval', $day_of_week);
    }
    $post_array = array();

    if($_REQUEST['id'] == 0)
    {
        $keygroup = $LockRequestApprovalForSecondApprovalController->actionInsert($post_array);
        $post_array = array(
            'company_id'  => (int) $_REQUEST['company_id'],
            'user_id'  => (int) $_REQUEST['user_id'],
            'permit_id'  => (int) $_REQUEST['permit_id'],
            'lock_id'  => (int) $_REQUEST['lock_id'],
            'created_timestamp'  => $_REQUEST['created_timestamp'],
            'created_by_user_id'  => (int) $_REQUEST['created_by_user_id'],
            'notified_admin_user_id'=> Array(),
            'admin_approved'  => false,
            'admin_approved_by'  => (int) 0,
            'admin_approved_on'  => (String) '',
            'admin_rejected'  => false,
            'admin_rejected_by'  => (int) 0,
            'admin_rejected_on'  => (String) '',
            'date_from' => (String)$_REQUEST['date_from'], // 2020-07-02
            'date_to' => (String)$_REQUEST['date_to'],
            'time_from' => (String)$_REQUEST['time_from'], // 08:05:37
            'time_to' => (String)$_REQUEST['time_to'],
            'is_daily_timeslot' => (Boolean)$_REQUEST['is_daily_timeslot'],
            'day_of_week' => $day_of_week, // send as an array
        );
        //$keygroup->insert($post);
        if($LockRequestApprovalForSecondApprovalController->actionInsert($post_array) === TRUE){
            $update_success = TRUE;
            echo "<script>window.location='lock_request_approval_for_second_approval.php?success=true'</script>";
        }
        else{
            echo "<script>window.location='lock_request_approval_for_second_approval.php?success=false'</script>";
        }
    }
    else
    {
        if($LockRequestApprovalForSecondApprovalController->actionUpdateById($_REQUEST['id'],$post_array) === TRUE){
            $update_success = TRUE;
            echo "<script>window.location='lock_request_approval_for_second_approval.php?success=true'</script>";
        }
        else{
            echo "<script>window.location='lock_request_approval_for_second_approval.php?success=false'</script>";
        }
    }
}
//TODO: Complete the page
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Request Approval for Special Access</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="key_group_ID" value="<?php echo isset($_REQUEST['key_group_ID']) ? $_REQUEST['key_group_ID'] : 0;  ?>" />
									<?php
									$keys_group_name = '';
									//$key_grp_user_id = 0;
									$key_grp_user_id = 0;
									$company_id = 0; 
									$key_id = 0;
									
									if(isset($_REQUEST['key_group_ID']))
									{
										$keygroup = $app_data->keygroup;
										$cursor = $keygroup->find(array('key_group_ID' =>(int) $_REQUEST['key_group_ID']));
										 if($cursor->count() > 0)
										 {
											 foreach($cursor as $keygroup)
											 {
												$key_group_name = $keygroup['key_group_name'];
												$key_grp_user_id = $keygroup['key_grp_user_id'];
												$company_id = $keygroup['company_id'];
												//$key_id = json_decode($keygroup['key_id']);
												$key_id = $keygroup['key_id'];
											 }
										 }
									}
									?>
										<div class="form-group">
                                            <label> Key Group Name </label>
                                            <input class="form-control" name="key_group_name" value="<?php echo $key_group_name; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Owner ID (as Lock Group Admin)</label>
											<select name="key_grp_user_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0)
												{
													if($_SESSION['role'] == 1) { ?>
														<?php foreach($users as $user) { ?>
															<option <?php echo $key_grp_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } ?>
													<?php } else { ?>
														<?php foreach($users as $user) { 
														if($user['user_id'] == $current_user) {?>
															<option <?php echo $key_grp_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php } } ?>
														<?php } } ?>
											</select>
                                        </div>
										<div class="form-group">
                                            <label>Select Company</label>
											
											
											
											
											<?php if($_SESSION['role'] == 1) { ?>
											
											<select name="company_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'company');
												$companies = $collection->find();
												if($companies->count() > 0) 
												{?>
														<?php foreach($companies as $comp) { ?>
															<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
														<?php }
												}
											?>
											</select>
											<?php } else {
												
												
												
												$com = array();
											$collection1 = new MongoCollection($app_data, 'company');
												$companies = $collection1->find();
												if($companies->count() > 0) 
												{
													foreach($companies as $company)
													{
														$users = json_decode($company['user_id']);
														if(in_array($current_user,$users))
														{
															$com[] = $company['company_ID'];
														}
													}
												}
												?>
											<select name="company_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'company');
												$companies = $collection->find();
												if($companies->count() > 0) 
												{?>
														<?php foreach($companies as $comp) { 
														if(in_array($comp['company_ID'],$com))
															{?>
															<option <?php echo $company_id == $comp['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $comp['company_ID']; ?>"> <?php echo $comp['company_name']; ?> </option>
														<?php } }
												}
											?>
											</select>
											<?php } ?>
                                        </div>
										<?php if($_SESSION['role'] == 1) { ?>
										<div class="form-group">
                                            <label>Select Key</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'keys');
												$keys = $collection->find();
												if($keys->count() > 0) 
												{
													foreach($keys as $key) {
														//$key_group_ids = json_decode($key['key_group_id']);
														$key_group_ids = $key['key_group_id'];
													?>
															<input <?php if(is_array($key_id) && in_array($key['key_group_id'],$key_id) || in_array($_REQUEST['key_group_ID'],$key_group_ids)) { echo 'checked'; } ?> type="checkbox" name="keys[]" value="<?php echo $key['key_ID']; ?>" />
															<?php echo $key['key_name'].'<br/>'; ?>
														<?php } } ?>
                                        </div>
										<?php } else { ?>
										<div class="form-group">
                                            <label>Select Key</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'keys');
												$keys = $collection->find();
												if($keys->count() > 0) 
												{
													foreach($keys as $key) {
													if(in_array($key['company_id'],$com))
															{
																//$key_group_ids = json_decode($key['key_group_id']);
																$key_group_ids = $key['key_group_id'];
													?>
															<input <?php if(is_array($key_id) && in_array($key['key_ID'],$key_id) || in_array($_REQUEST['key_group_ID'],$key_group_ids)) { echo 'checked'; } ?> type="checkbox" name="keys[]" value="<?php echo $key['key_ID']; ?>" />
															<?php echo $key['key_name'].'<br/>'; ?>
															<?php } } } ?>
                                        </div>
										<?php } ?>
										
										<!-- Show Key Group User ID -->
										<div class="form-group">
                                            <label>Key Group User ID</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$search_query = array( 'company_id' => $company_id );
												$keygroup_user = $collection->find( $search_query ) ;
												if($keygroup_user->count() > 0) 
												{
													foreach($keygroup_user as $keygroup_user) {
														//$key_group_ids = json_decode($keygroup_user['key_group_id']);													
														$company_user_id = $keygroup_user['user_id'];
														$username = $keygroup_user['username'];
													?>
															
															<input <?php if(is_array($company_user_id) && in_array($company_user_id,$key_grp_user_id) || in_array($company_user_id,$key_grp_user_id)) { echo 'checked'; } ?> type="checkbox" name="key_grp_user_id[]" value="<?php echo $company_user_id; ?>" />
															<?php echo $username.'<br/>'; ?>
													<?php 
													} 
												} ?>
                                        </div>
										
										
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
