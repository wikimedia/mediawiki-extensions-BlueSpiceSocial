<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\EntityFactory;
use BlueSpice\Data\ResultSet;
use BlueSpice\Data\ISecondaryDataProvider;

class Reader extends \BlueSpice\Data\Entity\Reader {

	/**
	 *
	 * @var \BS\ExtendedSearch\Backend
	 */
	protected $searchBackend = null;

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	public function __construct( \BS\ExtendedSearch\Backend $searchBackend, $factory, \IContextSource $context = null, \Config $config = null ) {
		parent::__construct( $context, $config );
		$this->searchBackend = $searchBackend;
		$this->factory = $factory;
	}

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->searchBackend,
			$this->factory,
			$this->context,
			$this->getSchema()
		);
	}

	protected function makeSecondaryDataProvider() {
		return new SecondaryDataProvider(
			\MediaWiki\MediaWikiServices::getInstance()->getLinkRenderer()
		);
	}

	public function getSchema() {
		return new Schema();
	}

	public function read( $params ) {
		$primaryDataProvider = $this->makePrimaryDataProvider( $params );
		$dataSets = $primaryDataProvider->makeData( $params );

		$secondaryDataProvider = $this->makeSecondaryDataProvider();
		if( $secondaryDataProvider instanceof ISecondaryDataProvider ) {
			$dataSets = $secondaryDataProvider->extend( $dataSets );
		}

		$resultSet = new ResultSet( $dataSets, 0 );
		return $resultSet;
	}
}