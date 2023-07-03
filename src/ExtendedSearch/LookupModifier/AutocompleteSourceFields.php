<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\LookupModifier;

class AutocompleteSourceFields extends LookupModifier {

	public function apply() {
		$this->lookup->addSourceField( [
			'entitydata.id', 'entitydata.header', 'entitydata.type', 'prefixed_title'
		] );
	}

	public function undo() {
	}

}
