<div class="pffw-private-page-container">

<h3><?php _e( 'Your pages', 'pffw' ); ?></h3>

<?php 
$current_user_id = get_current_user_id();

// Get user pages
$args = array(
		'post_type' 		=> 'pffw_private_page',
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

$pages_query = new WP_Query( apply_filters( 'pffw_user_pages_query_parameters', $args ) );
?>

<?php if ( $pages_query->have_posts() ) : ?>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			PFFW_INCLUDES_DIR . '/core-addons/private-page',
			"private-page-customer_area_user_page_item.template.php",
			'templates',
			"private-page-customer_area_user_page_item.template.php" ); 
?>	
	
<table class="pffw-private-page-list"><tbody>

<?php 	while ( $pages_query->have_posts() ) : $pages_query->the_post(); global $post; ?>

	<tr class="pffw-private-page"><?php	include( $item_template ); ?></tr>
		
<?php 	endwhile; ?>

</tbody></table>

<?php else : ?>
<?php 	include( $this->plugin->get_template_file_path(
					PFFW_INCLUDES_DIR . '/core-addons/private-page',
					'private-page-customer_area_no_user_pages.template.php',
					'templates' ));	?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

</div>