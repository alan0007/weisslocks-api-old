<?php
include(dirname(__FILE__).'/configurations/config.php');
require dirname(__FILE__).'/common/config/Database.php';
require dirname(__FILE__).'/common/config/Constant.php';
require dirname(__FILE__).'/common/config/Utility.php';
require dirname(__FILE__).'/api/modules/v1/user/controllers/UserController.php';
require dirname(__FILE__).'/api/modules/v1/organization/controllers/CompanyController.php';
require dirname(__FILE__).'/api/modules/v1/lock/controllers/LockBluetoothController.php';
require dirname(__FILE__).'/api/modules/v1/lock/controllers/ApprovalRequestForLockController.php';
require dirname(__FILE__).'/api/modules/v1/lock/controllers/ApprovalForLockController.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\lock\controllers\ApprovalRequestForLockController;
use api\modules\v1\lock\controllers\ApprovalForLockController;
use common\config\Constant;
use common\config\Database;
use common\config\Utility;

checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'lock' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'approval_for_lock');
	$collection->remove( array( 'lock_ID' =>(int) $_REQUEST['id'] ) );
	$msg = "Deleted Sucessfully!!";
}

// Start
$Database = new Database();
$Constant = new Constant();
$Utility = new Utility();
$UserController = new UserController($Database);
$CompanyController = new CompanyController($Database);
$LockBluetoothController = new LockBluetoothController($Database);
$ApprovalRequestForLockController = new ApprovalRequestForLockController($Database);
$ApprovalForLockController = new ApprovalForLockController($Database);

$admin_details = $UserController->actionGetOneById($current_user);
$admin_role = (int)$admin_details['role'];

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
}
else if( $is_superadmin = TRUE ){
    $company_id = NULL;
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
?>
<style>
    .navwrap li{
        list-style: none;
        display: inline;
    }
    .pag-selected {
        font-weight: bold;
        text-decoration: underline;
    }
    .navwrap a{
        color:black;
    }
</style>

<?php include("header.php");?>

<body>

    <div id="wrapper">

       <?php include("menu.php");?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Lock Approval Setting</h1>
                </div>
                <div class="col-lg-12">
                    <div style="width:150px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="lock_approval_setting_manage.php"> Add Setting </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Lock Approval Setting Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
											<th>Company</th>
                                            <th>Lock Name</th>
                                            <th>Require Admin Approval</th>
                                            <th>Require Sub-Admin Approval</th>
                                            <th>Require 2nd Approval</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if( $is_superadmin === TRUE )
                                        {
                                            $cursor = $ApprovalForLockController->actionIndex();
                                            if($cursor->count() > 0)
                                            {
                                                foreach($cursor as $approval_for_lock)
                                                { ?>
                                                 <tr>
                                                     <td><?php echo $approval_for_lock['approval_for_lock_id']; ?></td>
                                                     <td><?php
                                                         $company_found = $CompanyController->actionGetOneById($approval_for_lock['company_id']);
                                                         echo $company_found['company_name'];
                                                    ?></td>
                                                     <td><?php
                                                         $lock_found = $LockBluetoothController->actionGetOneById($approval_for_lock['lock_id']);
                                                         echo $lock_found['lock_name'] . ' (ID: '.$approval_for_lock['lock_id'].')';
                                                         ?></td>
                                                     <td><?php echo ($approval_for_lock['require_admin_approval'] === TRUE ? 'Yes' : '-');?></td>
                                                     <td><?php echo ($approval_for_lock['require_subadmin_approval'] === TRUE ? 'Yes' : '-'); ?></td>
                                                     <td><?php echo ($approval_for_lock['require_second_approval'] === TRUE ? 'Yes' : '-'); ?></td>
                                                     <td>
                                                    <a href="lock_approval_setting_manage.php?approval_for_lock_id=<?php echo $approval_for_lock['approval_for_lock_id']; ?>">  Edit </a>
                                                    <a onclick="return confirm('Are you sure?')" href="locks.php?delete=approval_for_lock&id=<?php echo $approval_for_lock['approval_for_lock_id']; ?>">  Delete </a>
                                                    </td>
                                                </tr>
                                              <?php
                                                }
                                            }
                                            else
                                            {
                                              echo '<tr class="odd gradeX"><td colspan="5">No Locks Founds </td></tr>';
                                            }
                                        }
                                        // For Individual company Admin
										else 
										{
										    echo 'Nothing';
										}
									  ?>
                                    </tbody>
                                </table>
                            </div>

                         <div id="access">
                             Account Access : <?php echo ($is_superadmin === TRUE ? 'Superadmin' : 'Admin'); ?>
                         </div>
                         <div id="company">
                             Company : <?php echo $company_id; ?>
                         </div>
                    </div>
                    <!-- /.panel-body -->

                </div>
                <!-- /.col-lg-12 -->

            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php include("footer.php");?>
