<?php
namespace BlueSpice\Social\Job;

use Exception;
use MediaWiki\MediaWikiServices;

class Update extends \BlueSpice\Social\Job {
	const JOBCOMMAND = 'socialentityupdate';

	public function run() {
		$entity = $this->getEntity();
		$entity->setValuesByObject( (object)$this->getParams() );
		$serviceUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
		$status = $entity->save( $serviceUser );
		if ( !$status->isOk() ) {
			throw new Exception( $status->getMessage() );
		}
	}
}
