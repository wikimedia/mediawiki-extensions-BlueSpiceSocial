<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;
use BlueSpice\Services;
use BS\ExtendedSearch\Backend;
use BlueSpice\ExtensionAttributeBasedRegistry;

class FilterOutActionEntities extends LookupModifierBase {

	public function apply() {
		$entityConfigFactory = Services::getInstance()->getService( 'BSEntityConfigFactory' );

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		foreach ( $registry->getAllKeys() as $type ) {
			$typeConfig = $entityConfigFactory->newFromType( $type );
			if ( $typeConfig->get( 'ExtendedSearchListable' ) == false ) {
				$this->oLookup->addBoolMustNotTerms( 'entitydata.type', $type );
			}
		}
	}

	public function undo() {
		$this->oLookup->removeBoolMustNot( 'entitydata.type' );
	}

	/**
	 * @return string[]
	 */
	public function getSearchTypes() {
		return [ Backend::QUERY_TYPE_SEARCH, Backend::QUERY_TYPE_AUTOCOMPLETE ];
	}

}
