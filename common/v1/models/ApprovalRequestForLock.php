<?php
namespace common\v1\models;

class ApprovalRequestForLock
{
// Use '' for string to avoid string translation
    public function tableName(){
        return 'approval_request_for_lock';
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