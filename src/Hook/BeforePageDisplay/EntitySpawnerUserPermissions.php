<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;
use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\Social\Entity;
use BlueSpice\EntityRegistry;

class EntitySpawnerUserPermissions extends BeforePageDisplay {

	protected function doProcess() {
		$activityStreamPermissions = [];

		foreach( EntityRegistry::getRegisterdTypeKeys() as $type ) {
			if( !$entity = Entity::newFromObject( (object)['type' => $type ] ) ) {
				continue;
			}

			if( !$entity instanceof Entity ) {
				continue;
			}

			if( !$entity->getConfig()->get( 'IsSpawnable' ) ) {
				continue;
			}

			$status = $entity->userCan( 'create', $this->out->getUser() );

			if( !$status->isOK() ) {
				continue;
			}

			$activityStreamPermissions[] = $type;
		}

		$this->out->addJsConfigVars( 'bsgSocialUserSpawnerEntities', $activityStreamPermissions );

		return true;
	}

	protected function skipProcessing() {
		if( $this->out->getRequest()->getVal('action', 'view') !== 'view' ) {
			return true;
		}

		return false;
	}
}