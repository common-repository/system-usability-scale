<?php

function suscale_get_grade($suscale_score) {
    if ($suscale_score >= 92.5) {
        return "A";
    }
    else if (82.5 <= $suscale_score && $suscale_score < 92.5) {
        return "B";
    }
    else if (70 <= $suscale_score && $suscale_score < 82.5) {
        return "C";
    }
    else if (52.5 <= $suscale_score && $suscale_score < 70) {
        return "D";
    }
    else if (37.5 <= $suscale_score && $suscale_score < 52.5) {
        return "E";
    }
    else if (20 <= $suscale_score && $suscale_score < 37.5) {
        return "F";
    }
    else return "G";
}

function suscale_get_info($suscale_score) {
    $final_string = " - Best: A / Worst: G";
    $string = "Grade " . suscale_get_grade($suscale_score) . " system" . $final_string;
    return $string;
}

function suscale_sanitize_validate_questionnaire($json_received, $userID, $systemID){
    if(is_numeric($userID) && is_numeric($systemID)){ 
        $return_data = array();
        $return_data['json_sanitized'] = array();
        $actual_systems = get_option('suscale_pages');
        if(in_array($systemID, $actual_systems)){                       // Check for correct systemID
            if($userID != 0){                                       
                if(get_user_by('id', $userID)){                         // Check for correct userID
                    $return_data['userID_sanitized'] = (int)$userID;
                }
                else{
                    return "Invalid User ID: {$userID}";                // UserID doesn't correspond to any user in wp
                }                                              
            }else{
                $return_data['userID_sanitized'] = (int)0;
            }
            $return_data['systemID_sanitized'] = (int)$systemID;
            foreach ($json_received as $key => $value) {
                if( is_numeric($value)){
                    if((int)$value > 5){
                        $return_data['json_sanitized'][$key] = (int)5;
                    }
                    else if((int)$value < 1){
                        $return_data['json_sanitized'][$key] = (int)1;
                    }
                    else {
                        $return_data['json_sanitized'][$key] = (int)$value;
                    }
                }
                else{
                    return "Question ({$key}) is not numeric.";
                }

            }
            return $return_data;
        }else{
            return "Invalid system ID: {$systemID}";                    // SystemID is not present in $actual_systems
        }
    }
    return false;
}

function suscale_sanitize_validate_queue_names($array){
    $temp = array();
    foreach ($array as $key => $value) {
        $temp[$key] = preg_replace( '/[^A-Za-z0-9_\-]/', '', $value);
    }
    return $temp;
}

function suscale_sanitize_search($name, $macro, $only_unregistered){
    if($only_unregistered === "false" || $only_unregistered === "true"){
        $data = array();
        $data["name"]               = sanitize_user($name);
        $data["macro"]              = sanitize_text_field($macro);
        $data["only_unregistered"]  = $only_unregistered;
        return $data;
    }
    return false;
}

function suscale_remove_shortcode($text_of_page){
    $new_text =  preg_replace('/\[suscale[^\]]*\]/', '', $text_of_page); 
    return $new_text;
}

function suscale_sanitize_delete($sys_num, $also_delete_sus){
    $return_data = array();
    $actual_pages = get_option( 'suscale_pages' );
    if(is_numeric($sys_num)){
        if(in_array((int)$sys_num, $actual_pages)){
            $return_data["sys_num"] = (int)$sys_num;
            if(sanitize_text_field($also_delete_sus) === "true"){
                $return_data["also_delete_sus"] = true;
            }
            else {
                $return_data["also_delete_sus"] = false;
            }
            return $return_data;   
        }
        else{
            return "Error: This post (id {$sys_num}) doesn't have a SUS button";
        }
    }else{
        return "Error in systemID: " . $sys_num;
    }
}

function suscale_esc_js_array($array){
    $temp = $array;
    foreach ($array as $key => $value) {
        if(is_array($value)){
            foreach ($value as $key_inside => $value_inside) {
                $temp[$key][$key_inside] = esc_js($value_inside);
            }
        }else{
            $temp[esc_js($key)] = esc_js($value);
        }
    }
    return $temp;
}
