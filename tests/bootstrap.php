<?php
/**
 * Bootstrap the test suite.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

if ( ! defined( 'PROJECT' ) ) {
	define( 'PROJECT', __DIR__ . '/../inc/' );
}

if ( ! file_exists( __DIR__ . '/../vendor/autoload.php' ) ) {
	throw new PHPUnit_Framework_Exception(
		'ERROR: You must use Composer to install the test suite\'s dependencies!' . PHP_EOL
	);
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/test-tools/TestCase.php';

// Ensure our error_log() calls are written to STDOUT.
ini_set( 'error_log', 'php://stdout' );

WP_Mock::bootstrap();
WP_Mock::tearDown();
