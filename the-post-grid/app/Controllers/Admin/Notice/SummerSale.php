<?php
/**
 * Notice Controller class.
 *
 * @package RT_TPG
 */

namespace RT\ThePostGrid\Controllers\Admin\Notice;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Notice Controller class.
 */
class SummerSale {

	/**
	 * Class Constructor
	 */
	public function __construct() {
		add_action( 'admin_notices', [ __CLASS__, 'summer_special_deal_admin_notice' ] );
		add_action( 'wp_ajax_dismiss_summer_notice', [ __CLASS__, 'dismiss_summer_notice' ] );
	}

	public static function summer_special_deal_admin_notice() {
		// Set expiration date (April 7)
		$expiration_date = strtotime( 'May 31, 2025' );

		// Check if the current date is past the expiration date
		if ( time() > $expiration_date ) {
			return;
		}

		// Check if notice is dismissed
		if ( get_user_meta( get_current_user_id(), 'dismissed_summer_notice', true ) ) {
			return;
		}


		PromoNotice::render(
			[
				'class'    => 'summer-notice',
				'theme'    => 'summer',
				'icon'     => 'sun',
				'label'    => __( 'Summer Sale', 'the-post-grid' ),
				'discount' => __( 'Up to 40% off', 'the-post-grid' ),
				'title'    => __( 'Save up to 40% on The Post Grid Pro', 'the-post-grid' ),
				'text'     => __( 'Unlock sliders, AJAX pagination, the archive builder and more premium layouts at our best summer price.', 'the-post-grid' ),
			]
		);
		?>
        <script>
            jQuery(document).on('click', '.summer-notice .notice-dismiss', function () {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_summer_notice',
                    security: '<?php echo esc_js( wp_create_nonce( 'dismiss_summer_notice' ) ); ?>',
                })
            })
        </script>
		<?php
	}

	public static function dismiss_summer_notice() {
		check_ajax_referer( 'dismiss_summer_notice', 'security' );
		update_user_meta( get_current_user_id(), 'dismissed_summer_notice', true );
		wp_die();
	}

}