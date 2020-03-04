<?php
class NotificationController{

    // private object properties
    private $url = 'https://fcm.googleapis.com/fcm/send';
    private $headers;
    public $fields;

    //Result Response
    public $notification_message;
    public $result;

    // public object properties
    public $token; // User notification token
    public $notification;
    public $notification_title;
    public $notification_body;

    public $data;
    public $data_title;
    public $data_body;

    public $message_id;

	//------------
	// Firebase Notification
	//------------
	function sendNotification(){
        $this->headers = array
        (
            'Authorization: key=AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I',
            'Content-Type: application/json'
        );

        $this->fields = array
        (
            'to'=> $this->token,
            'notification' => $this->notification,
            'data' => $this->data,
            'message_id' => $this->message_id
        );

        $ch = curl_init();
        // curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_URL, $this->url );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $this->headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $this->fields ) );
        $this->result = curl_exec($ch );
        curl_close( $ch );

	}
	
}

?>