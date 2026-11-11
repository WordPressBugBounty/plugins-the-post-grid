<?php
/**
 * Notice Controller class.
 *
 * @package RT_TPG
 */

namespace RT\ThePostGrid\Controllers\Admin\Notice;

// Do not allow directly accessing this file.
use RT\ThePostGrid\Helpers\Fns;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Notice Controller class.
 */
class BlackFriday {

	/**
	 * Black friday notice.
	 *
	 * @return void
	 */
	public function __construct() {
		if ( ! Fns::is_black_friday_active() ) {
			return;
		}
		add_action( 'admin_notices', [ __CLASS__, 'render_notice' ], 1 );
		add_action( 'admin_footer', [ __CLASS__, 'admin_footer_scripts' ] );
		add_action( 'wp_ajax_rttpg_dismiss_black_friday_notice', [ __CLASS__, 'dismiss_black_friday_notice' ] );
	}

	/**
	 * Black friday notice.
	 *
	 * @return void
	 */
	public static function render_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$plugin_name   = 'The Post Grid';
		$download_link = 'https://www.radiustheme.com/downloads/the-post-grid-pro-for-wordpress/';
		?>
        <style>
            .tpg-black-friday-notice.notice { position: relative; margin: 5px 0 15px; padding: 0 !important; border: 0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 2px rgba(16, 24, 40, .06), 0 4px 12px rgba(16, 24, 40, .06); }
            .tpg-black-friday-notice .notice-dismiss { top: 50%; right: 8px; transform: translateY(-50%); width: 28px; height: 28px; padding: 0; border-radius: 50%; background: rgba(255, 255, 255, .95); justify-content: center; align-items: center; }
            .tpg-black-friday-notice .notice-dismiss:before { color: #1d2327; }
            .tpg-black-friday-notice .notice-dismiss:hover:before,
            .tpg-black-friday-notice .notice-dismiss:focus:before { color: #d63638; }
        </style>
        <div class="notice notice-info is-dismissible tpg-black-friday-notice">
            <a href="<?php echo esc_url( $download_link ); ?>" style="display: block" target="_blank">
                <img alt="<?php echo esc_attr( $plugin_name ); ?>"
                     style="width: 100%;display: block;min-height: 30px;object-fit: cover"
                     src="<?php echo esc_url( rtTPG()->get_assets_uri( 'images/offer/black-friday.webp' ) ); ?>"/>
            </a>
        </div>
		<?php
	}

	public static function admin_footer_scripts() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
        <script>
            document.addEventListener('click', function (e) {
                const button = e.target.closest('.tpg-black-friday-notice .notice-dismiss');
                if (!button || typeof ajaxurl === 'undefined') {
                    return;
                }
                e.preventDefault();

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: new URLSearchParams({
                        'action': 'rttpg_dismiss_black_friday_notice',
                        'nonce': <?php echo wp_json_encode( wp_create_nonce( 'rttpg-dismissible-notice' ) ); ?>
                    }),
                });

                const notice = button.closest('.tpg-black-friday-notice');
                if (notice) {
                    notice.remove();
                }
            });
        </script>
		<?php
	}

	public static function dismiss_black_friday_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'User permission error', 'the-post-grid' ), 403 );
		}
		check_ajax_referer( 'rttpg-dismissible-notice', 'nonce' );
		update_option( 'rttpg_dismiss_bf_notice_' . Fns::black_friday_season_year(), '1', false );
		wp_die();
	}

}