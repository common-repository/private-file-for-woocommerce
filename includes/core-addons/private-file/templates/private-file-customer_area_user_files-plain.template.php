<div class="pffw-private-file-container">

<h3><?php _e( 'Your files', 'pffw' ); ?></h3>

<?php 
$current_user_id = get_current_user_id();

// Get user files
$args = array(
		'post_type' 		=> 'pffw_private_file',
		'posts_per_page' 	=> -1,
		'orderby' 			=> 'date',
		'order' 			=> 'DESC',
		'meta_query' 		=> array(
				array(
						'key' 		=> 'pffw_owner',
						'value' 	=> $current_user_id,
						'compare' 	=> '='
				)
		)
);

$files_query = new WP_Query( apply_filters( 'pffw_user_files_query_parameters', $args ) );
?>

<?php if ( $files_query->have_posts() ) : ?>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			PFFW_INCLUDES_DIR . '/core-addons/private-file',
			"private-file-customer_area_user_file_item-{$display_mode}.template.php",
			'templates',
			"private-file-customer_area_user_file_item.template.php" ); 
?>	
	
<table class="pffw-private-file-list"><tbody>

<?php 	while ( $files_query->have_posts() ) : $files_query->the_post(); global $post; ?>

	<tr class="pffw-private-file"><?php	include( $item_template ); ?></tr>
		
<?php 	endwhile; ?>

</tbody></table>

<?php else : ?>
<?php 	include( $this->plugin->get_template_file_path(
					PFFW_INCLUDES_DIR . '/core-addons/private-file',
					'private-file-customer_area_no_user_files.template.php',
					'templates' ));	?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

</div>