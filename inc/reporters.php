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
 *
 * @todo Apply some better logic to the filtered $server array construction.
 */
function server_superglobal( $report ) {
	$whitelist = array(
		'SERVER_SOFTWARE',
		'REQUEST_URI',
		'PATH',
		'USER',
		'HOME',
		'FCGI_ROLE',
		'QUERY_STRING',
		'REQUEST_METHOD',
		'CONTENT_TYPE',
		'CONTENT_LENGTH',
		'SCRIPT_NAME',
		'DOCUMENT_URI',
		'DOCUMENT_ROOT',
		'SERVER_PROTOCOL',
		'REQUEST_SCHEME',
		'GATEWAY_INTERFACE',
		'REMOTE_ADDR',
		'REMOTE_PORT',
		'SERVER_ADDR',
		'SERVER_PORT',
		'SERVER_NAME',
		'REDIRECT_STATUS',
		'SCRIPT_FILENAME',
		'HTTP_HOST',
		'HTTP_CONNECTION',
		'HTTP_CACHE_CONTROL',
		'HTTP_ACCEPT',
		'HTTP_UPGRADE_INSECURE_REQUESTS',
		'HTTP_USER_AGENT',
		'HTTP_DNT',
		'HTTP_ACCEPT_ENCODING',
		'HTTP_ACCEPT_LANGUAGE',
		'PHP_SELF',
		'REQUEST_TIME_FLOAT',
		'REQUEST_TIME',
	);

	/**
	 * Filter the $_SERVER keys that should be able to appear in reports.
	 *
	 * By default, keys that contain potentially sensitive data (such as HTTP_COOKIE) will be
	 * stripped for security purposes.
	 */
	$whitelist = apply_filters( 'wp404_server_superglobal_whitelisted_keys', $whitelist );
	$server    = array();

	// Build our filtered $server array.
	foreach ( $whitelist as $key ) {
		$server[ $key ] = isset( $_SERVER[ $key ] ) ? $_SERVER[ $key ] : null;
	}

	return array_merge( $report, array(
		'$_SERVER' => $server,
	) );
}

/**
 * Try to determine if we have a post ID and, if so, get data directly from the database (bypassing
 * any sort of cache) to get that post data.
 *
 * @global $wpdb
 *
 * @param array    $report   The WP404 report array.
 * @param WP_Query $wp_query The WP_Query object that determined the 404 status.
 * @return The filtered $report array.
 */
function post_exists( $report, $wp_query ) {
	global $wpdb;

	$post = array();

	// WP_Query found something, but the user can't see it.
	if ( 0 < $wp_query->found_posts ) {
		$post = $wpdb->get_row( $wp_query->request ); // WPCS: unprepared SQL ok.

	} elseif ( $post_id = get_query_var( 'p', false ) ) {

		// We have a post ID in the query string.
		$post = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1",
			$post_id
		) );
	}

	return array_merge( $report, array(
		'post_data' => $post,
	) );
}

/**
 * If the SAVEQUERIES constant is defined as TRUE, WordPress will log all the queries that have
 * been made, which can help in some extreme debugging situations.
 *
 * @global $wpdb
 *
 * @param array $report The WP404 report array.
 * @return The filtered $report array.
 */
function queries( $report ) {
	global $wpdb;

	$queries = ! empty( $wpdb->queries ) ? $wpdb->queries : __( 'No query data was saved.', 'wp404' );

	return array_merge( $report, array(
		'queries' => $queries,
	) );
}
