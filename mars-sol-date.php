<?php
/**
 * Plugin Name:       Mars Sol Date
 * Description:       A dynamic WordPress block to display the post's publication date in Mars sol units, with Sol 1 as the timestamp of the first post on the site.
 * Version:           0.1.0
 * Author:            Brandon Kraft
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mars-sol-date
 *
 * @package mars-sol-date
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get or set the Unix timestamp of the site's first post across public types.
 * Uses a persistent option for performance. Refreshed if a new earliest post is published.
 *
 * @return int|false Unix timestamp or false on failure/no posts.
 */
function mars_sol_date_get_first_post_time() {
	$cached = get_option( 'mars_sol_date_first_post_time' );
	if ( $cached && is_numeric( $cached ) && $cached > 0 ) {
		return (int) $cached;
	}
	// All public types except attachment.
	$post_types = get_post_types(
		array(
			'public' => true,
		),
		'names'
	);
	if ( isset( $post_types['attachment'] ) ) {
		unset( $post_types['attachment'] );
	}
	$args         = array(
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'post_status'    => 'publish',
		'post_type'      => array_values( $post_types ),
		'fields'         => 'ids',
	);
	$oldest_posts = get_posts( $args );
	// Fallback: only 'post' type if none found.
	if ( empty( $oldest_posts ) ) {
		$oldest_posts = get_posts(
			array(
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'post_type'      => 'post',
				'fields'         => 'ids',
			)
		);
	}
	if ( empty( $oldest_posts ) ) {
		return false;
	}
	$first_post      = get_post( $oldest_posts[0] );
	$first_post_time = $first_post ? get_post_time( 'U', false, $first_post ) : false;
	if ( $first_post_time ) {
		update_option( 'mars_sol_date_first_post_time', $first_post_time, false );
	}
	return $first_post_time;
}

/**
 * Reset earliest post time cache if a new post is saved/inserted that's earlier than the current first post.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    The post object.
 */
function mars_sol_date_reset_cache_on_post_change( $post_id, $post ) {
	if (
		wp_is_post_revision( $post_id ) ||
		! in_array( $post->post_status, array( 'publish', 'future' ), true )
	) {
		return;
	}
	$first_post_time = mars_sol_date_get_first_post_time();
	$this_post_time  = get_post_time( 'U', false, $post );
	if ( ! $first_post_time || ( $this_post_time && $this_post_time < $first_post_time ) ) {
		delete_option( 'mars_sol_date_first_post_time' );
		mars_sol_date_get_first_post_time();
	}
}

/**
 * Hooks: Reset earliest post time cache if needed.
 */
add_action( 'save_post', 'mars_sol_date_reset_cache_on_post_change', 10, 2 );
add_action( 'wp_insert_post', 'mars_sol_date_reset_cache_on_post_change', 10, 2 );

/**
 * Registers the block.
 *
 * @return void
 */
function mars_sol_date_block_init() {
	register_block_type( __DIR__ . '/build/' );
	wp_set_script_translations( 'mars-sol-date-date-editor-script', 'mars-sol-date' );
}
add_action( 'init', 'mars_sol_date_block_init' );
