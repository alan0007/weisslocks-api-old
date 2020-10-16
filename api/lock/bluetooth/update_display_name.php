<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Database.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Constant.php';
//require dirname(dirname(dirname(__FILE__))).'/modules/v1/user/controllers/UserController.php';
//require dirname(dirname(dirname(__FILE__))).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockBluetoothController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

//use api\modules\v1\user\controllers\UserController;
//use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use common\config\Constant;
use common\config\Database;

$response = array();

//List Locks under Permit
// Updated 2020-03-04
if( isset($_REQUEST['lock_id']) && isset($_REQUEST['display_name']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Lock ID';

    $Database = new Database();
    $Constant = new Constant();
    $LockBluetoothController = new LockBluetoothController($Database);

    $result = $LockBluetoothController->actionGetOneById($_REQUEST['lock_id']);
    unset($result['_id']);
    $response['data']['lock'] = $result;

    $set_success = $LockBluetoothController->actionUpdateDisplayName($_REQUEST['lock_id'],$_REQUEST['display_name']);

    if ( $set_success == TRUE ){
        $response['status'] = 'true';
        unset($response['error']);
    }

    $response['data']['update_success']= $set_success;
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
