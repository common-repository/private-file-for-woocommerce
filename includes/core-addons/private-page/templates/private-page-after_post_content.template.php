	<?php if ( is_user_logged_in() ) { ?>
 	<a href="<?php echo wc_get_account_endpoint_url( 'private' ); ?>" title="<?php _e('Back to private section','pffw'); ?>"><?php _e('Back to private section','pffw'); ?></a>
 <?php } 
 else { ?>
 	<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('Login / Register','woocommerce'); ?>"><?php _e('Login / Register','woocommerce'); ?></a>
 <?php } ?>