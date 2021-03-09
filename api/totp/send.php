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
use api\modules\v1\organization\controllers\CompanyController;
use common\config\Constant;
use common\config\Database;

$response = array();

if( isset($_REQUEST['phone_number']) && isset($_REQUEST['country_code']) && isset($_REQUEST['user_id']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Phone Number';
    $datetime = date("c");
    $user_id = $_REQUEST['user_id'];

//    if (strpos($_REQUEST['country_code'], '+') === FALSE){
//        $response['error'] = 'Invalid Country Code';
//        exit(json_encode($response, JSON_PRETTY_PRINT));
//    }

    $Database = new Database();
    $Constant = new Constant();
    $TotpController = new TotpController($Database);
    $Sms365Controller = new Sms365Controller();
    $TwilioSMSController = new TwilioSMSController();

//    try{
        $totp_now = $TotpController->actionGenerateTotp($user_id);
        //    $response['data']['otp_setting'] = $totp;
        $response['data']['otp_interval'] = $TotpController->interval;
        $response['data']['otp_generated'] = $totp_now;

        $message = $TotpController->actionSmsMessage($totp_now);

        // Insert into totp_token
        if ( $insert_token = $TotpController->actionInsert($user_id,$totp_now,$datetime) ){
            $response['data']['otp']['insertion'] = $insert_token;
            $token = $TotpController->actionVerifyTotp($user_id,$totp_now);
            unset($token['_id']);
            $response['data']['otp'] = $token;
            $response['status'] = 'true';
        }
        else{
            $response['error'] = 'Token insertion failed';
            exit(json_encode($response, JSON_PRETTY_PRINT));
        }

        $phone_number_with_country_code_plus = '+'.$_REQUEST['country_code'].$_REQUEST['phone_number'];
        $phone_number_with_country_code_no_plus = $_REQUEST['country_code'].$_REQUEST['phone_number'];

        // Send SMS
        //    $sms_result = $TwilioSMSController->sendSMS($_REQUEST['phone_number'],$message);
        //    $sms_result = $Sms365Controller->sendSMS($_REQUEST['phone_number'],$message);
        try {
            $sms_result = $TwilioSMSController->sendSMS($phone_number_with_country_code_plus, $message);
            $response['data']['sms_result'] = $sms_result;
        }
        catch(Exception $e){
            $response['error'] = $e;
            $response['data']['sms_result'] = "Send SMS Fatal error. Uncaught exception";
            $response['status'] = 'false';
        }

        if( isset($sms_result) && $sms_result != null ){
            $response['status'] = 'true';
            unset($response['error']);
        }
        else{
            $response['error'] = 'SMS failed';
            $response['status'] = 'false';
        }
//    }
//    catch(Exception $e) {
//        $response['error'] = $e;
//    }
}

// Display JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);