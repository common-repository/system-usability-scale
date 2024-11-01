<?php

function suscale_script_admin_page() {
?>
    <div id="sus_settings_body">
        <div class="wrap">
            <h1>Setting for System Usability Scale</h1>
                <p><em>If you are in trouble, please click on <a id="readme">readme</a>.</em></p>
                <h3>Edit systems</h3>
                <div class="sus_container_table_fields">
                    <table id="sus_load_table" class="widefat" cellspacing="0">
                        <tr class="alternate">
                            <th><strong>System ID</strong></th><th><strong>Name</strong></th>
                        </tr>
                    </table>
                    <div class="wp-block-buttons">
                        <div class="wp-block-button">
                            <button class="wp-block-button__link sus_button" id="sus_submit_button" onclick="suscale_update_names()">Submit changes</button>
                        </div>
                    </div>
                </div>
        </div>
        <h3>Search</h3>
        <div class="sus_search_body">
            <div class="sus_container_table_fields">
                <div class="search_input">
                    <input id="sus_srch_name" placeholder="Username" />
                    <span class="align_label">
                        <input type="checkbox" id="sus_btn_unregistered" value="Unregistered users"/>
                        <label for="sus_btn_unregistered">Unregistered</label>
                    </span> 
                </div> 
                <div class="search_input add_margin_left">
                    <input id="sus_srch_macro" placeholder="Macro" />
                    <span class="align_label">
                        <input type="checkbox" id="sus_btn_default_macro" value="Default macro"/>
                        <label for="sus_btn_default_macro">Default</label>
                    </span>
                </div>
                <input type="button" id="sus_srch_btn" value="Search" class="sus_button add_margin_left" />
            </div>
            <div class="sus_container_table_fields flex-column">
                <div id="table_here" class="sus_container_table_fields">
                    <table id="table_result" class="widefat" cellspacing="0"></table>
                </div>
            </div>
            </div>
        </div>
    <?php
    return;
}

function suscale_loads_settings_page(){
    $css_path   = plugins_url( 'css/suscale_settings_page.css', __FILE__ );
    $js_path    = plugins_url( 'scripts/suscale_settings_page.js', __FILE__ );

    if(get_admin_page_title() === "SUS - settings page"){
        wp_enqueue_script( 'sus-settings-js', $js_path, array("jquery-core") );
        wp_enqueue_style( 'sus-settings-style', $css_path );
        wp_localize_script( 'sus-settings-js', 'wp_ajax', array(
            "ajaxurl"   => admin_url('admin-ajax.php'),
            'nonce'     => suscale_nonces_settings()
        ));
    }
}

add_action( 'admin_enqueue_scripts', 'suscale_loads_settings_page' );

function suscale_admin_menu_options() {
    add_menu_page('SUS - settings page', 'S.U.S. setting', 'manage_options', 'sus-admin', 'suscale_script_admin_page', '', 200);
}
add_action('admin_menu', 'suscale_admin_menu_options');


function suscale_nonces_settings(){
    return array(
        "suscale_loadAllSystems"    => wp_create_nonce( 'suscale_loadAllSystems_nonce' ),
        "suscale_updateNames"       => wp_create_nonce( 'suscale_updateNames_nonce' ),
        "suscale_search"            => wp_create_nonce( 'suscale_search_nonce' ),
        "suscale_deleteSystem"      => wp_create_nonce( 'suscale_deleteSystem_nonce' ),
        "suscale_openReadme"        => wp_create_nonce( 'suscale_openReadme_nonce' )
        );
}

?>