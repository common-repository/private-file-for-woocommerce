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



if (!class_exists('PFFW_PrivatePageFrontendInterface')) :



/**

 * Frontend interface for private files

 * 

 * 

 */

class PFFW_PrivatePageFrontendInterface {

	

	public function __construct( $plugin, $private_page_addon ) {

		$this->plugin = $plugin;

		$this->private_page_addon = $private_page_addon;



		if ( $plugin->get_option( PFFW_PrivatePageAdminInterface::$OPTION_ENABLE_ADDON ) ) {			

			add_action( 'pffw_customer_area_content', array( &$this, 'print_customer_area_content' ), 10 );

	        add_filter( 'the_content', array( &$this, 'after_post_content' ), 3000 );

			add_filter( "get_previous_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );

			add_filter( "get_next_post_where", array( &$this, 'disable_single_post_navigation' ), 1, 3 );

		}

	}



	/*------- FUNCTIONS TO PRINT IN THE FRONTEND ---------------------------------------------------------------------*/

		public function after_post_content( $content ) {
		// If not on a matching post type, we do nothing
		if ( !is_singular('pffw_private_page') ) return $content;		

		ob_start();
		include( $this->plugin->get_template_file_path(
				PFFW_INCLUDES_DIR . '/core-addons/private-page',
				'private-page-after_post_content.template.php',
				'templates' ));	
  		$out = ob_get_contents();
  		ob_end_clean(); 
  		
  		return $content . $out;
	}

	public function print_customer_area_content() {		

		include( $this->plugin->get_template_file_path(

				PFFW_INCLUDES_DIR . '/core-addons/private-page',

				"private-page-customer_area_user_pages.template.php",

				'templates' ));

	}



	/**

	 * Disable the navigation on the single page templates for private files

	 */

	// TODO improve this by getting the proper previous/next file for the same owner

	public function disable_single_post_navigation( $where, $in_same_cat, $excluded_categories ) {

		if ( get_post_type()=='pffw_private_page' )	return "WHERE 1=0";		

		return $where;

	}

	

	/** @var PFFW_Plugin */

	private $plugin;



	/** @var PFFW_PrivatePageAddOn */

	private $private_page_addon;

}

	

endif; // if (!class_exists('PFFW_PrivatePageFrontendInterface')) :