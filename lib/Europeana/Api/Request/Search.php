<?php
namespace Europeana\Api\Request;
use Exception;
use W3C\Http\Request;


/**
 * @link https://sites.google.com/site/projecteuropeana/documents/new-ingestion-process-and-portal-planning/api-1/api
 */
class Search extends Request {

	/**
	 * @var {string}
	 * a name of callback function. If you set a funtion the JSON response will be wrapped by this function call.
	 */
	public $callback;

	/**
	 * @var {string}
	 * [default=standard] the search profile describing the required resultset (what the API returns back).
	 */
	public $profile;

	/**
	 * @var {string}
	 * query facet filtering, see the list of defined facets in Europeana. This parameter can be defined more than once, if more than one facet filter is required.
	 *
	 * @link https://lucene.apache.org/core/old_versioned_docs/versions/3_0_0/queryparsersyntax.html
	 */
	public $qf;

	/**
	 * @var {string}
	 * [required] the term to find search for. All query grammar of Solr is supported
	 *
	 * @link https://lucene.apache.org/core/old_versioned_docs/versions/3_0_0/queryparsersyntax.html
	 */
	public $query;

	/**
	 * @var {int}
	 * [default=12] the number of records to return once. The maximal value is 100, default is 12.
	 */
	public $rows;

	/**
	 * @var {int}
	 * [default=1] the item in the search results to start (first item = 1, default is 1).
	 */
	public $start;

	/**
	 * @var {string}
	 * [required] the API key you get when you register (do not confuse with the other key, called Private key). Mandatory parameter.
	 */
	public $wskey;

	/**
	 * @var {array}
	 */
	protected $allowed_facets;

	/**
	 * @var {array}
	 */
	protected $allowed_profile;

	/**
	 * @var {string}
	 */
	protected $original_qf;


	protected function buildQueryParams() {
		$result = urldecode( $this->query );

		// add rows
		$result .= '&rows=' . (int) $this->rows;

		// add start
		$result .= '&start=' . (int) $this->start;

		// add the api key
		$result .= '&wskey=' . $this->wskey;

		// url encode the query string
		$result = \Europeana\Api\Helpers\Request::urlencodeQueryParams( $result );

		return $result;
	}

	/**
	 * @param {object|array|string} $data
	 * data to send in the call
	 *
	 * @return {array} $result
	 * @return {bool|string} $result['response']
	 * @return {array} $result['info']
	 */
	public function call( $data = array() ) {
		if ( empty( $data ) ) {
			$data = $this->buildQueryParams();
		}

		return parent::post( $this->endpoint, $data );
	}

	protected function init() {
		parent::init();

		$this->callback = '';
		$this->profile = 'standard';
		$this->qf = '';
		$this->query = '';
		$this->rows = 12;
		$this->start = 1;
		$this->wskey = '';

		$this->endpoint = 'http://europeana.eu/api/v2/search.json';

		$this->allowed_facets = array(
			'COMPLETENESS',
			'COUNTRY',
			'LANGUAGE',
			'PROVIDER',
			'RIGHTS',
			'TYPE',
			'UGC',
			'YEAR'
		);

		$this->allowed_profile = array(
			'standard',
			'portal',
			'facets',
			'breadcrumb',
			'minimal'
		);
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( $options = array() ) {
		parent::populate( $options );

		if ( isset( $options['query'] ) ) {
			$this->query = $options['query'];
		}

		if ( isset( $options['rows'] ) ) {
			$this->rows = (int) $options['rows'];
		}

		if ( isset( $options['start'] ) ) {
			$this->start = (int) $options['start'];
		}

		if ( isset( $options['wskey'] ) ) {
			$this->wskey = $options['wskey'];
		}
	}

}
