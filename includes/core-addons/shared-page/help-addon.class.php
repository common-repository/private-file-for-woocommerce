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

if (!class_exists('PFFW_HelpAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* 
*/
class PFFW_HelpAddOn extends PFFW_AddOn {

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;
		
		// We only do something within the admin interface
		if ( is_admin() ) {
			add_filter( 'pffw_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 1000, 1 );
			add_filter( 'pffw_after_settings_side', array( &$this, 'print_addons_sidebox' ), 800 );
			add_filter( 'pffw_after_settings_side', array( &$this, 'print_4wp_sidebox' ), 1000 );
			add_filter( 'pffw_before_settings_pffw_addons', array( &$this, 'print_addons' ) );
			add_filter( 'admin_init', array( &$this, 'add_dashboard_metaboxes' ) );
			
			$plugin_file = 'private-file-for-woocommerce/private-file-for-woocommerce.php';
			add_filter( "plugin_action_links_{$plugin_file}", array( &$this, 'print_plugin_action_links' ), 10, 2 );
		} 
	}	
	
	public function print_plugin_action_links( $links, $file ) {
		$link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' .  PFFW_Settings::$OPTIONS_PAGE_SLUG 
				. '&pffw_tab=pffw_addons">'
				. __( 'Shared Page', 'pffw' ) . '</a>';
		array_unshift( $links, $link );	
		return $links;
	}

	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/
	
	public function add_settings_tab( $tabs ) {
		//$tabs[ 'pffw_help' ] = __( 'Help', 'pffw' );
		//next update
		$tabs[ 'pffw_addons' ] = __( 'Shared Page', 'pffw' );
		return $tabs;
	}
	
	/**
	 * @param PFFW_Settings $pffw_settings
	 */
	public function print_addons( $pffw_settings ) {
		include( dirname( __FILE__ ) . '/templates/shared-page.template.php' );
	}
	
	
	public function add_dashboard_metaboxes() {	
		add_meta_box('pffw_dashboard_addons', __( 'Enhance your customer area', 'pffw' ), 
				array( &$this, 'get_addons_sidebox_content' ), 'private-file-for-woocommerce', 'side' );
		add_meta_box('pffw_dashboard_4wp', __( 'Get more from 4wp', 'pffw' ), 
				array( &$this, 'get_4wp_sidebox_content' ), 'private-file-for-woocommerce', 'side' );
	}
	
	/**
	 * @param PFFW_Settings $pffw_settings
	 */
	public function print_addons_sidebox( $pffw_settings ) {	
		$pffw_settings->print_sidebox( __( 'Enhance your private file for woocommerce', 'pffw' ),
				$this->get_addons_sidebox_content() );
	}


	/**
	 * @param PFFW_Settings $pffw_settings
	 */
	public function print_4wp_sidebox( $pffw_settings ) {
		$pffw_settings->print_sidebox( __( 'Get more from 4wp', 'pffw' ), 
				$this->get_4wp_sidebox_content() );		
	}
	
	/**
	 * @param PFFW_Settings $pffw_settings
	 */
	public function get_addons_sidebox_content( $args = null ) {	
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
			
		$content = sprintf( '<p>%s</p><p><a href="%s" class="button-primary" target="_blank">%s</a></p>', 
						__( '&laquo Private File For Woocommerce &raquo; is a very modular plugin. We have built it so that it can be ' 
							. 'extended in many ways. You can also view all plugin for you by clicking the '
							. 'link below.' , 'pffw' ),
						"https://4wp.it/",
						__( 'Browse all plugin', 'pffw' ) );
		
		if ( $echo ) echo $content;
		
		return $content;
	}


	/**
	 * @param PFFW_Settings $pffw_settings
	 */
	public function get_4wp_sidebox_content( $args = null ) {
		// Extract parameters and provide defaults for the missing ones
		$args = extract( wp_parse_args( $args, array(
				'echo'	=> false
			) ), EXTR_SKIP );
		
		$content = sprintf( '<p>&raquo; ' . 
				__( 'If you like our plugins, you might want to <a href="%s">check our website</a> for more.', 'pffw' ) 
				. '</p>', 'https://www.4wp.it' );
	
		$content .= '<p>&raquo; ' . __( 'If you want to get updates about our plugins, you can:', 'pffw' ) . '</p><ul>';
		$content .= sprintf( '<li><a href="%2$s">%1$s</a>', 
				__( 'Follow us on Twitter', 'pffw' ), 
				"http://twitter.com/");
		$content .= sprintf( '<li><a href="%2$s">%1$s</a>', 
				__( 'Follow us on Google+', 'pffw' ), 
				"https://plus.google.com");
		$content .= sprintf( '<li><a href="%2$s">%1$s</a>', 
				__( 'Follow us on Facebook', 'pffw' ), 
				"http://www.facebook.com/");
		$content .= '</ul>';
		
		if ( $echo ) echo $content;
		
		return $content;	
	}
	
	/** @var PFFW_Plugin */
	private $plugin;
}

// Make sure the addon is loaded
global $pffw_he_addon;
$pffw_he_addon = new PFFW_HelpAddOn();

endif; // if (!class_exists('PFFW_HelpAddOn')) 

