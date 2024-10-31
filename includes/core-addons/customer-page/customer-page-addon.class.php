<?php
/*  Copyright 2021 4wp

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

require_once( PFFW_INCLUDES_DIR . '/addon.class.php' );

require_once( dirname(__FILE__) . '/customer-page-shortcode.class.php' );
// require_once( dirname(__FILE__) . '/private-file-frontend-interface.class.php' );
// require_once( dirname(__FILE__) . '/private-file-theme-utils.class.php' );

if (!class_exists('PFFW_CustomerPageAddOn')) :

/**
 * Add-on to show the customer page
*
* 
*/
class PFFW_CustomerPageAddOn extends PFFW_AddOn {

	public function run_addon( $pffw_plugin ) {
		$this->pffw_plugin = $pffw_plugin;
		$this->customer_page_shortcode = new PFFW_CustomerPageShortcode( $pffw_plugin );
		
		//if ( !is_admin() ) {
			//add_action( 'pffw_before_customer_area_template', array( &$this, 'default_welcome_message' ) );
		//}
	}	
	
	/*------- Some default messages above/below the templates --------------------------------------------------------*/

	//public function default_welcome_message() {
		//global $current_user;
		//$out = sprintf( __('Hello %s,', 'pffw'), $current_user->display_name );
		//$out = sprintf( '<h2 class="pffw_page_title">%s <small><a href="%s" class="logout-link">%s</a></small></h2>', 
		//		$out, wp_logout_url( get_permalink() ), __('Logout', 'pffw') );
		
		//echo apply_filters( "pffw_default_welcome_message", $out );
	//}
	
	/*------- INITIALISATIONS ----------------------------------------------------------------------------------------*/

	/** @var PFFW_Plugin */
	private $pffw_plugin;

	/** @var PFFW_CustomerPageShortcode */
	private $customer_page_shortcode;
}

// Make sure the addon is loaded
global $pffw_cp_addon;
$pffw_cp_addon = new PFFW_CustomerPageAddOn();

endif; // if (!class_exists('PFFW_CustomerPageAddOn')) :