<?php
namespace api\modules\v1\lock\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/LockRequestApprovalForSecondApproval.php');

use common\config\Database;
use common\v1\models\LockRequestApprovalForSecondApproval;
use MongoCursorException;

class LockRequestApprovalForSecondApprovalController
{

    public $modelClass = 'common\v1\models\LockRequestApprovalForSecondApproval';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new LockRequestApprovalForSecondApproval();
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

    public function actionGetOneById($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('id'=>(int)$id));
        return $result; // return array
    }

    public function actionGetByCompanyId($company_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('company_id'=>(int)$company_id));
        return $result; // return object
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

    public function actionGetByUserIdAndLockId($user_id,$lock_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (int)$user_id ),
                array( 'lock_id' => (int)$lock_id )
            )
        );
        $result = $collection->find($criteria);
        return $result; // return object
    }

    public function actionGetByUserIdAndCompanyId($user_id,$company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (int)$user_id ),
                array( 'company_id' => (int)$company_id )
            )
        );
        $result = $collection->find($criteria);
        return $result; // return object
    }

    public function actionGetByUserIdAndApproved($user_id,$company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (int)$user_id ),
                array( 'admin_approved' => TRUE )
            )
        );
        $result = $collection->find($criteria);
        return $result; // return object
    }

    public function actionInsert($post_array)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $post = array(
            'id'  => getNext_users_Sequence('lock_request_approval_for_second_approval'),
            'company_id'  => (int) $post_array['company_id'],
            'user_id'  => (int) $post_array['user_id'],
            'permit_id'  => (int) $post_array['permit_id'],
            'lock_id'  => (int) $post_array['lock_id'],
            'created_timestamp'  => $post_array['created_timestamp'],
            'created_by_user_id'  => (int) $post_array['created_by_user_id'],
            'notified_admin_user_id'=> Array(),
            'admin_approved'  => false,
            'admin_approved_by'  => (int) 0, // 2020-07-02T00:00:00+08:00
            'admin_approved_on'  => (String) '',
            'admin_rejected'  => false,
            'admin_rejected_by'  => (int) 0,
            'admin_rejected_on'  => (String) '', // 2020-07-02T00:00:00+08:00
            'valid_from' => (String)$post_array['valid_from'], // format: 'c' = 2020-07-02T00:00:00+08:00
            'valid_to' => (String)$post_array['valid_to'], // format: 'c' = 2020-07-02T00:00:00+08:00
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

    public function actionUpdateById($id,$post_array){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('id'=>(int) $id);
        $post = array(
            'company_id'  => (int) $post_array['company_id'],
            'user_id'  => (int) $post_array['user_id'],
            'permit_id'  => (int) $post_array['permit_id'],
            'lock_id'  => (int) $post_array['lock_id'],
            'created_timestamp'  => $post_array['created_timestamp'],
            'created_by_user_id'  => (int) $post_array['created_by_user_id'],
            'notified_admin_user_id'=> Array(),
            'admin_approved'  => (Boolean)$post_array['admin_approved'],
            'admin_approved_by'  => (int)$post_array['admin_approved_by'],
            'admin_approved_on'  => (String)$post_array['admin_approved_on'],
            'admin_rejected'  => (Boolean)$post_array['admin_rejected'],
            'admin_rejected_by'  => (int) $post_array['admin_rejected_by'],
            'admin_rejected_on'  => (String) $post_array['admin_rejected_on'],
            'valid_from' => (String)$post_array['valid_from'], // format: 'c' = 2020-07-02T00:00:00+08:00
            'valid_to' => (String)$post_array['valid_to'], // format: 'c' = 2020-07-02T00:00:00+08:00
        );
        $result = $collection->update( $criteria ,array('$set' => $post));
        return $result;
    }

    public function actionDeleteById($id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        if($collection->remove( array( 'id' =>(int)$id ) )){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function actionApproveById($id,$post_array){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('id'=>(int) $id);
        $valid_from = (string) date("c");
        $added_minutes = 300;
        $add_in_minutes = "+".$added_minutes." minutes";
        $valid_to = strtotime($add_in_minutes, strtotime($valid_from));
        $valid_to = date("c", $valid_to);

        $post = array(
            'admin_approved'  => (Boolean)TRUE,
            'admin_approved_by'  => (int)$post_array['admin_approved_by'],
            'admin_approved_on'  => (String)$post_array['admin_approved_on'],
            'admin_rejected' => false,
            'admin_rejected_by' => (int)0,
            'admin_rejected_on' => '',
            'valid_from' => $valid_from,
            'valid_to' => $valid_to,
        );
        try{
            $result = $collection->update( $criteria ,array('$set' => $post));
        }
        catch (MongoCursorException $e) {
            $result = $e->getMessage();
        }
        return $result;
    }

    public function actionRejectById($id,$post_array){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('id'=>(int) $id);
        $post = array(
            'admin_approved'  => (Boolean)FALSE,
            'admin_approved_by'  => (int)0,
            'admin_approved_on'  => '',
            'admin_rejected'  => (Boolean)TRUE,
            'admin_rejected_by'  => (int) $post_array['admin_rejected_by'],
            'admin_rejected_on'  => (String) $post_array['admin_rejected_on'],
            'valid_from' => '',
            'valid_to' => '',
        );
        try{
            $result = $collection->update( $criteria ,array('$set' => $post));
        }
        catch (MongoCursorException $e) {
            $result = $e->getMessage();
        }
        return $result;
    }


}