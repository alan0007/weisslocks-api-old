<?php
namespace api\modules\v1\lock\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/ApprovalRequestForLock.php');

use common\config\Database;
use common\v1\models\ApprovalRequestForLock;

class ApprovalRequestForLockController
{

    public $modelClass = 'common\v1\models\ApprovalForLock';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new ApprovalRequestForLock();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);
    }

    public function actionGetCollection(){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->table_name);
        return $collection;
    }

    public function actionIndex()
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find();
        return $result; // return object
    }

    public function actionGetAll( )
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find();
        return $result; // return object
    }

    public function actionGetById($company_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('company_ID'=>(int)$company_id));
        return $result; // return object
    }

    public function actionGetOneById($company_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('company_ID'=>(int)$company_id));
        return $result; // return array
    }

    public function actionGetByCondition($condition_array)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find($condition_array);
        return $result; // return object
    }

    public function actionGetByLockId($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('lock_id'=>(int)$id));
        return $result; // return object
    }

    public function actionInsert($company_id,$user_id,$created_by_user_id,$lock_id,
                                 $datetime,$from_date,$to_date,$from_time,$to_time)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $post = array(
            'approval_request_for_lock_id'  => getNext_users_Sequence('approval_request_for_lock'),
            'company_id'  => (int) $company_id,
            'user_id'  => (int) $user_id,
            'permit_id'  => 0,
            'lock_id'  => (int) $lock_id,
            'created_timestamp'  => $datetime,
            'created_by_user_id'  => (int) $created_by_user_id,
            'notified_admin_user_id'=> Array(),
            'admin_approved'  => false,
            'admin_approved_by'  => (int) 0,
            'admin_approved_on'  => (String) '',
            'admin_rejected'  => false,
            'admin_rejected_by'  => (int) 0,
            'admin_rejected_on'  => (String) '',
            'subadmin_approved'  => false,
            'subadmin_approved_by'  => (int) 0,
            'subadmin_approved_on'  => (String)'',
//        'valid_from' => (String)'',
            'valid_until' => (String)'', // 2020-07-02T08:05:37+08:00
            'from_date' => (String)$from_date, // 2020-07-02
            'to_date' => (String)$to_date,
            'from_time' => (String)$from_time, // 08:05:37
            'to_time' => (String)$to_time,

        );

        if ($collection->insert($post)){
//            $Reg_Query = array('_id' => $post['_id'] );
//            $locksData = $collection->findOne( $Reg_Query );
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
}