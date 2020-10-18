<?php
namespace common\v1\models;

class KeyGroup
{
    public $id;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'keygroup';
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