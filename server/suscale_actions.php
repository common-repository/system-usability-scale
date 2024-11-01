<?php

add_action( "wp_ajax_nopriv_suscale_redirect", "suscale_redirect_questionnaire" );
add_action( "wp_ajax_suscale_redirect"       , "suscale_redirect_questionnaire" );
/**
 *  
 *  AJAX : Redirect to questionnaire page after click on suscale_btn
 * */
function suscale_redirect_questionnaire(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_redirect_nonce')){
        die("Unauthorized");
    }  
    echo json_encode(array('redirect_url'=>esc_url($_SERVER['HTTP_ORIGIN']. '/system-usability-scale/'))) ;     
    exit();
}

/**
 * 
 * AJAX : Request of all macros existing
 * */

add_action( "wp_ajax_nopriv_suscale_loadMacros" , "suscale_load_macros" );
add_action( "wp_ajax_suscale_loadMacros"        , "suscale_load_macros" );

function suscale_load_macros() {
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_loadMacros_nonce')){
        die("Unauthorized");
    }  
    global $wpdb;
    $pr = $wpdb->prefix;
    $query_string = "SELECT DISTINCT macroSystem FROM `{$pr}plugin_suscale_systems`";
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $result = $mysqli->query($query_string);
    $response = array();
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $row["systemID"] = esc_js($row["systemID"]);
        $row["name"] = esc_js($row["name"]);
        $row["macroSystem"] = esc_js($row["macroSystem"]);
        array_push($response, $row);
    }
    echo json_encode($response);
    exit();
}

/**
 * AJAX : Select a specific macro and load it into Results page
 * */

add_action( "wp_ajax_nopriv_suscale_loadSpecificMacro" , "suscale_load_specific_macro" );
add_action( "wp_ajax_suscale_loadSpecificMacro"        , "suscale_load_specific_macro" );

function suscale_load_specific_macro(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_loadSpecificMacro_nonce')){
        die("Unauthorized");
    }
    require(__DIR__ . "/suscale_functions_support.php");
    global $wpdb;
    $pr = $wpdb->prefix;
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $macro = sanitize_text_field($_POST["suscale_macro"]);
    if($macro == "no_macro_required"){
        $query_string = "SELECT * FROM `{$pr}plugin_suscale`";
        $result = $mysqli->query($query_string); 
        $names = $mysqli->query("SELECT * FROM `{$pr}plugin_suscale_systems`");
    }else{
        $query_string = "SELECT * FROM `{$pr}plugin_suscale` WHERE systemID IN (SELECT systemID FROM `{$pr}plugin_suscale_systems` WHERE macroSystem='$macro')";
        $result = $mysqli->query($query_string);
        $names = $mysqli->query("SELECT * FROM `{$pr}plugin_suscale_systems WHERE macroSystem='$macro'");
    }
    
    $scores = array();
    $keys = array();

    $total_systems = 0;
    $total_suss = 0;

    // WHILE LOOP FOR CREATE THE BIDIM ARRAY NAMED SCORES: [SYSTEMID][{SCORE,COUNT,AVG,GRADE,NAME}]         
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $system_key = $row["systemID"];                             // KEY FOR ACCESS TO BIDIM ARRAY
        if (!array_key_exists($system_key, $scores)) {              // IF SYSTEM IS NOT IN SCORES, CREATE AN ARRAY FOR THIS SYSTEM
            $name = "system-{$system_key}";
            foreach ($names as $_row) {
                if ($_row["systemID"] == $system_key) {
                    $name = $_row["name"];
                    break;
                }
            }
            $temp = array(
                "systemID" => $system_key,
                "tot_score_sys" => $row["score"],
                "tot_count_sys" => 1,
                "sys_avg" => 1,
                "sys_grade" => "Z",
                "sys_name" => $name
            );
            $scores[$system_key] = $temp;
            $total_systems++;
            array_push($keys, $system_key);
        }
        else {                                                      // IF THE ARRAY FOR THIS SYSTEM IS ALREADY CREATED, UPDATE IT
            $old_score = $scores[$system_key]["tot_score_sys"];
            $old_count = $scores[$system_key]["tot_count_sys"];
            $new_score = $old_score + $row["score"];
            $new_count = $old_count + 1;
            $scores[$system_key]["tot_score_sys"] = $new_score;
            $scores[$system_key]["tot_count_sys"] = $new_count;
        }
        $total_suss++;
    }

    $score_avg = 0;

    foreach ($scores as $row) {             // CALCULATE GRADE AND AVG FOR EACH SYSTEM - SUM ALL AVG IN score_avg
        $_sysID = $row["systemID"];
        $temp_score = $row["tot_score_sys"];
        $temp_tot = $row["tot_count_sys"];
        $temp_avg = $temp_score / $temp_tot;
        $scores[$_sysID]["sys_avg"] = $temp_avg;
        $scores[$_sysID]["sys_grade"] = suscale_get_grade($temp_avg);
        $score_avg += $temp_avg;
    }

    if($total_systems != 0){                                        // PREVENT DIVISION BY ZERO AND CALCULATE COMPLEXIVE AVG
        $score_avg = $score_avg / $total_systems;
    }

    $grade = suscale_get_grade($score_avg);
    $response_load = array(
        'avg_sus' => esc_js($score_avg),
        'grade_total' => esc_html($grade),
        'scores' => suscale_esc_js_array($scores),
        'num_sys' => esc_js($total_systems),
        'num_sus' => esc_js($total_suss),
        'type' => esc_js('load'),
        'keys' => suscale_esc_js_array($keys)
    );

    echo json_encode($response_load);
    exit();
}


