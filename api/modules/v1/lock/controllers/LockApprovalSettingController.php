<?php
namespace api\modules\v1\lock\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/ApprovalForLock.php');

use common\config\Database;
use common\v1\models\ApprovalForLock;

class LockApprovalSettingController
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
        $this->Model = new ApprovalForLock();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);
    }

    public function actionIndex(){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->table_name);
        $result = $collection->find();
        return $result; // return object
    }

    public function actionGetCollection(){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->table_name);
        return $collection;
    }

    public function actionGetById($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('approval_for_lock_id'=>(int)$id));
        return $result;
    }

    public function actionGetOneById($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('approval_for_lock_id'=>(int)$id));
        return $result; // return array
    }

    public function actionGetByLockId($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('lock_id'=>(int)$id));
        return $result;
    }

    public function actionGetByCondition($condition_array)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find($condition_array);
        return $result;
    }

    public function actionDeleteById($id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->remove(array('lock_id'=>(int)$id));
        return $result;
    }

    public function actionInsert($post_array){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $post = array(
            'approval_for_lock_id'  => getNext_users_Sequence('approval_for_lock'),
            'company_id' => (int) $post_array['company_id'],
            'lock_id' => (int) $post_array['lock_id'],
            'require_admin_approval' => (boolean) $post_array['require_admin_approval'],
            'require_subadmin_approval' => (boolean)$post_array['require_subadmin_approval'],
            'require_second_approval'  => (boolean) $post_array['require_second_approval']
        );
        if($collection->insert($post)){
            $Reg_Query = array('_id' => $post['_id'] ) ;
            $result = $collection->findOne( $Reg_Query );
        }
        else{
            $result = NULL;
        }
        return $result;

    }

    public function actionUpdateById($approval_for_lock_id,$post_array){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('approval_for_lock_id'=>(int) $approval_for_lock_id);
        $post = array(
            'company_id' => (int) $post_array['company_id'],
            'lock_id' => (int) $post_array['lock_id'],
            'require_admin_approval' => (boolean) $post_array['require_admin_approval'],
            'require_subadmin_approval' => (boolean)$post_array['require_subadmin_approval'],
            'require_second_approval'  => (boolean) $post_array['require_second_approval']
        );
        $result = $collection->update( $criteria ,array('$set' => $post));
        return $result;
    }
}