<?php
namespace api\modules\v1\totp\controllers;

include dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/otphp-master/lib/otphp.php';
include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/TotpToken.php');

use common\v1\models\TotpToken;
use DateTime;

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
    public function __construct($db)
    {
        $this->interval = 300;
        $this->totp = new \OTPHP\TOTP("weisslocksSMS2020");
        $this->totp->interval = $this->interval;

        $this->Model = new TotpToken();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);

    }

    public function actionGenerateTotp($user_id){ // Generate OTP that last 5 minutes
        $this->totp = new \OTPHP\TOTP((string)$user_id);
        $totp_now = $this->totp->now();
        return $totp_now;
    }

    public function actionInsert($user_id,$totp_now,$datetime){ // Generate OTP that last 5 minutes
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $minutes = $this->interval / 60;
        $add_minutes = '+'.$minutes.' minutes';
        $valid_until = date('c', strtotime($add_minutes, strtotime($datetime)));

        $post = array(
            'id' => $this->Database->getNext_users_Sequence($this->table_name),
            'user_id'     => (int) $user_id,
            'totp'     => (int) $totp_now,
            'valid_until'     => $valid_until,
            'interval' => $this->totp->interval
        );

        if ($collection->insert($post)){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function actionVerifyTotpUsingLibrary($otp)
    {
//        $this->totp = new \OTPHP\TOTP($user_id);
        $result = $this->totp->verify($otp); // => true
        return $result;
    }

    public function actionVerifyTotp($user_id,$totp)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (int)$user_id ),
                array( 'totp' => (int)$totp )
            )
        );
        $result = $collection->findOne($criteria);
        return $result; // return array
    }

    public function actionVerifyTokenValidity($totp_datetime){
        $datetime = date("c");
        if (strtotime($datetime) <= strtotime($totp_datetime)) {
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function actionSmsMessage($totp_now)
    {
        $result = 'Weisslocks OTP: '. $totp_now;
        return $result;
    }

}