/**
 * AJAX : Submit a questionnaire
 * */

add_action( "wp_ajax_nopriv_suscale_submit" , "suscale_submit_questionnaire" );
add_action( "wp_ajax_suscale_submit"        , "suscale_submit_questionnaire" );

function suscale_submit_questionnaire(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_submit_nonce')){
        die("Unauthorized");
    }

    require(__DIR__ . "/suscale_functions_support.php");

    global $wpdb;
    $pr = $wpdb->prefix;

    if (!(isset($_POST["suscale_questionnaire"]) && isset($_POST["suscale_userID"]) && isset($_POST["suscale_systemID"]))) {
        die("Some data are missing.");
    }

    if($_POST["suscale_userID"] === "" || $_POST["suscale_systemID"] === ""){
        die("Unable to continue. You need to arrive here via redirect.");
    }

    $sanitized_questionnaire = suscale_sanitize_validate_questionnaire(
        $_POST["suscale_questionnaire"],
        $_POST["suscale_userID"],
        $_POST["suscale_systemID"]);

    if(! $sanitized_questionnaire){      
        die("Incorrect questionnaire.");
    }
    else if(! is_array($sanitized_questionnaire)){          // Print the eventually error message from the sanitize function
        die($sanitized_questionnaire);
    }

    $json_sanitized = $sanitized_questionnaire['json_sanitized'];
    $userID         = $sanitized_questionnaire['userID_sanitized'];
    $systemID       = $sanitized_questionnaire['systemID_sanitized'];
    
    $prima          = $json_sanitized["prima"];
    $seconda        = $json_sanitized["seconda"];
    $terza          = $json_sanitized["terza"];
    $quarta         = $json_sanitized["quarta"];
    $quinta         = $json_sanitized["quinta"];
    $sesta          = $json_sanitized["sesta"];
    $settima        = $json_sanitized["settima"];
    $ottava         = $json_sanitized["ottava"];
    $nona           = $json_sanitized["nona"];
    $decima         = $json_sanitized["decima"];

    $X = ($prima + $terza + $quinta + $settima + $nona) - 5;
    $Y = 25 - ($seconda + $quarta + $sesta + $ottava + $decima);
    $sum = $X + $Y;
    $suscale_score = $sum * 2.5;

    $query_string = "INSERT INTO {$pr}plugin_suscale VALUES (0,'$prima','$seconda','$terza','$quarta','$quinta','$sesta','$settima','$ottava','$nona','$decima','$suscale_score', '$systemID', '$userID')";

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $result = $mysqli->query($query_string);

    $info = suscale_get_info($suscale_score);

    $response = array(
        'response' => esc_js($suscale_score),
        'type' => esc_js('insert'),
        'info' => esc_html($info),
        'userID' => esc_js($userID),
        'systemID' => esc_js($systemID)
    );

    echo json_encode($response);
    exit();
}

/**
 * 
 *  AJAX : Loads systems for table
 * */

add_action( "wp_ajax_suscale_loadAllSystems" , "suscale_load_all_systems" );
add_action( "wp_ajax_nopriv_suscale_loadAllSystems" , "suscale_load_all_systems" );

