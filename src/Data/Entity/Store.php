<?php

namespace BlueSpice\Social\Data\Entity;

use IContextSource;
use BlueSpice\Data\IStore;
use BS\ExtendedSearch\Backend;
use BlueSpice\EntityFactory;
use BlueSpice\Services;

class Store implements IStore {

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var Backend
	 */
	protected $searchBackend = null;

	/**
	 *
	 * @var string
	 */
	protected $searchBackendKey = 'local';

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param EntityFactory|null $factory
	 * @param Backend|null $searchBackend
	 */
	public function __construct( $context, EntityFactory $factory = null,
		Backend $searchBackend = null ) {
		$this->context = $context;
		$this->searchBackend = $searchBackend;
		$this->factory = $factory;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			$this->getSearchBackend(),
			$this->getFactory(),
			$this->context
		);
	}

	/**
	 *
	 * @return Writer
	 */
	public function getWriter() {
		return new Writer( $this->context );
	}

	/**
	 *
	 * @return Backend
	 */
	protected function getSearchBackend() {
		if ( $this->searchBackend ) {
			return $this->searchBackend;
		}
		$this->searchBackend = Backend::instance(
			$this->getSearchBackendKey()
		);
		return $this->searchBackend;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSearchBackendKey() {
		return $this->searchBackendKey;
	}

	/**
	 *
	 * @return EntityFactory
	 */
	protected function getFactory() {
		if ( $this->factory ) {
			return $this->factory;
		}
		$this->factory = Services::getInstance()->getBSEntityFactory();
		return $this->factory;
	}
}
