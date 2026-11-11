<?php
/**
 * Shared renderer for seasonal promo notices.
 *
 * @package RT_TPG
 */

namespace RT\ThePostGrid\Controllers\Admin\Notice;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Renders a seasonal promo notice card (Summer Sale, Eid, ...).
 */
class PromoNotice {

	/**
	 * Whether the shared styles were already printed.
	 *
	 * @var bool
	 */
	private static $styles_printed = false;

	/**
	 * Built-in icons, keyed by name.
	 *
	 * @var array
	 */
	private static $icons = [
		'sun'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4.5"/><path d="M12 1.5v3M12 19.5v3M1.5 12h3M19.5 12h3M4.6 4.6l2.1 2.1M17.3 17.3l2.1 2.1M4.6 19.4l2.1-2.1M17.3 6.7l2.1-2.1" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>',
		'moon'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 14.6A8.6 8.6 0 0 1 9.4 3.5a8.6 8.6 0 1 0 11.1 11.1z"/></svg>',
		'arrow' => '<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M4 10h11M11 5.5 15.5 10 11 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
	];

	/**
	 * Render a promo notice.
	 *
	 * @param array $args {
	 *     @type string $class    Unique notice class, used by the dismiss script.
	 *     @type string $theme    Colour theme: 'summer' or 'eid'.
	 *     @type string $icon     Icon key from self::$icons.
	 *     @type string $label    Campaign label, e.g. "Summer Sale".
	 *     @type string $discount Discount badge, e.g. "Up to 40% off".
	 *     @type string $title    Headline.
	 *     @type string $text     Supporting sentence.
	 *     @type string $cta      Button text.
	 *     @type string $url      Button URL.
	 *     @type string $note     Small note next to the button.
	 * }
	 *
	 * @return void
	 */
	public static function render( array $args ) {
		$args = wp_parse_args(
			$args,
			[
				'class'    => '',
				'theme'    => 'summer',
				'icon'     => 'sun',
				'label'    => '',
				'discount' => '',
				'title'    => '',
				'text'     => '',
				'cta'      => __( 'Get the deal', 'the-post-grid' ),
				'url'      => 'https://www.radiustheme.com/downloads/the-post-grid-pro-for-wordpress/',
				'note'     => __( 'Limited-time offer', 'the-post-grid' ),
			]
		);

		$allowed_svg = [
			'svg'    => [ 'viewbox' => true, 'aria-hidden' => true, 'focusable' => true ],
			'path'   => [ 'd' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'fill' => true ],
			'circle' => [ 'cx' => true, 'cy' => true, 'r' => true ],
		];
		$icon        = self::$icons[ $args['icon'] ] ?? '';

		self::print_styles();
		?>
        <div class="notice is-dismissible rttpg-promo-notice rttpg-promo-notice--<?php echo esc_attr( $args['theme'] ); ?> <?php echo esc_attr( $args['class'] ); ?>">
            <img class="rttpg-promo-notice__logo" src="<?php echo esc_url( rtTPG()->get_assets_uri( 'images/post-grid-gif.gif' ) ); ?>" alt="<?php echo esc_attr__( 'The Post Grid', 'the-post-grid' ); ?>" width="56" height="56"/>

            <div class="rttpg-promo-notice__body">
                <div class="rttpg-promo-notice__badges">
                    <span class="rttpg-promo-notice__label"><?php echo wp_kses( $icon, $allowed_svg ); ?><?php echo esc_html( $args['label'] ); ?></span>
					<?php if ( $args['discount'] ) : ?>
                        <span class="rttpg-promo-notice__discount"><?php echo esc_html( $args['discount'] ); ?></span>
					<?php endif; ?>
                </div>

                <h3 class="rttpg-promo-notice__title"><?php echo esc_html( $args['title'] ); ?></h3>
                <p class="rttpg-promo-notice__text"><?php echo esc_html( $args['text'] ); ?></p>

                <div class="rttpg-promo-notice__actions">
                    <a href="<?php echo esc_url( $args['url'] ); ?>" class="rttpg-promo-notice__cta" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $args['cta'] ); ?>
						<?php echo wp_kses( self::$icons['arrow'], $allowed_svg ); ?>
                    </a>
					<?php if ( $args['note'] ) : ?>
                        <span class="rttpg-promo-notice__note"><?php echo esc_html( $args['note'] ); ?></span>
					<?php endif; ?>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Print the shared styles once per page.
	 *
	 * @return void
	 */
	private static function print_styles() {
		if ( self::$styles_printed ) {
			return;
		}
		self::$styles_printed = true;
		?>
        <style>
            div.notice.rttpg-promo-notice {
                --rttpg-brand: #4c6fff;
                --rttpg-brand-dark: #3a5be8;
                --rttpg-text: #1d2327;
                --rttpg-muted: #50575e;
                --rttpg-accent: #b45309;
                --rttpg-accent-bg: #fef3c7;
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 16px;
                margin: 5px 0 15px;
                padding: 18px 48px 18px 18px;
                border: 1px solid #e2e4e9;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 4px 12px rgba(16, 24, 40, .04);
            }

            div.notice.rttpg-promo-notice--eid {
                --rttpg-accent: #047857;
                --rttpg-accent-bg: #d1fae5;
            }

            .rttpg-promo-notice__logo {
                flex: 0 0 auto;
                width: 56px;
                height: 56px;
            }

            .rttpg-promo-notice__body {
                flex: 1 1 auto;
                min-width: 0;
            }

            .rttpg-promo-notice__badges {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .rttpg-promo-notice__label,
            .rttpg-promo-notice__discount {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 3px 9px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 600;
                line-height: 1.4;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            .rttpg-promo-notice__label {
                background: var(--rttpg-accent-bg);
                color: var(--rttpg-accent);
            }

            .rttpg-promo-notice__label svg {
                width: 13px;
                height: 13px;
                fill: currentColor;
            }

            .rttpg-promo-notice__discount {
                background: #fee2e2;
                color: #b91c1c;
            }

            div.notice.rttpg-promo-notice .rttpg-promo-notice__title {
                margin: 8px 0 0;
                padding: 0;
                color: var(--rttpg-text);
                font-size: 15px;
                font-weight: 600;
                line-height: 1.4;
            }

            div.notice.rttpg-promo-notice .rttpg-promo-notice__text {
                margin: 4px 0 0;
                padding: 0;
                color: var(--rttpg-muted);
                font-size: 13px;
                line-height: 1.6;
            }

            .rttpg-promo-notice__actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;
                margin-top: 14px;
            }

            div.notice.rttpg-promo-notice a.rttpg-promo-notice__cta {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                min-height: 34px;
                padding: 0 14px;
                border-radius: 6px;
                background: var(--rttpg-brand);
                color: #fff;
                font-size: 13px;
                font-weight: 500;
                line-height: 1;
                text-decoration: none;
                white-space: nowrap;
                box-shadow: none;
                transition: background-color .15s ease;
            }

            div.notice.rttpg-promo-notice a.rttpg-promo-notice__cta:hover {
                background: var(--rttpg-brand-dark);
                color: #fff;
            }

            div.notice.rttpg-promo-notice a.rttpg-promo-notice__cta:focus {
                outline: none;
                color: #fff;
                box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--rttpg-brand);
            }

            div.notice.rttpg-promo-notice a.rttpg-promo-notice__cta svg {
                width: 14px;
                height: 14px;
                transition: transform .15s ease;
            }

            div.notice.rttpg-promo-notice a.rttpg-promo-notice__cta:hover svg {
                transform: translateX(2px);
            }

            .rttpg-promo-notice__note {
                color: #8c8f94;
                font-size: 12px;
            }

            div.notice.rttpg-promo-notice .notice-dismiss {
                top: 10px;
                right: 10px;
                width: 28px;
                height: 28px;
                padding: 0;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            div.notice.rttpg-promo-notice .notice-dismiss:before {
                color: #8c8f94;
            }

            div.notice.rttpg-promo-notice .notice-dismiss:hover {
                background: #f0f0f1;
            }

            div.notice.rttpg-promo-notice .notice-dismiss:hover:before {
                color: var(--rttpg-text);
            }

            @media (max-width: 600px) {
                div.notice.rttpg-promo-notice {
                    padding: 16px 40px 16px 16px;
                    gap: 12px;
                }

                .rttpg-promo-notice__logo {
                    width: 40px;
                    height: 40px;
                }
            }
        </style>
		<?php
	}
}
