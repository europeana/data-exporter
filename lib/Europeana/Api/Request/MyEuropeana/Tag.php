<?php
namespace	Europeana\Api\Request\MyEuropeana;
use W3C\Http\Request;


/**
 * @link http://labs.europeana.eu/api/myeuropeana/#tags
 */
class Tag extends Request {

	/**
	 * @var {string}
	 * a single tag
	 */
	public $tag;

	/**
	 * @var {string}
	 * the europeanaId of the object you wish to retrieve
	 */
	public $europeanaid;


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
			$data = array(
				'tag' => $this->tag,
				'europeanaid' => $this->europeanaid
			);
		}

		return parent::post( $this->endpoint, $data );
	}

	public function init() {
		parent::init();

		$this->europeanaid = '';
		$this->tag = '';
		$this->endpoint = 'http://europeana.eu/api/v2/mydata/tag.json';
	}

	/**
	 * @param {array} $options
	 */
	protected function populate( $options = array() ) {
		parent::populate( $options );

		if ( isset( $options['europeanaid'] ) ) {
			$this->europeanaid = $options['europeanaid'];
		}

		if ( isset( $options['tag'] ) ) {
			$this->tag = $options['tag'];
		}
	}

}
