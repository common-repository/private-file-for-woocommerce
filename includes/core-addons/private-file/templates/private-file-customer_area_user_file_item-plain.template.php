<td class="date">
	<a title="<?php echo esc_attr( sprintf( __( 'Added on %s', 'pffw' ), get_the_date() ) ); ?>">
		<?php the_date(); ?></a>
</td>
<td class="title">
	<a title="<?php echo esc_attr( sprintf( __( 'Added on %s', 'pffw' ), get_the_date() ) ); ?>">
		<?php the_title(); ?></a>
</td>
<td class="view-link">
	<a href="<?php PFFW_PrivateFileThemeUtils::the_file_link( get_the_ID(), 'view' ); ?>" title="<?php esc_attr_e( 'View', 'pffw' ); ?>">	
		<?php _e( 'View', 'pffw' ); ?></a>
</td> 
<td class="download-link">
	<a href="<?php PFFW_PrivateFileThemeUtils::the_file_link( get_the_ID(), 'download' ); ?>" title="<?php esc_attr_e( 'Download', 'pffw' ); ?>">
		<?php _e( 'Download', 'pffw' ); ?></a>
</td>