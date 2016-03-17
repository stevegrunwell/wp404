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

		$wp_query = new \stdClass;

		M::wpFunction( 'is_404', array(
			'times'  => 1,
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'times'  => 1,
			'args'   => array( null, null ),
			'return' => 'URI',
		) );

		M::expectAction( 'wp404_before_report', $wp_query );

		M::onFilter( 'wp404_report_data' )
			->with( array( 'uri' => 'URI' ), $wp_query )
			->reply( array( 'foo' => 'bar' ) );

		M::wpFunction( '_x', array(
			'times'  => 1,
			'return' => 'PREFIX: %s %s',
		) );

		M::wpFunction( __NAMESPACE__ . '\error_log', array(
			'times'  => 1,
			'args'   => array( 'PREFIX: URI ' . print_r( array(), true ) ),
		) );

		Core\template_redirect();

		$wp_query = null;
	}

	public function test_template_redirect_returns_early_if_not_404() {
		M::wpFunction( 'is_404', array(
			'times'  => 1,
			'return' => false,
		) );

		$this->assertFalse( Core\template_redirect() );
	}

	public function test_template_redirect_returns_early_if_report_is_false() {
		global $wp_query;

		$wp_query = new \stdClass;

		M::wpFunction( 'is_404', array(
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'return' => 'URI',
		) );

		M::expectAction( 'wp404_before_report', $wp_query );

		M::onFilter( 'wp404_report_data' )
			->with( array(), $wp_query )
			->reply( false );

		$this->assertFalse( Core\template_redirect() );

		$wp_query = null;
	}

	public function test_template_redirect_still_reports_with_empty_data() {
		global $wp_query;

		$this->markTestIncomplete( 'Not yet verifying the log output' );

		$wp_query = new \stdClass;

		M::wpFunction( 'is_404', array(
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'return' => 'URI',
		) );

		M::expectAction( 'wp404_before_report', $wp_query );

		M::onFilter( 'wp404_report_data' )
			->with( array(), $wp_query )
			->reply( array() );

		M::wpFunction( '_x', array(
			'times'  => 1,
			'return' => "PREFIX: %s %s",
		) );

		Core\template_redirect();

		$wp_query = null;
	}

}
