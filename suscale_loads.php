<?php

function suscale_is_sus_page($page_in){
    $actual_pages = get_option( 'suscale_pages' );
    foreach ($actual_pages as $key => $value) {
        if($value == $page_in){
            return true;
        }
    }
    return false;
}       

function suscale_add_styles_scripts() {
    $current_page_id = get_the_ID();

    // CSS
    $css_path_questionnaire = plugins_url( "public/css/suscale_style_questionnaire.css", __FILE__);
    $css_path_panel         = plugins_url( "public/css/suscale_style_results.css", __FILE__);
    // $css_path_button_page   = plugins_url( "css/suscale_button_page.css", __FILE__);  <- no need of css

    // JS
    $js_path_questionnaire  = plugins_url( "public/scripts/suscale_script_questionnaire.js", __FILE__);
    $js_path_panel          = plugins_url( "public/scripts/suscale_script_results.js", __FILE__);
    $js_path_button_page    = plugins_url( "public/scripts/suscale_script_button.js", __FILE__);

    
    // Check what page is and load style and scripts if it's sus's page
    if( $current_page_id == (int)get_option("suscale_questionnaire_page_id")){
        wp_enqueue_style( 'suscale_style', $css_path_questionnaire);
        wp_enqueue_script( 'suscale_script_questionnaire', $js_path_questionnaire, array('jquery-core'));
        wp_localize_script( 'suscale_script_questionnaire', 'wp_ajax', array(
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => suscale_nonces("questionnaire")));
    
    }else if( $current_page_id == (int)get_option("suscale_results_page_id")){
        wp_enqueue_style( 'suscale_style_admin', $css_path_panel);
        wp_enqueue_script( 'suscale_script_admin', $js_path_panel, array('jquery-core'));
        wp_localize_script( 'suscale_script_admin', 'wp_ajax', array(
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => suscale_nonces("results")));
    
    }else if( suscale_is_sus_page($current_page_id)){
        // wp_enqueue_style( 'suscale_style_button_page', $css_path_button_page);
        wp_enqueue_script( 'suscale_script_button_page', $js_path_button_page, array('jquery-core'));
        wp_localize_script( 'suscale_script_button_page', 'wp_ajax', array(
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('suscale_redirect_nonce')));
    } 
 
}
add_action( 'wp_enqueue_scripts', 'suscale_add_styles_scripts');

function suscale_nonces($page){
    if($page == "results"){
        return array(
            "suscale_loadAllSystems"    => wp_create_nonce( 'suscale_loadAllSystems_nonce' ),
            "suscale_loadMacros"        => wp_create_nonce( 'suscale_loadMacros_nonce' ),
            "suscale_loadSpecificMacro" => wp_create_nonce( 'suscale_loadSpecificMacro_nonce' ),
            "suscale_openReadme"        => wp_create_nonce( 'suscale_openReadme_nonce' ));

    }else if($page == "questionnaire"){
        return array("suscale_submit"   => wp_create_nonce('suscale_submit_nonce'));
    }
}