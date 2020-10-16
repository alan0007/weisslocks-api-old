<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/totp/controllers/TotpController.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

include dirname(dirname(dirname(__FILE__))).'/otphp-master/lib/otphp.php';

use api\modules\v1\totp\controllers\TotpController;
use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

if( isset($_REQUEST['otp']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Code';

    $Database = new Database();
    $Constant = new Constant();
    $TotpController = new TotpController();

    // OTP verified for current time
    $result = $TotpController->actionVerifyTotp($_REQUEST['otp']);
//    $result = $totp->verify($_REQUEST['otp']); // => true
    $response['data']['otp_result'] = $result;
    if ( $result == TRUE){
        unset($response['error']);
        $response['status'] = 'true';
    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);