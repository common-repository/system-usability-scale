<?php

$DS = DIRECTORY_SEPARATOR;

function suscale_activate(){
	global $wpdb;
	$pr = $wpdb->prefix;
	$DS = DIRECTORY_SEPARATOR;
	$data_e_ora = date("Y-m-d h:i:s");

	/* PAGE SYSTEM USABILITY SCALE */

	$questionnaire_html_path = dirname(__FILE__) . $DS . "defaults" . $DS . "pages" . $DS . "page_questionnaire.html";
	$suscale_stream = fopen($questionnaire_html_path, "r");
	$suscale_db_page = fread($suscale_stream, filesize($questionnaire_html_path));
	fclose($suscale_stream);
	$handle = fopen(dirname(__FILE__) . "/console.txt", "w");
	fwrite($handle, $suscale_db_page);
	fclose($handle);
	/* QUESTIONNAIRE PAGE */
	wp_insert_post(array(
			'post_content' => $suscale_db_page,
			'post_name' => 'system-usability-scale',
			'post_title' => 'System Usablity Scale',
			'comment_status' => 'closed',
			'post_status' => 'publish',
			'post_type' => 'page'
		));
	
	$page_id_questionnaire = $wpdb->get_var("SELECT ID FROM {$pr}posts WHERE post_name ='system-usability-scale' "); 

	add_option("suscale_questionnaire_page_id", $page_id_questionnaire);

	// CSS - questionnaire
	$questionnaire_style_path = dirname(__FILE__). $DS . "defaults" . $DS . "css" . $DS . "suscale_style_questionnaire.css";
	$from = fopen($questionnaire_style_path, "r");
	$read = fread($from, filesize($questionnaire_style_path));
	fclose($from);
	$read_mod = str_replace(".page-id-332", ".page-id-".$page_id_questionnaire, $read);
	$to = fopen(dirname(__FILE__). $DS . "public" . $DS . "css" . $DS . "suscale_style_questionnaire.css", "wa+");
	fwrite($to, $read_mod);
	fclose($to);


	/* RESULTS PAGE */

	$results_html_path = dirname(__FILE__) . $DS . "defaults" . $DS . "pages" . $DS . "page_results.html";
	$handle_results_html = fopen($results_html_path, "r");
	$results_text = fread($handle_results_html, filesize($results_html_path));
	fclose($handle_results_html);
	wp_insert_post(array(
			'post_content' => $results_text,
			'post_name' => 'sus-results',
			'post_title' => 'System Usablity Scale - Results',
			'comment_status' => 'closed',
			'post_status' => 'private',
			'post_type' => 'page'
		));

	$page_id_results = $wpdb->get_var("SELECT ID FROM {$pr}posts WHERE post_name='sus-results'"); 	

	add_option("suscale_results_page_id", $page_id_results);

	// CSS - results page
	$results_style_path = dirname(__FILE__). $DS . "defaults" . $DS . "css" . $DS . "suscale_style_results.css";
	$from = fopen($results_style_path, "r");
	$read = fread($from, filesize($results_style_path));
	fclose($from);
	$read_ad = str_replace(".page-id-335", ".page-id-".$page_id_results, $read);
	$to = fopen(dirname(__FILE__). $DS . "public" . $DS . "css" . $DS . "suscale_style_results.css", "wa+");
	fwrite($to, $read_ad);
	fclose($to);

	/* SETUP DATABASE IF THIS IS THE FIRST INSTALL */

	if(! get_option("suscale_page_created")){
		$create_table = "CREATE TABLE {$pr}plugin_suscale (
			id_autoinc INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			prima INT NOT NULL,
			seconda INT NOT NULL,
			terza INT NOT NULL,
			quarta INT NOT NULL,
			quinta INT NOT NULL,
			sesta INT NOT NULL,
			settima INT NOT NULL,
			ottava INT NOT NULL,
			nona INT NOT NULL,
			decima INT NOT NULL,
			score FLOAT(2) NOT NULL,
			systemID INT NOT NULL,
			utente INT NOT NULL)";

		$table_name_system = "CREATE TABLE {$pr}plugin_suscale_systems(
			systemID INT UNSIGNED PRIMARY KEY,
			name VARCHAR(30) DEFAULT NULL,
			macroSystem VARCHAR(30) NOT NULL)";
		
		$wpdb->query($create_table);
		
		$wpdb->query($table_name_system);
		
		add_option("suscale_page_created", 1);
		add_option('suscale_pages', array());   		// If if the first install, create array for save all pages with sus inside

	}
	return;
}

register_activation_hook( __DIR__ . '/suscale.php', 'suscale_activate' );

function suscale_deactivate() {
    global $wpdb;
	$pr = $wpdb->prefix;
	delete_option("suscale_questionnaire_page_id");
	delete_option("suscale_results_page_id");
	delete_option("suscale_page_id");

    $wpdb->query("DELETE FROM {$pr}posts WHERE post_name='system-usability-scale' OR post_name='sus-results'");
}
register_deactivation_hook( __DIR__ . '/suscale.php', "suscale_deactivate" );

?>