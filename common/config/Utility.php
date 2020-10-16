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
}