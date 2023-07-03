<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BS\ExtendedSearch\Backend;
use BS\ExtendedSearch\Source\LookupModifier\LookupModifier;
use MediaWiki\MediaWikiServices;

class FilterOutActionEntities extends LookupModifier {

	public function apply() {
		$entityConfigFactory = MediaWikiServices::getInstance()->getService(
			'BSEntityConfigFactory'
		);

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		foreach ( $registry->getAllKeys() as $type ) {
			$typeConfig = $entityConfigFactory->newFromType( $type );
			if ( $typeConfig === null ) {
				continue;
			}
			if ( $typeConfig->get( 'ExtendedSearchListable' ) == false ) {
				$this->lookup->addBoolMustNotTerms( 'entitydata.type', $type );
			}
		}
	}

	public function undo() {
		$this->lookup->removeBoolMustNot( 'entitydata.type' );
	}

	/**
	 * @return string[]
	 */
	public function getSearchTypes() {
		return [ Backend::QUERY_TYPE_SEARCH, Backend::QUERY_TYPE_AUTOCOMPLETE ];
	}

}
