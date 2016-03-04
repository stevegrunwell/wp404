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
		'reporters.php',
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
			'return' => 'PREFIX: %s %s',
		) );

		M::wpFunction( __NAMESPACE__ . '\error_log', array(
			'times'  => 1,
			'args'   => array( 'PREFIX: URI ' . print_r( array(), true ) ),
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

	public function test_template_redirect_returns_early_if_report_is_false() {
		global $wp_query;

		M::wpFunction( 'is_404', array(
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'return' => 'URI',
		) );

		M::onFilter( 'wp404_report_data' )
			->with( array(), $wp_query )
			->reply( false );

		$this->assertFalse( Core\template_redirect() );
	}

	public function test_template_redirect_still_reports_with_empty_data() {
		global $wp_query;

		$this->markTestIncomplete( 'Not yet verifying the log output' );

		M::wpFunction( 'is_404', array(
			'return' => true,
		) );

		M::wpFunction( 'add_query_arg', array(
			'return' => 'URI',
		) );

		M::onFilter( 'wp404_report_data' )
			->with( array(), $wp_query )
			->reply( array() );

		M::wpFunction( '_x', array(
			'times'  => 1,
			'return' => "PREFIX: %s %s",
		) );

		Core\template_redirect();
	}

	public function test_register_default_reporters_with_implicit_method() {
		$callback = 'server_superglobal';

		// This test depends on this function still being around.
		if ( ! function_exists( '\WP404\Reporters\server_superglobal' ) ) {
			$this->fail( 'The Reporters\server_superglobal() reporter is missing!' );
		}

		M::onFilter( 'wp404_default_reporters' )
			->with( $this->get_default_reporters() )
			->reply( array( $callback ) );

		M::expectFilterAdded( 'wp404_report_data', '\WP404\Reporters\\' . $callback, 10, 2 );

		Core\register_default_reporters();
	}

	public function test_register_default_reporters_with_implicit_method_in_global_namespace() {
		$callback = 'mytheme_function';

		// This test depends on this function *not* being around.
		if ( function_exists( '\WP404\Reporters\mytheme_function' ) ) {
			$this->fail( 'We defined mytheme_function() as a function that would never exist. Little did we know...' );
		}

		M::onFilter( 'wp404_default_reporters' )
			->with( $this->get_default_reporters() )
			->reply( array( $callback ) );

		M::expectFilterAdded( 'wp404_report_data', $callback, 10, 2 );

		Core\register_default_reporters();
	}

	public function test_register_default_reporters_with_explicit_method() {
		$callback = 'Namespace\method';

		M::onFilter( 'wp404_default_reporters' )
			->with( $this->get_default_reporters() )
			->reply( array( $callback ) );

		M::expectFilterAdded( 'wp404_report_data', $callback, 10, 2 );

		Core\register_default_reporters();
	}

	public function test_register_default_reporters_with_array_definition() {
		$callback = array(
			'reporter' => 'Namespace\method',
			'priority' => 25,
		);

		M::onFilter( 'wp404_default_reporters' )
			->with( $this->get_default_reporters() )
			->reply( array( $callback ) );

		M::wpPassthruFunction( 'wp_parse_args', array(
			'times'  => 1,
		) );

		M::expectFilterAdded( 'wp404_report_data', $callback['reporter'], $callback['priority'], 2 );

		Core\register_default_reporters();
	}

	public function test_register_default_reporters_with_class_method() {
		$callback = array( 'MyClass', 'method' );

		M::onFilter( 'wp404_default_reporters' )
			->with( $this->get_default_reporters() )
			->reply( array( $callback ) );

		M::expectFilterAdded( 'wp404_report_data', $callback, 10, 2 );

		Core\register_default_reporters();
	}

	public function test_register_default_reporters_with_nested_class_method() {
		$callback = array(
			'reporter' => array( 'MyClass', 'method' ),
			'priority' => 25,
		);

		M::onFilter( 'wp404_default_reporters' )
			->with( $this->get_default_reporters() )
			->reply( array( $callback ) );

		M::wpPassthruFunction( 'wp_parse_args', array(
			'times'  => 1,
		) );

		M::expectFilterAdded( 'wp404_report_data', $callback['reporter'], $callback['priority'], 2 );

		Core\register_default_reporters();
	}

	// Shortcut to get the array with the default reporters.
	protected function get_default_reporters() {
		return array(
			'\WP404\Reporters\server_superglobal',
			'\WP404\Reporters\post_exists',
		);
	}

}
