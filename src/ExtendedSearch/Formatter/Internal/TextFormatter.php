<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter\Internal;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Entity\Text as TextEntity;
use BS\ExtendedSearch\SearchResult;

class TextFormatter extends EntityFormatter {

	/**
	 * @param Entity $entity
	 * @param array &$resultData
	 * @param SearchResult $resultObject
	 *
	 * @return void
	 */
	public function format( $entity, array &$resultData, SearchResult $resultObject ) {
		parent::format( $entity, $resultData, $resultObject );

		$highlight = $this->getHighlight( 'entitydata.text', $resultObject );
		$resultData['highlight'] = $highlight;
		$resultData['rendered_content_snippet'] = $entity->get( TextEntity::ATTR_PARSED_TEXT );
	}

	/**
	 * @param string $field
	 * @param SearchResult $resultObject
	 *
	 * @return string
	 */
	protected function getHighlight( string $field, SearchResult $resultObject ) {
		$highlights = $resultObject->getParam( 'highlight' );
		if ( isset( $highlights[$field] ) ) {
			return implode( ' ', $highlights[$field] );
		}
		return '';
	}
}
