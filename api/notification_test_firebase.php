<?php
include(dirname(dirname(__FILE__)).'/configurations/config.php');
$response = array();

$sandbox_pem = dirname(__FILE__) . '/production(1).pem';
$live_pem = dirname(__FILE__) . '/livepuch.pem';

define('UPLOAD_PATH', '/permit_to_enter/');

//require (dirname(dirname(__FILE__)).'/composer/vendor/autoload.php');

?>

<head>
<meta name="google-signin-client_id" content="437775881518-65u2sbp3r824qjsmpu03b43dok3tptm8.apps.googleusercontent.com">

<script src="https://www.gstatic.com/firebasejs/5.7.0/firebase.js"></script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
</head>

<script>
  // Initialize Firebase
  var config = {
    apiKey: "AIzaSyBkbzunwO5BVFTlMATk8IHeMTzk2DNo3V0",
    authDomain: "weiocks-7e0ce.firebaseapp.com",
    databaseURL: "https://weiocks-7e0ce.firebaseio.com",
    projectId: "weiocks-7e0ce",
    storageBucket: "weiocks-7e0ce.appspot.com",
    messagingSenderId: "437775881518"
  };
  firebase.initializeApp(config);
</script>

<script>
var admin = require('firebase-admin');

var serviceAccount = require('weiocks-7e0ce-firebase-adminsdk-lcamy-f4d13369bc.json');

admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  databaseURL: 'https://weiocks-7e0ce.firebaseio.com/'
});


</script>

<script>
curl -X POST -H "Authorization: key=AAAAZe151S4:APA91bHe2hJxJ5z2Do1tEAK0uLOcRFwWOLSLx21nGCqVSOZ_sFhV8UCs74ZwV1uSY5x_v0bC7OX8PjALjZD-Sjd60-uv7u2q9fuWnPKKi_p_8UrYbIZki8g0cM8hbMPHFp-zRcZzYoB9" -H "Content-Type: application/json" -d '{
  "notification": {
    "title": "Portugal vs. Denmark",
    "body": "5 to 1",
    "icon": "firebase-logo.png",
    "click_action": "https://www.google.com"
  },
  "to": "fSeCiJqCmnM:APA91bE7dwKAY2TjXZLjxHROPpTAuPO4-JQgQHPxP80HVIJRXhBW5r-0zFlXCWQnwNoZA_I7Y269y4GuwfHuP0GDQdpxunyfcAOc03OT2orQxENqHyZZEAK24sTwSV5ZutrQirtI16Kb"
}' "https://fcm.googleapis.com/fcm/send"
</script>


