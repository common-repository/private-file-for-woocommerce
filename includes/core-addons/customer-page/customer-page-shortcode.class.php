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

if (!class_exists('PFFW_CustomerPageShortcode')) :

/**
 * Handles the [private-file-for-woocommerce] shortcode
 * 
 * 
 */
class PFFW_CustomerPageShortcode {

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->setup();	
	}
	
	/**
	 * Setup the WordPress hooks we need
	 */
	public function setup() {
		if ( is_admin() ) return;		
		add_shortcode( 'private-file-for-woocommerce', array( &$this, 'process_shortcode' ) );
	}
	
	/**
	 * Replace the [private-file-for-woocommerce] shortcode with a page representing the customer area. The shortcode takes no
	 * parameter and does not accept any content.
	 * 
	 * @param array $attrs
	 * @param string $content
	 */
	public function process_shortcode( $params = array(), $content = null ) {
		// If not logged-in, we should do so.
		if ( !is_user_logged_in() ) {
	  		ob_start();
	  		
	  		do_action( 'pffw_before_login_required_template' );
	  		
	  		include( $this->plugin->get_template_file_path(
	  				PFFW_INCLUDES_DIR . '/core-addons/customer-page',
	  				'customer-page-login-required.template.php',
	  				'templates' ));

	  		do_action( 'pffw_after_login_required_template' );
	  		
	  		$out = ob_get_contents();
	  		ob_end_clean(); 
	  		
			return $out;
		} 
		
		// Build the HTML output for a logged-in user. 
  		ob_start();
	  		
	  	do_action( 'pffw_before_customer_area_template' );
	  		
  		include( $this->plugin->get_template_file_path(
  				PFFW_INCLUDES_DIR . '/core-addons/customer-page',
  				'customer-page.template.php',
  				'templates' ));	  	
	  		
	  	do_action( 'pffw_after_customer_area_template' );
	  	
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
		return $out;
	}
	
	/** @var PFFW_Plugin The plugin instance */
	private $plugin;
}

endif; // if (!class_exists('PFFW_CustomerPageShortcode')) :