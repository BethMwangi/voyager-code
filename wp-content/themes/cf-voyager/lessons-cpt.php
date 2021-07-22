<?php

/**
 * Template Name: Lessons CPT
 *
 */

get_header();

$custom_terms = get_terms('lesson_types');

foreach ($custom_terms as $custom_term) {
	wp_reset_query();
	$args = array(
		'post_type' => 'cpt_lessons',
		'posts_per_page' => 5,
		'post_status' => 'publish',
		'tax_query' => array(
			array(
				'taxonomy' => 'lesson_types',
				'field' => 'slug',
				'terms' => $custom_term->slug,
			),
		),
	);
}

$the_query = new WP_Query($args);

?>

<?php
if ($the_query->have_posts()) : ?>
	<?php
	echo esc_html($custom_term->name); ?>
	<ul>
		<?php
		while ($the_query->have_posts()) : ?>
			<?php
			$the_query->the_post(); ?>

			<?php
			if (get_post_meta($post->ID, 'promoted_checkbox', true)) { ?>
				<li>
					<a href="<?php
					the_permalink(); ?>">
						<?php
						the_title(); ?>
					</a>
				</li>
				<?php
			} else { ?>
				<?php
			} ?>
			<?php
		endwhile; ?>
	</ul>
	<?php
endif;
?>
<?php
get_footer();

