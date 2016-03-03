<?php
/**
 * Plugin Name: WP404
 * Plugin URI:  https://github.com/stevegrunwell/wp404
 * Description: Get in-depth information about the 404 errors that occur on your WordPress site.
 * Version:     0.1.0
 * Author:      Steve Grunwell
 * Author URI:  https://stevegrunwell.com
 * License:     MIT
 * Text Domain: wp404
 * Domain Path: /languages
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404;

require_once __DIR__ . '/inc/core.php';
require_once __DIR__ . '/inc/reporters.php';

// Register the initial reporters.
add_filter( 'wp404_report_data', __NAMESPACE__ . '\Reporters\server_superglobal', 10, 2 );
add_filter( 'wp404_report_data', __NAMESPACE__ . '\Reporters\post_exists', 10, 2 );
