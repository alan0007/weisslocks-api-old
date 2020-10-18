<?php
namespace api\modules\v1\user\controllers;

include(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/User.php');

use common\config\Database;
use common\v1\models\User;

class UserController
{
    public $modelClass = 'common\v1\models\User';
    public $Model;
    public $Database;
    private $server_name;
    private $app_data;
    private $table_name;
    private $collection;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->Model = new User();
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

    public function actionGetById($user_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('user_id'=>(int)$user_id));
        return $result; // return array
    }

    public function actionGetOneById($user_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('user_id'=>(int)$user_id));
        return $result; // return array
    }

    public function actionGetOneByUsername($username){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->findOne(array('username'=>(string)$username));
        return $result; // return array
    }

    public function actionGetOneByIdAndCompanyId($user_id,$company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (int)$user_id ),
                array( 'company_id' => (int)$company_id )
            )
        );
        $result = $collection->findOne($criteria);
        return $result; // return array
    }

    public function actionGetOneByUsernameAndCompanyId($username,$company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'username'=> (string)$username ),
                array( 'company_id' => (int)$company_id )
            )
        );
        $result = $collection->findOne($criteria);
        return $result; // return array
    }

    public function actionGetOneByEmail($email){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('email'=>$email));
        return $result; // return array
    }

    public function actionGetByCompanyId($company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $result = $collection->find(array('company_id'=>(int)$company_id));
        return $result; // return object
    }

    public function actionLogin($collection,$Login_Query){
        $cursor = $collection->findOne( $Login_Query );
        return $cursor; // return array
    }

    public function actionLoginUsingUsernameAndPassword($username,$password){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'username'=> $username ),
                array( 'password' => md5($password) )
            )
        );
        $result = $collection->findOne($criteria);
        return $result; // return array
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

    public function actionUpdatePassword($user_id,$password){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('user_id'=>(int)$user_id );

        $collection->update( $criteria ,array('$set' => array(
            'password'  => md5($password)
        )));

        if ($collection != null){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function actionInsert($post)
    {
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        if ($collection->insert($post)){
            return TRUE;
        }
        else{
            return FALSE;
        }

    }

    public function actionVerifyPhoneNumber($country_code, $phone_number){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'country_code'=> $country_code ),
                array( 'phone_number' => $phone_number )
            )
        );
        $result = $collection->findOne($criteria);
        return $result; // return array
    }

    public function actionUpdateApproval($user_id,$approval){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array('user_id'=>(int)$user_id );

        $collection->update(
            $criteria ,array(
                '$set' => array(
                    'approved' =>(int)$approval,
                    'token' => time().rand()
                )
            )
        );

        if ($collection != null){
            return TRUE;
        }
        else{
            return FALSE;
        }
    }

    public function actionGetAdminOfCompany($company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'company_id'=> (int)$company_id ),
                array( 'role' => 3 )
            )
        );
        $result = $collection->find($criteria);
        return $result; // return object
    }

    public function actionIsAdmin($user_id,$company_id){
        $collection = $this->Database->getCollectionPhp5($this->app_data,$this->Model->tableName());
        $criteria = array(
            '$and' => array(
                array( 'user_id'=> (int)$user_id ),
                array( 'company_id'=> (int)$company_id ),
                array( 'role' => 3 )
            )
        );
        $result = $collection->findOne($criteria);

        if (isset($result)){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function actionVerifyApproved($approved){
        if ((int)$approved == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }

}