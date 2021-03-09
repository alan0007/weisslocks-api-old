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
require dirname(__FILE__).'/api/modules/v1/lock/controllers/ApprovalRequestForLockController.php';
require dirname(__FILE__).'/api/modules/v1/lock/controllers/ApprovalForLockController.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockController;
use api\modules\v1\lock\controllers\ApprovalRequestForLockController;
use api\modules\v1\lock\controllers\ApprovalForLockController;
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
$LockController = new LockController($Database);
$ApprovalRequestForLockController = new ApprovalRequestForLockController($Database);
$ApprovalForLockController = new ApprovalForLockController($Database);

$admin_details = $UserController->actionGetOneById($current_user);
$admin_role = (int)$admin_details['role'];

// Delete
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'approval_for_lock' && $_REQUEST['id'] != '')
{
    $ApprovalForLockController->actionDeleteById($_REQUEST['id']);
    $msg = "Deleted Sucessfully!!";
}

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

// Get Company List
$company_list = $CompanyController->actionIndex();

// Save Process
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
    // New data
	if($_REQUEST['approval_for_lock_id'] == 0)
	{
        $post_array = array(
//		        'approval_for_lock_id'  => getNext_users_Sequence('approval_for_lock_id'),
				'company_id' => (int) $_REQUEST['company_id'],
				'lock_id' => (int) $_REQUEST['lock_id'],
				'require_admin_approval' => (boolean) $_REQUEST['require_admin_approval'],
				'require_subadmin_approval' => (boolean)$_REQUEST['require_subadmin_approval'],
				'require_second_approval'  => (boolean) $_REQUEST['require_second_approval']
			);
        $result = $ApprovalForLockController->actionInsert($post_array);
	}
	else
	{
        $approval_for_lock_id = $_REQUEST['approval_for_lock_id'];
        $post_array = array(
//		        'approval_for_lock_id'  => getNext_users_Sequence('approval_for_lock_id'),
            'company_id' => (int) $_REQUEST['company_id'],
            'lock_id' => (int) $_REQUEST['lock_id'],
            'require_admin_approval' => (boolean) $_REQUEST['require_admin_approval'],
            'require_subadmin_approval' => (boolean)$_REQUEST['require_subadmin_approval'],
            'require_second_approval'  => (boolean) $_REQUEST['require_second_approval']
        );
        $ApprovalForLockController->actionUpdateById($approval_for_lock_id,$post_array);
	}
	echo "<script>window.location='lock_approval_setting.php?sucess=true'</script>";
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Lock Approval Setting</h1>
                </div>
                <div class="col-lg-6">
                    <form role="form" action="" method="post">
                        <input type="hidden" name="approval_for_lock_id" value="<?php echo isset($_REQUEST['approval_for_lock_id']) ? $_REQUEST['approval_for_lock_id'] : 0;  ?>" />
                        <?php
                        $company_id = 0;
                        $lock_id = 0;
                        $require_admin_approval = FALSE;
                        $require_subadmin_approval = FALSE;
                        $require_second_approval = FALSE;

                        if(isset($_REQUEST['approval_for_lock_id']) && $_REQUEST['approval_for_lock_id'] != 0)
                        {
                            $cursor = $ApprovalForLockController->actionGetOneById($_REQUEST['approval_for_lock_id']);
                            if(isset($cursor))
                            {
                                $company_id = $cursor['company_id'];
                                $lock_id = (int) $cursor['lock_id'];
                                $require_admin_approval = $cursor['require_admin_approval'];
                                $require_subadmin_approval = $cursor['require_subadmin_approval'];
                                if (isset($cursor['require_second_approval'])){
                                    $require_second_approval = $cursor['require_second_approval'];
                                }
                            }

                        }
                        ?>
                        <!-- Company -->
                        <?php if($_SESSION['role'] == 1)
                        { ?>
                            <div class="form-group">
                                <label>Select Company</label>
                                <select name="company_id" class="form-control">
                                    <?php
                                    if($company_list->count() > 0)
                                    {?>
                                        <?php foreach($company_list as $company) { ?>
                                        <option <?php echo $company_id == $company['company_ID'] ? 'selected="selected"' : ''; ?> value="<?php echo $company['company_ID']; ?>"> <?php echo $company['company_name']; ?> </option>
                                    <?php }
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php }
                        else { ?>
                            <div class="form-group">
                                <label>Company</label>
                                <select name="company_id" class="form-control">
                                    <option <?php echo 'selected="selected"'; ?> value="<?php echo $company_id; ?>"> <?php echo $company_found['company_name'];; ?> </option>
                                </select>
                            </div>
                        <?php } ?>

                        <!-- Lock -->
                        <?php if($_SESSION['role'] == 1) { ?>
                        <div class="form-group">
                            <label>Select Lock</label><br/>
                            <select name="lock_id" class="form-control">
                            <?php
                            // Get Lock List
                            $lock_list = $LockController->actionIndex();
                            if($lock_list->count() > 0)
                            {
                                foreach($lock_list as $lock)
                                {
                                    echo $lock_id;
                                    $current_lock_id = $lock['lock_ID'];
                                    $current_company_id = $lock['company_id'];
                                    $current_company_details = $CompanyController->actionGetOneById($current_company_id);
                                    $current_company_name = $current_company_details['company_name'];
                                    ?>
                                    <option <?php echo $lock['lock_ID'] == $lock_id ? 'selected="selected"' : ''; ?> value="<?php echo $lock['lock_ID']; ?>"> <?php echo $lock['lock_name']." (ID:".$lock['lock_ID'].", Company: ".$current_company_name.")"; ?></option>
                                <?php
                                }
                            } ?>
                            </select>
                        </div>
                        <?php } else { ?>
                        <div class="form-group">
                            <label>Select Lock</label><br/>
                            <select name="lock_id" class="form-control">
                            <?php
                            // Get Lock List
                            $lock_list_by_company_id = $LockController->actionGetByCompanyId($company_id);
                            if($lock_list_by_company_id->count() > 0)
                            {
                                foreach($lock_list_by_company_id as $lock)
                                {
                                    ?>
                                    <option <?php echo $lock['lock_ID'] == $lock_id ? 'selected="selected"' : ''; ?> value="<?php echo $lock['lock_ID']; ?>"> <?php echo $lock['lock_name']; ?></option>
                                    <?php
                                }
                            } ?>
                            </select>
                        </div>
                        <?php } ?>

                        <!-- Show Key Group User ID -->
                        <div class="form-group">
                            <label> Require 1st Layer Admin Approval </label>
                            <select name="require_admin_approval" class="form-control">
                                <option <?php echo $require_admin_approval == 1 ? 'selected="selected"' : ''; ?> value="1"> Yes </option>
                                <option <?php echo $require_admin_approval == 0 ? 'selected="selected"' : ''; ?> value="0"> No </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label> Require 1st Layer Sub-Admin Approval </label>
                            <select name="require_subadmin_approval" class="form-control">
                                <option <?php echo $require_subadmin_approval == 1 ? 'selected="selected"' : ''; ?> value="1"> Yes </option>
                                <option <?php echo $require_subadmin_approval == 0 ? 'selected="selected"' : ''; ?> value="0"> No </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label> Require 2nd layer Approval </label>
                            <select name="require_second_approval" class="form-control">
                                <option <?php echo $require_second_approval == 1 ? 'selected="selected"' : ''; ?> value="1"> Yes </option>
                                <option <?php echo $require_second_approval == 0 ? 'selected="selected"' : ''; ?> value="0"> No </option>
                            </select>
                        </div>

                        <input type="submit" class="btn btn-default" value="Save" name="process">
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include("footer.php");?>
