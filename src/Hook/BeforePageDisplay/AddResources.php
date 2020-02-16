<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;

use BlueSpice\Social\ResourceCollector;
use BlueSpice\Hook\BeforePageDisplay;

class AddResources extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.social.icon' );

		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}

		try{
			$oCollector = ResourceCollector::getMain();
		} catch ( \Exception $ex ) {
			return true;
		}

		if ( !empty( $oCollector->getModuleStyles() ) ) {
			$this->out->addModuleStyles( $oCollector->getModuleStyles() );
		}

		$this->out->addModules( 'ext.bluespice.social' );
		$this->out->addModules( 'ext.bluespice.social.messages' );
		$this->out->addModules( 'ext.bluespice.social.timeline' );
		$this->out->addModuleStyles( 'ext.bluespice.social.timeline.styles' );
		// isLoaded is not working correctly
		// also this modules can not be loaded within JS because stuff breaks
		$extensions = \ExtensionRegistry::getInstance()->getAllThings();
		if ( isset( $extensions[ 'MultimediaViewer' ] ) ) {
			$this->aScripts = array_merge(
				$this->aScripts,
				[ 'mmv.head', 'mmv.bootstrap.autostart' ]
			);
		}

		$this->out->addJsConfigVars(
			'bsgSocialVarMessageKeys',
			$oCollector->getVarMessageKeys()
		);
		return true;
	}
}
