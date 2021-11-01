<?php

/**
 * Provides the markup for any WP Editor field
 *
 * @link       http://digitalapps.co
 * @since      1.0.0
 *
 * @package     WP_Global_Site_Tag
 * @subpackage  WP_Global_Site_Tag/admin/partials
 */

if ( ! empty( $atts['label'] ) ) {

    ?><label for="<?php

    echo esc_attr( $atts['id'] );

    ?>"><?php

        esc_html_e( $atts['label'], 'wp-global-site-tag' );

    ?>: </label><?php

} ?>

<textarea
    id="<?php echo esc_attr( $atts['id'] ); ?>"
    class="widefat"
    rows="5"
    name="<?php echo esc_attr( $atts['name'] ); ?>"><?php echo esc_html_e( $atts['value'] ); ?>
</textarea>

<span class="description"><?php esc_html_e( $atts['description'], 'wp-global-site-tag' ); ?></span>