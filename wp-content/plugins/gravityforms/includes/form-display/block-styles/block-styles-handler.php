<?php

namespace Gravity_Forms\Gravity_Forms\Form_Display\Block_Styles;

use GFCommon;
use Gravity_Forms\Gravity_Forms\Form_Display\Block_Styles\Views\Confirmation_View;
use Gravity_Forms\Gravity_Forms\Form_Display\Block_Styles\Views\Form_View;
use Gravity_Forms\Gravity_Forms\Theme_Layers\API\Fluent\Theme_Layer_Builder;

class Block_Styles_Handler {

	const NAME = 'block_styles';

	protected $defaults_map;

	public function __construct( $defaults_map ) {
		$this->defaults_map = $defaults_map;
	}

	public function handle() {
		$layer = new Theme_Layer_Builder();
		$layer->set_name( self::NAME )
		      ->set_form_css_properties( array( $this, 'form_css_properties' ) )
		      ->set_overidden_fields( $this->overriden_fields() )
		      ->set_styles( array( $this, 'styles' ) )
		      ->register();
	}

	public function form_css_properties( $form_id, $settings, $block_settings ) {
		$applied_settings = wp_parse_args( $block_settings, $this->defaults_map );

		// Bail early if orbital isn't applied.
		if ( $applied_settings['theme'] !== 'orbital' ) {
			return array();
		}

		$color_palette = GFCommon::generate_block_styles_palette( $applied_settings );

		return array(
			/* Global CSS API: Theme */
			'gform-theme-color-primary'              => $color_palette['primary']['color'],
			'gform-theme-color-primary-rgb'          => implode( ', ', $color_palette['primary']['color-rgb'] ),
			'gform-theme-color-primary-contrast'     => $color_palette['primary']['color-contrast'],
			'gform-theme-color-primary-contrast-rgb' => implode( ', ', $color_palette['primary']['color-contrast-rgb'] ),
			'gform-theme-color-primary-shade'        => $color_palette['primary']['color-shade'],

			'gform-theme-color-secondary'              => $color_palette['secondary']['color'],
			'gform-theme-color-secondary-rgb'          => implode( ', ', $color_palette['secondary']['color-rgb'] ),
			'gform-theme-color-secondary-contrast'     => $color_palette['secondary']['color-contrast'],
			'gform-theme-color-secondary-contrast-rgb' => implode( ', ', $color_palette['secondary']['color-contrast-rgb'] ),
			'gform-theme-color-secondary-shade'        => $color_palette['secondary']['color-shade'],

			'gform-theme-color-light'       => $color_palette['light']['color'],
			'gform-theme-color-light-shade' => $color_palette['light']['color-shade'],
			'gform-theme-color-light-tint'  => $color_palette['light']['color-tint'],

			'gform-theme-color-dark'       => $color_palette['dark']['color'],
			'gform-theme-color-dark-rgb'   => implode( ', ', $color_palette['dark']['color-rgb'] ),
			'gform-theme-color-dark-shade' => $color_palette['dark']['color-shade'],
			'gform-theme-color-dark-tint'  => $color_palette['dark']['color-tint'],

			'gform-theme-border-radius' => $applied_settings['inputBorderRadius'] . 'px',

			/* Global CSS API: Typography */
			'gform-theme-font-size-secondary' => $applied_settings['labelFontSize'] . 'px',
			'gform-theme-font-size-tertiary'  => $applied_settings['descriptionFontSize'] . 'px',

			/* Global CSS API: Icons */
			'gform-theme-icon-control-number' => "url(\"data:image/svg+xml,%3Csvg width='8' height='14' viewBox='0 0 8 14' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M4 0C4.26522 5.96046e-08 4.51957 0.105357 4.70711 0.292893L7.70711 3.29289C8.09763 3.68342 8.09763 4.31658 7.70711 4.70711C7.31658 5.09763 6.68342 5.09763 6.29289 4.70711L4 2.41421L1.70711 4.70711C1.31658 5.09763 0.683417 5.09763 0.292893 4.70711C-0.0976311 4.31658 -0.097631 3.68342 0.292893 3.29289L3.29289 0.292893C3.48043 0.105357 3.73478 0 4 0ZM0.292893 9.29289C0.683417 8.90237 1.31658 8.90237 1.70711 9.29289L4 11.5858L6.29289 9.29289C6.68342 8.90237 7.31658 8.90237 7.70711 9.29289C8.09763 9.68342 8.09763 10.3166 7.70711 10.7071L4.70711 13.7071C4.31658 14.0976 3.68342 14.0976 3.29289 13.7071L0.292893 10.7071C-0.0976311 10.3166 -0.0976311 9.68342 0.292893 9.29289Z' fill='{$color_palette['dark']['color-tint']}'/%3E%3C/svg%3E\")",
			'gform-theme-icon-control-select' => "url(\"data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M0.292893 0.292893C0.683417 -0.097631 1.31658 -0.097631 1.70711 0.292893L5 3.58579L8.29289 0.292893C8.68342 -0.0976311 9.31658 -0.0976311 9.70711 0.292893C10.0976 0.683417 10.0976 1.31658 9.70711 1.70711L5.70711 5.70711C5.31658 6.09763 4.68342 6.09763 4.29289 5.70711L0.292893 1.70711C-0.0976311 1.31658 -0.0976311 0.683418 0.292893 0.292893Z' fill='{$color_palette['dark']['color-tint']}'/%3E%3C/svg%3E\")",
			'gform-theme-icon-control-search' => "url(\"data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' width='640' height='640'%3E%3Cpath d='M256 128c-70.692 0-128 57.308-128 128 0 70.691 57.308 128 128 128 70.691 0 128-57.309 128-128 0-70.692-57.309-128-128-128zM64 256c0-106.039 85.961-192 192-192s192 85.961 192 192c0 41.466-13.146 79.863-35.498 111.248l154.125 154.125c12.496 12.496 12.496 32.758 0 45.254s-32.758 12.496-45.254 0L367.248 412.502C335.862 434.854 297.467 448 256 448c-106.039 0-192-85.962-192-192z' fill='{$color_palette['dark']['color-tint']}'/%3E%3C/svg%3E\")",

			/* Global CSS API: Controls - Default For All Types */
			'gform-theme-control-border-color' => $applied_settings['inputBorderColor'],
			'gform-theme-control-size'         => 'var(--gform-theme-control-size-' . $applied_settings['inputSize'] . ')',

			/* Global CSS API: Control - Choice (Checkbox, Radio, & Consent) */
			'gform-theme-control-choice-size'         => 'var(--gform-theme-control-choice-size-' . $applied_settings['inputSize'] . ')',
			'gform-theme-control-checkbox-check-size' => 'var(--gform-theme-control-checkbox-check-size-' . $applied_settings['inputSize'] . ')',
			'gform-theme-control-radio-check-size'    => 'var(--gform-theme-control-radio-check-size-' . $applied_settings['inputSize'] . ')',

			/* Global CSS API: Control - Label */
			'gform-theme-control-label-color-primary'    => $applied_settings['labelColor'],
			'gform-theme-control-label-color-secondary'  => $applied_settings['labelColor'],
			'gform-theme-control-label-color-tertiary'   => $applied_settings['descriptionColor'],
			'gform-theme-control-label-color-quaternary' => $applied_settings['descriptionColor'],

			/* Global CSS API: Control - Description */
			'gform-theme-control-description-color' => $applied_settings['descriptionColor'],

			/* Global CSS API: Control - Button */
			'gform-theme-control-button-font-size'              => 'var(--gform-theme-control-button-font-size-' . $applied_settings['inputSize'] . ')',
			'gform-theme-control-button-padding-inline'         => 'var(--gform-theme-control-button-padding-inline-' . $applied_settings['inputSize'] . ')',
			'gform-theme-control-button-size'                   => 'var(--gform-theme-control-button-size-' . $applied_settings['inputSize'] . ')',
			'gform-theme-control-button-border-color-secondary' => $applied_settings['inputBorderColor'],

			/* Global CSS API: Control - File */
			'gform-theme-control-file-button-background-color'       => $color_palette['secondary']['color-shade'],
			'gform-theme-control-file-button-background-color-hover' => GFCommon::darken_color( $color_palette['secondary']['color-shade'], 2 ),

			/* Global CSS API: Field - Page */
			'gform-theme-field-page-steps-number-color' => 'rgba(' . implode( ', ', GFCommon::darken_color( $applied_settings['labelColor'], 0, 'rgb' ) ) . ', 0.8)',
		);
	}

	private function overriden_fields() {
		return array(
			'form'         => Form_View::class,
			'confirmation' => Confirmation_View::class,
		);
	}

	public function styles( $form, $ajax, $settings, $block_settings ) {
		$theme_slug = \GFFormDisplay::get_form_theme_slug( $form );

		if ( $theme_slug !== 'orbital' ) {
			return array();
		}

		return array(
			'theme'      => array(
				array( 'gravity_forms_orbital_theme' ),
			),
			'foundation' => array(
				array( 'gravity_forms_theme_foundation' ),
			),
			'framework'  => array(
				array( 'gravity_forms_theme_framework' ),
			),
			'reset'      => array(
				array( 'gravity_forms_theme_reset' ),
			),
		);
	}

}
