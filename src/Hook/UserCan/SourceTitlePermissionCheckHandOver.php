<?php

/**
 * Handover read permission check of bssocial titles to the entity
*/
namespace BlueSpice\Social\Hook\UserCan;
use BlueSpice\Hook\UserCan;
use BlueSpice\Social\Entity;

class SourceTitlePermissionCheckHandOver extends UserCan {

	protected function doProcess() {
		if( $this->title->getNamespace() !== NS_SOCIALENTITY ) {
			return true;
		}
		if( $this->action !== 'read' ) {
			//No one should be able to modify this articles besides a sysop
			if( $this->action !== 'wikiadmin' && !$this->user->isAllowed( 'wikiadmin' ) ) {
				$this->result = false;
				return false;
			}
			return true;
		}
		$oEntity = Entity::newFromTitle( $this->title );
		if( !$oEntity instanceof Entity /*|| !$oEntity->exists()*/ ) {
			//entity does not need to exist, i guess
			return true;
		}
		$oStatus = $oEntity->userCan();
		if( !$oStatus->isOK() ) {
			$this->result = false;
			return false;
		}
		return true;
	}
}