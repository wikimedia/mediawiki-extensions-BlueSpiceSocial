<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter\Internal;

use BlueSpice\Social\ExtendedSearch\Formatter\Internal\EntityFormatter;
use BlueSpice\Social\Entity\Text as TextEntity;

class TextFormatter extends EntityFormatter {
	public function __construct( $entity, array &$result, $resultObject ) {
		parent::__construct( $entity, $result, $resultObject );
	}

	public function format() {
		parent::format();

		$highlight = $this->getHighlight( 'entitydata.text' );
		$result['highlight'] = $highlight;
		$result['rendered_content_snippet'] = $this->getSnippet();
	}

	protected function getHighlight( $field ) {
		$highlights = $this->resultObject->getHighlights();
		$highlightParts = [];
		if( isset( $highlights[$field] ) ) {
			return implode( ' ', $highlights[$field] );
		}
		return '';
	}

	protected function getSnippet() {
		$text = $this->entity->get( TextEntity::ATTR_PARSED_TEXT );
		return $text;
	}
}

