<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Database.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Constant.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Utility.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockBluetoothController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use common\config\Constant;
use common\config\Database;
use common\config\Utility;

$response = array();

//Open Locks under Permit
// Updated 2020-03-04
if( isset($_REQUEST['lock_id']) &&
    isset($_REQUEST['latitude']) && isset($_REQUEST['longitude']) )
{
	$lock_id = $_REQUEST['lock_id'];

    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';

    $error_message = '';

    $datetime = date("c");
    $date_now = date("Y-m-d");
    $time_now = date("H:i:s");
    $day_of_week_now = date('w');
//    $response['datetime_now'] = $datetime;
//    $response['date_now'] = $date_now;
//    $response['time_now'] = $time_now;
//    $response['day_of_week_now'] = $day_of_week_now;

    $latitude_from = (float) $_REQUEST['latitude'];
    $longitude_from = (float) $_REQUEST['longitude'];
    $response['data']['latitude'] = $latitude_from;
    $response['data']['longitude'] = $longitude_from;
    $latitude_to = 0.0;
    $longitude_to = 0.0;
    $unlock_radius = 0.0;
    $geo_fencing = NULL;

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $Utility = new Utility();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);

    // Get Lock
    $lock_details = $LockBluetoothController->actionGetOneById($lock_id);
    if(isset($lock_details)){
        unset($lock_details['_id']);
        $response['data']['lock']['geo_fencing'] = $lock_details['geo_fencing'];
        $response['data']['lock']['latitude'] = $lock_details['latitude'];
        $response['data']['lock']['longitude'] = $lock_details['longitude'];
        $response['data']['lock']['unlock_radius'] = $lock_details['unlock_radius'];
        $unlock_radius = $lock_details['unlock_radius'];
        $geo_fencing = $lock_details['geo_fencing'];
    }
    else{
        $response['error'] = 'No lock found';
    }
    $latitude_to = $lock_details['latitude'];
    $longitude_to = $lock_details['longitude'];

    $distance_in_meter = $Utility->haversineGreatCircleDistance($latitude_from,$longitude_from,$latitude_to,$longitude_to);
    if(isset($distance_in_meter)){
        $response['status'] = 'true';
        unset($response['error']);
        $response['data']['distance'] = $distance_in_meter;

        // Distance Comparison
        $difference = $unlock_radius - $distance_in_meter;
        $response['data']['difference_with_unlock_radius'] = $difference;
        if ( $difference >= 0 ){
            $response['data']['ignore_geo_fencing'] = FALSE;
            $response['data']['unlock_with_geo_fencing'] = TRUE;
        }
        else{
            if( $geo_fencing === FALSE){
                $response['data']['ignore_geo_fencing'] = TRUE;
                $response['data']['unlock_with_geo_fencing'] = TRUE;
            }
            else{
                $response['data']['unlock_with_geo_fencing'] = FALSE;
            }
        }

    }
    else{
        $response['error'] = 'Distance calculation error';
    }
}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
