<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Social\Entity;

class EntitySpawnerUserPermissions extends BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$activityStreamPermissions = [];

		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		foreach ( $registry->getAllKeys() as $type ) {
			$entity = $this->getServices()->getBSEntityFactory()->newFromObject(
				(object)[ 'type' => $type ]
			);
			if ( !$entity ) {
				continue;
			}

			if ( !$entity instanceof Entity ) {
				continue;
			}

			if ( !$entity->getConfig()->get( 'IsSpawnable' ) ) {
				continue;
			}

			$status = $entity->userCan( 'create', $this->out->getUser() );

			if ( !$status->isOK() ) {
				continue;
			}

			$activityStreamPermissions[] = $type;
		}

		$this->out->addJsConfigVars(
			'bsgSocialUserSpawnerEntities',
			$activityStreamPermissions
		);

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}

		return false;
	}
}
