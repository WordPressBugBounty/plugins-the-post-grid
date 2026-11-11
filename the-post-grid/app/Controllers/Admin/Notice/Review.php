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
 * Review class.
 */
class Review {

	/**
	 * Review notice.
	 *
	 * @return void
	 */
	public function __construct() {
		if ( Fns::is_black_friday_active() ) {
			return;
		}
		add_action( 'admin_notices', [ $this, 'render_review_notice' ] );
		add_action( 'admin_init', [ __CLASS__, 'update_notice_status' ] );
	}

	/**
	 * Check if review notice should be shown or not
	 *
	 * @return void
	 */

	public function render_review_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$spare_me     = get_option( 'rttpg_spare_me', '0' );
		$install_date = (int) get_option( 'rttpg_plugin_activation_time' );
		$remind_time  = (int) get_option( 'rttpg_remind_me' );
		$now          = time();

		// Early exit if user opted out permanently or already rated.
		if ( in_array( $spare_me, [ '1', '3' ], true ) ) {
			return;
		}

		// Calculate date thresholds.
		$ten_days_after_install    = $install_date + ( 10 * DAY_IN_SECONDS );
		$remind_due                = $remind_time + ( 15 * DAY_IN_SECONDS );
		$should_show_reminder      = $remind_time && ( $now >= $remind_due ); //15 days later from reminding
		$should_show_after_install = $install_date && ( $now >= $ten_days_after_install ) && $spare_me !== '2';

