<?php
namespace common\v1\models;


class TotpToken
{
    public $id;
    public $user_id;
    public $totp;
    public $valid_until;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'totp_token';
    }

    public function columnName(){
        return [
            'id' => '_id',
            'user_id' => 'user_id',
            'totp' => 'totp',
            'valid_until' => 'valid_until',
            'interval' => 'interval'
        ];
    }

    public function rules(){
        return [

        ];
    }
}