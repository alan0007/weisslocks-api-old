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
require dirname(dirname(__FILE__)).'/modules/v1/lock/controllers/LockBluetoothController.php';
require dirname(dirname(__FILE__)).'/modules/v1/accessControl/controllers/AccessControlController.php';
require dirname(dirname(dirname(__FILE__))).'/gmail/phpmailer/PHPMailerAutoload.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use common\config\Constant;
use common\config\Database;

$response = array();

if(isset($_REQUEST['user_id']) && isset($_REQUEST['company_id']) &&
    isset($_REQUEST['admin_user_id']) && isset($_REQUEST['approval']))
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Information';

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $AccessControlController = new AccessControlController($Database);

    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $response['data']['company_check']  = 'Valid Company ID';
    }
    else
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Company ID';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }

    $admin_details = $UserController->actionGetOneById($_REQUEST['admin_user_id']);
    $response['data']['company_id']  = $admin_details['company_id'];
    $response['data']['admin_role']  = $admin_details['role'];
    $admin_role = (int)$admin_details['role'];

    // Check is admin
    if( (int)$admin_details['company_id'] == (int)$_REQUEST['company_id'] && in_array($admin_role,array(2,3))){
        $is_admin = TRUE;
    }
    else{
        $is_admin = FALSE;
    }
    $response['data']['is_admin'] = $is_admin;

    if ($is_admin ===  TRUE ){
        unset($response['error']);
        // Check for valid user
        $valid_user = $UserController->actionGetOneByIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);
        if (isset($valid_user)){
            $user_emailTosend = $valid_user['email'];
            $username = $valid_user['username'];
//            $device_id = $valid_user['device_id'];
//            $device_name = $valid_user['device_name'];
            $response['data']['valid_user'] = 'true';
            $response['status'] = 'true';
        }
        else{
            $response['data']['valid_user'] = 'false';
        }

        // Start Approval
        if($response['data']['valid_user'] == 'true')
        {
            if(in_array($_REQUEST['approval'],array(0,1,2)))
            {
                if ($UserController->actionUpdateApproval($_REQUEST['user_id'],$_REQUEST['approval'])){
                    $response['data']['approval'] = 'true';
                    $response['data']['approval_code'] = $_REQUEST['approval'];

                    switch ($_REQUEST['approval']) {
                        case 0:
                            $response['data']['approval_result'] = "pending";
                            break;
                        case 1:
                            $response['data']['approval_result'] = "approved";
                            // Generate Verification Token
                            if( !(isset($valid_user['auth_key'])) || $valid_user['auth_key'] == NULL ){
                                if ($UserController->actionGenerateAuthenticationKey($_REQUEST['user_id'])){
                                    $response['data']['auth_key_generated'] = TRUE;
                                }
                                else{
                                    $response['data']['auth_key_generated'] = FALSE;
                                }
                            }
                            break;
                        case 2:
                            $response['data']['approval_result'] = "rejected";
                            break;
                        default:
                            $response['data']['approval_result'] = "invalid";
                    }

                }

//                if($_REQUEST['approval'] == 1)
//                {
//                    $mail = new PHPMailer;
//                    $mail->isSMTP();
//                    $mail->Host = 'smtp.gmail.com';
//                    $mail->Port = 587;
//                    $mail->SMTPSecure = 'tls';
//                    $mail->SMTPAuth = true;
//                    $mail->Username = "sendweisslocks@gmail.com";
//                    $mail->Password = "AppRegistration";
//                    $mail->
//                    setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
//                    // $mail->addAddress('archirayan5@gmail.com');
//                    $mail->addAddress( $user_emailTosend );
//                    $mail->Subject = 'Weiss Locks - Successfully Approved';
//
//                    $users = $UserController->actionGetOneById($_REQUEST['user']);
//                    if(isset($users)){
//                        $token = $users['token'];
//                    }
//                    $mail->msgHTML('
//					Dear Candidate,
//					<br/><br/>
//					You are Approved By Administrator.
//					<br/><br/>
//					Username : '.$username.'
//					');
//                    // <a href="http://app.weisslocks.com/setpassword.php?token='.$token.'"> Click here to Set Password </a>
//                    $mail->send();
//
//                    // Send Notifications Start
//                    if($device_name == 1)
//                    {
//                        $passphrase = 'IOSPUSH';
//                        $deviceToken = $device_id;
//                        $ctx = stream_context_create();
//                        stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem);
//                        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
//                        $fp = stream_socket_client(
//                            'ssl://gateway.sandbox.push.apple.com:2195', $err,
//                            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
//                        $body['aps'] = array(
//                            'alert' => array(
//                                'title' => 'Weiss Locks',
//                                'body' => 'You Are Successfully Approved',
//                            ),
//                            'sound' => 'default'
//                        );
//                        $payload = json_encode($body);
//                        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
//                        $result = fwrite($fp, $msg, strlen($msg));
//                        fclose($fp);
//                    }
//                    if($device_name == 2)
//                    {
//                        $registrationIds = array( $device_id );
//                        $msg = array
//                        (
//                            'title'		=> 'Weiss Locks',
//                            'message' 	=> 'You Are Successfully Approved',
//                        );
//                        $fields = array
//                        (
//                            'registration_ids' 	=> $registrationIds,
//                            'data'			=> $msg
//                        );
//                        $headers = array
//                        (
//                            'Authorization: key=' . API_ACCESS_KEY,
//                            'Content-Type: application/json'
//                        );
//                        $ch = curl_init();
//                        // curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
//                        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//                        curl_setopt( $ch,CURLOPT_POST, true );
//                        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//                        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//                        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//                        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
//                        $result = curl_exec($ch );
//                        curl_close( $ch );
//                    }
//                }
//
//                if($_REQUEST['approval'] == 2)
//                {
//                    require dirname(dirname(__FILE__)).'/gmail/phpmailer/PHPMailerAutoload.php';
//                    $mail = new PHPMailer;
//                    $mail->isSMTP();
//                    $mail->Host = 'smtp.gmail.com';
//                    $mail->Port = 587;
//                    $mail->SMTPSecure = 'tls';
//                    $mail->SMTPAuth = true;
//                    $mail->Username = "sendweisslocks@gmail.com";
//                    $mail->Password = "AppRegistration";
//                    $mail->
//                    setFrom('sendweisslocks@gmail.com', 'Weiss Locks');
//                    // $mail->addAddress('archirayan5@gmail.com');
//                    $mail->addAddress( $user_emailTosend );
//                    $mail->Subject = 'Weiss Locks - Account Application Rejected';
//
//                    $users = $UserController->actionGetOneById($_REQUEST['user']);
//                    if(isset($users)){
//                        $token = $users['token'];
//                    }
//                    $mail->msgHTML('
//					Dear Candidate,
//					<br/><br/>
//					You are Rejected By Administrator.
//					<br/><br/>
//					Username : '.$username.'
//					');
//                    // <a href="http://app.weisslocks.com/setpassword.php?token='.$token.'"> Click here to Set Password </a>
//                    $mail->send();
//
//                    // Send Notifications Start
//                    if($device_name == 1)
//                    {
//                        $passphrase = 'IOSPUSH';
//                        $deviceToken = $device_id;
//                        $ctx = stream_context_create();
//                        stream_context_set_option($ctx, 'ssl', 'local_cert', $sandbox_pem);
//                        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
//                        $fp = stream_socket_client(
//                            'ssl://gateway.sandbox.push.apple.com:2195', $err,
//                            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
//                        $body['aps'] = array(
//                            'alert' => array(
//                                'title' => 'Weiss Locks',
//                                'body' => 'You Are Rejected',
//                            ),
//                            'sound' => 'default'
//                        );
//                        $payload = json_encode($body);
//                        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
//                        $result = fwrite($fp, $msg, strlen($msg));
//                        fclose($fp);
//                    }
//                    if($device_name == 2)
//                    {
//                        $registrationIds = array( $device_id );
//                        $msg = array
//                        (
//                            'title'		=> 'Weiss Locks',
//                            'message' 	=> 'You Are Successfully Approved',
//                        );
//                        $fields = array
//                        (
//                            'registration_ids' 	=> $registrationIds,
//                            'data'			=> $msg
//                        );
//                        $headers = array
//                        (
//                            'Authorization: key=' . API_ACCESS_KEY,
//                            'Content-Type: application/json'
//                        );
//                        $ch = curl_init();
//                        // curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
//                        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//                        curl_setopt( $ch,CURLOPT_POST, true );
//                        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//                        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//                        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
//                        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
//                        $result = curl_exec($ch );
//                        curl_close( $ch );
//                    }
//                }
            }
        }

    }
    else{
        $response['error'] = 'Insufficient Permission for approval';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);