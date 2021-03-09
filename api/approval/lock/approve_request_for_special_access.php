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
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockBluetoothController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/accessControl/controllers/AccessControlController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockRequestApprovalForSpecialAccessController.php';
//require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/ApprovalForLockController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use api\modules\v1\lock\controllers\LockRequestApprovalForSpecialAccessController;
//use api\modules\v1\lock\controllers\ApprovalForLockController;
use common\config\Constant;
use common\config\Database;

$response = array();

$NotificationController = new NotificationController;
//$SmsController = new SmsController;

//date_default_timezone_set('Asia/Singapore');
$datetime = date("c");

// Admin Approval - Approve without checking credentials (Demo Version Only)
if(isset($_REQUEST['company_id'])
    && isset($_REQUEST['user_id'])
    && isset($_REQUEST['id']))
{
    $response['status'] = 'false';
    $id = $_REQUEST['id'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $AccessControlController = new AccessControlController($Database);
    $LockRequestApprovalForSpecialAccessController = new LockRequestApprovalForSpecialAccessController($Database);
//    $ApprovalForLockController = new ApprovalForLockController($Database);
    $NotificationController = new NotificationController;
    //$SmsController = new SmsController;

//    $collection_admin = $app_data->users;
//    $criteria_admin = array(
//    '$and' => array(
//    array( 'company_id'=> $_REQUEST['company_id'] ),
//    array( 'user_id'=> $_REQUEST['admin_user_id'] )
//    )
//    );

//    $cursor_admin = $collection_admin->findOne($criteria_admin);
//    if(isset($cursor_admin)){
//    $role = $cursor_admin['role'];
//    if (in_array($role, array(2, 3))){
//    $response['is_admin'] = true;
//    }
//    }
//    else{
//    $response['is_admin'] = false;
//    }

    $response['is_admin'] = true;
    $result = array();

    if ($response['is_admin'] == true) {
        $valid_to = strtotime("+1440 minutes", strtotime($datetime));
        $post_array = array(
            'admin_approved' => (Boolean)TRUE,
            'admin_approved_by' => (int) $_REQUEST['user_id'],
            'admin_approved_on' => $datetime,
        );
        $result = $LockRequestApprovalForSpecialAccessController->actionApproveById($id,$post_array);
        $response['data'][] = $result;
        if ($result['err'] == NULL){
            $response['status'] = 'true';
        }else{
            $response['status'] = 'false';
        }
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);