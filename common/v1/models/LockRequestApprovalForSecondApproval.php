<?php
namespace common\v1\models;

class LockRequestApprovalForSecondApproval
{
// Use '' for string to avoid string translation
    public function tableName(){
        return 'lock_request_approval_for_second_approval';
    }

    public function columnName(){
        return [
            'id' => '_id'
        ];
    }

    public function rules(){
        return [

        ];
    }
}