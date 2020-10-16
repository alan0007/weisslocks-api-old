<?php


namespace api\modules\v1\permit\controllers;

//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/Permit.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/config/Database.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/composer/vendor/autoload.php';

//use common\config\Database;
use common\v1\models\Permit;

class PermitController
{
    public $modelClass = 'common\v1\models\Permit';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new Permit();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);
    }

    public function actionIndex()
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find();
        return $result;
    }

    public function actionGetById($company_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('company_ID'=>(int)$company_id));
        return $result;
    }

    public function actionGetOneById($company_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('company_ID'=>(int)$company_id));
        return $result;
    }

    public function actionGetByIdAndCompanyId($user_id,$company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (string)$user_id ),
                array( 'company_id' => (string)$company_id )
            )
        );
        $result = $collection->find($criteria);
        return $result; // return object
    }

    public function actionGetByCondition($condition_array)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find($condition_array);
        return $result;
    }

    public function actionUpdate()
    {

    }
}