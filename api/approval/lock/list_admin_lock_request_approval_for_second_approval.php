<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Database.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Constant.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/accessControl/controllers/AccessControlController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForSecondApprovalController.php';
//require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalForLockController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSecondApprovalController;
//use api\modules\v1\lock\controllers\ApprovalForLockController;
use common\config\Constant;
use common\config\Database;

$response = array();

$NotificationController = new NotificationController;
//$SmsController = new SmsController;

//date_default_timezone_set('Asia/Singapore');
$datetime = date("c");

//----------------------------------
// Request Approval for Locks
//----------------------------------
if(isset($_REQUEST['company_id'])
    && isset($_REQUEST['user_id']))
{
    $response['status'] = 'false';
    $current_user_id = (int) $_REQUEST['user_id'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockController = new LockController($Database);
    $AccessControlController = new AccessControlController($Database);
    $LockRequestApprovalForSecondApprovalController = new LockRequestApprovalForSecondApprovalController($Database);
//    $ApprovalForLockController = new ApprovalForLockController($Database);
    $NotificationController = new NotificationController;
    //$SmsController = new SmsController;


    $cursor_user = $UserController->actionGetOneById( $current_user_id );

    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $com['company_id'] = $company_found['company_ID']; //Put in company details array
        $response['data']['company_check']  = 'Valid Company ID';
//        $response['data']['company_user_id']  = $company_found['user_id'];
    }
    else
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Company ID';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }
    
    // Verify User
    if(isset($cursor_user)) {
        $role = $cursor_user['role'];

//        $response['role'] = $role;

        if (in_array($role, array(2, 3))) {
            $response['is_admin'] = true;
            $is_admin = true;
        } else {
            $response['is_admin'] = false;
            $is_admin = false;
        }
    }

    $cursor_approval_request = $LockRequestApprovalForSecondApprovalController->actionGetByCompanyId($_REQUEST['company_id']);
    
    $response['data'] = array();
    $i=0; // count
    if ($cursor_approval_request->count() > 0) {
        foreach ($cursor_approval_request as $approval_request) {
            $response['status'] = 'true';
            unset($approval_request['_id']);
            $user_id = $approval_request['user_id'];
            //Get Username
            $cursor = $UserController->actionGetOneById($user_id);

            if (isset($cursor)) {
                $response['status'] = 'true';
                unset($cursor['_id']);
                $username = $cursor['username'];
                $full_name = $cursor['full_name'];
                if (isset($cursor['first_name'])) {
                    $first_name = $cursor['first_name'];
                } else {
                    $first_name = "";
                }
                if (isset($cursor['last_name'])) {
                    $last_name = $cursor['last_name'];;
                } else {
                    $last_name = "";
                }
            } else {
                $full_name = "";
            }

            if ($is_admin == true) {
                $response['data'][$i]['id'] = $approval_request['id'];
                $response['data'][$i]['company_id'] = $approval_request['company_id'];
                $response['data'][$i]['user_id'] = $approval_request['user_id'];
                $response['data'][$i]['username'] = $username;
                //            $response['data'][$i]['full_name'] = $full_name; // User full name
                $response['data'][$i]['first_name'] = $first_name; // User full name
                $response['data'][$i]['last_name'] = $last_name; // User full name
                $response['data'][$i]['permit_id'] = $approval_request['permit_id'];
                $response['data'][$i]['lock_id'] = $approval_request['lock_id'];
                $response['data'][$i]['created_timestamp'] = $approval_request['created_timestamp'];
                $response['data'][$i]['created_by_user_id'] = $approval_request['created_by_user_id'];
                $response['data'][$i]['notified_admin_user_id'] = $approval_request['notified_admin_user_id'];
                $response['data'][$i]['admin_approved'] = $approval_request['admin_approved'];
                $response['data'][$i]['admin_approved_by'] = $approval_request['admin_approved_by'];
                $response['data'][$i]['admin_approved_on'] = $approval_request['admin_approved_on'];
                $response['data'][$i]['admin_rejected'] = $approval_request['admin_rejected'];
                $response['data'][$i]['admin_rejected_by'] = $approval_request['admin_rejected_by'];
                $response['data'][$i]['admin_rejected_on'] = $approval_request['admin_rejected_on'];
                $response['data'][$i]['valid_from'] = $approval_request['valid_from'];
                $response['data'][$i]['valid_to'] = $approval_request['valid_to'];

                if ($approval_request['admin_approved'] == true) {
                    $response['data'][$i]['approval_status'] = "approved";
                } else if ($approval_request['admin_rejected'] == false && $approval_request['admin_approved_by'] == 0) {
                    $response['data'][$i]['approval_status'] = "pending";
                }

                if ($approval_request['admin_rejected'] == true) {
                    $response['data'][$i]['approval_status'] = "rejected";
                }

                // Lock Name
                $cursor_lock = $LockController->actionGetOneById($approval_request['lock_id']);
                if (isset($cursor_lock)) {
                    $response['data'][$i]['lock_name'] = $cursor_lock['lock_name'];
                    $response['data'][$i]['serial_number'] = $cursor_lock['serial_number'];
                    $response['data'][$i]['lock_type'] = $cursor_lock['lock_type'];
                    $response['data'][$i]['lock_model'] = $cursor_lock['lock_model'];
                    $response['data'][$i]['lock_mechanism'] = $cursor_lock['lock_mechanism'];
                    $response['data'][$i]['brand'] = $cursor_lock['brand'];
                    $response['data'][$i]['entrance_visibility'] = $cursor_lock['entrance_visibility'];
                    $response['data'][$i]['lock_group_id'] = $cursor_lock['lock_group_id'];
                    $response['data'][$i]['log_number'] = $cursor_lock['log_number'];
                    $response['data'][$i]['site_id'] = $cursor_lock['site_id'];
                    $response['data'][$i]['geo_fencing'] = $cursor_lock['geo_fencing'];
                    $response['data'][$i]['latitude'] = $cursor_lock['latitude'];
                    $response['data'][$i]['longitude'] = $cursor_lock['longitude'];
                    $response['data'][$i]['unlock_radius'] = $cursor_lock['unlock_radius'];
                }

                // Datetime from and To
                $datetime = strtotime($approval_request['created_timestamp']);
                $request_date = date('Y-M-d', $datetime);

                $response['data'][$i]['request_datetime'] = $approval_request['created_timestamp'];

                $response['data'][$i]['valid_from'] = $approval_request['admin_approved_on'];
                //            $response['data'][$i]['valid_to'] = $approval_request['valid_until'];

                // Admin full name
                $approval_admin_id = (int)$approval_request['admin_approved_by'];
                $cursor_approval_admin = $UserController->actionGetOneById($approval_admin_id);

                if (isset($cursor_approval_admin)) {
                    $response['data'][$i]['admin_full_name'] = $cursor_approval_admin['full_name'];
                }
            } else {
                $response['status'] = 'false';
            }

            $i++;
        }
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);