<?php
/**
 * Block Name: block1
 *
 * This is the template that displays the testimonial block.
 */

$image = get_field('image');
$id = 'work-' . $block['id'];
$align_class = $block['align'] ? 'align' . $block['align'] : '';

?>
<div id="<?php echo $id; ?>" class=" <?php echo $align_class; ?>">
    <h2><?php the_field('name'); ?></h2>
    <figure>
    	<img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
    </figure>
    <p>
      <?php the_field('description'); ?>
    </p>
</div>