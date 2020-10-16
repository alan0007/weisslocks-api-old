<?php
namespace api\modules\v1\totp\controllers;

include dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/otphp-master/lib/otphp.php';


class TotpController
{
    public $modelClass = '';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $totp;
    public $interval;

    // constructor with $db as database connection
    public function __construct()
    {
        $this->interval = 300;
        $this->totp = new \OTPHP\TOTP("weisslocksSMS2020");
        $this->totp->interval = 300;
    }

    public function actionGenerateTotp(){ // Generate OTP that last 5 minutes
        $totp_now = $this->totp->now();
        return $totp_now;
    }

    public function actionVerifyTotp($otp)
    {
        $result = $this->totp->verify($_REQUEST['otp']); // => true
        return $result;
    }

    public function actionSmsMessage($totp_now)
    {
        $result = 'Weisslocks OTP: '. $totp_now;
        return $result;
    }

}