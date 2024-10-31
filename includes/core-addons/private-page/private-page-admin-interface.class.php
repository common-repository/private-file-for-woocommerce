<?php

/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)



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



if (!class_exists('PFFW_PrivatePageAdminInterface')) :



/**

 * Administation area for private files

 * 

 * @author Vincent Prat @ MarvinLabs

 */

class PFFW_PrivatePageAdminInterface {

	

	public function __construct( $plugin, $private_page_addon ) {

		$this->plugin = $plugin;

		$this->private_page_addon = $private_page_addon;



		// Settings

		add_filter( 'pffw_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 10, 1 );

		add_action( 'pffw_addon_print_settings_pffw_private_pages', array( &$this, 'print_settings' ), 10, 2 );

		add_filter( 'pffw_addon_validate_options_pffw_private_pages', array( &$this, 'validate_options' ), 10, 3 );

		

		if ( $plugin->get_option( self::$OPTION_ENABLE_ADDON ) ) {

			// Admin menu

			add_action('pffw_admin_submenu_pages', array( &$this, 'add_menu_items' ), 11 );

			

			// Page listing

			add_filter( 'manage_edit-pffw_private_page_columns', array( &$this, 'user_column_register' ));

			add_action( 'manage_pffw_private_page_posts_custom_column', array( &$this, 'user_column_display'), 10, 2 );

			add_filter( 'manage_edit-pffw_private_page_sortable_columns', array( &$this, 'user_column_register_sortable' ));

			add_filter( 'request', array( &$this, 'user_column_orderby' ));

	

			// Page edit page

			add_action( 'admin_menu', array( &$this, 'register_edit_page_meta_boxes' ));

			add_action( 'save_post', array( &$this, 'do_save_post' ));

			add_action( 'admin_notices', array( &$this, 'print_save_post_messages' ));

		}		

	}



	/**

	 * Add the menu item

	 */

	public function add_menu_items( $submenus ) {

		$separator = '<span style="display:block;  

				        margin: 3px 5px 6px -5px; 

				        padding:0; 

				        height:1px; 

				        line-height:1px; 

				        background:#ddd;"></span>';

		

		$my_submenus = array(

				array(

					'page_title'	=> __( 'Private Pages', 'pffw' ),

					'title'			=> $separator . __( 'Private Pages', 'pffw' ),

					'slug'			=> "edit.php?post_type=pffw_private_page",

					'function' 		=> null,

					'capability'	=> 'pffw_pp_edit'

				),

				array(

					'page_title'	=> __( 'New Private Page', 'pffw' ),

					'title'			=> __( 'New Private Page', 'pffw' ),

					'slug'			=> "post-new.php?post_type=pffw_private_page",

					'function' 		=> null,

					'capability'	=> 'pffw_pp_edit'

				),

			); 

	

		foreach ( $my_submenus as $submenu ) {

			$submenus[] = $submenu;

		}

	

		return $submenus;

	}

	

	/*------- CUSTOMISATION OF THE LISTING OF PRIVATE FILES ----------------------------------------------------------*/

	

	/**

	 * Register the column

	 */

	public function user_column_register( $columns ) {

		$columns['pffw_owner'] = __( 'Owner', 'pffw' );

		return $columns;

	}

	

	/**

	 * Display the column content

	 */

	public function user_column_display( $column_name, $post_id ) {

		if ( 'pffw_owner' != $column_name )

			return;

	

		$owner_id = $this->private_page_addon->get_page_owner_id( $post_id );

		if ( $owner_id ) {

			$owner = new WP_User( $owner_id );

			echo  "  Email: " . $owner->user_email . "  ***** " . $owner->first_name . " " . $owner->last_name;

		} else {

			_e( 'Nobody', 'pffw' ); 

		}

	}

	

	/**

	 * Register the column as sortable

	 */

	public function user_column_register_sortable( $columns ) {

		$columns['pffw_owner'] = 'pffw_owner';

	

		return $columns;

	}

	

	/**

	 * Handle sorting of data

	 */

	public function user_column_orderby( $vars ) {

		if ( isset( $vars['orderby'] ) && 'pffw_owner' == $vars['orderby'] ) {

			$vars = array_merge( $vars, array(

					'meta_key' 	=> 'pffw_owner',

					'orderby' 	=> 'meta_value'

				) );

		}

	

		return $vars;

	}

	

	/*------- CUSTOMISATION OF THE EDIT PAGE OF A PRIVATE FILES ------------------------------------------------------*/



	/**

	 * Register some additional boxes on the page to edit the files

	 */

	public function register_edit_page_meta_boxes() {		

		add_meta_box( 

				'pffw_private_page_owner', 

				__('Owner', 'pffw'), 

				array( &$this, 'print_owner_meta_box'), 

				'pffw_private_page', 

				'normal', 'high');

	}



	/**

	 * Print the metabox to select the owner of the file

	 */

	public function print_owner_meta_box() {

		global $post;

		wp_nonce_field( plugin_basename(__FILE__), 'wp_pffw_nonce_owner' );

	

		$current_uid = $this->private_page_addon->get_page_owner_id( $post->ID );		

		$all_users = get_users();

		

		do_action( "pffw_private_page_owner_meta_box_header" );

?>

		<div id="pffw-owner" class="metabox-row">

			<span class="label"><label for="pffw_owner"><?php _e('Select the owner of this page', 'pffw');?></label></span> 	

			<span class="field">

				<select name="pffw_owner" id="pffw_owner">

<?php 			foreach ( $all_users as $u ) :

					$selected =  ( $current_uid!=$u->ID ? '' : ' selected="selected"' );

?>

					<option value="<?php echo $u->ID;?>"<?php echo $selected; ?>><?php echo  "  Email: " . $u->user_email . "  ***** " . $u->first_name . " " . $u->last_name; ?>

					</option>

<?php 			endforeach; ?>				

				</select>

			</span>

		</div>

<?php

		do_action( "pffw_private_page_owner_meta_box_footer" );

	}

	

	/**

	 * Print the eventual errors that occured during a post save/update

	 */

	public function print_save_post_messages() {

		$notices = $this->get_save_post_notices();

		if ( $notices ) {

			foreach ( $notices as $n ) {

				echo sprintf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $n['type'] ), esc_html( $n['msg'] ) );

			}

		}

