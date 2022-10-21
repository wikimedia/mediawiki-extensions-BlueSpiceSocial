<?php

namespace BlueSpice\Social\Data\Entity;

use Exception;
use MWStake\MediaWiki\Component\DataStore\RecordSet;

class Writer extends \BlueSpice\Data\Entity\Writer\Content {

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param RecordSet $dataSet
	 * @return RecordSet
	 */
	public function remove( $dataSet ) {
		throw new Exception( 'Removing entity store is not supported yet' );
	}
}
