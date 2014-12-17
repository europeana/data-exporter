<?php

namespace	Europeana\Api\Request;


/**
 * @link https://sites.google.com/site/projecteuropeana/documents/new-ingestion-process-and-portal-planning/api-1/api
 */
class Search extends RequestAbstract {

	/**
	 * @var string
	 * a name of callback function. If you set a funtion the JSON response will be wrapped by this function call.
	 */
	public $callback;

	/**
	 * @var string
	 * [default=standard] the search profile describing the required resultset (what the API returns back).
	 */
	public $profile;

	/**
	 * @var string
	 * query facet filtering, see the list of defined facets in Europeana. This parameter can be defined more than once, if more than one facet filter is required.
	 *
	 * @link https://lucene.apache.org/core/old_versioned_docs/versions/3_0_0/queryparsersyntax.html
	 */
	public $qf;

	/**
	 * @var string
	 * [required] the term to find search for. All query grammar of Solr is supported
	 *
	 * @link https://lucene.apache.org/core/old_versioned_docs/versions/3_0_0/queryparsersyntax.html
	 */
	public $query;

	/**
	 * @var int
	 * [default=12] the number of records to return once. The maximal value is 100, default is 12.
	 */
	public $rows;

	/**
	 * @var int
	 * [default=1] the item in the search results to start (first item = 1, default is 1).
	 */
	public $start;

	/**
	 * @var string
	 * [required] the API key you get when you register (do not confuse with the other key, called Private key). Mandatory parameter.
	 */
	public $wskey;

	/**
	 * @var array
	 */
	protected $_allowed_facets;

	/**
	 * @var array
	 */
	protected $_allowed_profile;

	/**
	 * @var string
	 */
	protected $_original_qf;


	/**
	 * @param array $array
	 */
	protected function buildQf( $array = array() ) {
		$result = array();

			if ( empty( $array ) || empty( $array['facets'] ) ) {
				return;
			}

			for ( $i = 0; $i < count( $array['facets'] ); $i += 1 ) {
				$inclusion = null;

				if ( $array['inclusions'][$i] == 'exclude' ) {
					$inclusion = '-';
				}

				if ( !empty( $array['facets'][$i] ) && isset( $array['values'][$i] ) )  {
					$result[] = $inclusion . $array['facets'][$i] . ':' . $array['values'][$i];
				} else if ( !empty( $array['values'][$i] ) ) {
					$result[] = $inclusion . $array['values'][$i];
				}
			}

		$this->_original_qf = $this->qf;
		$this->qf = $result;
	}

	protected function buildUrl() {
		return parent::buildUrl();
	}

	public function init() {
		parent::init();

		$this->callback = null;
		$this->profile = 'standard';
		$this->qf = null;
		$this->query = null;
		$this->rows = 12;
		$this->start = 1;
		$this->wskey = null;

		$this->_endpoint = 'http://europeana.eu/api/v2/search.json';

		$this->_allowed_facets = array(
			'COMPLETENESS',
			'COUNTRY',
			'LANGUAGE',
			'PROVIDER',
			'RIGHTS',
			'TYPE',
			'UGC',
			'YEAR'
		);

		$this->_allowed_profile = array(
			'standard',
			'portal',
			'facets',
			'breadcrumb',
			'minimal'
		);
	}

}
