<?php
namespace	Europeana\Api\Request;
use	W3C\Http\HttpRequestInterface;


interface RequestInterface {

	public function __construct( HttpRequestInterface $HttpRequest, array $properties = array() );
	public function __get( $property );
	public function call();
	public function init();

}
