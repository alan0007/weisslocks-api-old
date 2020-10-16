<?php
namespace api\modules\v1\sms\controllers;

// Required if your environment does not handle autoloading
require (dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php');
// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;
use Twilio\Twiml\MessagingResponse;

class TwilioController
{
    // object properties
    public $sms_body;
    public $sms_from;
    private $csv_file;

    // Send SMS
    function sendSMSTwilio()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = 'AC3e1ee0291639db29c9f767a738f83f01';
        $token = '92fbaba05960a3ed899b9591c9ae343a';
        $client = new Client($sid, $token);

        // Use the client to do fun stuff like send text messages!
        $client->messages->create(
        // the number you'd like to send the message to
            '+6588688828',
            [// A Twilio phone number you purchased at twilio.com/console
                'from' => '+14242228296',
                // the body of the text message you'd like to send
                'body' => 'Hey Alan! Good luck on the sms test!']
        );
    }

    function getdata(){
        $filename = $this->csv_file;
        $i = 0;
        $fields = array();

        $assoc_array = [];
        // open for reading
        if (($handle = fopen($filename, "r")) !== false) {
            // extract header data
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // save as keys
                $keys = $data;
            }
            // loop remaining rows of data
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // push associative subarrays
                $assoc_array[] = array_combine($keys, $data);
            }
            // close when done
            fclose($handle);
        }
        return $assoc_array;
    }

    function allowedNumber(){
        $allowed_list = array(
            "+6588688828",
            "+6596874908",
            //"+60146088568",
            "+639496885418",
            "+639087326665"
        );
        // Default = False
        $result = False;
        if(in_array($this->sms_from, $allowed_list)){
            $result = True;
        }
        return $result;
    }

    // Respond based on code received
    function responseSMS()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = 'AC3e1ee0291639db29c9f767a738f83f01';
        $token = '92fbaba05960a3ed899b9591c9ae343a';
        //$client = new Client($sid, $token);

        $response = new MessagingResponse;
        $body = $this->sms_body;
        $from = $this->sms_from;
        $prefix = "Access Code: ";
        $default = "Error: Invalid Code";

        if ($this->allowedNumber() == True) {
            $allowed = "Phone Number allowed. \n";

            // Decode HTTP to plain text, like space or special symbol, %20,...
            $message_decoded = strval(urldecode($body));
            // echo $message_decoded;

            // Set path to CSV file
            $this->csv_file = dirname(dirname(__FILE__)) . '/assets/match_access_code.csv';

            // Get csv data in array form
            $csv_array = $this->getdata();
            // print_r($csv_array);

            $key = array_search($message_decoded, array_column($csv_array, 'key'));
            // print_r($key);
            $result = $csv_array[$key]['value'];
            $result = $prefix . "" . $result;
            // echo $result;

            if ($result == NULL) {
                $result = $default;
            }
        }
        else{
            $allowed = "Phone Number Not allowed.";
            $result = $allowed;
        }
        $response->message($result);
        print($response);
        //echo json_encode($response, JSON_PRETTY_PRINT);
    }
}