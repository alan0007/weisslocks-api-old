<?php
class ApprovalForController{

    // private object properties
    private $url = 'https://fcm.googleapis.com/fcm/send';
    private $headers;

    //Result Response
    public $sms_message_sent;
    public $sms_message_result;

    // public object properties
    public $token; // User notification token
    public $fields;
	
	//------------
	// Firebase Notification
	//------------
	function sendNotification(){


	}
	
}

?>