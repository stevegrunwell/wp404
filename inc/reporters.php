<?php
/**
 * WP404 reporters.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404\Reporters;

/**
 * Capture the $_SERVER superglobal.
 *
 * @param array $report The WP404 report array.
 * @return The filtered $report array.
 */
function server_superglobal( $report ) {
	return array_merge( $report, array(
		'$_SERVER' => $_SERVER,
	) );
}

/**
 * Try to determine if we have a post ID and, if so, get data directly from the database (bypassing
 * any sort of cache) to get that post data.
 *
 * @global $wpdb
 *
 * @param array $report The WP404 report array.
 * @param WP_Query $wp_query The WP_Query object that determined the 404 status.
 * @return The filtered $report array.
 */
function post_exists( $report, $wp_query ) {
	global $wpdb;

	$post = array();

	// WP_Query found something, but the user can't see it.
	if ( 0 < $wp_query->found_posts ) {
		$post = $wpdb->get_row( $wp_query->request );

	// We have a post ID in the query string.
	} elseif ( $post_id = get_query_var( 'p', false ) ) {
		$post = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1",
			$post_id
		) );
	}

	return array_merge( $report, array(
		'post_data' => $post,
	) );
}
