<?php

namespace BlueSpice\Social\Data\Entity;

use IContextSource;
use RequestContext;
use BlueSpice\Data\Entity\IStore;
use BS\ExtendedSearch\Backend;
use BlueSpice\EntityFactory;
use BlueSpice\Services;

class Store extends \BS\ExtendedSearch\Data\Store implements IStore {

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param Backend|null $searchBackend
	 * @param EntityFactory|null $factory
	 */
	public function __construct( Backend $searchBackend = null, EntityFactory $factory = null ) {
		parent::__construct( $searchBackend );
		$this->factory = $factory;
	}

	/**
	 *
	 * @param IContextSource|null $context
	 * @return Reader
	 */
	public function getReader( IContextSource $context = null ) {
		if ( !$context ) {
			$context = RequestContext::getMain();
		}
		return new Reader(
			$this->getSearchBackend(),
			$this->getFactory(),
			$context
		);
	}

	/**
	 *
	 * @param IContextSource|null $context
	 * @return Writer
	 */
	public function getWriter( IContextSource $context = null ) {
		if ( !$context ) {
			$context = RequestContext::getMain();
		}
		return new Writer( $context );
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
