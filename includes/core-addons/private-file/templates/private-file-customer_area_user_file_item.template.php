<td class="title">
	<p><strong><?php the_title(); ?></strong></p>
	<p><?php echo esc_attr( sprintf( __( 'Added on %s', 'pffw' ), get_the_date() ) ); ?></p>
		
</td>
<td class="download-link">
	<a href="<?php PFFW_PrivateFileThemeUtils::the_file_link( get_the_ID(), 'download' ); ?>" title="<?php esc_attr_e( 'Download', 'pffw' ); ?>">
		<?php _e( 'Download', 'pffw' ); ?></a>
</td>