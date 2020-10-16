<?php


namespace api\modules\v1\organization\controllers;

//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/Company.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/config/Database.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/composer/vendor/autoload.php';

use common\config\Database;
use common\v1\models\Company;

class CompanyController
{
    public $modelClass = 'common\v1\models\Company';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new Company();
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

    public function actionUpdate()
    {

    }

    public function actionUpdateUserList($company_id,$user_id_array)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = $collection->findOne(array('company_ID'=>(int)$company_id));
        $success = $collection->update( $criteria ,array('$set' => array(
            'user_id'  => json_encode($user_id_array)
        )));
        if ($success){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }
}