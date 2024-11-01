<?php
/*
Plugin Name:    System Usability Scale
Plugin URI:     https://wordpress.org/plugins/system-usability-scale/
Description:    Select one or more posts, and write on it the shortcode [suscale], to insert a special button which redirects users to the System Usability Scale's questionnaire. Choose who can submit this, consulte the results, edit and personalize the name assigned to each system and make a search among submitted questionnaires. Credits: Project designed for the Course of Progettazione e Produzione Multimediale del Prof. Alberto Del Bimbo - Università degli studi di firenze; Idea by Andrea Ferracani; Development by Giuseppe Parrotta.
Author:         Giuseppe Parrotta
Version:        1.0
Author URI:     http://gparrotta.altervista.org/
License:        GPL v2
License URI:    https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    die('Invalid request.'); // Exit if accessed directly
}

define("SUSCALE_PATH", plugin_dir_path(__FILE__));


/* REQUIREMENTS */

require_once( SUSCALE_PATH . "suscale_init.php" );                       // SETUP DB AND ACTIV HOOK
require_once( SUSCALE_PATH . "suscale_loads.php" );                      // LOAD SCRIPTS AND STYLES
require_once( SUSCALE_PATH . "suscale_core.php" );                       // FILE CONTAINING THE SHORTCODE FUNCTION
require_once( SUSCALE_PATH . "server/suscale_actions.php" );             // ADD ACTIONS

if( is_admin() ){
    require_once( SUSCALE_PATH . "admin/suscale_settings_page.php" );     // MENU IN ADMIN BAR
}
