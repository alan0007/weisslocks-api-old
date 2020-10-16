<?php
namespace common\v1\models;


class Company
{
    public $id;
    public $company_id;
    public $company_ref;
    public $company_name;
    public $company_address;
    public $company_contact;
    public $user_id;
    public $contracted_name;
    public $contracted_ref_id;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'company';
    }

    public function columnName(){
        return [
            'id' => '_id',
            'company_id' => 'company_ID',
            'company_ref' => 'company_ref',
            'company_name' => 'company_name',
            'company_address' => 'company_address',
            'company_contact' => 'company_contact',
            'user_id' => 'user_id',
            'contracted_name' => 'contracted_name',
            'contracted_ref_id' => 'contracted_ref_id',
        ];
    }

    public function rules(){
        return [

        ];
    }
}