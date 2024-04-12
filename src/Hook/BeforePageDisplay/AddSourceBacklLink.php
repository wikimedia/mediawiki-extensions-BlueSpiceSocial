<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\Social\Entity;
use MediaWiki\MediaWikiServices;

class AddSourceBacklLink extends BeforePageDisplay {

	protected function skipProcessing() {
		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}
		$title = $this->out->getTitle();
		if ( $title && !$title->exists() ) {
			return true;
		}
		if ( $title->getNamespace() != NS_SOCIALENTITY ) {
			return true;
		}
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $title );
		if ( !$entity instanceof Entity || !$entity->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $this->out->getTitle() );

		$this->out->addBacklinkSubtitle(
			$entity->getBackLinkTitle()
		);
		return true;
	}
}
