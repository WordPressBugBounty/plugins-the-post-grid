<?php

namespace RT\ThePostGrid\Controllers\Api;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

class GetCategories {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_post_route' ] );
	}

	public function register_post_route() {
		register_rest_route(
			'rttpg/v1',
			'categories',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_all_posts' ],
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	public function get_all_posts( $data ) {
		$send_data = [
			'categories' => [],
		];

		$category = $data['category_lists'] ?? [];

		if ( is_array( $category ) && count( $category ) > 0 ) {
			$categories = array_map( 'absint', wp_list_pluck( $category, 'value' ) );
		} else {
			$categories = get_terms(
				[
					'taxonomy'   => 'category',
					'orderby'    => 'count',
					'order'      => 'DESC',
					'hide_empty' => 0,
					'fields'     => 'ids',
					'number'     => 5,
				]
			);
		}

		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat ) {
				$cat_info = get_term( $cat, 'category' );
				if ( ! $cat_info || is_wp_error( $cat_info ) ) {
					continue;
				}
				$cat_thumb                 = function_exists( 'rtTpgPro' ) ? get_term_meta( $cat, rtTpgPro()->category_thumb_meta_key, true ) : 0;
				$cat_image                 = $cat_thumb ? wp_get_attachment_image_src( $cat_thumb, sanitize_text_field( $data['image_size'] ?? 'thumbnail' ) ) : false;
				$send_data['categories'][] = [
					'id'    => esc_html( $cat ),
					'name'  => esc_html( $cat_info->name ),
					'image' => $cat_image ? $cat_image[0] : '',
					'count' => $cat_info->count,
					'link'  => get_term_link( $cat_info ),

				];
			}
		} else {
			$send_data['message'] = 'No category found';
		}

		wp_reset_postdata();

		return rest_ensure_response( $send_data );
	}

}
