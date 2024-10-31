<h2 class="pffw_page_title"><?php _e( 'Hello', 'pffw'); ?></h2>
<p><?php _e( 'You must login to access your own customer area. '
	. 'If you do not have an account yet, please register or ' 
	. 'contact us so that we can create it.', 'pffw' ); ?></p>

<ul>
	<li><a href="<?php echo wp_login_url( get_permalink() ); ?>"><?php _e( 'Login', 'pffw' ); ?></a></li>
<?php if ( get_option( 'users_can_register' ) ) : ?>
	<li><a href="<?php echo wp_registration_url(); ?>"><?php _e( 'Register', 'pffw' ); ?></a></li>
<?php endif; ?>
</ul>
