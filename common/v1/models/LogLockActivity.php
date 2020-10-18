<?php


namespace common\v1\models;


class LogLockActivity
{
    public $id;
    public $user_id;
    public $company_id;
    public $description;
    public $category;
    public $timestamp;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'log_lock_activity';
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