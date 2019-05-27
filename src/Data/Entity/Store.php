<?php

namespace BlueSpice\Social\Data\Entity;
use BlueSpice\Data\IStore;
use BS\ExtendedSearch\Backend;
use BlueSpice\EntityFactory;
use BlueSpice\Services;

class Store implements IStore {

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var \BS\ExtendedSearch\Backend
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
	 * @param \IContextSource $context
	 * @return IStore
	 */
	public function __construct( $context, EntityFactory $factory = null, Backend $searchBackend = null ) {
		$this->context = $context;
		$this->searchBackend = $searchBackend;
		$this->factory = $factory;
	}

	public function getReader() {
		return new Reader( $this->getSearchBackend(), $this->getFactory(), $this->context );
	}

	public function getWriter() {
		return new Writer( $this->context );
	}

	protected function getSearchBackend() {
		if( $this->searchBackend ) {
			return $this->searchBackend;
		}
		$this->searchBackend = \BS\ExtendedSearch\Backend::instance(
			$this->getSearchBackendKey()
		);
		return $this->searchBackend;
	}

	protected function getSearchBackendKey() {
		return $this->searchBackendKey;
	}

	protected function getFactory() {
		if( $this->factory ) {
			return $this->factory;
		}
		$this->factory = Services::getInstance()->getBSEntityFactory();
		return $this->factory;
	}
}