<?php

namespace BlueSpice\Social\Data\Entity;
use BlueSpice\Social\ExtendedSearch\MappingProvider\Entity as MapingProvider;

class Record extends \BlueSpice\Data\Record {

	/**
	 *
	 * @param \Elastica\Result $dataSet
	 */
	public function __construct( $dataSet, \Status $status = null ) {
		$data = $dataSet->getData();
		return parent::__construct( (object) $data[MapingProvider::PREFIX], $status );
	}
}