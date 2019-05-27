<?php

namespace BlueSpice\Social\EntityListContext;

use BlueSpice\Social\Entity;
use BlueSpice\Data\Filter\Numeric;

class Children extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_OUTPUT_TYPE = 'EntityListChildrenOutputType';
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListTypeChildrenAllowed';

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 */
	public function __construct( \IContextSource $context, \Config $config, \User $user = null, Entity $entity = null ) {
		parent::__construct( $context, $config, $user, $entity );
		if( !$this->entity || !$this->entity->exists() ) {
			throw new \MWException( 'Parent entity missing' );
		}
	}

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 3;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortProperty() {
		return Entity::ATTR_TIMESTAMP_CREATED;
	}

	/**
	 *
	 * @return array
	 */
	public function getFilters() {
		$filters = parent::getFilters();
		$filters[] = $this->getParentIDFilter();
		return $filters;
	}

	protected function getParentIDFilter() {
		if( $this->entity->get( Entity::ATTR_ID, 0 ) < 1 ) {
			throw new \MWException(
				'Non existing parent would result in endless loop'
			);
		}
		return (object)[
			Numeric::KEY_PROPERTY => Entity::ATTR_PARENT_ID,
			Numeric::KEY_VALUE => $this->entity->get( Entity::ATTR_ID ),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];
	}

	/**
	 *
	 * @return boolean
	 */
	public function showEntityListMenu() {
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function showEntityListMore() {
		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function useEndlessScroll() {
		return false;
	}

	/**
	 *
	 * @return boolean
	 */
	public function useMoreScroll() {
		return true;
	}

	/**
	 *
	 * @return Entity|null
	 */
	public function getParent() {
		return $this->entity;
	}
}
