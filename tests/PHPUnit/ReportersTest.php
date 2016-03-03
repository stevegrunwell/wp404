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

	protected $testFiles = array(
		'reporters.php',
	);

	public function test_server_superglobal() {
		$this->assertEquals( array(
			'$_SERVER' => $_SERVER,
		), Reporters\server_superglobal( array() ) );
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

}
