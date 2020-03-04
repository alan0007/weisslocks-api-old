<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//Old Server
//$server = "mongodb://mahesh.archirayan:HJHKICKcLIpJ9c2020@127.0.0.1:27017/app_data";
$server = "mongodb://alanchin007:0yuec7bhj41r1Fzd6KMXtRGmu@127.0.0.1:27017/app_data";

//Legacy php5 class
$Connection = new MongoClient( $server );
//Php 7 class
//$Connection = new MongoDB\Driver\Manager( $server );
//$Connection = new MongoDB\Driver\Manager();
//$Connection = new MongoDB\Driver\Manager("mongodb://alanchin007:0yuec7bhj41r1Fzd6KMXtRGmu@127.0.0.1:27017/app_data");
//$Connection = new MongoDB\Client("mongodb://alanchin007:0yuec7bhj41r1Fzd6KMXtRGmu@127.0.0.1:27017/app_data")

//$Connection->connected ? "Connected successfully" : "Connection failed";

date_default_timezone_set('Asia/Singapore');
session_start();
define("DATA_PER_PAGE",10);

define("SITE_URL","http://app.weisslocks.com/");
//Old Firebase Server API KEY
//define( 'API_ACCESS_KEY', 'AAAAVOkhDvo:APA91bGRILek2Q_yUzhTAhgaoOaDPeuhpLrHJEBL58y66xgdGdai7U6MCpqwWOV231Qk98Wj6p_oJpbkua9omdqeLvaKJVox_elZHK84tMZbmuqiOCOUixTcHWyQkYNNIF2AmlIg1Pvh' );
define( 'API_ACCESS_KEY', 'AAAAZe151S4:APA91bHe2hJxJ5z2Do1tEAK0uLOcRFwWOLSLx21nGCqVSOZ_sFhV8UCs74ZwV1uSY5x_v0bC7OX8PjALjZD-Sjd60-uv7u2q9fuWnPKKi_p_8UrYbIZki8g0cM8hbMPHFp-zRcZzYoB9' );
define( 'API_ACCESS_KEY_LEGACY', 'AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I' );



if (!class_exists('Mongo'))
{
   exit('MongoDB is NOT Installed');
}

// $Connection = new MongoClient();
$app_data = $Connection->selectDB('app_data');


function checklogin(){
    if(empty($_SESSION['user_id']) || empty($_SESSION['username'])){
        echo "<script>window.location='login.php'</script>";
    }
}

function getNext_users_Sequence($name) // 'users' for User table
    {
		$server = "mongodb://alanchin007:0yuec7bhj41r1Fzd6KMXtRGmu@127.0.0.1:27017/app_data";
        $m = new MongoClient($server);
        $db = $m->app_data;
        $collection = $db->login;
        $result =  $collection->findAndModify(
            ['_id' => $name],
            ['$inc' => ['seq' => 1]],
            ['seq' => true],
            ['new' => true, 'upsert' => true]
        );
        if (isset($result['seq']))
        {
            return $result['seq'];
        }
        else
        {
            return false;
        }
}

function Security_token($length = 50)
    {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . time();
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) 
		{
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
?>