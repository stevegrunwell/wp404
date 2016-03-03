<?php
/**
 * Core plugin functionality.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404\Core;

/**
 * Capture details about a 404 request.
 *
 * @global $wp_query
 */
function template_redirect() {
	global $wp_query;

	if ( ! is_404() ) {
		return false;
	}

	$report = array(
		'uri' => add_query_arg( null, null ),
	);

	/**
	 * Fires when the visitor has arrived on a 404 page.
	 *
	 * @param array    $report The data that has been compiled for this 404 error.
	 * @param WP_Query $wp_query The WP_Query object that determined the 404 status.
	 */
	$report = apply_filters( 'wp404_report_data', $report, $wp_query );

	// Don't write anything if $report comes back empty.
	if ( ! $report ) {
		return false;
	}

	// Write to the error log.
	error_log( sprintf(
		_x( '[WP404] %s', 'prefix for error_log entries', 'wp404' ),
		wp_json_encode( $report, JSON_PRETTY_PRINT )
	) );
}
add_action( 'template_redirect', __NAMESPACE__ . '\template_redirect', 1 );
