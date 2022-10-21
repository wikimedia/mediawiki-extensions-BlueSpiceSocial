<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\Social\ExtendedSearch\MappingProvider\Entity as MapingProvider;
use Status;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {

	/**
	 *
	 * @param \Elastica\Result $dataSet
	 * @param Status|null $status
	 */
	public function __construct( $dataSet, Status $status = null ) {
		$data = $dataSet->getData();
		return parent::__construct( (object)$data[MapingProvider::PREFIX], $status );
	}
}
