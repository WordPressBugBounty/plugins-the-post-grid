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
class EidSpecial {

	/**
	 * Class Constructor
	 */
	public function __construct() {
		add_action( 'admin_notices', [ __CLASS__, 'eid_special_deal_admin_notice' ] );
		add_action( 'wp_ajax_dismiss_eid_notice', [ __CLASS__, 'dismiss_eid_notice' ] );
	}

	public static function eid_special_deal_admin_notice() {
		// Set expiration date (April 7)
		$expiration_date = strtotime( 'April 7, 2025' );

		// Check if the current date is past the expiration date
		if ( time() > $expiration_date ) {
			return;
		}

		// Check if notice is dismissed
		if ( get_user_meta( get_current_user_id(), 'dismissed_ramadan_notice', true ) ) {
			return;
		}


		PromoNotice::render(
			[
				'class'    => 'ramadan-notice',
				'theme'    => 'eid',
				'icon'     => 'moon',
				'label'    => __( 'Eid Special', 'the-post-grid' ),
				'discount' => __( 'Up to 40% off', 'the-post-grid' ),
				'title'    => __( 'Celebrate Eid with up to 40% off The Post Grid Pro', 'the-post-grid' ),
				'text'     => __( 'Unlock sliders, AJAX pagination, the archive builder and more premium layouts at a special Eid price.', 'the-post-grid' ),
			]
		);
		?>
        <script>
            jQuery(document).on('click', '.ramadan-notice .notice-dismiss', function () {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_eid_notice',
                    security: '<?php echo esc_js( wp_create_nonce( 'dismiss_eid_notice' ) ); ?>',
                })
            })
        </script>
		<?php
	}

	public static function dismiss_eid_notice() {
		check_ajax_referer( 'dismiss_eid_notice', 'security' );
		update_user_meta( get_current_user_id(), 'dismissed_ramadan_notice', true );
		wp_die();
	}

}