<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/log/controllers/LogUserActivityController.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

use api\modules\v1\log\controllers\LogUserActivityController;
use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

if( isset($_REQUEST['company_id']) && isset($_REQUEST['user_id']) &&
    isset($_REQUEST['description']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid information entered';

    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LogActivityController = new LogUserActivityController($Database);

    $company_id = $_REQUEST['company_id'];
    $user_id = $_REQUEST['user_id'];
    $description = $_REQUEST['description'];
    $category = 'user_activity';

    $cursor = $UserController->actionGetOneById($_REQUEST['user_id']);
    if(!isset($cursor)){
        $response['error'] = 'Invalid User';
    }
    else{
        if($LogActivityController->actionInsert($company_id,$user_id,$description,$category))
        {
            $response['status'] = 'true';
            unset($response['error']);
            $response['data']['log_creation']  = 'success';
        }
    }
}


// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);