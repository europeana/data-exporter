<?php
namespace	Europeana\Api\Response;


interface ResponseInterface {

	public function __construct( array $response );
	public function __get( $property );
	public function getRequestUrl();
	public function init();

}