<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(__FILE__)).'/modules/v1/totp/controllers/TotpController.php';
require dirname(dirname(__FILE__)).'/modules/v1/sms/controllers/Sms365Controller.php';
require dirname(dirname(__FILE__)).'/modules/v1/sms/controllers/TwilioSmsController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

include dirname(dirname(dirname(__FILE__))).'/otphp-master/lib/otphp.php';

use api\modules\v1\totp\controllers\TotpController;
use api\modules\v1\sms\controllers\Sms365Controller;
use api\modules\v1\sms\controllers\TwilioSmsController;
use api\modules\v1\user\controllers\UserController;
//use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';


if(isset($_REQUEST['country_code']) && isset($_REQUEST['phone_number'])) {
    $response['status'] = 'false';
    $response['error'] = 'Invalid Credentials';

    $country_code = $_REQUEST['country_code'];
    $phone_number = $_REQUEST['phone_number'];

    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $TotpController = new TotpController($Database);
    $Sms365Controller = new Sms365Controller();
    $TwilioSMSController = new TwilioSMSController();

    $result = $UserController->actionVerifyPhoneNumber($country_code,$phone_number);

    if(isset($result['user_id']))
    {
        $response['status'] = 'true';
        unset($response['error']);
        $response['data']['user_id'] = $result['user_id'];
        $response['data']['username'] = $result['username'];
        $response['data']['company_id'] = $result['company_id'];
        $response['data']['company_ref_id'] = $result['company_ref_id'];

        $totp_now = $TotpController->actionGenerateTotp($result['user_id']);
        //    $response['data']['otp_setting'] = $totp;
        $response['data']['otp_interval'] = $TotpController->interval;
        $response['data']['otp'] = $totp_now;

        $message = $TotpController->actionSmsMessage($totp_now);

        $phone_number_with_country_code_plus = '+'.$_REQUEST['country_code'].$_REQUEST['phone_number'];
        $phone_number_with_country_code_no_plus = $_REQUEST['country_code'].$_REQUEST['phone_number'];

        // Send SMS
        //    $sms_result = $TwilioSMSController->sendSMS($_REQUEST['phone_number'],$message);
        //    $sms_result = $Sms365Controller->sendSMS($_REQUEST['phone_number'],$message);
        $sms_result = $TwilioSMSController->sendSMS($phone_number_with_country_code_plus,$message);
        $response['data']['sms_sent']  = 'SMS sent to '.$country_code.''.$phone_number;
        $response['data']['sms_result'] = $sms_result;
    }

}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);