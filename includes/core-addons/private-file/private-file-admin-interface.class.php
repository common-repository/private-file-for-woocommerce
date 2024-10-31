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

if (!class_exists('PFFW_PrivateFileAdminInterface')) :

/**
 * Administation area for private files
 * 
 * 
 */
class PFFW_PrivateFileAdminInterface {
	
	public function __construct( $plugin, $private_file_addon ) {
		$this->plugin = $plugin;
		$this->private_file_addon = $private_file_addon;

		// Settings
		add_filter( 'pffw_addon_settings_tabs', array( &$this, 'add_settings_tab' ), 10, 1 );
		add_action( 'pffw_addon_print_settings_pffw_private_files', array( &$this, 'print_settings' ), 10, 2 );
		add_filter( 'pffw_addon_validate_options_pffw_private_files', array( &$this, 'validate_options' ), 10, 3 );
		
		if ( $plugin->get_option( self::$OPTION_ENABLE_ADDON ) ) {
			// Admin menu
			add_action('pffw_admin_submenu_pages', array( &$this, 'add_menu_items' ), 10 );
			
			// File listing
			add_filter( 'manage_edit-pffw_private_file_columns', array( &$this, 'user_column_register' ));
			add_action( 'manage_pffw_private_file_posts_custom_column', array( &$this, 'user_column_display'), 10, 2 );
			add_filter( 'manage_edit-pffw_private_file_sortable_columns', array( &$this, 'user_column_register_sortable' ));
			add_filter( 'request', array( &$this, 'user_column_orderby' ));
	
			// File edit page
			add_action( 'admin_menu', array( &$this, 'register_edit_page_meta_boxes' ));
			add_action( 'save_post', array( &$this, 'do_save_post' ));
			add_action( 'admin_notices', array( &$this, 'print_save_post_messages' ));
			add_filter( 'upload_dir', array( &$this, 'custom_upload_dir' ));
			add_action( 'post_edit_form_tag' , array( &$this, 'post_edit_form_tag' ));
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
					'page_title'	=> __( 'Private Files', 'pffw' ),
					'title'			=> $separator . __( 'Private Files', 'pffw' ),
					'slug'			=> "edit.php?post_type=pffw_private_file",
					'function' 		=> null,
					'capability'	=> 'pffw_pf_edit'
				),
				array(
					'page_title'	=> __( 'New Private File', 'pffw' ),
					'title'			=> __( 'New Private File', 'pffw' ),
					'slug'			=> "post-new.php?post_type=pffw_private_file",
					'function' 		=> null,
					'capability'	=> 'pffw_pf_edit'
				),
				array(
					'page_title'	=> __( 'Private File Categories', 'pffw' ),
					'title'			=> __( 'Private File Categories', 'pffw' ),
					'slug'			=> "edit-tags.php?taxonomy=pffw_private_file_category",
					'function' 		=> null,
					'capability'	=> 'pffw_pf_edit'
				)
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
	
		$owner_id = $this->private_file_addon->get_file_owner_id( $post_id );
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
	 * Alter the edit form tag to say we have files to upload
	 */
	public function post_edit_form_tag() {
		global $post;
		if ( !$post || get_post_type($post->ID)!='pffw_private_file' ) return;
		echo ' enctype="multipart/form-data" autocomplete="off"';
	}
	
	/**
	 * Register some additional boxes on the page to edit the files
	 */
	public function register_edit_page_meta_boxes() {
		add_meta_box( 
				'pffw_private_file_upload', 
				__('File', 'pffw'), 
				array( &$this, 'print_upload_meta_box'), 
				'pffw_private_file', 
				'normal', 'high');
		
		add_meta_box( 
				'pffw_private_file_owner', 
				__('Owner', 'pffw'), 
				array( &$this, 'print_owner_meta_box'), 
				'pffw_private_file', 
				'normal', 'high');
	}

	/**
	 * Print the metabox to upload a file
	 */
	public function print_upload_meta_box() {
		global $post;
		wp_nonce_field( plugin_basename(__FILE__), 'wp_pffw_nonce_file' );
	
		$current_file = get_post_meta( $post->ID, 'pffw_private_file_file', true );

		do_action( "pffw_private_file_upload_meta_box_header" );
?>
		
<?php	if ( !empty( $current_file ) && isset( $current_file['url'] ) ) : ?>
		<div id="pffw-current-file" class="metabox-row">
			<p><?php _e('Current file:', 'pffw');?> 
				<a href="<?php PFFW_PrivateFileThemeUtils::the_file_link( $post->ID, 'view' ); ?>" target="_blank">
					<?php echo basename($current_file['file']); ?></a>
			</p>
		</div>		
<?php 	endif; ?> 

		<div id="pffw-upload-file" class="metabox-row">
			<span class="label"><label for="pffw_private_file_file"><?php _e('Upload a file', 'pffw');?></label></span> 	
			<span class="field"><input type="file" name="pffw_private_file_file" id="pffw_private_file_file" /></span>
		</div>
				
<?php 
		do_action( "pffw_private_file_upload_meta_box_footer" );
	}

	/**
	 * Print the metabox to select the owner of the file
	 */
	public function print_owner_meta_box() {
		global $post;
		wp_nonce_field( plugin_basename(__FILE__), 'wp_pffw_nonce_owner' );
	
		$current_uid = $this->private_file_addon->get_file_owner_id( $post->ID );		
		$all_users = get_users();
		
		do_action( "pffw_private_file_owner_meta_box_header" );
?>
		<div id="pffw-owner" class="metabox-row">
			<span class="label"><label for="pffw_owner"><?php _e('Select the owner of this file', 'pffw');?></label></span> 	
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
		do_action( "pffw_private_file_owner_meta_box_footer" );
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
		if ( isset( $_SESSION['pffw_private_file_save_post_notices'] ) ) {
			unset( $_SESSION['pffw_private_file_save_post_notices'] ); 
		}
	}

	/**
	 * Remove the stored notices
	 */
	private function get_save_post_notices() {
		return empty( $_SESSION[ 'pffw_private_file_save_post_notices' ] ) 
				? false 
				: $_SESSION['pffw_private_file_save_post_notices'];
	}
	
	public function add_save_post_notice( $msg, $type = 'error' ) {
		if ( empty( $_SESSION[ 'pffw_private_file_save_post_notices' ] ) ) {
			$_SESSION[ 'pffw_private_file_save_post_notices' ] = array();
	 	}
	 	$_SESSION[ 'pffw_private_file_save_post_notices' ][] = array(
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
		if ( !$post || get_post_type( $post->ID )!='pffw_private_file' ) return;
	
		// Other addons can do something before we save
		do_action( "pffw_private_file_before_do_save_post" );
		
		// Save the owner details
		if ( !wp_verify_nonce( $_POST['wp_pffw_nonce_owner'], plugin_basename(__FILE__) ) ) return $post_id;

		$previous_owner_id = $this->private_file_addon->get_file_owner_id( $post_id );
		$new_owner_id = $_POST['pffw_owner'];
		update_post_meta( $post_id, 'pffw_owner', $new_owner_id );

		// Save the file
		if ( !wp_verify_nonce( $_POST['wp_pffw_nonce_file'], plugin_basename(__FILE__) ) ) return $post_id;

		// If nothing to upload but owner changed, we'll simply move the file
		$previous_file = get_post_meta( $post_id, 'pffw_private_file_file', true );		
		
		if ( empty( $_FILES['pffw_private_file_file']['name'] ) ) {
			if ( $previous_file ) {
				$previous_file['path'] = $this->plugin->get_user_file_path( 
						$previous_owner_id, $previous_file['file'], true );
	
				if ( $previous_owner_id!=$new_owner_id 
						&& file_exists( $previous_file['path'] ) ) {
	
					$new_file_path = $this->plugin->get_user_file_path( 
							$new_owner_id, $previous_file['file'], true );
					if ( copy( $previous_file['path'], $new_file_path ) ) unlink( $previous_file['path'] );
	
					$new_file = $previous_file;
					$new_file['path'] = $new_file_path;
					update_post_meta( $post_id, 'pffw_private_file_file', $previous_file );
					
					pffw_log_debug( 'moved private file from ' . $previous_file['path'] . ' to ' . $new_file_path);
				}
			}
			
			// Other addons can do something after we save
			do_action( "pffw_private_file_after_do_save_post", $post_id, $this->private_file_addon, $this );
		
			return $post_id;
		}

		// Do some file type checking on the uploaded file if needed
		$new_file_name = $_FILES['pffw_private_file_file']['name']; 
		$supported_types = apply_filters( 'pffw_private_file_supported_types', null );
		if ( $supported_types!=null ) {
			$arr_file_type = wp_check_filetype( basename( $_FILES['pffw_private_file_file']['name'] ) );
			$uploaded_type = $arr_file_type['type'];
			
			if ( !in_array( $uploaded_type, $supported_types ) ) {
				$msg =  sprintf( __("This file type is not allowed. You can only upload: %s", 'pffw',
							implode( ', ', $supported_types ) ) );
				pffw_log_debug( $msg );
				
				$this->add_save_post_notice( $msg );
				return;
			}
		}
		
		// Delete the existing file if any
		if ( $previous_file ) {
			$previous_file['path'] = $this->plugin->get_user_file_path( 
					$previous_owner_id, $previous_file['file'], true );

			if ( $previous_file['path'] && file_exists( $previous_file['path'] ) ) {
				unlink( $previous_file['path'] );
				pffw_log_debug( 'deleted old private file from ' . $previous_file['path'] );
			}
		}
		
		// Use the WordPress API to upload the file
		$upload = wp_handle_upload( $_FILES['pffw_private_file_file'], array( 'test_form' => false ) );
		
		if ( empty( $upload ) ) {
			$msg = sprintf( __( 'An unknown error happened while uploading your file.', 'pffw' ) );
			pffw_log_debug( $msg );
			$this->add_save_post_notice( $msg );
		} else if ( isset( $upload['error'] ) ) {
			$msg = sprintf( __( 'An error happened while uploading your file: %s', 'pffw' ), $upload['error'] );
			pffw_log_debug( $msg );
			$this->add_save_post_notice( $msg );
		} else {
			$upload['file'] = basename( $upload['file'] );
			update_post_meta( $post_id, 'pffw_private_file_file', $upload );
			pffw_log_debug( 'Uploaded new private file: ' . print_r( $upload, true ) );

			do_action( "pffw_private_file_after_new_upload" );
		}
		
		// Other addons can do something after we save
		do_action( "pffw_private_file_after_do_save_post", $post_id, $this->private_file_addon, $this );
	}

	public function custom_upload_dir( $default_dir ) {
		if ( ! isset( $_POST['post_ID'] ) || $_POST['post_ID'] < 0 ) return $default_dir;	
		if ( $_POST['post_type'] != 'pffw_private_file' ) return $default_dir;
		if ( ! isset( $_POST['pffw_owner'] ) ) return $default_dir;	
	
		$dir = $this->plugin->get_base_upload_directory();
		$url = $this->plugin->get_base_upload_url();
	
		$bdir = $dir;
		$burl = $url;
	
		$subdir = '/' . $this->plugin->get_user_storage_directory( $_POST[ 'pffw_owner' ] );
		
		$dir .= $subdir;
		$url .= $subdir;
	
		$custom_dir = array( 
			'path'    => $dir,
			'url'     => $url, 
			'subdir'  => $subdir, 
			'basedir' => $bdir, 
			'baseurl' => $burl,
			'error'   => false, 
		);
	
		return $custom_dir;
	}

	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/

	public function add_settings_tab( $tabs ) {
		$tabs[ 'pffw_private_files' ] = __( 'Private Files', 'pffw' );
		return $tabs;
	}
	
	/**
	 * Add our fields to the settings page
	 * 
	 * @param PFFW_Settings $pffw_settings The settings class
	 */
	public function print_settings( $pffw_settings, $options_group ) {
		add_settings_section(
				'pffw_private_files_addon_general',
				__('General settings', 'pffw'),
				array( &$this, 'print_frontend_section_info' ),
				PFFW_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_ENABLE_ADDON,
				__('Enable add-on', 'pffw'),
				array( &$pffw_settings, 'print_input_field' ),
				PFFW_Settings::$OPTIONS_PAGE_SLUG,
				'pffw_private_files_addon_general',
				array(
					'option_id' => self::$OPTION_ENABLE_ADDON,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'Check this to enable the private files add-on.', 'pffw' ) )
			);
		
		add_settings_section(
				'pffw_private_files_addon_frontend',
				__('Frontend Integration', 'pffw'),
				array( &$this, 'print_frontend_section_info' ),
				PFFW_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				self::$OPTION_SHOW_AFTER_POST_CONTENT,
				__('Show after post', 'pffw'),
				array( &$pffw_settings, 'print_input_field' ),
				PFFW_Settings::$OPTIONS_PAGE_SLUG,
				'pffw_private_files_addon_frontend',
				array(
					'option_id' => self::$OPTION_SHOW_AFTER_POST_CONTENT,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'Show the view and download links below the post content for a customer file.', 'pffw' ) )
			);
		
		add_settings_field(
				self::$OPTION_FILE_LIST_MODE, 
				__('File list', 'pffw'),
				array( &$pffw_settings, 'print_select_field' ), 
				PFFW_Settings::$OPTIONS_PAGE_SLUG,
				'pffw_private_files_addon_frontend',
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

		add_settings_field(
				self::$OPTION_HIDE_EMPTY_CATEGORIES,
				__('Empty categories', 'pffw'),
				array( &$pffw_settings, 'print_input_field' ),
				PFFW_Settings::$OPTIONS_PAGE_SLUG,
				'pffw_private_files_addon_frontend',
				array(
					'option_id' => self::$OPTION_HIDE_EMPTY_CATEGORIES,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'When listing files by category, empty categories will be hidden if you check this.', 
							'pffw' ) )
			);

		add_settings_section(
				'pffw_private_files_addon_storage',
				__('File Storage', 'pffw'),
				array( &$this, 'print_storage_section_info' ),
				PFFW_Settings::$OPTIONS_PAGE_SLUG
			);
	}
	
