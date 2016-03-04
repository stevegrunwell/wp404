<?php
/**
 * Tests for the various Reporters.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404;

use WP_Mock as M;
use Mockery;

class ReportersTest extends TestCase {

	/**
	 * Ideally we'll have an easier way of getting this from Reporters\server_superglobal(), but this
	 * should match the $whitelist definition in that function.
	 * @var array $defaultServerSuperglobalWhitelist
	 */
	protected $defaultServerSuperglobalWhitelist = array(
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

	protected $testFiles = array(
		'reporters.php',
	);

	public function test_server_superglobal() {
		$server  = $_SERVER;
		$_SERVER = array(
			'foo' => 'FOO VALUE',
			'baz' => 'BAZ VALUE',
		);

		M::onFilter( 'wp404_server_superglobal_whitelisted_keys' )
			->with( $this->defaultServerSuperglobalWhitelist )
			->reply( array( 'foo', 'bar' ) );

		$this->assertEquals( array(
			'$_SERVER' => array(
				'foo' => 'FOO VALUE',
				'bar' => '',
			),
		), Reporters\server_superglobal( array() ) );

		// Restore $_SERVER.
		$_SERVER = $server;
	}

	public function test_post_exists_with_found_posts() {
		global $wpdb;

		$wpdb = Mockery::mock( '\WP_Query' )->makePartial();
		$wpdb->shouldReceive( 'get_row' )
			->once()
			->with( 'REQUEST_SQL' )
			->andReturn( array(
				'ID'         => 17,
				'post_title' => 'Hello World',
			) );

		$wp_query = new \stdClass;
		$wp_query->found_posts = 1;
		$wp_query->request     = 'REQUEST_SQL';

		$this->assertEquals( array(
			'post_data' => array(
				'ID'         => 17,
				'post_title' => 'Hello World',
			),
		), Reporters\post_exists( array(), $wp_query ) );

		$wpdb = null;
	}

	public function test_post_exists_with_p_argument() {
		global $wpdb;

		M::wpFunction( 'get_query_var', array(
			'times'  => 1,
			'args'   => array( 'p', false ),
			'return' => 17,
		) );

		$wpdb = Mockery::mock( '\wpdb' );
		$wpdb->posts = 'TABLE';
		$wpdb->shouldReceive( 'prepare' )
			->once()
			->with( 'SELECT * FROM TABLE WHERE ID = %d LIMIT 1', 17 )
			->andReturn( 'PREPARED' );
		$wpdb->shouldReceive( 'get_row' )
			->once()
			->with( 'PREPARED' )
			->andReturn( array(
				'ID'         => 17,
				'post_title' => 'Hello World',
			) );

		$wp_query = new \stdClass;
		$wp_query->found_posts = 0;

		$this->assertEquals( array(
			'post_data' => array(
				'ID'         => 17,
				'post_title' => 'Hello World',
			),
		), Reporters\post_exists( array(), $wp_query ) );

		$wpdb = null;
	}

	public function test_post_exists_with_no_p_argument() {
		$wp_query = new \stdClass;
		$wp_query->found_posts = 0;

		M::wpFunction( 'get_query_var', array(
			'times'  => 1,
			'args'   => array( 'p', false ),
			'return' => false,
		) );

		$this->assertEquals( array(
			'post_data' => array(),
		), Reporters\post_exists( array(), $wp_query ) );
	}

	public function test_queries() {
		global $wpdb;

		$wpdb = new \stdClass;
		$wpdb->queries = array( 'foo', 'bar' );

		$this->assertEquals( array(
			'queries' => $wpdb->queries,
		), Reporters\queries( array() ) );

		$wpdb = null;
	}

	public function test_queries_without_save_queries() {
		global $wpdb;

		$wpdb = new \stdClass;
		$wpdb->queries = array();

		M::wpPassthruFunction( '__', array(
			'times'  => 1,
		) );

		$this->assertEquals( array(
			'queries' => 'No query data was saved.',
		), Reporters\queries( array() ) );

		$wpdb = null;
	}

}
