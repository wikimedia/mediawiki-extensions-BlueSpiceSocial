<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter;

use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity;
use BlueSpice\Social\ExtendedSearch\Formatter\Internal\EntityFormatter as InternalEntityFormatter;
use BS\ExtendedSearch\ISearchSource;
use BS\ExtendedSearch\Source\Formatter\WikiPageFormatter;

class EntityFormatter extends WikiPageFormatter {
	/** @var EntityFactory */
	protected $entityFactory;

	/**
	 * @param ISearchSource $source
	 * @param EntityFactory $entityFactory
	 */
	public function __construct( $source, $entityFactory ) {
		parent::__construct( $source );

		$this->entityFactory = $entityFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getResultStructure( $defaultResultStructure = [] ): array {
		$resultStructure = $defaultResultStructure;
		$resultStructure['headerText'] = 'entitydata.header';
		$resultStructure['page_anchor'] = 'page_anchor';
		$resultStructure['highlight'] = 'highlight';

		$resultStructure['secondaryInfos']['top']['items'][] = [
			"name" => "owner"
		];
		$resultStructure['secondaryInfos']['top']['items'][] = [
			"name" => "entity_type"
		];

		$resultStructure['featured']['highlight'] = "rendered_content_snippet";

		return $resultStructure;
	}

	/**
	 * @inheritDoc
	 */
	public function format( &$resultData, $resultObject ): void {
		if ( $this->source->getTypeKey() !== $resultObject->getType() ) {
			return;
		}

		parent::format( $resultData, $resultObject );

		$entity = $this->getEntityFromResult( $resultData );
		if ( !$entity ) {
			return;
		}

		// Transfer formatting to internal formatter - different depending on the Entity type
		$internalFormatterClass = $entity->getConfig()->get( 'ExtendedSearchResultFormatter' );
		/** @var InternalEntityFormatter $internalFormatter */
		$internalFormatter = new $internalFormatterClass();
		$internalFormatter->setLinkRenderer( $this->linkRenderer );
		$internalFormatter->format( $entity, $resultData, $resultObject );
	}

	/**
	 * @inheritDoc
	 */
	public function formatAutocompleteResults( &$results, $searchData ): void {
		parent::formatAutocompleteResults( $results, $searchData );
		foreach ( $results as &$result ) {
			if ( $result['type'] !== $this->source->getTypeKey()
				|| !isset( $result['entitydata'] ) ) {
				continue;
			}

			$result['basename'] = $result['entitydata']['header'];
			$result['type'] .= " ({$result['entitydata']['type']})";

			$entity = $this->getEntityFromResult( $result );
			if ( !$entity ) {
				return;
			}

			$typeMsg = $this->getContext()->msg( $entity->getConfig()->get( 'TypeMessageKey' ) );
			$strippedHeader = strip_tags( $entity->getHeader() );
			$pageHeaderText = "({$typeMsg->plain()}) $strippedHeader";
			$result['page_anchor'] = $this->linkRenderer->makeLink(
				$entity->getTitle(), $pageHeaderText
			);
			$result['image_uri'] = $this->getImageUri(
				$entity->getTitle()->getPrefixedText(), 150
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function rankAutocompleteResults( &$results, $searchData ): void {
		foreach ( $results as &$result ) {
			if ( $result['type'] !== $this->source->getTypeKey() ) {
				parent::rankAutocompleteResults( $results, $searchData );
				continue;
			}

			$val = strtolower( $searchData['value'] );
			if ( !isset( $result['entitydata'] ) ) {
				continue;
			}
			$header = strtolower( $result['entitydata']['header'] );
			if ( $header == $val ) {
				$result['rank'] = self::AC_RANK_TOP;
			} elseif ( strpos( $header, $val ) !== false ) {
				$result['rank'] = self::AC_RANK_NORMAL;
			} else {
				$result['rank'] = self::AC_RANK_SECONDARY;
			}

			$result['is_ranked'] = true;
		}
	}

	/**
	 * @param array $result
	 * @return Entity|null
	 */
	private function getEntityFromResult( $result ) {
		if ( !isset( $result['entitydata']['id'] )
			|| !isset( $result['entitydata']['type'] ) ) {
			return null;
		}
		/** @var Entity $entity */
		$entity = $this->entityFactory->newFromID(
			$result['entitydata']['id'], $result['entitydata']['type']
		);
		if ( !$entity instanceof Entity || $entity->exists() == false ) {
			return null;
		}

		return $entity;
	}
}
