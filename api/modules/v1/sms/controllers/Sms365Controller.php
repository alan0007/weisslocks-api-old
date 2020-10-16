<?php
namespace api\modules\v1\sms\controllers;

class Sms365Controller{
	
	// database connection and table name
    //private $conn;
    //private $table_name = "ess_energy_alert";
 
	// object properties
    public $phone_number;
	public $phone_number_list;
	public $access_id;
    public $open_close;
	public $location;
	
	//Result Response
	public $sms_url;
	public $sms_message_sent;
	public $sms_message_result;
    private $url_base;
    private $username;
    private $password;
    private $campaign_name;
    private $sender;
    private $receiver;
    public $timestamp;

    // constructor
    public function __construct()
    {
        $this->url_base = "https://www.365smartsms.com/smsapi/httpsms.ashx?";
        $this->username = "username=pauto";
        $this->password = "password=p@ut0m@t!03$";
        $this->sender = "sender=91835301";
        $this->timestamp = date("U");
    }

    function sendSMS($phone_number,$message){
        $receiver = "receiver=".$phone_number;

		$body_message = urlencode($message);
		$msg = "msg=".$body_message;

		$scheduledtime = "scheduledtime=".date('Y-m-d H:i:s');
		$scheduledtime = urlencode($scheduledtime);
        $this->campaign_name = "campaignname=weisslocksSMS".rand().$phone_number.$this->timestamp;

        $url_full = $this->url_base."".$this->username."&".$this->password."&".$this->campaign_name."&".
            $this->sender."&".$receiver."&".$msg."&".$scheduledtime."";
        echo $url_full;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url_full);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POST, 1);

        //$headers = array();
        //$headers[] = 'Accept: application/json';
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->sms_message_result = curl_error($ch);
            $this->sms_message_sent = "no";
        }
        else{
            $this->sms_message_result = $result;
            $this->sms_message_sent = "yes";
        }
        curl_close ($ch);

        return $this->sms_message_result;
	}
	
}

?>