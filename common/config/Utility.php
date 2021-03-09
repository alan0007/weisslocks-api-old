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

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    function calculateDistanceDifference($unlock_radius,$distance_in_meter){
        $result = $unlock_radius - $distance_in_meter;
        return $result;
    }

    function compareDistance($difference){
        if ( $difference >= 0 ){
            return true;
        }
        else{
            return false;
        }
    }

    function checkIgnoreGeoFencing($geo_fencing){
        if ($geo_fencing === true){
            return false;
        }
        else{
            return true;
        }
    }

    function checkRequireGeoFencing($geo_fencing){
        if ($geo_fencing === true){
            return true;
        }
        else{
            return false;
        }
    }

    function compareGeoFencingDistance($geo_fencing,$difference){
        if ( $difference >= 0 ){
            return true;
        }
        else{
            if( $geo_fencing === false){
                return true;
            }
            else{
                return false;
            }
        }
    }

    function checkValidUserInCompany($user_id,$users_in_company){
        if(in_array( (int)$user_id,$users_in_company) ){
            $result['valid'] = true;
            $result['message']  = 'Valid User in Company ID';
        }
        else{
            $result['valid'] = false;
            $result['message']  = 'Invalid User in Company ID';
        }
        return $result;
    }

    function returnApprovalSettingText($admin_approved,$admin_approved_by,$admin_rejected){
        if ($admin_approved === true){
            return "approved";
        }
        if ($admin_approved === false && $admin_approved_by == 0){
            return "pending";
        }
        if ($admin_rejected === true){
            return "rejected";
        }
    }

    function daysOfWeekAllowedInteger($day,$day_list){
        $day = (int)$day;
        if( in_array($day,$day_list) ){
            return true;
        }else{
            return false;
        }
    }

    function daysOfWeekAllowedString($day,$day_list){
        $day = (string)$day;
        if( in_array($day,$day_list) ){
            return true;
        }else{
            return false;
        }
    }

    function checkApprovalSetting($input){
        if ($input === true){
            return true;
        }else{
            return false;
        }
    }

    function hasActiveApproval($approval_list){
        if (count($approval_list) >0){
            return true;
        }
        else{
            return false;
        }
    }

    function indicatorForNormalAccessWithoutSpecialAccess(
        $require_active_normal_access,$has_active_normal_access,
        $require_active_second_approval,$has_active_second_approval,
        $require_geo_fencing,$within_geo_fencing_distance
    ){
        $result['control_type'] = 'normal_access_with_approval';
        // Set all to false
        $result['show_icon_require_approval_for_normal_access'] = false;
        $result['show_icon_require_approval_for_second_approval'] = false;
        $result['show_icon_approved_normal_access'] = false;
        $result['show_icon_approved_second_approval'] = false;
        $result['show_icon_approved_lock_out_of_distance'] = false;
        $result['show_icon_can_request_second_approval'] = false;
        $result['show_icon_lock_red'] = false;
        $result['show_icon_lock_green'] = false;
        $result['show_icon_require_geo_fencing'] = false;
        $result['ignore_geo_fencing'] = true;
        $result['unlock_with_geo_fencing'] = false;
        $result['open_lock'] = false;

        // Geo Fencing
        if( $require_geo_fencing === true) {
            $result['ignore_geo_fencing'] = false;
            $result['show_icon_require_geo_fencing'] = true;
            // If can unlock under geo fencing
            if ($within_geo_fencing_distance === true) {
                $result['unlock_with_geo_fencing'] = true;
            }
        }
        else{
            $result['ignore_geo_fencing'] = true;
            $result['show_icon_require_geo_fencing'] = false;
            $result['unlock_with_geo_fencing'] = true;
        }

        if ($require_active_normal_access === true){
            $result['show_icon_require_approval_for_normal_access'] = true;
            // If there is active normal access
            if ($has_active_normal_access === true){
                $result['show_icon_approved_normal_access'] = true;
                $result['show_icon_lock_red'] = true;
                // If can unlock with geo fencing
                if ( $result['unlock_with_geo_fencing'] === true ){
                    $result['show_icon_can_request_second_approval'] = true;
                    // If require second approval
                    if ($require_active_second_approval === true) {
                        $result['show_icon_require_approval_for_second_approval'] = true;
                        // If there is active second approval
                        if ($has_active_second_approval === true) {
                            $result['show_icon_approved_second_approval'] = true;
                            $result['show_icon_lock_red'] = false;
                            $result['show_icon_lock_green'] = true; // Can Open Lock
                            $result['open_lock'] = true; // Can Open Lock
                        } else { // If Don't have is active second approval
                            $result['show_icon_lock_red'] = true;
                        }
                    }
                    else{ // If DON'T require second approval
                        $result['show_icon_lock_red'] = false;
                        $result['show_icon_lock_green'] = true;
                        $result['open_lock'] = true;
                    }
                }
            }
            else{ // If Dont have active normal access
                $result['show_icon_lock_red'] = true;
            }
        }
        else{ // If DON'T require normal access, DON'T require second approval automatically
            if ( $result['unlock_with_geo_fencing'] === true ){
                $result['show_icon_lock_green'] = true; // Can Open Lock
                $result['open_lock'] = true; // Can Open Lock
            }
        }

        return $result;
    }

    
    function indicatorForAccessControlPermission(
        $date_now,$time_now,
        $active_access_control_date_from,$active_access_control_date_to,
        $active_access_control_time_from,$active_access_control_time_to,
        $day_of_week_now,$allowed_days,
        $require_geo_fencing,$within_geo_fencing_distance
    ){
        $result['control_type'] = 'access_control_without_approval';
        // Set all to false        
        $result['show_icon_require_approval_for_normal_access'] = false;
        $result['show_icon_require_approval_for_second_approval'] = false;
        $result['show_icon_approved_normal_access'] = false;
        $result['show_icon_approved_second_approval'] = false;
        $result['show_icon_approved_lock_out_of_distance'] = false;
        $result['show_icon_can_request_second_approval'] = false;
        $result['show_icon_lock_red'] = false;
        $result['show_icon_lock_green'] = false;
        $result['show_icon_require_geo_fencing'] = false;
        $result['ignore_geo_fencing'] = true;
        $result['unlock_with_geo_fencing'] = false;
        $result['open_lock'] = false;
        
        // Date Allowed
        if ( strtotime($date_now) >= strtotime($active_access_control_date_from) &&
            strtotime($date_now) <= strtotime($active_access_control_date_to) ) {
            $result['request_date_allowed'] = true;
        }
        else{
            $result['request_date_allowed'] = false;
        }

        // Time Allowed
        if ( strtotime($time_now) >= strtotime($active_access_control_time_from) &&
            strtotime($time_now) <= strtotime($active_access_control_time_to) ) {
            $result['request_time_allowed'] = true;
        }
        else{
            $result['request_time_allowed'] = false;
        }
        // Allowed Day
        $result['request_day_allowed'] = true;
        if ( in_array( (int)$day_of_week_now,$allowed_days ) ){
            $result['request_day_allowed'] = true;
        }
        else{
            $result['request_day_allowed'] = false;
        }

        // Geo Fencing
        if( $require_geo_fencing === true) {
            $result['ignore_geo_fencing'] = false;
            $result['show_icon_require_geo_fencing'] = true;
            // If can unlock under geo fencing
            if ($within_geo_fencing_distance === true) {
                $result['unlock_with_geo_fencing'] = true;
            }
        }
        else{
            $result['ignore_geo_fencing'] = true;
            $result['show_icon_require_geo_fencing'] = false;
            $result['unlock_with_geo_fencing'] = true;
        }

        // Can Open Lock
        if ($result['request_day_allowed'] === true &&
            $result['request_date_allowed'] === true &&
            $result['request_time_allowed'] === true &&
            $result['unlock_with_geo_fencing'] === true )
        {
            $result['approved_but_cannot_open_lock'] = false;
            $result['approval_request_required'] = false;
            $result['show_icon_request'] = false;
            $result['show_icon_approved_lock_out_of_distance'] = false;
            $result['show_icon_lock_red'] = false;
            $result['show_icon_lock_green'] = true;
            $result['open_lock'] = true;
        }
        else{
            $result['approval_request_required'] = false;
            $result['show_icon_request'] = false;
            $result['show_icon_lock_red'] = true;
            $result['show_icon_lock_green'] = false;
            $result['open_lock'] = false;

            if ($result['request_date_allowed'] === true &&
                $result['request_time_allowed'] === true &&
                $result['request_day_allowed'] === true &&
                $result['unlock_with_geo_fencing'] === false)
            {
                $result['show_icon_approved_lock_out_of_distance'] = true;
            }
            else{
                $result['show_icon_approved_lock_out_of_distance'] = false;
            }
        }

        return $result;
    }

    function indicatorForNormalAccessWithSpecialAccess($has_active_normal_access){
        $result = $has_active_normal_access;
        return $result;
    }
}