<?php
namespace common\v1\models;

class LockRequestApprovalForNormalAccess
{
// Use '' for string to avoid string translation
    public function tableName(){
        return 'lock_request_approval_for_normal_access';
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