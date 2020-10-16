<?php


class LockServer
{
    public $username;
    public $password;

    // Use '' for string to avoid string translation
    public function lockServerUrl(){
        return 'https://locks.evs.com.sg/IntegAPI/api/v1/';
    }

    public function login(){
        return 'identity/tokens/';
    }

    public function verifyCode(){
        return 'identity/verifyCode';
    }

    public function os(){
        return 'os';
    }

    public function users(){
        return 'users/';
    }

    public function usersUpdate(){
        return 'users/';
    }

    public function locks(){
        return 'locks/';
    }

    public function locksUpdate(){
        return 'locks/';
    }

}