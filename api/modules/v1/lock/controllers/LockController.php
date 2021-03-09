<?php
namespace api\modules\v1\lock\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/Lock.php');

use common\config\Database;
use common\v1\models\Lock;

class LockController
{
    public $modelClass = 'common\v1\models\Lock';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new Lock();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);
    }

    public function actionGetCollection(){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->table_name);
        return $collection;
    }

    public function actionGetByCondition($condition_array)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find($condition_array);
        return $result;
    }

    public function actionIndex()
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find();
        return $result; // return object
    }

    public function actionGetByCompanyId($company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('company_id'=>(int)$company_id));
        return $result; // return array
    }

    public function actionGetOneById($id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('lock_ID'=>(int)$id));
        return $result; // return array
    }

    public function actionGetOneByCompanyId($company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('company_id'=>(int)$company_id));
        return $result; // return array
    }

    public function actionGetOneByName($name){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('lock_name'=>(string)$name));
        return $result; // return array
    }

    public function actionGetOneByDisplayName($display_name){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('display_name'=>(int)$display_name));
        return $result; // return array
    }

    public function actionGetByLockGroupId($id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('lock_group_id'=>(int)$id));
        return $result; // return object
    }

    public function actionUpdateDisplayName($id,$display_name){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('lock_ID'=>(int)$id );

        $collection->update( $criteria ,array('$set' => array(
            'display_name'  => $display_name
        )));

        if ($collection != null){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function action(){

    }
}