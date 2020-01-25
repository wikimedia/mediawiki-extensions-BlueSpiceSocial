<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\Social\ResourceCollector;

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

		if ( !empty( $oCollector->getModuleScripts() ) ) {
			$this->out->addModules( $oCollector->getModuleScripts() );
		}
		if ( !empty( $oCollector->getModuleStyles() ) ) {
			$this->out->addModuleStyles( $oCollector->getModuleStyles() );
		}

		$this->out->addModules( 'ext.bluespice.social.messages' );
		$this->out->addModules( 'ext.bluespice.social.timeline' );
		$this->out->addModuleStyles( 'ext.bluespice.social.timeline.styles' );

		$this->out->addJsConfigVars(
			'bsgSocialModuleStyles',
			$oCollector->getModuleStyles()
		);
		$this->out->addJsConfigVars(
			'bsgSocialModuleScripts',
			$oCollector->getModuleScripts()
		);
		$this->out->addJsConfigVars(
			'bsgSocialVarMessageKeys',
			$oCollector->getVarMessageKeys()
		);
		return true;
	}
}
