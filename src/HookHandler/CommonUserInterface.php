<?php

namespace BlueSpice\Social\HookHandler;

use BlueSpice\Social\MainLinkPanel;
use ConfigFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @var ConfigFactory
	 */
	private $configFactory = null;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		$this->configFactory = $configFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$config = $this->configFactory->makeConfig( 'bsg' );
		if ( $config->get( 'SocialMainLinksTimeline' ) ) {
			$registry->register(
				'MainLinksPanel',
				[
					'special-timeline' => [
						'factory' => static function () {
							return new MainLinkPanel();
						},
						'position' => 50
					]
				]
			);
		}
	}
}
