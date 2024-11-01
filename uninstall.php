<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option("suscale_page_created");
delete_option("suscale_pages");

global $wpdb;
$pr = $wpdb->prefix;

$wpdb->query("DROP TABLE {$pr}plugin_suscale");
$wpdb->query("DROP TABLE {$pr}plugin_suscale_systems");
$wpdb->query("DELETE FROM {$pr}posts WHERE post_name='system-usability-scale' OR post_name='sus-results'");

?>