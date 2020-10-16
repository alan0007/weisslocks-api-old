<?php


namespace common\v1\models;


class AccessControl
{
    public $id;
    public $access_control_id;
    public $pairing_name;
    public $lock_group_id;
    public $key_group_id;
    public $company_id;
    public $users;
    public $key_time_restricted;
    public $date_from;
    public $date_to;
    public $time_from_hh;
    public $time_from_mm;
    public $time_to_hh;
    public $time_to_mm;
    public $lat;
    public $long;
    public $radius;
    public $added_by;
    public $updated_by;
    public $allowed_days;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'KeyLockGroup';
    }

    public function columnName(){
        return [
            'id' => '_id',
            'access_control_id' => 'keyLockGroup_ID',
            'pairing_name' => 'pairing_name',
            'lock_group_id' => 'lock_group_id',
            'key_group_id' => 'key_group_id',
            'company_id' => 'company_id',
            'users' => 'users',
            'key_time_restricted' => 'key_time_restricted',
            'date_from' => 'date_from',
            'date_to' => 'date_to',
            'time_from_hh' => 'time_from_hh',
            'time_from_mm' => 'time_from_mm',
            'time_to_hh' => 'time_to_hh',
            'time_to_mm' => 'time_to_mm',
            'lat' => 'lat',
            'long' => 'long',
            'radius' => 'radius',
            'added_by' => 'added_by',
            'allowed_day'=>'allowed_Day'
        ];
    }

    public function rules(){
        return [

        ];
    }
}