function suscale_load_all_systems(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_loadAllSystems_nonce')){
        die("Unauthorized");
    }
    global $wpdb;
    $pr = $wpdb->prefix;

    $query_string = "SELECT * FROM `{$pr}plugin_suscale_systems`";
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $result = $mysqli->query($query_string);
    $systems = array();

    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $wp_post = get_post((int)$row["systemID"]);
        if(is_null($wp_post)){
            $query = "DELETE FROM {$pr}plugin_suscale_systems WHERE systemID=".(int)$row["systemID"];
            $mysqli->query($query);
            
            $actual_pages = get_option( 'suscale_pages' );
            foreach ($actual_pages as $key => $value) {
                if( $value == $wp_post->ID ){
                    unset($actual_pages[(int)$key]);
                    break;
                }
            }
            array_push($actual_pages, $suscale_page_id);
            update_option( "suscale_pages" , $actual_pages);
        }
        else{
            global $table_prefix;
            $asd = $wp_post->post_content;
            if( preg_match('/\[suscale[^\]]*\]/', $asd) ){
                $_systemID = esc_js($row["systemID"]);
                $_systemName = esc_js($row["name"]);
                $single_row = array(
                    "systemID" => esc_js($_systemID),
                    "systemName" => esc_js($_systemName)
                );
                array_push($systems, $single_row);
            }
            else{
                $query = "DELETE FROM {$pr}plugin_suscale_systems WHERE systemID=".$wp_post->ID;
                $mysqli->query($query);
                
                $actual_pages = get_option( 'suscale_pages' );
                foreach ($actual_pages as $key => $value) {
                    if( $value == $wp_post->ID ){
                        unset($actual_pages[(int)$key]);
                        break;
                    }
                }
                array_push($actual_pages, $suscale_page_id);
                update_option( "suscale_pages" , $actual_pages);
            }

        }
        
    }

    if(current_user_can( 'edit_post', (int)get_option('suscale_results_page_id'))){
        echo json_encode($systems);
        exit();
    }
    else{
        echo json_encode(array());
        exit();
    }
}

/**
 * 
 *  AJAX : Search
 * */

add_action( "wp_ajax_suscale_search" , "suscale_search" );

function suscale_search(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_search_nonce')){
        die("Unauthorized");
    }
    require __DIR__ . "/suscale_functions_support.php";
    global $wpdb;
    $pr = $wpdb->prefix;

    $only_unregistered = false;
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query_result = "";


    // CHECK FORM DATA
    if (isset($_POST["suscale_searchName"]) && isset($_POST["suscale_searchMacro"]) && isset($_POST["suscale_onlyUnregistered"])) {
        $sanitized = suscale_sanitize_search(
            $_POST["suscale_searchName"],
            $_POST["suscale_searchMacro"],
            $_POST["suscale_onlyUnregistered"]);  // Sanitize and validate $_POST values
    }
    else {
        die("Some data is missing.");
    }
    
    
    if(! $sanitized){
        die("Incorrect data received.");
    }
    else{
        $name = $sanitized["name"];
        $macro = $sanitized["macro"];
        $only_unregistered = $sanitized["only_unregistered"];
    }


    // SEARCH IN DATABASE

    if($only_unregistered == "true"){           // ONLY UNREGISTERED USER - ALL MACRO OR A SPECIFIC ONE
        if($macro !== ""){
            $query_result = "SELECT score, systemID FROM {$pr}plugin_suscale WHERE utente=0 AND systemID in (SELECT systemID FROM {$pr}plugin_suscale_systems WHERE macroSystem='{$macro}')";
        }
        else{
            $query_result = "SELECT score, systemID FROM {$pr}plugin_suscale WHERE utente=0";
        }
    }else{                                      // A SPECIF USER OR NO USER SPECIFIED - ALL MACRO OR A SPECIFIC ONE
        $id = username_exists($name);

        if($name != "" && ! is_bool($id)){      // REDUNANT NAME - HERE IF I FOUND A USERNAME | IF MACRO IS EMPTY, CHOICE ALL MACROS
            if($macro !== ""){
                $query_result = "SELECT score, systemID FROM `{$pr}plugin_suscale` WHERE utente={$id} AND systemID in (SELECT systemID FROM {$pr}plugin_suscale_systems WHERE macroSystem='{$macro}')";
            }else{                              
                $query_result = "SELECT score, systemID FROM `{$pr}plugin_suscale` WHERE utente=$id";
            }
        }else if($name == ""){
            if($macro !== ""){
                $query_result = "SELECT score, systemID FROM '{$pr}plugin_suscale' WHERE systemID in (SELECT systemID FROM {$pr}plugin_suscale_systems WHERE macroSystem='{$macro}')";
            }
            else{
                $query_result = "SELECT score, systemID FROM {$pr}plugin_suscale";
            }
        }else if(is_bool($id)){
            echo json_encode(array());
            exit();
        }
    }

    $res = $mysqli->query($query_result);
    $response = array();
    while ($roww = $res->fetch_array(MYSQLI_ASSOC)) {
        $roww['score'] = esc_js($roww['score']);
        $roww['systemID'] = esc_js($roww['systemID']);
        array_push($response, $roww);
    }
    echo json_encode($response);
    exit;
}

