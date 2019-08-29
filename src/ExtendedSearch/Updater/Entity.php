<?php

namespace BlueSpice\Social\ExtendedSearch\Updater;

use Exception;
use BlueSpice\Social\Entity as SocialEntity;
use BlueSpice\Entity as EntityBase;
use BS\ExtendedSearch\Source\Updater\Base as Updater;

class Entity extends Updater {

	/**
	 *
	 * @param array &$aHooks
	 */
	public function init( &$aHooks ) {
		$aHooks['BSEntityInvalidate'][] = [ $this, 'onBSEntityInvalidate' ];
	}

	/**
	 * Update index on article change.
	 * @param EntityBase $oEntity
	 * @return bool
	 */
	public function onBSEntityInvalidate( $oEntity ) {
		if ( !$oEntity instanceof SocialEntity || !$oEntity->exists() ) {
			return true;
		}
		// TODO: $oEntity->getConfig()->get('UpdateIndexJobClass')
		$oJob = new \BlueSpice\Social\ExtendedSearch\Job\Entity(
			$oEntity->getTitle()
		);

		// directly run
		try {
			$oJob->run();
		} catch ( Exception $e ) {
			bsDebugLog( $e->getMessage() );
		}
		return true;
	}
}
