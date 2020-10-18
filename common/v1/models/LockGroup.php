<?php
namespace common\v1\models;

class LockGroup
{
    public $id;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'lockgroup';
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