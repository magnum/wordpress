<?php
/**
 * The file contains the class the add the admin notice for end year sale.
 *
 * @package    miniorange-saml-20-single-sign-on
 * @author     miniOrange <info@miniorange.com>
 * @license    MIT/Expat
 * @link       https://miniorange.com
 */

/**
 * Adds End Year Sale Admin Notice for miniOrange SAML SSO Plugins
 */
class Mo_Saml_End_Year_Sale_Notice {
	/**
	 * Sale end date
	 *
	 * @var time
	 */
	private $sale_end_time;
	/**
	 * Initializing Sale banner
	 */
	public function __construct() {
		$this->sale_end_time = strtotime( '2025-01-05 23:59:59 ' . wp_timezone_string() );
		add_action( 'admin_notices', array( $this, 'display_end_year_sale_notice' ) );
		add_action( 'wp_ajax_mo_saml_dismiss_end_year_sale_notice', array( $this, 'dismiss_end_year_sale_notice' ) );
	}

	/**
	 * Show sale banner
	 *
	 * @return [html]
	 */
	public function display_end_year_sale_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( strtotime( current_time( 'mysql' ) ) > $this->sale_end_time ) {
			return;
		}

		// Check if notice has been dismissed.
		if ( get_option( 'mo_saml_end_year_sale_notice_dismissed' ) ) {
			if ( get_option( 'mo_saml_end_year_sale_notice_dismissed_time' ) && strtotime( current_time( 'mysql' ) ) > get_option( 'mo_saml_end_year_sale_notice_dismissed_time' ) ) {
				delete_option( 'mo_saml_end_year_sale_notice_dismissed' );
			}
			return;
		}
		?>        
		<div class="notice notice-info mo_saml_end_year_sale_notice">

			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/mini.png' ); ?>" alt="miniOrange Logo" class="mo_saml_end_year_sale_logo">
			
			<div style="display: flex;" class="mo_saml_end_year_sale_content">
				<div>
					<div class="mo_saml_end_year_sale_headline">
						miniOrange <span class="mo_saml_end_year_sale_theme_gradient">Year End Sale</span> is here!
					</div>
					
					<div class="mo_saml_end_year_sale_description">
						Go <strong>PRO</strong> with our biggest discount of the year. 
						Unlock premium features and save big on our SAML SSO plugin!
					</div>
				</div>
				<div class="mo_saml_end_year_sale_timer_container">
					<div class="mo_saml_end_year_sale_timer_title mo_saml_end_year_sale_theme_gradient">Ends in: </div>
					<div class="mo_saml_end_year_sale_timer">
						<div class="mo_saml_end_year_sale_timer_days mo_saml_time" id="mo-saml-days"></div>
						<span class="mo_saml_colon" id="mo-saml-colon-days">:</span>
						<div class="mo_saml_end_year_sale_timer_hours mo_saml_time" id="mo-saml-hours"></div>
						<span class="mo_saml_colon" id="mo-saml-colon-hours">:</span>
						<div class="mo_saml_end_year_sale_timer_mins mo_saml_time"id="mo-saml-minutes"></div>
						<span class="mo_saml_colon" id="mo-saml-colon-minutes">:</span>
						<div class="mo_saml_end_year_sale_timer_secs mo_saml_time" id="mo-saml-seconds"></div>
					</div>
				</div>
				<div class="mo_saml_end_year_sale_actions">
					<a href="https://plugins.miniorange.com/wp-saml-exclusive-deals#pricing" 
						target="_blank" 
						class="mo_saml_end_year_sale_cta">
						View Offers
					</a>
				</div>
			</div>
			
			<a href="#" 
				class="mo_saml_end_year_sale_close" 
				id="mo_saml_end_year_sale_notice_dismiss">
				&times;
			</a>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#mo_saml_end_year_sale_notice_dismiss').on('click', function(e) {
				e.preventDefault();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'mo_saml_dismiss_end_year_sale_notice',
						nonce: '<?php echo esc_attr( wp_create_nonce( 'mo_saml_end_year_sale_notice_nonce' ) ); ?>'
					},
					success: function(response) {
						$('.mo_saml_end_year_sale_notice').fadeOut();
					}
				});
			});
		});
		</script>
		<?php
	}
	/**
	 * Sale banner security
	 */
	public function dismiss_end_year_sale_notice() {
		// Verify nonce for security.
		check_ajax_referer( 'mo_saml_end_year_sale_notice_nonce', 'nonce' );

		// Set option to dismiss notice.
		update_option( 'mo_saml_end_year_sale_notice_dismissed', true );
		$dismiss_time = strtotime( current_time( 'mysql' ) ) + 604800;
		update_option( 'mo_saml_end_year_sale_notice_dismissed_time', $dismiss_time );
		wp_send_json_success();
	}
}

// Initialize the admin notice.
new Mo_Saml_End_Year_Sale_Notice();
?>