		$this->clear_save_post_notices();

	}

	

	/**

	 * Remove the notices stored in the session for save posts

	 */

	private function clear_save_post_notices() {

		if ( isset( $_SESSION['pffw_private_page_save_post_notices'] ) ) {

			unset( $_SESSION['pffw_private_page_save_post_notices'] ); 

		}

	}



	/**

	 * Remove the stored notices

	 */

	private function get_save_post_notices() {

		return empty( $_SESSION[ 'pffw_private_page_save_post_notices' ] ) 

				? false 

				: $_SESSION['pffw_private_page_save_post_notices'];

	}

	

	public function add_save_post_notice( $msg, $type = 'error' ) {

		if ( empty( $_SESSION[ 'pffw_private_page_save_post_notices' ] ) ) {

			$_SESSION[ 'pffw_private_page_save_post_notices' ] = array();

	 	}

	 	$_SESSION[ 'pffw_private_page_save_post_notices' ][] = array(

				'type' 	=> $type,

				'msg' 	=> $msg );

	}

	

	/**

	 * Callback to handle saving a post

	 *  

	 * @param int $post_id

	 * @param string $post

	 * @return void|unknown

	 */

	public function do_save_post( $post_id, $post = null ) {

		global $post;

		

		// When auto-saving, we don't do anything

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	

		// Only take care of our own post type

		if ( !$post || get_post_type( $post->ID )!='pffw_private_page' ) return;

	

		// Other addons can do something before we save

		do_action( "pffw_private_page_before_do_save_post" );

		

		// Save the owner details

		if ( !wp_verify_nonce( $_POST['wp_pffw_nonce_owner'], plugin_basename(__FILE__) ) ) return $post_id;



		$previous_owner_id = $this->private_page_addon->get_page_owner_id( $post_id );

		$new_owner_id = $_POST['pffw_owner'];

		update_post_meta( $post_id, 'pffw_owner', $new_owner_id );

		

		// Other addons can do something after we save

		do_action( "pffw_private_page_after_do_save_post", $post_id, $this->private_page_addon, $this );

	}



	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/



	public function add_settings_tab( $tabs ) {

		$tabs[ 'pffw_private_pages' ] = __( 'Private Pages', 'pffw' );

		return $tabs;

	}

	

	/**

	 * Add our fields to the settings page

	 * 

	 * @param PFFW_Settings $pffw_settings The settings class

	 */

	public function print_settings( $pffw_settings, $options_group ) {

		add_settings_section(

				'pffw_private_pages_addon_general',

				__('General settings', 'pffw'),

				array( &$this, 'print_frontend_section_info' ),

				PFFW_Settings::$OPTIONS_PAGE_SLUG

			);



		add_settings_field(

				self::$OPTION_ENABLE_ADDON,

				__('Enable pages', 'pffw'),

				array( &$pffw_settings, 'print_input_field' ),

				PFFW_Settings::$OPTIONS_PAGE_SLUG,

				'pffw_private_pages_addon_general',

				array(

					'option_id' => self::$OPTION_ENABLE_ADDON,

					'type' 		=> 'checkbox',

					'after'		=> 

						__( 'Check this to enable the private pages add-on.', 'pffw' ) )

			);

/*		

		add_settings_section(

				'pffw_private_pages_addon_frontend',

				__('Frontend Integration', 'pffw'),

				array( &$this, 'print_frontend_section_info' ),

				PFFW_Settings::$OPTIONS_PAGE_SLUG

			);

		

		add_settings_field(

				self::$OPTION_FILE_LIST_MODE, 

				__('Page list', 'pffw'),

				array( &$pffw_settings, 'print_select_field' ), 

				PFFW_Settings::$OPTIONS_PAGE_SLUG,

				'pffw_private_pages_addon_frontend',

				array( 

					'option_id' => self::$OPTION_FILE_LIST_MODE, 

					'options'	=> array( 

						'plain' 	=> __( "Don't group files", 'pffw' ),

						'year' 		=> __( 'Group by year', 'pffw' ),

						'category' 	=> __( 'Group by category', 'pffw' ) ),

	    			'after'	=> '<p class="description">'

	    				. __( 'You can choose how files will be organized by default in the customer area.', 'pffw' )

	    				. '</p>' )

			);	

*/

	}

	

	/**

	 * Validate our options

	 * 

	 * @param PFFW_Settings $pffw_settings

	 * @param array $input

	 * @param array $validated

	 */

	public function validate_options( $validated, $pffw_settings, $input ) {		

		$pffw_settings->validate_boolean( $input, $validated, self::$OPTION_ENABLE_ADDON );

//		$pffw_settings->validate_enum( $input, $validated, self::$OPTION_FILE_LIST_MODE, 

//				array( 'plain', 'year', 'category' ) );

		

		return $validated;

	}

	

	/**

	 * Set the default values for the options

	 * 

	 * @param array $defaults

	 * @return array

	 */

	public static function set_default_options( $defaults ) {

		$defaults[ self::$OPTION_ENABLE_ADDON ] = false;

		// $defaults[ self::$OPTION_FILE_LIST_MODE ] = 'year';



		$admin_role = get_role( 'administrator' );

		if ( $admin_role ) {

			$admin_role->add_cap( 'pffw_pp_edit' );

			$admin_role->add_cap( 'pffw_pp_read' );

		}

		

		return $defaults;

	}

	

	/**

	 * Print some info about the section

	 */

	public function print_frontend_section_info() {

		// echo '<p>' . __( 'Options for the private files add-on.', 'pffw' ) . '</p>';

	}



	// General options

	public static $OPTION_ENABLE_ADDON					= 'enable_private_pages';



	// Frontend options

	// public static $OPTION_FILE_LIST_MODE				= 'frontend_page_list_mode';

		

	/** @var PFFW_Plugin */

	private $plugin;



	/** @var PFFW_PrivatePageAddOn */

	private $private_page_addon;

}

	

// This filter needs to be executed too early to be registered in the constructor

add_filter( 'pffw_default_options', array( 'PFFW_PrivatePageAdminInterface', 'set_default_options' ) );



endif; // if (!class_exists('PFFW_PrivatePageAdminInterface')) :