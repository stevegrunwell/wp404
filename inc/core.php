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

	/**
	 * Fires when the visitor has arrived on a 404 page, but before reporters are executed.
	 *
	 * @param WP_Query $wp_query The current WP_Query object.
	 */
	do_action( 'wp404_before_report', $wp_query );

	/**
	 * Assemble the WP404 report for the current 404 error.
	 *
	 * @param array    $report The data that has been compiled for this 404 error.
	 * @param WP_Query $wp_query The WP_Query object that determined the 404 status.
	 */
	$report = apply_filters( 'wp404_report_data', array(), $wp_query );

	// Don't write anything if $report comes as a boolean FALSE.
	if ( false === $report ) {
		return false;
	}

	// Write to the error log.
	error_log( sprintf(
		/**
			* Translators: The first placeholder is the request URI, the second an array of report data.
		  */
		_x( '[WP404] %s %s', 'prefix for error_log entries', 'wp404' ),
		add_query_arg( null, null ),
		print_r( $report, true )
	) );
}
add_action( 'template_redirect', __NAMESPACE__ . '\template_redirect', 1 );

/**
 * Load the plugin textdomain.
 */
function load_textdomain() {
	load_plugin_textdomain( 'wp404', false, WP404_DIR . '/includes' );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\load_textdomain' );
