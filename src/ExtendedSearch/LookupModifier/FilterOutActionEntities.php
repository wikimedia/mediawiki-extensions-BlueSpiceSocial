<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;
use \MediaWiki\MediaWikiServices as MWServices;

class FilterOutActionEntities extends LookupModifierBase {

	public function apply() {
		$entityRegistry = MWServices::getInstance()->getService( 'BSEntityRegistry' );
		$entityConfigFactory = MWServices::getInstance()->getService( 'BSEntityConfigFactory' );

		$types = $entityRegistry->getTypes();
		foreach ( $types as $type ) {
			$typeConfig = $entityConfigFactory->newFromType( $type );
			if ( $typeConfig->get( 'ExtendedSearchListable' ) == false ) {
				$this->oLookup->addBoolMustNotTerms( 'entitydata.type', $type );
			}
		}
	}

	public function undo() {
		$this->oLookup->removeBoolMustNot( 'entitydata.type' );
	}

}
