<?php
/**
 * WP404 listeners for request data.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404\Request;

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
add_filter( 'wp404_report_data', __NAMESPACE__ . '\server_superglobal' );
