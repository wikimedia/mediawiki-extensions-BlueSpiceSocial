<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\LookupModifier;

class AddHighlighters extends LookupModifier {
	public function apply() {
		$this->lookup->addHighlighter( 'entitydata.renderedtext' );
		$this->lookup->addHighlighter( 'entitydata.text' );
		$this->lookup->addHighlighter( 'entitydata.teaser' );
	}

	public function undo() {
		$this->lookup->removeHighlighter( 'entitydata.renderedtext' );
		$this->lookup->removeHighlighter( 'entitydata.text' );
		$this->lookup->removeHighlighter( 'entitydata.teaser' );
	}

}
