<?php
namespace common\v1\models;

class LockRequestApprovalForSpecialAccess
{
// Use '' for string to avoid string translation
    public function tableName(){
        return 'lock_request_approval_for_special_access';
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