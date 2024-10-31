<?php


/**
 * Add new items into an array after a selected item.
 *
 * @param array $items
 * @param array $new_items
 * @param string $after
 * @return array
 */
function pffw_insert_tab_woocommerce_area( $items, $new_items, $after ) {
	// Search for the item position and +1 since is after the selected item key.
	$position = array_search( $after, array_keys( $items ) ) + 1;

	// Insert the new item.
	$array = array_slice( $items, 0, $position, true );
	$array += $new_items;
	$array += array_slice( $items, $position, count( $items ) - $position, true );

    return $array;
}

/**
 * Insert the new endpoint into the My Account menu.
 *
 * @param array $items
 * @return array
 */
function pffw_display_account_private( $items ) {
	$new_items = array();
	$new_items['private'] = __( 'Private', 'pffw' );

	// Add the new item after `orders`.
	return pffw_insert_tab_woocommerce_area( $items, $new_items, 'orders' );
}

add_filter( 'woocommerce_account_menu_items', 'pffw_display_account_private' );
//
add_action( 'woocommerce_account_private_endpoint', 'pffw_private_account_link_content' );
function pffw_private_account_link_content() {
    //include content of private files and private pages
    echo do_shortcode('[private-file-for-woocommerce]');
}
add_action( 'init', 'pffw_register_private_endpoint');
function pffw_register_private_endpoint() {
    add_rewrite_endpoint( 'private', EP_PAGES );
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'pffw_register_private_endpoint' );
//<br>
//<br>
//
function pffw_display_account_shared_page( $items ) {

	$new_items = array();
	$new_items['shared'] = __( 'Shared', 'pffw' );

	// Add the new item after `orders`.
	return pffw_insert_tab_woocommerce_area( $items, $new_items, 'orders' );
	
}

add_filter( 'woocommerce_account_menu_items', 'pffw_display_account_shared_page' );

function pffw_hide_shared_page_from_my_account () {
		$options = get_option('pffw_shared_page_options');
	if( $options ['checkbox'] == '' ) {
		$display = '
        <style>
 
.woocommerce-MyAccount-navigation-link.woocommerce-MyAccount-navigation-link--shared
{
    display:none!important;
}

        </style>
    
    ';
   
    echo $display;
 }
		
		
		}
	
add_action( 'woocommerce_before_account_navigation', 'pffw_hide_shared_page_from_my_account' );
//
add_action( 'woocommerce_account_shared_endpoint', 'pffw_shared_account_link_content' );
function pffw_shared_account_link_content() {
	$options = get_option('pffw_shared_page_options');
	if( $options ['checkbox'] == '1' ) {
    //include content shared page in my account woocommerce
echo get_option('shared_page');
	
}

}
add_action( 'init', 'pffw_register_shared_endpoint');
function pffw_register_shared_endpoint() {
	$options = get_option('pffw_shared_page_options');
	if( $options ['checkbox'] == '1' ) {
    add_rewrite_endpoint( 'shared', EP_PAGES );
	flush_rewrite_rules();
}}

register_activation_hook( __FILE__, 'pffw_register_shared_endpoint' );

//
add_action('admin_menu', 'pffw_add_global_shared_options');
function pffw_add_global_shared_options()  
{  
	$menu_title = __( 'Shared Page', 'pffw' );
	$options = get_option('pffw_shared_page_options');
	if( $options ['checkbox'] == '1' ) {
    add_options_page('Shared Page', $menu_title, 'manage_options', 'shared-page','pffw_global_shared_options');
	add_submenu_page( '#pffw', 'Shared Page', $menu_title, 'manage_options', 'options-general.php?page=shared-page','pffw_global_shared_options');
}
}


  function pffw_global_shared_options(){
	  

   if(isset($_POST['shared_page'])){
	 update_option( 'shared_page', wp_kses_post( stripslashes($_POST['shared_page'] ) ));
   }

?>
<div class='wrap'>
  <h2><?php esc_html_e( 'Shared Page with all customers in my account', 'pffw' ); ?></h2>
    <form method='post'>
      <?php
          $content = get_option('shared_page');
	      wp_enqueue_media();
	      if($content == ""){
			  
$content = "<h1><strong>Da qui puoi inserire il tuo contenuto</strong> <em>che sar&agrave; disponibile nell'area clienti woocommerce</em> nella sezione 'Sez. Condivisa'.</h1>
<p>Cancella questo contenuto, <strong>inserisci il tuo contenuto</strong> e poi <strong>salva le modifiche</strong>.</p>
<p>Accertati che ci sia<strong> il flag attivo</strong> inserito in <strong>Private file for Woocommerce/impostazioni/Pagina Condivisa</strong></p>
<p><em>Buon divertimento!</em></p>";
			  
}
          wp_editor( $content, 'shared_page', $settings = array('wpautop' => false,
    'media_buttons' => true,
    'textarea_rows' => 5,
    'tinymce' => true,
    'teeny' => true,
    'textarea_name' => 'shared_page',
    'quicktags' => true,
    'editor_height' => 600) );

          submit_button();
       ?>

   </form>
  </div><!-- .wrap -->
 <?php
}


//////


function pffw_shared_page_settings_text() {
    echo __( 'Check this to enable shared page add-on.', 'pffw' );
}

// add the admin settings and such
add_action('admin_init', 'pffw_shared_page_admin_init');

function pffw_shared_page_admin_init() {
	
	$settingstitle = __( 'Settings', 'pffw' );
	$enablesharedpage = __( 'Enable Shared Page', 'pffw' );
	
    register_setting('pffw_shared_page_options', 'pffw_shared_page_options', 'pffw_shared_page_options_validate');

    add_settings_section('pffw_shared_page_main', $settingstitle, 'pffw_shared_page_settings_text', 'shared-page-options');

    add_settings_field('pffw_shared_page_checkbox', $enablesharedpage, 'pffw_shared_page_setting_string', 'shared-page-options', 'pffw_shared_page_main');

}

function pffw_shared_page_setting_string() {

    $options = get_option('pffw_shared_page_options');

    echo "<input id='pffw_shared_page_checkbox' name='pffw_shared_page_options[checkbox]' type='checkbox' value='1'" . checked( 1, $options['checkbox'], false ) . " />";
}

// validate our options
function pffw_shared_page_options_validate($input) {

    $newinput['checkbox'] = trim($input['checkbox']);
    return $newinput;
}


