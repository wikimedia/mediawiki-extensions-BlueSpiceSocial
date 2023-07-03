<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter\Internal;

use BlueSpice\Social\Entity;
use BS\ExtendedSearch\SearchResult;
use MediaWiki\Linker\LinkRenderer;
use RequestContext;

class EntityFormatter {

	/** @var LinkRenderer */
	protected $linkRenderer;

	/**
	 *
	 * @param LinkRenderer $linkRenderer
	 */
	public function setLinkRenderer( $linkRenderer ) {
		$this->linkRenderer = $linkRenderer;
	}

	/**
	 * @param Entity $entity
	 * @param array &$resultData
	 * @param SearchResult $resultObject
	 *
	 * @return void
	 */
	public function format( $entity, array &$resultData, SearchResult $resultObject ) {
		// can these timestamps be different than indexed ones?
		$resultData['ctime'] = RequestContext::getMain()->getLanguage()->date(
			$entity->getTimestampCreated()
		);
		$resultData['mtime'] = RequestContext::getMain()->getLanguage()->date(
			$entity->getTimestampTouched()
		);

		$resultData['entity_type'] = $entity->get( Entity::ATTR_TYPE );
		$owner = $entity->getOwner();
		$resultData['page_anchor'] = $this->linkRenderer->makeLink( $entity->getTitle() );
		$name = $entity->get( Entity::ATTR_OWNER_REAL_NAME );
		if ( !$name ) {
			$name = $owner->getName();
		}
		$resultData['owner'] = $this->linkRenderer->makeLink(
			$owner->getUserpage(),
			$name
		);
	}
}
