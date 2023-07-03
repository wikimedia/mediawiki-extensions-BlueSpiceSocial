<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\LookupModifier;

/**
 * This class adds a field to the filterable fields, by adding aggregation
 * on which filters are based
 */
class EntityTypeAggregation extends LookupModifier {
	public function apply() {
		$this->lookup->setBucketTermsAggregation( 'entitydata.type' );
	}

	public function undo() {
		$this->lookup->removeBucketTermsAggregation( 'entitydata.type' );
	}

}