<?php 
	//$url = 'https://fcm.googleapis.com/fcm/send';
	$url = 'https://fcm.googleapis.com/v1/projects/weiocks-5087e/messages:send';
	//$url = 'https://fcm.googleapis.com/v1/projects/weiocks-7e0ce/messages:send';
	$test_token = 'fSeCiJqCmnM:APA91bE7dwKAY2TjXZLjxHROPpTAuPO4-JQgQHPxP80HVIJRXhBW5r-0zFlXCWQnwNoZA_I7Y269y4GuwfHuP0GDQdpxunyfcAOc03OT2orQxENqHyZZEAK24sTwSV5ZutrQirtI16Kb';
	$test_token_2 = 'eIql4jsPGr0:APA91bEX0YWQcW55gm1vKYPyC0K2vsSHirzToniLMyqmpCyi-uskyukTWABzEFrRBJc1_HiAH0W52fJbCwYJU8SWXOs-TIspdCto3_I-wKeFkq1-5HqXCD77c9LDym_z-xOWrgAK9VBN';
	$test_ios1_token = "crV_KS14XfY:APA91bHLr-MDQtWFNm2uetwSR8FpALy_imk4J0X6RwyXlELvYAnL2gLpMzXv5BnSb2buVZXGiVucO8kY63JrsCdSY-kSkX_5O9AlDOuTGy1OJqK3yy7iJ7osSuTAQRKJbfk9GtbToD4k";
	$test_ios6_token = "ey3Ry2g1VdY:APA91bGyUmnEqhd6o4QcebEqtywdHsC2ARZnYC2vBuQEtq-39DOWrHxVWtkrZhzQ1zuOytEV0shaXzmWnevHKLVWGbxDt1459rXOVe9-YD7AB36Wj8lOQWlYkxWKlTFoiAGWC8hX2Sy8";
	
	$registrationIds = array( $test_token );

	
	$msg = array
	(
		'title'		=> 'Fire Drill', 
		'body' 	=> 'This is to inform that there will be test notifications send out till 4th Nov. Pls do not be alarmed. No action is required. ' . $purpose . ''
		//'android' => array( 'notification' => array('click_action'=>'OPEN_ACTIVITY_1') )
	);
	
	$notification = array(
		"collapse_key" => "test1",
		"priority" => "HIGH",
		"ttl" => "3600s",
		"restricted_package_name" => "com.weisslocks.pte",
		"data" => array(
			"fireAlarmId" => "41",
			"companyId" => "25"
		),
		'data'			=> array(
			"fireAlarmId" => "41",
			"companyId" => "25"
		),
		"notification" => array (
			"title" => "Test Alarm",
			"body" => "This is juts a test alarm",
			"icon" => "ic_sp_logo",
			"color" => "#ff0000",
			"sound" => "default",
			"tag" => "Alarm Notification",
			"click_action" => "RESPOND_ALARM",
			//"body_loc_key": string,
			//"body_loc_args": [
			//string
			//],
			//"title_loc_key": string,
			//"title_loc_args": [
			//string
			//],
			"channel_id" => "FirebaseAlarm"
		),
		"android" => array(
		
			"collapse_key" => "test1",
			"priority" => "HIGH",
			"ttl" => "3600s",
			"restricted_package_name" => "com.weisslocks.pte",
			"data" => array(
				"fireAlarmId" => "41",
				"companyId" => "25"
			),
			
			"notification" => array (
				"title" => "Test Alarm",
				"body" => "This is juts a test alarm",
				"icon" => "ic_sp_logo",
				"color" => "#ff0000",
				"sound" => "default",
				"tag" => "Alarm Notification",
				"click_action" => "RESPOND_ALARM",
				//"body_loc_key": string,
				//"body_loc_args": [
				//string
				//],
				//"title_loc_key": string,
				//"title_loc_args": [
				//string
				//],
				"channel_id" => "FirebaseAlarm"
			)
		)	
	);
	
	/*
	$fields = array	(
		'name' 	=> "Test Notification",
		//'to' => $test_token,
		'data'			=> array(
			"fireAlarmId" => "41",
			"companyId" => "25"
		),
		"android" => array(
		
			"collapse_key" => "test1",
			"priority" => "HIGH",
			"ttl" => "3600s",
			"restricted_package_name" => "com.weisslocks.pte",
			"data" => array(
				"fireAlarmId" => "41",
				"companyId" => "25"
			),
			
			"notification" => array (
				"title" => "Test Alarm",
				"body" => "This is juts a test alarm",
				"icon" => "ic_sp_logo",
				"color" => "#ff0000",
				"sound" => "default",
				"tag" => "Alarm Notification",
				"click_action" => "RESPOND_ALARM",
				//"body_loc_key": string,
				//"body_loc_args": [
				//string
				//],
				//"title_loc_key": string,
				//"title_loc_args": [
				//string
				//],
				"channel_id" => "FirebaseAlarm"
			)
		)		
	);
	*/
	
	$fields = array
	(
		//'registration_ids' 	=> array($test_ios1_token),
		'to' => $test_token,
		'data'			=> $notification
	);
	$fields = json_encode ( $fields );
	//echo $fields . "<br>";
	
	$headers = array
	(
		//Server key - Android
		'Authorization: key=AAAAZe151S4:APA91bHe2hJxJ5z2Do1tEAK0uLOcRFwWOLSLx21nGCqVSOZ_sFhV8UCs74ZwV1uSY5x_v0bC7OX8PjALjZD-Sjd60-uv7u2q9fuWnPKKi_p_8UrYbIZki8g0cM8hbMPHFp-zRcZzYoB9',
		//Legacy Server key - Android
		//'Authorization: key=AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I',
		//Server key - IOS
		//'Authorization: key=AAAAqhLtNEQ:APA91bF-vMhLEfcoXI6NTS9lbh_K4jUwiJfmIim7-eWKhxx4Fmuru9SbTR0QyWgpxq6Q7CD3e9o-7Fel4aJ3ejR9v5vFJqUTRbLnOsZTbgASGcQ7CzLaIitJaclPJhmMLn5-gJwX-RTX',
		//Legacy Server key - IOS
		//'Authorization: key=AIzaSyCC2JerfivUETknNKDIZXzUY26dVClf4Jk',
		'Content-Type: application/json'
	);
	
	$ch = curl_init();
	// curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
	curl_setopt( $ch,CURLOPT_URL, $url );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, $fields );
	
	$result = curl_exec($ch );
	curl_close( $ch );
	
	$response['result'] = $result;
	echo $result;















