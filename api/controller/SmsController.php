<?php
class SmsController{
	
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
	
	
	// constructor with $db as database connection
    //public function __construct($db){
    //    $this->conn = $db;
    //}
	
	//------------
	// Twilio SMS
	//------------
	function sendSMSTwilio(){
		// Required if your environment does not handle autoloading
		//$dirname = realpath(__DIR__ . '/..');
		//$autoload = dirname().'/vendor/autoload.php';
		//require dirname(__FILE__).'/vendor/autoload.php'; // Loads the library
		//echo $autoload;
		
		// Your Account SID and Auth Token from twilio.com/console
		$sid = 'AC6b47342f4214c91015ee06609e964cab';
		$token = '066498307894441bf728a41e3d7c6d99';
		
		$client = new Client($sid, $token);
		
		$from_number_trial = '+12028688472'; //Trial number
		$from_number = ''; //Finalized number
		
		//$this->message_title = getEventClassification($request_targetMetricId,$request_isExceedingMaxima);
		
		$body_message = 'Event Alert for '.$this->message_title.'. Event: '.$state_message .'. Value now: '. $this->request_alertEvaluation.'. Date: '.$this->request_dateTime.'. Message: '. $this->alert_message;
		
		
		//Start Sending SMS
		if($this->phone_number_list != null){
			foreach ($this->phone_number_list as $phone){
				$sms_message = $client->messages->create(
					// the number you'd like to send the message to
					$phone_number,
					array(
						// A Twilio phone number you purchased at twilio.com/console
						'from' => $from_number_trial,
						// the body of the text message you'd like to send
						'body' => $body_message
					)
				);
				
				$response['sms_message_sent'] = $sms_message;
				$response['sms_message_result'] = $sms_message->sid;
			}
		}
		else{
			// Use the client to do fun stuff like send text messages!
			$sms_message = $client->messages->create(
				// the number you'd like to send the message to
				$this->phone_number,
				array(
					// A Twilio phone number you purchased at twilio.com/console
					'from' => $from_number_trial,
					// the body of the text message you'd like to send
					'body' => $body_message
				)
			);
			
			$response['twilio_sms_message_sent'] = $sms_message;
			$response['twilio_sms_message_result'] = $sms_message->sid;
		}
	}

	//------------
	// 365 SMS
	//------------
	function sendSMS365(){

		$url_base = "http://www.365smartsms.com/smsapi/httpsms.ashx?";
		$username = "username=pauto";
		$password = "password=p@ut0m@t!03$";
		$campaign_name = "campaignname=accessSMS".$this->access_id;
		$sender = "sender=91835301";
		$receiver = "receiver=".$this->phone_number;
		
		
		//$body_message = 'Event Alert for '.$this->message_title.'. Event: '.$state_message .'. Value set for min: '. $this->request_alertMinima .'. Value set or max: '.$this->request_alertMaxima.'. Date: '.$this->request_dateTime.'. Message: '. $this->alert_message;
		$body_message = 'Access Alert for '.$this->location.'. Door: '. $this->open_close;
		
		$body_message = str_replace(' ', '%20', $body_message);
		
		$msg = "msg=".$body_message;
		$scheduledtime = "scheduledtime=".date('Y-m-d H:i:s');
		$scheduledtime = str_replace(' ', '%20', $scheduledtime);
		
		//Start Sending SMS
		if($this->phone_number_list != null){
			foreach ($this->phone_number_list as $phone){
				
				$receiver = "receiver=".$phone['phone_number'];
				
				$url_full = $url_base."".$username."&".$password."&".$campaign_name."&".$sender."&".$receiver."&".$msg."&".$scheduledtime."";
				
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
			}
		}
		else{
			
			$url_full = $url_base."".$username."&".$password."&".$campaign_name."&".$sender."&".$receiver."&".$msg."&".$scheduledtime."";		
			//echo $url_full;
			$this->sms_url = $url_full;			
			
			//$url_api = $username."&".$password."&".$campaign_name."&".$sender."&".$receiver."&".$msg."&".$scheduledtime."";
			
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
			
		}
	}
	
}

?>