<?php

namespace BlueSpice\Social\Data\Entity;

use IContextSource;
use Config;
use BlueSpice\Services;
use BS\ExtendedSearch\Backend;
use BlueSpice\Data\ReaderParams;
use BlueSpice\EntityFactory;
use BlueSpice\Data\ResultSet;
use BlueSpice\Data\ISecondaryDataProvider;

class Reader extends \BlueSpice\Data\Entity\Reader\Content {

	/**
	 *
	 * @var Backend
	 */
	protected $searchBackend = null;

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param Backend $searchBackend
	 * @param EntityFactory $factory
	 * @param IContextSource|null $context
	 * @param Config|null $config
	 */
	public function __construct( Backend $searchBackend, $factory,
		IContextSource $context = null, Config $config = null ) {
		parent::__construct( $context, $config );
		$this->searchBackend = $searchBackend;
		$this->factory = $factory;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->searchBackend,
			$this->getSchema(),
			$this->factory,
			$this->context
		);
	}

	/**
	 *
	 * @return SecondaryDataProvider
	 */
	protected function makeSecondaryDataProvider() {
		return new SecondaryDataProvider(
			Services::getInstance()->getLinkRenderer()
		);
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return ResultSet
	 */
	public function read( $params ) {
		$primaryDataProvider = $this->makePrimaryDataProvider( $params );
		$dataSets = $primaryDataProvider->makeData( $params );

		$secondaryDataProvider = $this->makeSecondaryDataProvider();
		if ( $secondaryDataProvider instanceof ISecondaryDataProvider ) {
			$dataSets = $secondaryDataProvider->extend( $dataSets );
		}

		$resultSet = new ResultSet( $dataSets, 0 );
		return $resultSet;
	}
}
