<div class="pffw-private-file-container">
<h4><?php _e( 'Associated file', 'pffw' ); ?></h4>

<table class="pffw-private-file-list">
  <tbody>
		<tr class="pffw-private-file">
			<td class="title">
				<?php PFFW_PrivateFileThemeUtils::the_file_name( get_the_ID() ); ?>
			</td>
			<td class="download-link">
				<a href="<?php PFFW_PrivateFileThemeUtils::the_file_link( get_the_ID(), 'download' ); ?>" title="<?php esc_attr_e( 'Download', 'pffw' ); ?>">
					<?php _e( 'Download', 'pffw' ); ?></a>
			</td>
		</tr>
	</tbody>
</table>
</div>