/**
 * 
 *  AJAX : Delete
 * */

add_action( "wp_ajax_suscale_deleteSystem" , "suscale_delete_system" );

function suscale_delete_system(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_deleteSystem_nonce')){
        die("Unauthorized");
    }
    require __DIR__ . "/suscale_functions_support.php";
    global $wpdb;
    $pr = $wpdb->prefix;
    $also_delete_sus = false;
    if (isset($_POST["suscale_systemToDelete"])) {
        $sanitized = suscale_sanitize_delete(
            $_POST["suscale_systemToDelete"],
            $_POST['suscale_deleteSubmitQuests']);
    }
    else {
        die("No system to delete");
    }
    if(!is_array($sanitized)){
        die($sanitized);
    }
    $sys_num = $sanitized["sys_num"];
    $also_delete_sus = $sanitized["also_delete_sus"];
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $get_macro_query = "SELECT * FROM {$pr}plugin_suscale_systems WHERE systemID={$sys_num}";           // Query for get macro
    $res = $mysqli->query($get_macro_query);
    
    $macro = $res->fetch_array(MYSQLI_ASSOC)["macroSystem"];                                        // Macro taken from table

    
    $get_text = "SELECT post_content FROM {$pr}posts where ID=".$sys_num;                           // Get post_text query
    $res = $mysqli->query($get_text);           
    $text_of_page = $res->fetch_array(MYSQLI_ASSOC)["post_content"];

    $text_of_page = suscale_remove_shortcode($text_of_page);

    $query_update = "UPDATE {$pr}posts SET post_content='{$text_of_page}' WHERE ID=".$sys_num;      // Update post_text in db
    $mysqli->query($query_update);
    
    $delete_query = "DELETE FROM {$pr}plugin_suscale_systems WHERE systemID=".$sys_num;             // Delete from table of name
    $mysqli->query($delete_query);
    if($also_delete_sus){                                                                           // Delete also questionnaires
        $query_2 = "DELETE FROM {$pr}plugin_suscale WHERE systemID=".$sys_num;
        $mysqli->query($query_2);
    }

    $actual_pages = get_option( 'suscale_pages' );
    foreach ($actual_pages as $key => $value) {
        if( $value == $sys_num ){
            unset($actual_pages[(int)$key]);
            break;
        }
    }
    array_push($actual_pages, $suscale_page_id);
    update_option( "suscale_pages" , $actual_pages);
    echo json_encode(array("a" => "true"));
    exit();
}

/**
 * 
 *  AJAX : Update names
 * */

add_action( "wp_ajax_suscale_updateNames" , "suscale_update_names" );

function suscale_update_names(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_updateNames_nonce')){
        die("Unauthorized");
    }
    require __DIR__ . "/suscale_functions_support.php";

    global $wpdb;
    $pr = $wpdb->prefix;
    
    if (isset($_POST["suscale_queue"])) {
        $json_sanitized = suscale_sanitize_validate_queue_names($_POST["suscale_queue"]);
    }
    else{
        die("Queue is empty");
    }
    //var_dump($json_sanitized);
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    foreach ($json_sanitized as $key => $value) {
        if(strlen($value) > 0)
            $query = "UPDATE `{$pr}plugin_suscale_systems` SET `name`='$value' WHERE systemID=$key";
        else
            $query = "UPDATE `{$pr}plugin_suscale_systems` SET `name`='null' WHERE systemID=$key";
        $mysqli->query($query);
    }
    //var_dump(suscale_esc_js_array($json_sanitized));
    echo json_encode(suscale_esc_js_array($json_sanitized));
    exit;
}

/**
 * 
 *  AJAX : Return url readme
 * */

add_action( "wp_ajax_suscale_openReadme" , "suscale_redirect_readme" );
add_action( "wp_ajax_nopriv_suscale_openReadme" , "suscale_redirect_readme" );

function suscale_redirect_readme(){
    if(! wp_verify_nonce($_REQUEST['_nonce'], 'suscale_openReadme_nonce')){
        die("Unauthorized");
    }

    echo json_encode(esc_url(plugins_url("../public/suscale_readme.html", __FILE__)));    
    exit();
}   
