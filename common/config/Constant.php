<?php

namespace common\config;


class Constant
{
    public function getDefaultConstant(){
        define('SITE_URL','http://app.weisslocks.com/');
        //Old Firebase Server API KEY
        //define( 'API_ACCESS_KEY', 'AAAAVOkhDvo:APA91bGRILek2Q_yUzhTAhgaoOaDPeuhpLrHJEBL58y66xgdGdai7U6MCpqwWOV231Qk98Wj6p_oJpbkua9omdqeLvaKJVox_elZHK84tMZbmuqiOCOUixTcHWyQkYNNIF2AmlIg1Pvh' );
        define( 'API_ACCESS_KEY', 'AAAAZe151S4:APA91bHe2hJxJ5z2Do1tEAK0uLOcRFwWOLSLx21nGCqVSOZ_sFhV8UCs74ZwV1uSY5x_v0bC7OX8PjALjZD-Sjd60-uv7u2q9fuWnPKKi_p_8UrYbIZki8g0cM8hbMPHFp-zRcZzYoB9' );
        define( 'API_ACCESS_KEY_LEGACY', 'AIzaSyA3LvtSt7FkqyyQlDKUZRjePWYGegmy56I' );
        define('DATA_PER_PAGE', 10);
        define('UPLOAD_PATH', '/uploads/');
    }

    public function defaultLocksServerUrl(){
        return 'https://locks.evs.com.sg/IntegAPI/api/v1/'; //v1 API
    }

    public function getDatabaseName(){
        return 'app_data';
    }

}