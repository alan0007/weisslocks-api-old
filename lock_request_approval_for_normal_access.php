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
require dirname(__FILE__).'/api/modules/v1/lock/controllers/LockRequestApprovalForNormalAccessController.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockController;
use api\modules\v1\lock\controllers\LockRequestApprovalForNormalAccessController;
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
$LockRequestApprovalForNormalAccessController = new LockRequestApprovalForNormalAccessController($Database);

// Delete Record
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'record' && $_REQUEST['id'] != '')
{
    $delete = $LockRequestApprovalForNormalAccessController->actionDeleteById($_REQUEST['id'] );
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

// Get Data
//If superadmin
if($company_id == 0){
    $data = $LockRequestApprovalForNormalAccessController->actionIndex();
}else{
    $data = $LockRequestApprovalForNormalAccessController->actionGetByCompanyId($company_id);
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
                <h1 class="page-header">Lock Approval Request</h1>
            </div>
            <div class="col-lg-12">
                <div style="width:150px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="lock_request_approval_for_normal_access_manage.php"> Add </a></div>
                <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Lock Approval Request Added or updated Sucessfully!!</div>
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
                                <th>User</th>
                                <th>Permit</th>
                                <th>Lock Name</th>
                                <th>Admin Approved</th>
                                <th>Approved By</th>
                                <th>Approved On</th>
                                <th>Date From</th>
                                <th>Date To</th>
                                <th>Time From</th>
                                <th>Time To</th>
                                <th>Is Daily Timeslot</th>
                                <th>Day</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if($data->count() > 0)
                            {
                                foreach($data as $lock)
                                { ?>
                                    <tr>
                                        <td><?php echo $lock['id']; ?></td>
                                        <td><?php echo $company_name;?></td>
                                        <td><?php
                                            $user_found = $UserController->actionGetOneById($lock['user_id']);
                                            echo $user_found['username'] . ' (ID: '.$lock['user_id'].')';
                                            ?></td>
                                        <td><?php echo $lock['permit_id']; ?></td>
                                        <td><?php
                                            $lock_found = $LockBluetoothController->actionGetOneById($lock['lock_id']);
                                            echo $lock_found['lock_name'] . ' (ID: '.$lock['lock_id'].')';
                                            ?></td>
                                        <td><?php echo ($lock['admin_approved'] === TRUE ? 'Yes' : '-');?></td>
                                        <td><?php echo $lock['admin_approved_by']; ?></td>
                                        <td><?php echo $lock['admin_approved_on']; ?></td>
                                        <td><?php echo $lock['date_from']; ?></td>
                                        <td><?php echo $lock['date_to']; ?></td>
                                        <td><?php echo $lock['time_from']; ?></td>
                                        <td><?php echo $lock['time_to']; ?></td>
                                        <td><?php echo $lock['is_daily_timeslot']; ?></td>
                                        <td><?php echo $lock['day_of_week']; ?></td>
                                        <td>
                                            <a href="lock_request_approval_for_normal_access.php?id=<?php echo $lock['id']; ?>">  Edit </a>
                                            <a onclick="return confirm('Are you sure?')" href="lock_request_approval_for_normal_access.php?delete=record&id=<?php echo $lock['id']; ?>">  Delete </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            else
                            {
                                echo '<tr class="odd gradeX"><td colspan="5">No record is found </td></tr>';
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
