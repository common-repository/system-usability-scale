<?php

/* CREATE THE SHORTCODE [suscale macro="" required_login="" show_message=""] */

function suscale_load_button( $attr ) {
    $input = shortcode_atts( array(
        "macro" => "NO-MACRO-ASSIGNED",
        "required_login" => true,
        "show_message" => false
    ), $attr );

    $current_user_ID = wp_get_current_user()->ID;
    $systemID = get_the_ID();
    suscale_sanitize_content($input, $systemID);
    $macro = $input["macro"];
    $required_login = $input["required_login"];
    global $wpdb;
    global $table_prefix;
    $DS = DIRECTORY_SEPARATOR;
    $pr = $table_prefix;

    
    $wpdb->query( "INSERT INTO {$pr}plugin_suscale_systems VALUES ($systemID,'system-{$systemID}','{$macro}') ON DUPLICATE KEY UPDATE macroSystem='{$macro}'" );


    /* CREATE BUTTON FOR SUS AND ADD TO THIS PAGE */
    
    $sus_page = 'http://' . $_SERVER["HTTP_HOST"] . '/system-usability-scale/';

    $content = '<div class="sus_button_container">';

    // This will be read in scripts/sus_script_button
    $content .= '<input type="hidden" id="session_sys_id" value="'.$systemID.'"  />';
    $content .= '<input type="hidden" id="session_usr_id" value="'.$current_user_ID.'" />';
    $content .= '<input type="hidden" id="session_sus_page" value="'.$sus_page.'" />';

    if( $required_login === "false" ) {
        $required_login = false;
    }else{
        $required_login = true;
    }

    if( is_user_logged_in() && $required_login ) {   
        $content .= '<input type="button" name="submit" value="Start sus for this system" id="sus_btn"/>';
    }
    elseif(! $required_login) {
        $content .= '<input type="button" name="submit" value="Start sus for this system" id="sus_btn"/>';

    }elseif(! is_user_logged_in() && $required_login){
        if($input['show_message'] == true)
            $content .= "<p style='color:red;'><em>You are not allowed to submit System Usability Scale for this system.</em></p>";
    }
    
    $content .= '</div>';

    suscale_add_sus_page($systemID);
    do_action('wp_enqueue_scripts');
    return $content;
}
add_shortcode('suscale', 'suscale_load_button');

/* Check and if not present add $sus_page_id page to the list of sus pages */
function suscale_add_sus_page($sus_page_id){
    $actual_pages = get_option( 'suscale_pages' );
    foreach ($actual_pages as $key => $value) {
        if( $value == $sus_page_id ){
            return;
        }
    }
    array_push($actual_pages, $sus_page_id);
    update_option( "suscale_pages" , $actual_pages);
}

function suscale_sanitize_content(&$input, $systemID){
    $content = get_the_content($systemID);
    $macro = sanitize_text_field($input["macro"]);
    $required_login = ($input["required_login"]==="true") ? "true" : "false";
    $show_message = ($input["show_message"]==="true") ? "true" : "false";
    $content = preg_replace(
        '/macro( )?=( )?"([^"])+"/',
        'macro="'. $macro .'"',
        $content);
    $content = preg_replace(
        '/(required_login)( )?=( )?"([^"])+"/',
        'required_login="'. $required_login . '"',
        $content);
    $content = preg_replace(
        '/(show_message)( )?=( )?"([^"])+"/',
        'show_message="'. $show_message .'"',
        $content);
    wp_update_post(array(
        'ID'            => $systemID,
        'post_content'  => $content,
        'post_title'    => get_the_title($systemID)
        ), false, false);
}

?>