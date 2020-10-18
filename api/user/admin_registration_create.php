<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include(dirname(dirname(dirname(__FILE__))).'/configurations/config.php');
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Utility.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(__FILE__)).'/modules/v1/totp/controllers/TotpController.php';
require dirname(dirname(__FILE__)).'/modules/v1/sms/controllers/Sms365Controller.php';
require dirname(dirname(__FILE__)).'/modules/v1/sms/controllers/TwilioSmsController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

include dirname(dirname(dirname(__FILE__))).'/otphp-master/lib/otphp.php';


use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\totp\controllers\TotpController;
use api\modules\v1\sms\controllers\Sms365Controller;
use api\modules\v1\sms\controllers\TwilioSmsController;
use common\config\Constant;
use common\config\Database;
use common\config\Utility;

$response = array();

if( isset($_REQUEST['company_id']) && isset($_REQUEST['admin_user_id']) &&
    isset($_REQUEST['username']) && isset($_REQUEST['password']) &&
    isset($_REQUEST['first_name']) && isset($_REQUEST['last_name']) &&
    isset($_REQUEST['phone_number']) && isset($_REQUEST['country_code']) &&
    isset($_REQUEST['email']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid information entered';

    //Load no image uploaded.jpg if no image uploaded
    if ( !isset($_REQUEST['user_registration_image_name']) ||
        $_REQUEST['user_registration_image_name'] == null || $_REQUEST['user_registration_image_name'] == ""){
        $_REQUEST['user_registration_image_name'] = "no_uploaded_image.png";
    }

    if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL))
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Email Address';
        exit(json_encode($response));
    }

    if ( !isset($_REQUEST['department'])){
        $_REQUEST['department'] = "";
    }
    if ( !isset($_REQUEST['identification_last_4_digit'])){
        $_REQUEST['identification_last_4_digit'] = "";
    }

    $username = $_REQUEST['username'];

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $Utility = new Utility();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $TotpController = new TotpController($Database);
    $Sms365Controller = new Sms365Controller();
    $TwilioSMSController = new TwilioSMSController();


    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $response['data']['company']  = 'Valid Company ID';
    }
    else
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Company ID';
        exit(json_encode($response));
    }

    // Check is admin
    $is_admin = $UserController->actionIsAdmin($_REQUEST['admin_user_id'],$_REQUEST['company_id']);
    $response['is_admin'] = $is_admin;

    if ($is_admin ==  TRUE ){
        $cursor = $UserController->actionGetOneByUsernameAndCompanyId($username,$company_id);
        if(isset($cursor)){ // Verify account before registration
            $response['status'] = 'false';
            $response['error'] = 'User Already Exists...';
        }
        else{ // Start Registration
            $response['status'] = 'true';
            $phone_number = isset($_REQUEST['phone_number']) ? $_REQUEST['phone_number'] : '';
            $user_role = isset($_REQUEST['user_role']) ? $_REQUEST['user_role'] : 5;
            $UDID_IOS = isset($_REQUEST['UDID_IOS']) ? $_REQUEST['UDID_IOS'] : '';

            // Check Email validity
            $email_found = $UserController->actionGetOneByEmail($_REQUEST['email']);
            if (isset($email_found)){
                $response['data']['duplicate_email'] = TRUE;
            }else{
                $response['data']['duplicate_email'] = FALSE;
            }

            // Assign Key Group, Lock Group, Access Control
            $default_key_group_id = $Utility->actionAssignKeyGroupBasedOnCompanyId($_REQUEST['company_id']);
            $default_lock_group_id = $Utility->actionDefaultLockGroupBasedOnCompanyId($_REQUEST['company_id']);
            $default_access_control_id = $Utility->actionAssignAccessControlBasedOnCompanyId($_REQUEST['company_id']);

            $post = array(
                'user_id' => $Database->getNext_users_Sequence('weiss_locks_user'),
                'username'     => $_REQUEST['username'],
                'email'   => $_REQUEST['email'],
                'password'  => md5($_REQUEST['password']),
                'phone_number'  => $phone_number,
                'country_code'  => $_REQUEST['country_code'],
                'approved'  => 0,
                'role'  => $user_role,
                'company_id'  => (int) $company_id,
                'key_group_id'  => array($default_key_group_id),
                'key_id'  => '',
                'key_activated'  => '',
                'lock_group_id'  => array($default_lock_group_id),
                'access_control_id'  => $default_access_control_id,
                'payment_id'  => '',
                'invoice_no'  => '',
                'cc_name'  => '',
                'cc_num'  => '',
                'cc_validity'  => '',
                'registered_time'  => date('c'),
//            'device_name'  => $_REQUEST['device_name'],
//            'device_id'  => $_REQUEST['device_id'],
                'company_ref_id'  => '',
                'UDID_IOS'  => $UDID_IOS,
                // Additional Field
                'company_position'  => '',
                'user_registration_message'  => '',
                'user_registration_image_name' => '', //Image File Name
                'residence'  => '', //Residence-Yes/No
//            'nric'  => $_REQUEST['nric'], //NRIC
                'foreigner_ic'  => '', //Foreigner NRIC
                'visitor_company_name' => '', //For Visitor Only
                'registration_verified' => 0, //For Phone OTP,
                'participant' => 0,
                'lock_server_username' => '',
                'lock_server_password' => '',
                'first_name' => $_REQUEST['first_name'],
                'last_name' => $_REQUEST['last_name'],
                'full_name' =>'',
                'identification_last_4_digit' => $_REQUEST['identification_last_4_digit'],
                'department' => $_REQUEST['department']
            );
            $response['data']['account_creation']  = 'pending';

            if($UserController->actionInsert($post))
            {
                $response['data']['account_creation']  = 'success';

                $user_search = $UserController->actionGetOneByUsernameAndCompanyId($username,$company_id);
                unset($user_search['_id']);
                unset($user_search['password']);
                $response['data']['user'] = $user_search;
                $user_id = $user_search['user_id'];
//            $device_id = $user_search['device_id'];
//            $device_name = $user_search['device_name'];

                // Update User List in Company
                $cursor1 = $CompanyController->actionGetOneById($company_id);
                if(isset($cursor1))
                {
                    $user_id_array = json_decode($cursor1['user_id']);
                    $user_id_array[] = $user_id;
                    $response['data']['company_user_list_update_success'] = $user_search;
                    $CompanyController->actionUpdateUserList($company_id,$user_id_array);
                }
                unset($response['error']);

                //--------------
                // Send OTP
                //--------------
                $totp_now = $TotpController->actionGenerateTotp();
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
                $response['data']['sms_result'] = $sms_result;


//            //--- Start Image Save
//
//            //Get image string posted from Android App
//            //$base = $_REQUEST['image'];
//            // Get File name posted from App
//            //$filename = $_REQUEST['filename'];
//            //Decode Image
//            //$binary = base64_decode($base);
//            //header('Content-Type: bitmap; charset=utf-8');
//            //Images will be saved under 'www/api2/uploadedimages' folder
//            //$file = fopen('uploadimages/'.$filename, 'wb');
//            //Create File
//            //fwrite($file, $binary);
//            //fclose($file);
//            //echo 'Image upload complete';
//
//            //--- End Image Saving
//
//            // Mail to Admins Start
//            $state = 'all';
//            $collection = new MongoCollection($app_data, 'settings');
//            $settings = $collection->find(array('company_id'=>(int)$company_ID));
//            foreach($settings as $setting)
//            {
//                $state = $setting['send_notification'];
//                $mailed_users = $setting['users'];
//            }
//            require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
//            $mail = new PHPMailer;
//            $mail->isSMTP();
//            $mail->Host = 'smtp.gmail.com';
//            $mail->Port = 587;
//            $mail->SMTPSecure = 'tls';
//            $mail->SMTPAuth = true;
//            $mail->Username = "sendweisslocks@gmail.com";
//            $mail->Password = "AppRegistration";
//            $mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
//            if($state == 'all')
//            {
//                $collection5 = new MongoCollection($app_data, 'company');
//                $company_users = $collection5->find(array('company_ID'=>(int)$company_ID));
//                foreach($company_users as $company_user)
//                {
//                    $mailed_users = $company_user['user_id'];
//                    $mail_user = json_decode($mailed_users);
//                    for($i=0;$i<=count($mail_user);$i++)
//                    {
//                        $collection_user = new MongoCollection($app_data, 'users');
//                        $users = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
//                        if($users->count() > 0) {
//                            foreach($users as $user)
//                            {
//                                if($user['role'] >= 1 && $user['role'] <= 3)
//                                {
//                                    $mail->addAddress( $user['email'] );
//                                    // Temporarily remove notification
//                                    /*
//                                    if($user['device_name'] == 1)
//                                    {
//                                        $device_id_admin = $user['device_id'];
//                                        $passphrase = 'IOSPUSH';
//                                        $deviceToken = $device_id_admin;
//                                        $ctx = stream_context_create();
//                                        stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
//                                        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
//                                        $fp = stream_socket_client(
//                                            'ssl://gateway.sandbox.push.apple.com:2195', $err,
//                                            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
//                                        $body['aps'] = array(
//                                            'alert' => array(
//                                                'title' => 'Weiss Locks',
//                                                'body' => 'New Registration Added',
//                                             ),
//                                            'sound' => 'default'
//                                        );
//                                        $payload = json_encode($body);
//                                        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
//                                        $result = fwrite($fp, $msg, strlen($msg));
//                                        fclose($fp);
//                                    }
//
//                                    if($user['device_name'] == 2)
//                                    {
//                                        $device_id_admin = $user['device_id'];
//                                        $registrationIds = array( $device_id_admin );
//                                        $msg = array
//                                        (
//                                            'title'		=> 'Weiss Locks',
//                                            'message' 	=> 'New Registration Added',
//                                        );
//                                        $fields = array
//                                        (
//                                            'registration_ids' 	=> $registrationIds,
//                                            'data'			=> $msg
//                                        );
//                                        $headers = array
//                                        (
//                                            'Authorization: key=' . API_ACCESS_KEY,
//                                            'Content-Type: application/json'
//                                        );
//
//                                        $ch = curl_init();
//                                        // curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
//                                        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//                                        curl_setopt( $ch,CURLOPT_POST, true );
//                                        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//                                        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//                                        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//                                        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
//                                        $result = curl_exec($ch );
//                                        curl_close( $ch );
//                                    }
//                                    */
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//            else if($state == 'Custom')
//            {
//                $mail_user = json_decode($mailed_users);
//                $collection_user = new MongoCollection($app_data, 'users');
//                for($i=0;$i<=count($mail_user);$i++)
//                {
//                    $users = $collection_user->find(array('user_id'=>(int)$mail_user[$i]));
//                    if($users->count() > 0) {
//                        foreach($users as $user)
//                        {
//                            $mail->addAddress( $user['email'] );
//                            // Temporarily remove notification
//                            /*
//                            if($user['device_name'] == 1)
//                            {
//                                $passphrase = 'IOSPUSH';
//                                $deviceToken = $user['device_id'];
//                                $ctx = stream_context_create();
//                                stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
//                                stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
//                                $fp = stream_socket_client(
//                                    'ssl://gateway.sandbox.push.apple.com:2195', $err,
//                                    $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
//                                $body['aps'] = array(
//                                    'alert' => array(
//                                        'title' => 'Weiss Locks',
//                                        'body' => 'New Registration Added',
//                                     ),
//                                    'sound' => 'default'
//                                );
//                                $payload = json_encode($body);
//                                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
//                                $result = fwrite($fp, $msg, strlen($msg));
//                                fclose($fp);
//
//                            }
//
//
//
//                            if($user['device_name'] == 2)
//                            {
//                                $device_id_admin = $user['device_id'];
//                                $registrationIds = array( $device_id_admin );
//                                $msg = array
//                                (
//                                    'title'		=> 'Weiss Locks',
//                                    'message' 	=> 'New Registration Added',
//                                );
//                                $fields = array
//                                (
//                                    'registration_ids' 	=> $registrationIds,
//                                    'data'			=> $msg
//                                );
//                                $headers = array
//                                (
//                                    'Authorization: key=' . API_ACCESS_KEY,
//                                    'Content-Type: application/json'
//                                );
//
//                                $ch = curl_init();
//                                // curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
//                                curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//                                curl_setopt( $ch,CURLOPT_POST, true );
//                                curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//                                curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//                                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//                                curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
//                                $result = curl_exec($ch );
//                                curl_close( $ch );
//                            }
//                            */
//                        }
//                    }
//                }
//            }
//            $mail->Subject = 'Weiss Locks - New Registration Added';
//            $mail->msgHTML('
//						Dear Candidate,
//						<br/><br/>
//						New User Registered In the System. Details are As Follows...
//						<br/><br/>
//						Name : '.$full_name.'<br/>
//						User Name : '.$username.'<br/>
//						');
//            $mail->send();
//            // Mail to Admins End
//
//            // Mail to Users Start
//            $mail = new PHPMailer;
//            $mail->isSMTP();
//            $mail->Host = 'smtp.gmail.com';
//            $mail->Port = 587;
//            $mail->SMTPSecure = 'tls';
//            $mail->SMTPAuth = true;
//            $mail->Username = "sendweisslocks@gmail.com";
//            $mail->Password = "AppRegistration";
//            $mail->setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
//            $mail->addAddress( $_REQUEST['user_email'] );
//            $mail->Subject = 'Weiss Locks - Successfully Registered';
//            $mail->msgHTML('
//						Dear Candidate,
//						<br/><br/>
//						You are successfully Registered.
//						<br/><br/>
//						Name : '.$full_name.'<br/>
//						User Name : '.$username.'<br/>
//						');
//            $mail->send();
//            // Mail to Users Email
//            // Send Notifications Start
//
//            // Temporarily remove notification
//            /*
//            // For IOS
//            if($device_name == 1)
//            {
//                $passphrase = 'IOSPUSH';
//                $deviceToken = $device_id;
//                $ctx = stream_context_create();
//                stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem );
//                stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
//                $fp = stream_socket_client(
//                    'ssl://gateway.sandbox.push.apple.com:2195', $err,
//                    $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
//                $body['aps'] = array(
//                    'alert' => array(
//                        'title' => 'Weiss Locks',
//                        'body' => 'You Are Successfully Registered',
//                     ),
//                    'sound' => 'default'
//                );
//                $payload = json_encode($body);
//                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
//                $result = fwrite($fp, $msg, strlen($msg));
//                fclose($fp);
//            }
//
//            // For Andriod
//            if($device_name == 2)
//            {
//                $registrationIds = array( $device_id );
//                $msg = array
//                (
//                    'title'		=> 'Weiss Locks',
//                    'message' 	=> 'You Are Successfully Registered',
//                );
//                $fields = array
//                (
//                    'registration_ids' 	=> $registrationIds,
//                    'data'			=> $msg
//                );
//                $headers = array
//                (
//                    'Authorization: key=' . API_ACCESS_KEY,
//                    'Content-Type: application/json'
//                );
//
//                $ch = curl_init();
//                // curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
//                curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//                curl_setopt( $ch,CURLOPT_POST, true );
//                curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//                curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//                curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
//                $result = curl_exec($ch );
//                curl_close( $ch );
//            }
//            */
//        }
            }
        }
    }
    else{

    }

}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);