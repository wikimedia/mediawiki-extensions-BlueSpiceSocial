<?php

namespace BlueSpice\Social\HookHandler;

use BlueSpice\Social\MainLinkPanel;
use ConfigFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUILessVarsInit;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class CommonUserInterface implements MWStakeCommonUIRegisterSkinSlotComponents, MWStakeCommonUILessVarsInit {

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
						'position' => 70
					]
				]
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUILessVarsInit( $lessVars ): void {
		$lessVars->setVar( 'bs-primary-light-background', '#6E7B96' );
		$lessVars->setVar( 'bs-light-background', 'lighten(@bs-color-neutral, 38.43%)' );
		$lessVars->setVar( 'bs-primary-light-subelement-background', 'lighten(@bs-primary-light-background, 24.3%)' );
		$lessVars->setVar( 'bs-tertiary-light-background', 'lighten(@bs-color-tertiary, 45%)' );
		$lessVars->setVar( 'bs-color-neutral-headline', 'lighten(@bs-color-neutral, 27.8%)' );
		$lessVars->setVar( 'bs-color-lighten-information', '#747475' );
		$lessVars->setVar( 'bs-color-header-information', '#252525' );
		$lessVars->setVar( 'bs-color-header-information-link', '@bs-primary-light-subelement-border' );
		$lessVars->setVar( 'bs-color-social-entity-background', 'white' );
		$lessVars->setVar( 'bs-color-social-link', '#0060DF' );
		$lessVars->setVar( 'bs-color-social-link-dark', '@bs-primary-light-subelement-border' );
		$lessVars->setVar( 'navigation-tab-color', '@bs-color-primary' );
		$lessVars->setVar( 'navigation-color', 'lighten( @bs-color-neutral3, 23.1373)' );
		$lessVars->setVar( 'bs-social-background-color-archived', '#F1D8D8' );
		$lessVars->setVar(
			'bs-social-background-color-owned',
			'lighten(desaturate(spin(@bs-primary-light-background, 0.4545), 2.1605), 27.0588)'
		);
		$lessVars->setVar( 'bs-primary-light-subelement-border', '#3E5389' );
	}
}
