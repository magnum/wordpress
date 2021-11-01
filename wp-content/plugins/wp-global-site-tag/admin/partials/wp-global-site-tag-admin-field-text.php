<?php

/**
 * Provides the markup for any text field
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package     WP_Global_Site_Tag
 * @subpackage  WP_Global_Site_Tag/admin/partials
 */

if ( ! empty( $atts['label'] ) ) {

    ?><label for="<?php echo esc_attr( $atts['id'] ); ?>"><?php esc_html_e( $atts['label'], 'wp-global-site-tag' ); ?>: </label><?php

}

?><input
    class="<?php echo esc_attr( $atts['class'] ); ?>"
    id="<?php echo esc_attr( $atts['id'] ); ?>"
    name="<?php echo esc_attr( $atts['name'] ); ?>"
    placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
    type="<?php echo esc_attr( $atts['type'] ); ?>"
    value="<?php echo esc_attr( $atts['value'] ); ?>"
    <?php echo esc_attr( $atts['attribute'] ); ?> /><?php

if ( ! empty( $atts['description'] ) ) {

    ?><span class="description"><?php esc_html_e( $atts['description'], 'wp-global-site-tag' ); ?></span><?php

}