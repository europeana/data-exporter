<?php
namespace	Europeana\Api\Request;
use	Exception;


/**
 * @link https://sites.google.com/site/projecteuropeana/documents/new-ingestion-process-and-portal-planning/api-1/api
 */
class OpenSearch extends RequestAbstract {

	/**
	 * @var int
	 * [default = 12] defines the number of search results, possible values: 12, 24, 48, 100
	 */
	public $count;

	/**
	 * @var string
	 * [required] the term to find search for. Similar to query parameter in search.json.
	 *
	 * @link https://lucene.apache.org/core/old_versioned_docs/versions/3_0_0/queryparsersyntax.html
	 */
	public $searchTerms;

	/**
	 * @var int
	 * [default = 1] The item in the search results to start (first item = 1)
	 */
	public $startIndex;

	/**
	 * @var string
	 * [default = relevance] Sort order of results, options are relevance, title, publication
	 */
	public $sort;


	/**
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->count = 12;
		$this->searchTerms = null;
		$this->sort = 'relevance';
		$this->startIndex = 1;

		$this->_endpoint = 'http://europeana.eu/api/v2/opensearch.rss';
	}

}