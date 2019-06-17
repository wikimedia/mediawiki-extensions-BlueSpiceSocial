<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter;

use BS\ExtendedSearch\Source\Formatter\WikiPageFormatter;

class EntityFormatter extends WikiPageFormatter {

	/**
	 *
	 * @param array $defaultResultStructure
	 * @return array
	 */
	public function getResultStructure( $defaultResultStructure = [] ) {
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
	 *
	 * @param array &$result
	 * @param \Elastica\Result $resultObject
	 * @return bool
	 */
	public function format( &$result, $resultObject ) {
		if ( $this->source->getTypeKey() != $resultObject->getType() ) {
			return true;
		}

		parent::format( $result, $resultObject );

		$entityFactory = \MediaWiki\MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );
		$entity = $entityFactory->newFromID( $result['entitydata']['id'], $result['namespace'] );
		if ( !( $entity instanceof \BlueSpice\Social\Entity )
				|| $entity->exists() == false ) {
			return;
		}

		// Transfer formatting to internal formatter - different depending on the Entity type
		$internalFormatterClass = $entity->getConfig()->get( 'ExtendedSearchResultFormatter' );
		$internalFormatter = new $internalFormatterClass( $entity, $result, $resultObject );
		$internalFormatter->setLinkRenderer( $this->linkRenderer );
		$internalFormatter->format();
	}

	/**
	 *
	 * @param array &$results
	 * @param array $searchData
	 */
	public function formatAutocompleteResults( &$results, $searchData ) {
		parent::formatAutocompleteResults( $results, $searchData );
		foreach ( $results as &$result ) {
			if ( $result['type'] !== $this->source->getTypeKey() ) {
				continue;
			}

			$result['basename'] = $result['entitydata']['header'];
			$result['type'] .= " ({$result['entitydata']['type']})";

			$title = \Title::newFromText( $result['prefixed_title'] );
			if ( $title instanceof \Title ) {
				$result['pageAnchor'] = $this->linkRenderer->makeLink( $title, $result['basename'] );
				$result['image_uri'] = $this->getImageUri( $result['prefixed_title'], 150 );
			}
		}
	}

	/**
	 *
	 * @param array &$results
	 * @param array $searchData
	 */
	public function rankAutocompleteResults( &$results, $searchData ) {
		foreach ( $results as &$result ) {
			if ( $result['type'] !== $this->source->getTypeKey() ) {
				parent::rankAutocompleteResults( $results, $searchData );
				continue;
			}

			$val = strtolower( $searchData['value'] );
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
}
