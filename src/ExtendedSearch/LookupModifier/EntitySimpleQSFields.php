<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\LookupModifier;

class EntitySimpleQSFields extends LookupModifier {
	public function apply() {
		$queryString = $this->lookup->getQueryString();

		$fields = [ 'entitydata.header^2' ];
		if ( isset( $queryString['fields'] ) && is_array( $queryString['fields'] ) ) {
			$queryString['fields'] = array_merge( $queryString['fields'], $fields );
		} else {
			$queryString['fields'] = $fields;
		}

		$this->lookup->setQueryString( $queryString );

		$this->lookup->addSourceField( 'entitydata.header' );
	}

	public function undo() {
		$queryString = $this->lookup->getQueryString();

		if ( isset( $queryString['fields'] ) && is_array( $queryString['fields'] ) ) {
			$queryString['fields'] = array_diff( $queryString['fields'],
					[ 'entitydata.header^2' ] );
		}

		$this->lookup->setQueryString( $queryString );

		$this->lookup->removeSourceField( 'entitydata.header' );
	}

}
