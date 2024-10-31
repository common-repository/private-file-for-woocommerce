<?php
/*
Plugin Name: Private File For Woocommerce
Plugin URI: https://www.4wp.it
Version: 1.0.4
Description: Addons for my account woocommerce with the possibility to get a page on your site where they can access private file, private pages and one shared page. 
Author: Roberto Bottalico
Author URI: https://www.4wp.it/private-file-for-woocommerce
Text Domain: pffw
Domain Path: /languages
*/

/*  Copyright 2021 4wp.it 

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if ( !defined( 'PFFW_PLUGIN_DIR' ) ) define( 'PFFW_PLUGIN_DIR', 	plugin_dir_path( __FILE__ ) );
if ( !defined( 'PFFW_INCLUDES_DIR' ) ) define( 'PFFW_INCLUDES_DIR', 	PFFW_PLUGIN_DIR . '/includes' );

define( 'PFFW_LANGUAGE_DIR', 		'private-file-for-woocommerce/languages' );

define( 'PFFW_PLUGIN_URL', 			plugin_dir_url( __FILE__ ) );
define( 'PFFW_SCRIPTS_URL', 		PFFW_PLUGIN_URL . 'scripts' );
define( 'PFFW_ADMIN_THEME_URL', 	PFFW_PLUGIN_URL . 'themes/admin/default' );
define( 'PFFW_FRONTEND_THEME_URL', 	PFFW_PLUGIN_URL . 'themes/frontend/default' );
define( 'PFFW_PLUGIN_FILE', 		'private-file-for-woocommerce/private-file-for-woocommerce.php' );


/**
 * A function for debugging purposes
 */
if ( !function_exists( 'pffw_log_debug' ) ) {
function pffw_log_debug( $message ) {
	if (WP_DEBUG === true){
		if( is_array( $message ) || is_object( $message ) ){
			$msg = "PFFW \t" . print_r( $message, true );
		} else {
			$msg = "PFFW \t" . $message;
		}

		// ChromePhp::log( $msg );
		error_log( $msg );
	}
}
}

// Basic includes
include_once( PFFW_INCLUDES_DIR . '/plugin.class.php' );
include_once( PFFW_INCLUDES_DIR . '/theme-utils.class.php' );
include_once( PFFW_INCLUDES_DIR . '/settings-my-account-woocommerce.class.php' );

// Core addons
include_once( PFFW_INCLUDES_DIR . '/core-addons/admin-area/admin-area-addon.class.php' );
include_once( PFFW_INCLUDES_DIR . '/core-addons/shared-page/help-addon.class.php' );
include_once( PFFW_INCLUDES_DIR . '/core-addons/capabilities/capabilities-addon.class.php' );
include_once( PFFW_INCLUDES_DIR . '/core-addons/private-page/private-page-addon.class.php' );
include_once( PFFW_INCLUDES_DIR . '/core-addons/private-file/private-file-addon.class.php' );
include_once( PFFW_INCLUDES_DIR . '/core-addons/customer-page/customer-page-addon.class.php' );

// Start the plugin!

function pffw_private_file_for_woocommerce_run() {

    global $pffw_plugin;
	$pffw_plugin = new PFFW_Plugin();
	$pffw_plugin->run();

	return $pffw_plugin;

}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php') ){
	$PFFW_Plugin = pffw_private_file_for_woocommerce_run();
} else {
	add_action( 'admin_notices', 'PFFW_Plugin_installed_notice' );
}

function PFFW_Plugin_installed_notice()
{
	?>
    <div class="error">
      <p><?php esc_html_e( 'Private File For Woocommerce requires the WooCommerce', 'pffw'); ?></p>
    </div>
    <?php
}

