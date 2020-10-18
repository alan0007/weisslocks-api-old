<?php


namespace common\v1\models;


class LogUserActivity
{
    public $id;
    public $user_id;
    public $company_id;
    public $description;
    public $category;
    public $timestamp;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'log_user_activity';
    }

    public function columnName(){
        return [

        ];
    }

    public function rules(){
        return [

        ];
    }
}