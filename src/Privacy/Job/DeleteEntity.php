<?php

namespace BlueSpice\Social\Privacy\Job;

use BlueSpice\Services;

class DeleteEntity extends \Job {

	/**
	 *
	 * @param \Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params ) {
		parent::__construct( "privacyDeleteEntity", $title, $params );
	}

	/**
	 *
	 */
	public function run() {
		$entity = Services::getInstance()->getBSEntityFactory()->newFromSourceTitle( $this->title );
		$entity->set( \BlueSpice\Entity::ATTR_OWNER_ID, $this->params['deletedUserId'] );

		$serviceUser = Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->getUser();
		$entity->save( $serviceUser );
	}
}
