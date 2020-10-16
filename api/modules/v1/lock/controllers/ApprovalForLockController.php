<?php
namespace api\modules\v1\lock\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/ApprovalForLock.php');

use common\config\Database;
use common\v1\models\ApprovalForLock;

class ApprovalForLockController
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
        $this->Model = new ApprovalForLock();
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

    public function actionGetByLockId($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('lock_id'=>(int)$id));
        return $result;
    }


}