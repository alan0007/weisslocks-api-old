<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(dirname(dirname(__FILE__))).'/configurations/config.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Database.php';
require dirname(dirname(dirname(__FILE__))).'/common/config/Constant.php';
require dirname(dirname(__FILE__)).'/modules/v1/user/controllers/UserController.php';
require dirname(dirname(__FILE__)).'/modules/v1/organization/controllers/CompanyController.php';
require dirname(dirname(__FILE__)).'/modules/v1/log/controllers/LogUserActivityController.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(__FILE__))).'/composer/vendor/autoload.php';

use api\modules\v1\user\controllers\UserController;
use api\modules\v1\organization\controllers\CompanyController;
use api\modules\v1\log\controllers\LogUserActivityController;
use common\config\Constant;
use common\config\Database;

$response = array();

if(isset($_REQUEST['admin_user_id']) && isset($_REQUEST['company_id']) )
{
    $response['status'] = 'false';
    $response['error'] = 'Invalid Parameters';
    $datetime = date("c");

    // Start
    $Database = new Database();
    $Constant = new Constant();
    $UserController = new UserController($Database);
    $CompanyController = new CompanyController($Database);
    $LogUserActivityController = new LogUserActivityController($Database);

    // Verify company id
    $company_found = $CompanyController->actionGetOneById($_REQUEST['company_id']);
    if(isset($company_found))
    {
        $company_id = $company_found['company_ID'];
        $response['data']['company_check']  = 'Valid Company ID';
    }
    else
    {
        $response['status'] = 'false';
        $response['error'] = 'Invalid Company ID';
        exit(json_encode($response, JSON_PRETTY_PRINT));
    }

    $admin_details = $UserController->actionGetOneById($_REQUEST['admin_user_id']);
    $response['data']['company_id']  = $admin_details['company_id'];
//    $response['data']['admin_role']  = $admin_details['role'];
    $admin_role = (int)$admin_details['role'];

    // Check is admin
    if( (int)$admin_details['company_id'] == (int)$_REQUEST['company_id'] && in_array($admin_role,array(2,3))){
        $is_admin = TRUE;
    }
    else{
        $is_admin = FALSE;
    }
    $response['data']['is_admin'] = $is_admin;

    if ($is_admin === TRUE ){
        $response['data']['can_manage'] = TRUE;
        $user_list = $UserController->actionGetByCompanyId($_REQUEST['company_id']);
        if($user_list->count() > 0) {
            foreach ($user_list as $uu) {
                if(in_array($uu['role'],array(4,5,6,7)))
                {
                    $start_date = new DateTime( date('c',strtotime( $uu['registered_time'] )) );
                    $since_start = $start_date->diff(new DateTime( date('c') ));

                    /*echo date('d F Y, H:i') . '---' . $uu['registered_time'] . '-----';
                    echo $since_start->days.' days total -- ';
                    echo $since_start->y.' years -- ';
                    echo $since_start->m.' months -- ';
                    echo $since_start->d.' days -- ';
                    echo $since_start->h.' hours -- ';
                    echo $since_start->i.' minutes -- ';
                    echo $since_start->s.' seconds---';
                    echo '<br>';*/

                    if($since_start->i <= 10 && $since_start->d == 0 && $since_start->h == 0)
                    {
                        $uu['registered_since'] = 'NOW';
                        //echo 'Now<br><br>';
                    }
                    else if($since_start->d == 0 && $since_start->h >= 0)
                    {
                        $uu['registered_since'] = date('H:i', strtotime( $uu['registered_time']));
                        //echo 'Before 1 day<br><br>';
                    }
                    else if($since_start->days == 1)
                    {
                        $uu['registered_since'] = 'Yesterday';
                        //echo 'Yesterday<br><br>';
                    }
                    else if($since_start->days >= 2)
                    {
                        $uu['registered_since'] = date('Y-m-d', strtotime( $uu['registered_time']));
                        //echo  date('d/m', strtotime( $uu['registered_time'])) . ' <br><br>';
                    }
                    else {
                        $uu['duration'] = '';
                    }

                    unset($uu['_id']);
                    unset($uu['password']);
                    unset($uu['UDID_IOS']);
                    unset($uu['token']);
                    unset($uu['device_id']);

                    unset($response['error']);
                    $response['status'] = 'true';

                    $response['data']['users'][] = $uu;
                }
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);