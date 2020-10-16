<?php
namespace common\config;

use MongoClient;
use MongoCollection;

class Database
{
    public $conn;

    public function serverName(){
        return 'mongodb://alanchin007:0yuec7bhj41r1Fzd6KMXtRGmu@127.0.0.1:27017/app_data';
    }

    public function connectServerPhp5($server){ //Legacy php5 class
        $connection = new MongoClient($server);
        if (!class_exists('Mongo'))
        {
            exit('MongoDB is NOT Installed');
        }
        $app_data = $connection->selectDB('app_data');
        return $app_data;
    }

    public function getCollectionPhp5($app_data,$table_name){
        $collection = new MongoCollection($app_data, $table_name);
        return $collection;
    }

//    public function connectServer($server){ //Php 7 clas
//        $connection = new MongoDB\Driver\Manager($server);
//        return $connection;
//    }

    public function checklogin()
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
            echo "<script>window.location='login.php'</script>";
        }
    }

    public function getNext_users_Sequence($name) // 'users' for User table
    {
        $server = "mongodb://alanchin007:0yuec7bhj41r1Fzd6KMXtRGmu@127.0.0.1:27017/app_data";
        $m = new MongoClient($server);
        $db = $m->app_data;
        $collection = $db->login;
        $result = $collection->findAndModify(
            ['_id' => $name],
            ['$inc' => ['seq' => 1]],
            ['seq' => true],
            ['new' => true, 'upsert' => true]
        );
        if (isset($result['seq'])) {
            return $result['seq'];
        } else {
            return false;
        }
    }

    public function generateSecurityToken($length = 50)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . time();
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
?>