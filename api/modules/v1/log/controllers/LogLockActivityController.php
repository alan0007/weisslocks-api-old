<?php
namespace api\modules\v1\log\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/LogLockActivity.php');

use common\config\Database;
use common\v1\models\LogLockActivity;

class LogLockActivityController
{
    public $modelClass = 'common\v1\models\LogLockActivity';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new LogLockActivity();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);
    }

    public function actionIndex()
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find();
        return $result; // return object
    }

    public function actionGetUserCollection(){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        return $collection;
    }

    public function actionGetById($id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('log_activity_id'=>(int)$id));
        return $result; // return array
    }

    public function actionGetOneById($id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('log_activity_id'=>(int)$id));
        return $result; // return array
    }

    public function actionGetByUserId($user_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('user_id'=>(int)$user_id));
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

    public function actionInsert($company_id,$user_id,$lock_id,$lock_type,$description,$category,$status,$error_message,$datetime)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $post = array(
            'log_lock_activity_id' => $this->Database->getNext_users_Sequence($this->table_name),
            'company_id'     => (int) $company_id,
            'user_id'     => (int) $user_id,
            'lock_id'     => (int) $lock_id,
            'lock_type'     => $lock_type,
            'description'  => $description,
            'category'  => $category,
            'status'  => $status,
            'error_message' => $error_message,
            'timestamp'  => $datetime
        );

        if ($collection->insert($post)){
            return TRUE;
        }
        else{
            return FALSE;
        }

    }

}