<?php

namespace BlueSpice\Social\HookHandler;

use BlueSpice\Social\MainLinkPanel;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class DiscoverySkin implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
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
