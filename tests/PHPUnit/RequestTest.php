<?php
/**
 * Tests for the Request-related reporters.
 *
 * @package WP404
 * @author  Steve Grunwell
 */

namespace WP404;

use WP_Mock as M;

class RequestTest extends TestCase {

	protected $testFiles = array(
		'request.php',
	);

	public function test_server_superglobal() {
		$this->assertEquals( array(
			'$_SERVER' => $_SERVER,
		), Request\server_superglobal( array() ) );
	}

}
