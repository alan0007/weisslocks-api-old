<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(dirname(dirname(dirname(__FILE__)))).'/configurations/config.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Database.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/common/config/Constant.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/lock/controllers/LockBluetoothController.php';
require dirname(dirname(dirname(__FILE__))).'/modules/v1/accessControl/controllers/AccessControlController.php';

include(dirname(dirname(dirname(__FILE__))).'/controller/NotificationController.php');
//include(dirname(__FILE__).'/controller/SmsController.php');

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(__FILE__)))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\lock\controllers\LockBluetoothController;
use api\modules\v1\accessControl\controllers\AccessControlController;
use common\config\Constant;
use common\config\Database;

$response = array();

//List Locks under Permit
// Updated 2020-03-04
if(isset($_REQUEST['company_id']) &&
    isset($_REQUEST['user_id']) &&
    isset($_REQUEST['lock_name'])
)
{
    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LockBluetoothController = new LockBluetoothController($Database);
    $AccessControlController = new AccessControlController($Database);

    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $com['company_id'] = $company_found['company_ID']; //Put in company details array
        $response['data']['company_check']  = 'Valid Company ID';
//        $response['data']['company_user_id']  = $company_found['user_id'];
    }
    else
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Company ID';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }

    $user_details = $UserController->actionGetOneByIdAndCompanyId($_REQUEST['user_id'],$_REQUEST['company_id']);

    if(isset($user_details['user_id'])) {
        $response['data']['user_check'] = 'Valid User ID';
        if ($user_details['role'] == 1) {
            $response['data']['error'] = 'Superadmin not allowed';
            exit(json_encode($response, JSON_PRETTY_PRINT));
        } else {
            // Valid user in company
//            $users_in_company = json_decode($company_found['user_id']);
            $user_list_in_company = str_replace("[", "", $company_found['user_id']);
            $user_list_in_company = str_replace("]", "", $user_list_in_company);
            $users_in_company = explode(",", $user_list_in_company);
            if (in_array((int)$user_details['user_id'], $users_in_company)) {
                $com['company_ID'] = $company_found['company_ID'];
                $response['data']['user_in_company_check'] = 'Valid User in Company ID';
            } else {
                $response['data']['user_in_company_check'] = 'Invalid User in Company ID';
                exit(json_encode($response, JSON_PRETTY_PRINT));
            }

            $user_id = $user_details['user_id'];
            $username = $user_details['username'];
            $lock_group_id = $user_details['lock_group_id'];

            $com['user_id'] = (int)$user_id; //Put in company details array
            //$com['company_id'] = (int)$user['company_id'];

            if ($user_details['role'] == 3) {
                $response['data']['role'] = 'admin';
            } else if ($user_details['role'] == 4) {
                $response['data']['role'] = 'staff';
            } else if ($user_details['role'] == 5) {
                $response['data']['role'] = 'contractor';
            }

            //Added by Alan 2018-02-25
            //----------------
            // Access Control (KeyLockGroup)
            //----------------
            $access_control = $AccessControlController->actionGetByCompanyId($_REQUEST['company_id']);

            if ($access_control->count() > 0) {
                //$response['status'] = 'true';
                $i = 0;
                foreach ($access_control as $accessControl) {
                    //if(in_array($com['user_id'],$accessControl['user_id'])) //If User company and User ID is correct
                    $response['data']['lock_group_count'] = count($lock_group_id);
                    if (is_array($lock_group_id)) {
                        if (in_array((int)$accessControl['lock_group_id'], $lock_group_id)) {//If have keygroup
                            unset($accessControl['_id']);
                            $access_date_from = $accessControl['date_from'];
                            $access_date_to = $accessControl['date_to'];
                            $access_time_from_hh = $accessControl['time_from_hh'];
                            $access_time_from_mm = $accessControl['time_from_mm'];
                            $access_time_to_hh = $accessControl['time_to_hh'];
                            $access_time_to_mm = $accessControl['time_to_mm'];
                            $lock_group_id = $accessControl['lock_group_id'];

                            //$response['access_control'][] = $accessControl;
//                            $response['data']['access_control'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
//                            $response['data']['access_control'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
//                            $response['data']['access_control'][$i]['pairing_name'] = $accessControl['pairing_name'];
//                            $response['data']['access_control'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
//                            $response['data']['access_control'][$i]['key_group_id'] = $accessControl['key_group_id'];
//                            $response['data']['access_control'][$i]['company_id'] = $accessControl['company_id'];
//                            $response['data']['access_control'][$i]['users'] = $accessControl['users'];
//                            $response['data']['access_control'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
//                            $response['data']['access_control'][$i]['date_from'] = $accessControl['date_from'];
//                            $response['data']['access_control'][$i]['date_to'] = $accessControl['date_to'];
//                            $response['data']['access_control'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
//                            $response['data']['access_control'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
//                            $response['data']['access_control'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
//                            $response['data']['access_control'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
//                            $response['data']['access_control'][$i]['lat'] = $accessControl['lat'];
//                            $response['data']['access_control'][$i]['long'] = $accessControl['long'];
//                            $response['data']['access_control'][$i]['radius_in_m'] = $accessControl['radius'];
//                            $response['data']['access_control'][$i]['added_by'] = $accessControl['added_by'];
//                            $response['data']['access_control'][$i]['allowed_days'] = $accessControl['allowed_days'];

                            $response['data']['radius_in_meter'] = $accessControl['radius'];
                            $allowed_days = $accessControl['allowed_days'];

                            //unset($accessControl['keyLockGroup_ID']);
                            //unset($accessControl['pairing_name']);

                            $i++;
                        }
                    } else {
                        if ($lock_group_id == $accessControl['$lock_group_id']) //If User company and User ID is correct
                        {
                            //$response['status'] = 'true';
                            unset($accessControl['_id']);
                            $access_date_from = $accessControl['date_from'];
                            $access_date_to = $accessControl['date_to'];
                            $access_time_from_hh = $accessControl['time_from_hh'];
                            $access_time_from_mm = $accessControl['time_from_mm'];
                            $access_time_to_hh = $accessControl['time_to_hh'];
                            $access_time_to_mm = $accessControl['time_to_mm'];
                            $lock_group_id = $accessControl['lock_group_id'];

                            //$response['access_control'][] = $accessControl;
//                            $response['data']['access_control'][$i]['access_control_ID'] = $accessControl['keyLockGroup_ID'];
//                            $response['data']['access_control'][$i]['keyLockGroup_ID'] = $accessControl['keyLockGroup_ID'];
//                            $response['data']['access_control'][$i]['pairing_name'] = $accessControl['pairing_name'];
//                            $response['data']['access_control'][$i]['lock_group_id'] = $accessControl['lock_group_id'];
//                            $response['data']['access_control'][$i]['key_group_id'] = $accessControl['key_group_id'];
//                            $response['data']['access_control'][$i]['company_id'] = $accessControl['company_id'];
//                            $response['data']['access_control'][$i]['users'] = $accessControl['users'];
//                            $response['data']['access_control'][$i]['key_time_restricted'] = $accessControl['key_time_restricted'];
//                            $response['data']['access_control'][$i]['date_from'] = $accessControl['date_from'];
//                            $response['data']['access_control'][$i]['date_to'] = $accessControl['date_to'];
//                            $response['data']['access_control'][$i]['time_from_hh'] = $accessControl['time_from_hh'];
//                            $response['data']['access_control'][$i]['time_from_mm'] = $accessControl['time_from_mm'];
//                            $response['data']['access_control'][$i]['time_to_hh'] = $accessControl['time_to_hh'];
//                            $response['data']['access_control'][$i]['time_to_mm'] = $accessControl['time_to_mm'];
//                            $response['data']['access_control'][$i]['lat'] = $accessControl['lat'];
//                            $response['data']['access_control'][$i]['long'] = $accessControl['long'];
//                            $response['data']['access_control'][$i]['radius'] = $accessControl['radius'];
//                            $response['data']['access_control'][$i]['added_by'] = $accessControl['added_by'];

                            //unset($accessControl['keyLockGroup_ID']);
                            //unset($accessControl['pairing_name']);
                            $response['data']['radius_in_meter'] = $accessControl['radius'];
                            $allowed_days = $accessControl['allowed_days'];

                            $i++;
                        }
                    }
                }
            }

            $lock_details = $LockBluetoothController->actionGetOneByName($_REQUEST['lock_name']);
            if(isset($lock_details))
            {
                $response['status'] = 'true';
                unset($response['error']);

                unset($lock_details['_id']);
                $response['data']['lock'] = $lock_details;
                $response['data']['allowed_days'] = $allowed_days;
            }
        }
    }

}


header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