	/**
	 * Validate our options
	 * 
	 * @param PFFW_Settings $pffw_settings
	 * @param array $input
	 * @param array $validated
	 */
	public function validate_options( $validated, $pffw_settings, $input ) {
		// TODO OUTPUT ALLOWED FILE TYPES
		
		$pffw_settings->validate_boolean( $input, $validated, self::$OPTION_ENABLE_ADDON );
		$pffw_settings->validate_boolean( $input, $validated, self::$OPTION_SHOW_AFTER_POST_CONTENT );
		$pffw_settings->validate_enum( $input, $validated, self::$OPTION_FILE_LIST_MODE, 
				array( 'plain', 'year', 'category' ) );
		$pffw_settings->validate_boolean( $input, $validated, self::$OPTION_HIDE_EMPTY_CATEGORIES );
		
		return $validated;
	}
	
	/**
	 * Set the default values for the options
	 * 
	 * @param array $defaults
	 * @return array
	 */
	public static function set_default_options( $defaults ) {
		$defaults[ self::$OPTION_ENABLE_ADDON ] = true;
		$defaults[ self::$OPTION_SHOW_AFTER_POST_CONTENT ] = true;
		$defaults[ self::$OPTION_FILE_LIST_MODE ] = 'year';
		$defaults[ self::$OPTION_HIDE_EMPTY_CATEGORIES ] = true;

		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'pffw_pf_edit' );
			$admin_role->add_cap( 'pffw_pf_read' );
		}
		
		return $defaults;
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_frontend_section_info() {
		// echo '<p>' . __( 'Options for the private files add-on.', 'pffw' ) . '</p>';
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_storage_section_info() {
		$storage_dir = $this->plugin->get_base_upload_directory();
		$sample_storage_dir = $storage_dir . '/' . $this->plugin->get_user_storage_directory( get_current_user_id() );
		
		$required_perms = '775';
		$current_perms = substr( sprintf('%o', fileperms( $storage_dir ) ), -3);
		
		echo '<div class="pffw-section-description">';
		echo '<p>' 
				. sprintf( __( 'The files will be stored in the following directory: <code>%s</code>.', 'pffw' ),
						$storage_dir ) 
				. '</p>';

		echo '<p>'
				. sprintf( __( 'Each user has his own sub-directory. For instance, yours is: <code>%s</code>.', 'pffw' ),
						$sample_storage_dir )
				. '</p>';

		if ( $required_perms > $current_perms ) {
			echo '<p style="color: red;">' 
				. sprintf( __('That directory should at least have the permissions set to 750. Currently it is '
						. '%s. You should adjust that directory permissions as upload or download might not work ' 
						. 'properly.', 'pffw' ), $current_perms ) 
				. '</p>';
		}
		echo '</div>';
	}

	// General options
	public static $OPTION_ENABLE_ADDON					= 'enable_private_files';

	// Frontend options
	public static $OPTION_SHOW_AFTER_POST_CONTENT		= 'frontend_show_after_post_content';
	public static $OPTION_FILE_LIST_MODE				= 'frontend_file_list_mode';
	public static $OPTION_HIDE_EMPTY_CATEGORIES			= 'frontend_hide_empty_file_categories';
		
	/** @var PFFW_Plugin */
	private $plugin;

	/** @var PFFW_PrivateFileAddOn */
	private $private_file_addon;
}
	
// This filter needs to be executed too early to be registered in the constructor
add_filter( 'pffw_default_options', array( 'PFFW_PrivateFileAdminInterface', 'set_default_options' ) );

endif; // if (!class_exists('PFFW_PrivateFileAdminInterface')) :