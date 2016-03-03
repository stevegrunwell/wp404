<?php
/**
 * Tests for the main plugin functionality.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404;

use WP_Mock as M;

class CoreTest extends TestCase {

	protected $testFiles = array(
		'core.php',
	);

	public function test_template_redirect() {
		global $wp_query;

		$this->markTestIncomplete( 'Not yet verifying the log output' );

		M::wpFunction( 'is_404', array(
			'times'  => 1,
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'times'  => 1,
			'args'   => array( null, null ),
			'return' => 'URI',
		) );

		M::onFilter( 'wp404_report_data' )
			->with( array( 'uri' => 'URI' ), $wp_query )
			->reply( array( 'foo' => 'bar' ) );

		M::wpFunction( '_x', array(
			'times'  => 1,
			'return' => 'PREFIX: %s',
		) );

		M::wpFunction( 'wp_json_encode', array(
			'times'  => 1,
			'args'   => array( array( 'foo' => 'bar' ), JSON_PRETTY_PRINT ),
			'return' => 'JSON_DATA',
		) );

		Core\template_redirect();
	}

	public function test_template_redirect_returns_early_if_not_404() {
		M::wpFunction( 'is_404', array(
			'times'  => 1,
			'return' => false,
		) );

		$this->assertFalse( Core\template_redirect() );
	}

	public function test_template_redirect_returns_early_if_report_is_empty() {
		global $wp_query;

		M::wpFunction( 'is_404', array(
			'times'  => 1,
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'return' => 'URI',
		) );

		M::onFilter( 'wp404_report_data' )
			->with( array( 'uri' => 'URI' ), $wp_query )
			->reply( false );

		$this->assertFalse( Core\template_redirect() );
	}

}
