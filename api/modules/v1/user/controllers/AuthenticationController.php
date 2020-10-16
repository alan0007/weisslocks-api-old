<?php
namespace api\modules\v1\user\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/User.php');

use common\config\Database;
use common\v1\models\User;

class AuthenticationController
{
    private $collection;
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new User();
        $this->table_name = $this->Model->tableName();
        $this->Database = $db;
        $this->server_name = $this->Database->serverName();
        $this->app_data = $this->Database->connectServerPhp5($this->server_name);
    }

    public function actionGet(){

    }

}