<?php

namespace W3c\Http;

/**
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616.html
 * @link http://www.ietf.org/rfc/rfc2616.txt
 */
interface HttpRequestInterface {

	public function getCurlInfo();
	public function get( $url );
	public function post( $url, array $data );

}