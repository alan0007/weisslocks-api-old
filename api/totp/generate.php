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
require dirname(dirname(__FILE__)).'/modules/v1/sms/controllers/Sms365Controller.php';
require dirname(dirname(__FILE__)).'/modules/v1/sms/controllers/TwilioSmsController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

include dirname(dirname(dirname(__FILE__))).'/otphp-master/lib/otphp.php';

use api\modules\v1\totp\controllers\TotpController;
use api\modules\v1\sms\controllers\Sms365Controller;
use api\modules\v1\sms\controllers\TwilioSmsController;
use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

if( isset($_REQUEST['phone_number']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Phone Number';

    $Database = new Database();
    $Constant = new Constant();
    $TotpController = new TotpController();
    $Sms365Controller = new Sms365Controller();
    $TwilioSMSController = new TwilioSMSController();

    $totp_now = $TotpController->actionGenerateTotp();
//    $response['data']['otp_setting'] = $totp;
    $response['data']['otp_interval'] = $TotpController->interval;
    $response['data']['otp'] = $totp_now;

    $message = $TotpController->actionSmsMessage($totp_now);

    // Send SMS
//    $sms_result = $TwilioSMSController->sendSMS($_REQUEST['phone_number'],$message);
//    $sms_result = $Sms365Controller->sendSMS($_REQUEST['phone_number'],$message);
    $sms_result = $TwilioSMSController->sendSMS($_REQUEST['phone_number'],$message);
    $response['data']['sms_result'] = $sms_result;

    unset($response['error']);

}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);