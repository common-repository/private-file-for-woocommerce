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

require_once( dirname(__FILE__) . '/private-page-admin-interface.class.php' );
require_once( dirname(__FILE__) . '/private-page-frontend-interface.class.php' );

if (!class_exists('PFFW_PrivatePageAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* 
*/
class PFFW_PrivatePageAddOn extends PFFW_AddOn {

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;

		if ( $plugin->get_option( PFFW_PrivatePageAdminInterface::$OPTION_ENABLE_ADDON ) ) {
			add_action( 'init', array( &$this, 'register_custom_types' ) );
			
			add_action( 'init', array( &$this, 'add_post_type_rewrites' ) );
			add_filter( 'post_type_link', array( &$this, 'built_post_type_permalink' ), 1, 3);
			
			add_action( 'template_redirect', array( &$this, 'protect_access' ) );
			
			add_filter( 'pffw_configurable_capability_groups', array( &$this, 'declare_configurable_capabilities' ) );
		}
				
		// Init the admin interface if needed
		if ( is_admin() ) {
			$this->admin_interface = new PFFW_PrivatePageAdminInterface( $plugin, $this );
		} else {
			$this->frontend_interface = new PFFW_PrivatePageFrontendInterface( $plugin, $this );
		}
	}	
	
	/**
	 * When the plugin is upgraded
	 * 
	 * @param unknown $from_version
	 * @param unknown $to_version
	 */
	public function plugin_version_upgrade( $from_version, $to_version ) {
		// If upgrading from before 1.6.0 we should flush rewrite rules
		if ( $from_version<'1.6.0' ) {
			global $wp_rewrite;  
			$wp_rewrite->flush_rules();
		}
	}
		
	/*------- FUNCTIONS TO ACCESS THE POST META ----------------------------------------------------------------------*/

	/**
	 * Get the name of the owner associated to the given post
	 *
	 * @param int $post_id
	 * @return boolean|int
	 */
	public function get_page_owner_id( $post_id ) {
		$owner_id = get_post_meta( $post_id, 'pffw_owner', true );
		if ( !$owner_id || empty( $owner_id ) ) return false;
		return apply_filters( 'pffw_get_page_owner_id', $owner_id );
	}
	
	/**
	 * Get the number of times the page has been viewed
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function get_page_view_count( $post_id ) {
		$count = get_post_meta( $post_id, 'pffw_private_page_view_count', true );	
		if ( !$count || empty( $count ) ) return 0;	
		return intval( $count );
	}
	
	/**
	 * Get the number of times the page has been viewed
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function increment_page_view_count( $post_id ) {
		update_post_meta( $post_id, 
			'pffw_private_page_view_count', 
			$this->get_page_download_count( $post_id ) + 1 );
	}

	/*------- HANDLE FILE VIEWING AND DOWNLOADING --------------------------------------------------------------------*/
	
	/**
	 * Protect access to single pages for private files: only for author and owner.
	 */
	public function protect_access() {		
		// If not on a matching post type, we do nothing
		if ( !is_singular('pffw_private_page') ) return;
		
		// If not logged-in, we ask for details
		if ( !is_user_logged_in() ) {
			wp_redirect( get_permalink( get_option('woocommerce_myaccount_page_id')). '/?redirect_to=' . $_SERVER['REQUEST_URI'] );
			exit;
		}

		// If not authorized to view the page, we bail	
		$post = get_queried_object();
		$author_id = $post->post_author;

		$current_user_id = get_current_user_id();
		$owner_id = $this->get_page_owner_id( $post->ID );
		
		if ( $owner_id!=$current_user_id && $author_id!=$current_user_id ) {
			wp_die( __( "You are not authorized to view this page", "pffw" ) );
			exit();
		}
	}

	/*------- INITIALISATIONS ----------------------------------------------------------------------------------------*/
	
	public function declare_configurable_capabilities( $capability_groups ) {
		$capability_groups[] = array(
				'group_name' => __( 'Private Pages', 'pffw' ),
				'capabilities' => array(
						'pffw_pp_edit' 		=> __('Create/Edit/Delete pages', 'pffw' ),
						'pffw_pp_read' 		=> __('Access pages', 'pffw' )
					)
			);
		
		return $capability_groups;
	}
	
	/**
	 * Register the custom post type for files and the associated taxonomies
	 */
	public function register_custom_types() {
		$labels = array(
				'name' 				=> _x( 'Private Pages', 'pffw_private_page', 'pffw' ),
				'singular_name' 	=> _x( 'Private Page', 'pffw_private_page', 'pffw' ),
				'add_new' 			=> _x( 'Add New', 'pffw_private_page', 'pffw' ),
				'add_new_item' 		=> _x( 'Add New Private Page', 'pffw_private_page', 'pffw' ),
				'edit_item' 		=> _x( 'Edit Private Page', 'pffw_private_page', 'pffw' ),
				'new_item' 			=> _x( 'New Private Page', 'pffw_private_page', 'pffw' ),
				'view_item' 		=> _x( 'View Private Page', 'pffw_private_page', 'pffw' ),
				'search_items' 		=> _x( 'Search Private Pages', 'pffw_private_page', 'pffw' ),
				'not_found' 		=> _x( 'No private pages found', 'pffw_private_page', 'pffw' ),
				'not_found_in_trash'=> _x( 'No private pages found in Trash', 'pffw_private_page', 'pffw' ),
				'parent_item_colon' => _x( 'Parent Private Page:', 'pffw_private_page', 'pffw' ),
				'menu_name' 		=> _x( 'Private Pages', 'pffw_private_page', 'pffw' ),
			);

		$args = array(
				'labels' 				=> $labels,
				'hierarchical' 			=> false,
				'supports' 				=> array( 'title', 'editor', 'author', 'thumbnail', 'comments' ),
				'taxonomies' 			=> array(),
				'public' 				=> true,
				'show_ui' 				=> true,
				'show_in_menu' 			=> false,
				'show_in_nav_menus' 	=> false,
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> true,
				'has_archive' 			=> false,
				'query_var' 			=> 'pffw_private_page',
				'can_export' 			=> false,
				'rewrite' 				=> false,
				'capabilities' 			=> array(
						'edit_post' 			=> 'pffw_pp_edit',
						'edit_posts' 			=> 'pffw_pp_edit',
						'edit_others_posts' 	=> 'pffw_pp_edit',
						'publish_posts' 		=> 'pffw_pp_edit',
						'read_post' 			=> 'pffw_pp_read',
						'read_private_posts' 	=> 'pffw_pp_edit',
						'delete_post' 			=> 'pffw_pp_edit'
					)
			);

		register_post_type( 'pffw_private_page', apply_filters( 'pffw_private_page_post_type_args', $args ) );
	}

	/**
	 * Add the rewrite rule for the private files.  
	 */
	function add_post_type_rewrites() {
		global $wp_rewrite;
		
		$pf_slug = 'private-pages';
		
		$wp_rewrite->add_rewrite_tag('%pffw_private_page%', '([^/]+)', 'pffw_private_page=');
		$wp_rewrite->add_rewrite_tag('%owner_name%', '([^/]+)', 'pffw_pp_owner_name=');
		$wp_rewrite->add_permastruct( 'pffw_private_page',
				$pf_slug . '/%pffw_private_page%',
				false);
	}

	/**
	 * Build the permalink for the private files
	 * 
	 * @param unknown $post_link
	 * @param unknown $post
	 * @param unknown $leavename
	 * @return unknown|mixed
	 */
	function built_post_type_permalink( $post_link, $post, $leavename ) {
		// Only change permalinks for private files
		if ( $post->post_type!='pffw_private_page') return $post_link;
	
		// Only change permalinks for published posts
		$draft_or_pending = isset( $post->post_status )
		&& in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );
		if( $draft_or_pending and !$leavename ) return $post_link;
	
		// Change the permalink
		global $wp_rewrite, $pffw_pp_addon;
	
		$permalink = $wp_rewrite->get_extra_permastruct( 'pffw_private_page' );
		$permalink = str_replace( "%pffw_private_page%", $post->post_name, $permalink );
	
		$owner_id = $pffw_pp_addon->get_page_owner_id( $post->ID );
		if ( $owner_id ) {
			$owner = get_userdata( $owner_id );
			$owner = sanitize_title_with_dashes( $owner->user_nicename );
		} else {
			$owner = 'unknown';
		}
		$permalink = str_replace( '%owner_name%', $owner, $permalink );
	
		$post_date = strtotime( $post->post_date );
		$permalink = str_replace( "%year%", 	date( "Y", $post_date ), $permalink );
		$permalink = str_replace( "%monthnum%", date( "m", $post_date ), $permalink );
		$permalink = str_replace( "%day%", 		date( "d", $post_date ), $permalink );
		
		$permalink = home_url() . "/" . user_trailingslashit( $permalink );
		$permalink = str_replace( "//", "/", $permalink );
		$permalink = str_replace( ":/", "://", $permalink );
	
		return $permalink;
	}
	
	/** @var PFFW_Plugin */
	private $plugin;

	/** @var PFFW_PrivatePageAdminInterface */
	private $admin_interface;

	/** @var PFFW_PrivatePageFrontendInterface */
	private $frontend_interface;
}

// Make sure the addon is loaded
global $pffw_pp_addon;
$pffw_pp_addon = new PFFW_PrivatePageAddOn();

endif; // if (!class_exists('PFFW_PrivatePageAddOn')) 
