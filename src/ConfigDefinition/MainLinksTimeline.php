<?php

namespace BlueSpice\Social\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;
use ExtensionRegistry;

class MainLinksTimeline extends BooleanSetting {

	/**
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SKINNING . '/BlueSpiceSocial',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceSocial/' . static::FEATURE_SKINNING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceSocial',
		];
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-social-config-mainlinks-timeline-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-social-config-mainlinks-timeline-help';
	}

	/**
	 * @return bool
	 */
	public function isHidden() {
		return !ExtensionRegistry::getInstance()->isLoaded( 'BlueSpiceDiscovery' );
	}

}
