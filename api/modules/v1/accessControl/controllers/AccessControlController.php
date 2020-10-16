<?php


namespace api\modules\v1\accessControl\controllers;

//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/AccessControl.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/config/Database.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/composer/vendor/autoload.php';

use common\config\Database;
use common\v1\models\AccessControl;

class AccessControlController
{
    public $modelClass = 'common\v1\models\AccessControl';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new AccessControl();
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

    public function actionGetById($access_control_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('keyLockGroup_ID'=>(int)$access_control_id));
        return $result;
    }

    public function actionGetByCompanyId($company_id)
    {
        $this->collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('company_id'=>(int)$company_id);
        $result = $this->collection->find($criteria);
        return $result; // return object
    }

    public function actionGetOneByCompanyId($company_id)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('company_id'=>(int)$company_id));
        return $result; // return array
    }

    public function actionUpdate()
    {

    }
}