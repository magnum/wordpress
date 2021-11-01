<?php

/**
 * Template Name: homepage1
 *
 * Template for displaying a page without sidebar even if a sidebar widget is published.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

get_header();
$container = get_theme_mod('understrap_container_type');
?>

<?php if (is_front_page()) : ?>
	<?php get_template_part('global-templates/hero'); ?>
<?php endif; ?>


<div class="wrapper" id="full-width-page-wrapper">

	<div class="<?php echo esc_attr($container); ?>" id="content">

		<div class="row">

			<div class="col-md-12 content-area" id="primary">

				<main class="site-main" id="main" role="main">
					<?php
					$query = new WP_Query(array(
						'post_type' => 'work',
					));
					?>
					<div class="row">
						<?php while ($query->have_posts()) : $query->the_post(); ?>
							<?php //get_template_part( 'loop-templates/content', 'page' ); 
								?>
							<div class="col-12 col-md-4">
								<h2><?php the_title(); ?></h2>
								<?php
									$feat_image = get_the_post_thumbnail();
									?>
								<?php echo $feat_image; ?>
							</div>
						<?php endwhile; // end of the loop. 
						?>
					</div>
				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #full-width-page-wrapper -->

<?php get_footer(); ?>