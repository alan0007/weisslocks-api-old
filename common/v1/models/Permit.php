<?php
namespace common\v1\models;

class Permit
{
    public $id;
    public $permit_id;
    public $user_id;
    public $username;
    public $visitor_company_name;
    public $company_id;
    public $company_ref_id;
    public $role;
    public $location;
    public $host_name;
    public $host_email_phone;
    public $date_from;
    public $date_to;
    public $time_from;
    public $time_to;
    public $pte_user_name_data;
    public $pte_user_email_data;
    public $pte_user_phone_data;
    public $pte_company_name_data;
    public $pte_company_position_data;
    public $pte_user_message_data;
    public $last_login;
    public $pte_user_image_name_data;
    public $nric;
    public $foreigner_ic;
    public $phone_number_valid;
    public $self_host;
    public $registered_time;
    public $approved;
    public $subadmin_approved;
    public $admin_approved;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'permit_to_enter';
    }

    public function columnName(){
        return [
            'id' => '_id',
            'permit_id' => 'permit_id',
            'user_id' => 'user_id',
            'username' => 'username',
            'visitor_company_name' => 'visitor_company_name',
            'company_id' => 'company_id',
            'company_ref_id' => 'company_ref_id',
            'role' => 'role',
            'location' => 'location',
            'host_name' => 'host_name',
            'host_email_phone' => 'host_email_phone',
            'date_from' => 'date_from',
            'date_to' => 'date_to',
            'time_from' => 'time_from',
            'time_to' => 'time_to',
            'pte_user_name_data' => 'pte_user_name_data',
            'pte_user_email_data' => 'pte_user_email_data',
            'pte_user_phone_data' => 'pte_user_phone_data',
            'pte_company_name_data' => 'pte_company_name_data',
            'pte_company_position_data' => 'pte_company_position_data',
            'pte_user_message_data' => 'pte_user_message_data',
            'pte_user_image_name_data' => 'pte_user_image_name_data',
            'nric' => 'nric',
            'foreigner_ic' => 'foreigner_ic',
            'phone_number_valid' => 'phone_number_valid',
            'self_host' => 'self_host',
            'registered_time' => 'registered_time',
            'approved' => 'approved',
            'subadmin_approved' => 'subadmin_approved',
            'admin_approved' => 'admin_approved',
        ];
    }

    public function rules(){
        return [

        ];
    }
}