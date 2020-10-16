<?php


namespace admin\modules\v1\accessControl\controllers;

require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/AccessControl.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/User.php';
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/v1/models/Company.php';
//require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/common/config/Database.php';

// Required if your environment does not handle autoloading
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/composer/vendor/autoload.php';

use common\config\Database;
use common\v1\models\User;
use common\v1\models\Company;
use MongoCollection;


class AccessControlController
{

}