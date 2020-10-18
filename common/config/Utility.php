<?php

namespace common\config;


class Utility
{
    function generateRandomString($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    function dayOfWeek($day_number){
        switch($day_number){
            case "1":
                return "monday";
            case "2":
                return "tuesday";
            case "3":
                return "tuesday";
            case "4":
                return "wednesday";
            case "5":
                return "friday";
            case "6":
                return "saturday";
            case "7":
                return "sunday";
        }
    }

    // After registration, assign according to company_id
    public function actionDefaultLockGroupBasedOnCompanyId($company_id){
        switch ((int)$company_id) {
            case 12: // SP
                return 2;
            case 25: // IOS
                return 116;
            case 27: // PA
                return 115;
        }
    }

    // After registration, assign according to company_id
    public function actionAssignKeyGroupBasedOnCompanyId($company_id){
        switch ((int)$company_id) {
            case 12: // SP
                return 103;
            case 25: // IOS
                return 105;
            case 27: // PA
                return 104;
        }
    }

    // After registration, assign according to company_id
    public function actionAssignAccessControlBasedOnCompanyId($company_id){
        switch ((int)$company_id) {
            case 12: // SP
                return 88;
            case 25: // IOS
                return 90;
            case 27: // PA
                return 89;
        }
    }
}