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
	 * Fires when the visitor has arrived on a 404 page.
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
 * Register the default reporters.
 */
function register_default_reporters() {
	$defaults = array(
		'\WP404\Reporters\server_superglobal',
		'\WP404\Reporters\post_exists',
	);

	/**
	 * Filter the default reporters used by WP404.
	 *
	 * @param array An array of callable functions or methods that should be attached to the
	 *              wp404_report_data filter.
	 */
	$reporters = apply_filters( 'wp404_default_reporters', $defaults );

	foreach ( $reporters as $reporter ) {
		$reporter_parts = explode( '\\', $reporter );
		$priority       = 10;

		/*
		 * If this is true, we've been given a function with no indication as to its namespace.
		 *
		 * See if it's referring to a named reporter.
		 */
		if ( 1 === count( $reporter_parts ) ) {
			$function_name = end( $reporter_parts );

			if ( function_exists( '\WP404\Reporters\\' . $function_name ) ) {
				$reporter = '\WP404\Reporters\\' . $function_name;
			}
		}

		add_filter( 'wp404_report_data', $reporter, $priority, 2 );
	}
}

/**
 * Load the plugin textdomain.
 */
function load_textdomain() {
	load_plugin_textdomain( 'wp404', false, WP404_DIR . '/includes' );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\load_textdomain' );
