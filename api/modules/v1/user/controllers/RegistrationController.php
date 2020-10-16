<?php
namespace api\modules\v1\user\controllers;

//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/User.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/Company.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/config/Database.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/composer/vendor/autoload.php';

use common\config\Database;
use common\v1\models\User;
use common\v1\models\Company;
use MongoCollection;


class RegistrationController
{
    private $collection;

    // constructor with $db as database connection
    public function __construct()
    {
        $User = new User();
        $table_name = $User->tableName();
//        $this->collection = new MongoCollection($app_data, $table_name);
    }

    public function actionGetUserCollection(){
        $User = new User;
        $Database = new Database();
        $server_name = $Database->serverName();
        $app_data = $Database->connectServerPhp5($server_name);
        $collection = $Database->getCollectionPhp5($app_data,$User->tableName());
        return $collection;
    }

    public function actionGetCompanyCollection(){
        $Company = new Company();
        $Database = new Database();
        $server_name = $Database->serverName();
        $app_data = $Database->connectServerPhp5($server_name);
        $collection = $Database->getCollectionPhp5($app_data,$Company->tableName());
        return $collection;
    }

    public function actionRegister(){

    }

    public function actionUploadImage(){

    }

    public function actionViewRegistration(){

    }

    public function actionApproveRegistration(){

    }

    public function actionRejectRegistration(){

    }
}