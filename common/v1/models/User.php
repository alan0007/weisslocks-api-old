<?php
namespace common\v1\models;


class User
{
    public $id;
    public $user_id;
    public $username;
    public $email;
    public $full_name;
    public $password;
    public $phone_number;
    public $approved;
    public $role;
    public $company_id;
    public $company_ref_id;
    public $user_company;
    public $user_company_ref_id;
    public $key_group_id;
    public $key_id;
    public $lock_group_id;
    public $KeyLockGroup;
    public $payment_id;
    public $invoice_no;
    public $device_id;
    public $udid_ios;
    public $company_position;
    public $user_registration_message;
    public $user_registration_image_name;
    public $token;
    public $last_login;
    public $participant;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'users';
    }

    public function columnName(){
        return [
            'id' => '_id',
            'user_id' => 'user_id',
            'username' => 'username',
            'email' => 'email',
            'full_name' => 'full_name',
            'password' => 'password',
            'phone_number' => 'phone_number',
            'approved' => 'approved',
            'role' => 'role',
            'company_id' => 'company_id',
            'company_ref_id' => 'company_ref_id',
            'user_company' => 'user_company',
            'user_company_ref_id' => 'user_company_ref_id',
            'key_group_id' => 'key_group_id',
            'key_id' => 'key_id',
            'lock_group_id' => 'lock_group_id',
            'key_lock_group' => 'KeyLockGroup',
            'payment_id' => 'payment_id',
            'invoice_no' => 'invoice_no',
            'device_id' => 'device_id',
            'udid_ios' => 'UDID_IOS',
            'company_position' => 'company_position',
            'user_registration_message' => 'user_registration_message',
            'user_registration_image_name' => 'user_registration_image_name',
            'token' => 'token',
            'last_login' => 'last_login',
            'participant' => 'participant',
            'lock_server_username' => 'lock_server_username',
            'lock_server_password' => 'lock_server_password',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'identification_number' => 'identification_number',
            'department' => 'department',
        ];
    }

    public function rules(){
        return [
            [['username', 'email', 'full_name', 'full_name', 'password', 'phone_number', 'company_ref_id',
                'user_company_ref_id', 'payment_id', 'invoice_no', 'device_id', 'udid_ios', 'company_position',
                'user_registration_message', 'user_registration_image_name', 'token', 'last_login',
                'lock_server_username', 'lock_server_password'], 'string'],
            [['user_id', 'approved', 'role', 'company_id', 'participant'], 'integer'],
            [['key_group_id', 'key_id', 'lock_group_id', 'key_lock_group'], 'array'],
        ];
    }
}