		if ( $should_show_reminder || $should_show_after_install ) {
			$this->rttpg_display_admin_notice();
		}
	}

	/**
	 * Display Admin Notice, asking for a review
	 *
	 * @return void
	 */
	public function rttpg_display_admin_notice() {
		global $pagenow;

		$exclude_pages = [
			'themes.php',
			'users.php',
			'tools.php',
			'options-general.php',
			'options-writing.php',
			'options-reading.php',
			'options-discussion.php',
			'options-media.php',
			'options-permalink.php',
			'options-privacy.php',
			'edit-comments.php',
			'upload.php',
			'media-new.php',
			'admin.php',
			'import.php',
			'export.php',
			'site-health.php',
			'export-personal-data.php',
			'erase-personal-data.php',
		];

		if ( in_array( $pagenow, $exclude_pages, true ) ) {
			return;
		}

		$nonce         = wp_create_nonce( 'rttpg_notice_nonce' );
		$current_url   = self::rttpg_current_admin_url();
		$dont_disturb  = add_query_arg( [ '_wpnonce' => $nonce, 'rttpg_spare_me' => '1' ], $current_url );
		$remind_me     = add_query_arg( [ '_wpnonce' => $nonce, 'rttpg_remind_me' => '1' ], $current_url );
		$rated         = add_query_arg( [ '_wpnonce' => $nonce, 'rttpg_rated' => '1' ], $current_url );
		$review_url    = 'https://wordpress.org/support/plugin/the-post-grid/reviews/?filter=5#new-post';
		$plugin_name   = 'The Post Grid';
		$image_url     = rtTPG()->get_assets_uri( 'images/post-grid-gif.gif' );
		$star_svg      = '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z"/></svg>';
		$allowed_svg   = [
			'svg'  => [ 'viewbox' => true, 'aria-hidden' => true, 'focusable' => true ],
			'path' => [ 'd' => true ],
		];
		self::admin_scripts();
		?>
        <div class="notice rttpg-review-notice" role="region" aria-label="<?php echo esc_attr__( 'Rate The Post Grid', 'the-post-grid' ); ?>">
            <img class="rttpg-review-notice__logo" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $plugin_name ); ?>" width="56" height="56"/>

            <div class="rttpg-review-notice__body">
                <div class="rttpg-review-notice__head">
                    <h3 class="rttpg-review-notice__title">
						<?php
						/* translators: %s: plugin name */
						echo esc_html( sprintf( __( 'Enjoying %s?', 'the-post-grid' ), $plugin_name ) );
						?>
                    </h3>
                    <span class="rttpg-review-notice__stars" aria-hidden="true">
						<?php echo wp_kses( str_repeat( $star_svg, 5 ), $allowed_svg ); ?>
					</span>
                </div>

                <p class="rttpg-review-notice__text">
					<?php esc_html_e( 'Thanks for building with us! A quick 5-star review on WordPress.org helps other people discover the plugin and keeps us improving it.', 'the-post-grid' ); ?>
                </p>

                <div class="rttpg-review-notice__actions">
                    <a href="<?php echo esc_url( $review_url ); ?>" class="rttpg-review-btn rttpg-review-btn--primary" target="_blank" rel="noopener noreferrer">
						<?php echo wp_kses( $star_svg, $allowed_svg ); ?>
						<?php esc_html_e( 'Leave a review', 'the-post-grid' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $rated ); ?>" class="rttpg-review-btn rttpg-review-btn--secondary">
						<?php esc_html_e( 'I already did', 'the-post-grid' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $remind_me ); ?>" class="rttpg-review-btn rttpg-review-btn--link">
						<?php esc_html_e( 'Remind me later', 'the-post-grid' ); ?>
                    </a>
                </div>
            </div>

            <a href="<?php echo esc_url( $dont_disturb ); ?>" class="rttpg-review-notice__close" title="<?php echo esc_attr__( "Don't show again", 'the-post-grid' ); ?>">
                <span class="screen-reader-text"><?php esc_html_e( "Dismiss this notice and don't show it again", 'the-post-grid' ); ?></span>
            </a>
        </div>
		<?php
	}

	/**
	 * Current admin URL.
	 *
	 * @return string
	 */
	protected static function rttpg_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri ?? '' );

		if ( ! $uri ) {
			return '';
		}

		return remove_query_arg(
			[
				'_wpnonce',
				'_wc_notice_nonce',
				'wc_db_update',
				'wc_db_update_nonce',
				'wc-hide-notice',
			],
			admin_url( $uri )
		);
	}

	/**
	 * Remove the notice for the user if review already done
	 *
	 * @return void
	 */
	public static function update_notice_status() {
		// Only administrators may change the site-wide review notice state.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Verify nonce
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'rttpg_notice_nonce' ) ) {
			return;
		}

		// Map GET parameters to update callbacks
		$actions = [
			'rttpg_spare_me'  => function () {
				update_option( 'rttpg_spare_me', '1' );
			},
			'rttpg_remind_me' => function () {
				$now = time();
				update_option( 'rttpg_remind_me', $now );
				update_option( 'rttpg_spare_me', '2' );
			},
			'rttpg_rated'     => function () {
				update_option( 'rttpg_rated', 'yes' );
				update_option( 'rttpg_spare_me', '3' );
			},
		];

		// Loop through actions and execute if GET parameter exists and equals 1
		foreach ( $actions as $key => $callback ) {
			if ( isset( $_GET[ $key ] ) && absint( $_GET[ $key ] ) === 1 ) {
				$callback();
			}
		}
	}


	/**
	 * Admin Scripts
	 * @return void
	 */
	public static function admin_scripts() {
		?>
        <style>
            .rttpg-review-notice {
                --rttpg-brand: #4c6fff;
                --rttpg-brand-dark: #3a5be8;
                --rttpg-text: #1d2327;
                --rttpg-muted: #50575e;
                --rttpg-border: #e2e4e9;
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 16px;
                margin: 5px 0 15px;
                padding: 18px 48px 18px 18px;
                border: 1px solid var(--rttpg-border);
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 4px 12px rgba(16, 24, 40, .04);
            }

            .rttpg-review-notice.notice {
                border-left-width: 1px;
            }

            .rttpg-review-notice__logo {
                flex: 0 0 auto;
                width: 56px;
                height: 56px;
            }

            .rttpg-review-notice__body {
                flex: 1 1 auto;
                min-width: 0;
            }

            .rttpg-review-notice__head {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 6px 10px;
            }

            .rttpg-review-notice .rttpg-review-notice__title {
                margin: 0;
                padding: 0;
                color: var(--rttpg-text);
                font-size: 15px;
                font-weight: 600;
                line-height: 1.4;
            }

            .rttpg-review-notice__stars {
                display: inline-flex;
                gap: 2px;
                color: #f5a623;
            }

            .rttpg-review-notice__stars svg {
                width: 14px;
                height: 14px;
                fill: currentColor;
            }

            .rttpg-review-notice .rttpg-review-notice__text {
                margin: 4px 0 0;
                padding: 0;
                color: var(--rttpg-muted);
                font-size: 13px;
                line-height: 1.6;
            }

            .rttpg-review-notice__actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
                margin-top: 14px;
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                min-height: 34px;
                padding: 0 14px;
                border: 1px solid transparent;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 500;
                line-height: 1;
                text-decoration: none;
                white-space: nowrap;
                box-shadow: none;
                cursor: pointer;
                transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn:focus {
                outline: none;
                box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--rttpg-brand);
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn svg {
                width: 14px;
                height: 14px;
                fill: currentColor;
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn--primary,
            div.notice.rttpg-review-notice a.rttpg-review-btn--primary:focus {
                background: var(--rttpg-brand);
                color: #fff;
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn--primary:hover {
                background: var(--rttpg-brand-dark);
                color: #fff;
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn--secondary,
            div.notice.rttpg-review-notice a.rttpg-review-btn--secondary:focus {
                border-color: #d0d5dd;
                background: #fff;
                color: var(--rttpg-text);
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn--secondary:hover {
                border-color: #b4bac4;
                background: #f6f7f9;
                color: var(--rttpg-text);
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn--link,
            div.notice.rttpg-review-notice a.rttpg-review-btn--link:focus {
                padding: 0 8px;
                background: transparent;
                color: var(--rttpg-muted);
            }

            div.notice.rttpg-review-notice a.rttpg-review-btn--link:hover {
                color: var(--rttpg-brand-dark);
                text-decoration: underline;
                text-underline-offset: 3px;
            }

            div.notice.rttpg-review-notice a.rttpg-review-notice__close {
                position: absolute;
                top: 10px;
                right: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 6px;
                color: #8c8f94;
                text-decoration: none;
                transition: background-color .15s ease, color .15s ease;
            }

            div.notice.rttpg-review-notice a.rttpg-review-notice__close:before {
                content: "\f335";
                font: normal 20px/1 dashicons;
            }

            div.notice.rttpg-review-notice a.rttpg-review-notice__close:hover,
            div.notice.rttpg-review-notice a.rttpg-review-notice__close:focus {
                background: #f0f0f1;
                color: var(--rttpg-text);
                outline: none;
                box-shadow: none;
            }

            @media (max-width: 600px) {
                .rttpg-review-notice {
                    padding: 16px 40px 16px 16px;
                    gap: 12px;
                }

                .rttpg-review-notice__logo {
                    width: 40px;
                    height: 40px;
                }
            }
        </style>
		<?php
	}

}