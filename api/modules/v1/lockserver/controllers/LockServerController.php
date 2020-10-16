<?php
include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/LockServer.php');


class LockServerController
{
    public $LockServer;

    public $lock_server_url;
    public $full_url;
    public $lock_server_username;
    public $lock_server_password;
    public $lock_server_device_id;
    public $result;
    public $sms_code;
    public $mobile_os;
    public $token;
    public $token_temporary;
    public $authorization;

    public function login(){
        $this->LockServer = new LockServer();
        $this->lock_server_url = $this->LockServer->lockServerUrl();

        $this->full_url =  $this->lock_server_url."".$this->LockServer->login();

        $post = array(
            "username" => $this->lock_server_username,
            "password" => $this->lock_server_password,
            "deviceId" => $this->lock_server_device_id
        );

        $post_json = json_encode($post);
        $header = array('Content-Type: application/json');

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->full_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_json);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $this->result = curl_exec($curl);

        curl_close($curl);
    }

    public function verifyCode(){
        $this->LockServer = new LockServer();
        $this->lock_server_url = $this->LockServer->lockServerUrl();

        if ($this->mobile_os == null && $this->mobile_os != ''){
            $this->mobile_os = 'android';
        }
        $parameters = $this->LockServer->os()."=".$this->mobile_os;

        $this->full_url =  $this->lock_server_url."".$this->LockServer->verifyCode()."?".$parameters;

        $this->authorization = (string) 'Authorization: Bearer '.$this->token_temporary;
        $header = array('Content-Type: application/json',$this->authorization);

        $post = array(
            "code" => $this->sms_code
        );

        $post_json = json_encode($post);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->full_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_json);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $this->result = curl_exec($curl);

        curl_close($curl);
    }

    public function getUser(){
        $this->LockServer = new LockServer();
        $this->lock_server_url = $this->LockServer->lockServerUrl();

        $parameters = $this->LockServer->users()."".$this->lock_server_username."?includeLinks=True";
        $this->full_url =  $this->lock_server_url."".$parameters;

        $this->authorization = (string) 'Authorization: Bearer '.$this->token;
        $header = array('Content-Type: application/json',$this->authorization);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->full_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $this->result = curl_exec($curl);

        curl_close($curl);
    }
}