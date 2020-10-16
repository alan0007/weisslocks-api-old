<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Required if your environment does not handle autoloading
require (dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php');
include_once (dirname(dirname(dirname(__FILE__))).'/controller/TwilioController.php');
// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;
use Twilio\Twiml\MessagingResponse;
use TwilioController\TwilioSMS;

// Initiate Controller class
$TwilioSMS = new TwilioSMS;

// Get request
$TwilioSMS->sms_body = $_REQUEST['Body'];
$TwilioSMS->sms_from = $_REQUEST['From'];

// Process request
$TwilioSMS->responseSMS();
