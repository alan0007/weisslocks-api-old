<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/log/controllers/LogLockBluetoothActivityController.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

use api\modules\v1\log\controllers\LogLockBluetoothActivityController;
use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

if( isset($_REQUEST['company_id']) && isset($_REQUEST['user_id']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid information entered';

    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LogActivityController = new LogLockBluetoothActivityController($Database);

    $result = $LogActivityController->actionGetByUserIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);
    if( $result->count() > 0){
        $response['status'] = 'true';
        unset($response['error']);

        foreach($result as $log_activity){
            unset($log_activity['_id']);
            $response['data'][] = $log_activity;
            $response['status'] = 'true';
        }
    }

}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);