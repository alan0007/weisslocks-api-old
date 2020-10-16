<?php
namespace api\modules\v1\user\controllers;

//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/User.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/Company.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/config/Database.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/composer/vendor/autoload.php';

use common\config\Database;
use common\v1\models\User;
//use common\v1\models\Company;
use MongoCollection;


class LoginController
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

    public function actionGetUserCollection(){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        return $collection;
    }

    public function actionLogin($collection,$Login_Query){
        $cursor = $collection->findOne( $Login_Query );
        return $cursor;
    }

    public function actionUpdateLastLogin($user_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('user_id'=>(int)$user_id );
        $Security_token = $this->Database->generateSecurityToken();

        $collection->update( $criteria ,array('$set' => array(
            'last_login'  => date('H:i A,d F Y'),
//            'token' => $Security_token
        )));

        if ($collection != null){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function actionUpdateUdid($user_id,$role,$udid_ios){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('user_id'=>(int)$user_id );

        if(in_array($role,array(2,3,4,5,6,7,8))) {//Default is 2,3
            if ($udid_ios == '') {
                $collection->update($criteria, array('$set' => array(
                    'UDID_IOS' => $_REQUEST['UDID_IOS'],
                )));
                if ($collection != null) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
            else {
                return FALSE;
            }
        }
        else {
            return FALSE;
        }
    }
}