<?php

namespace BlueSpice\Social\ExtendedSearch\Job;

use BlueSpice\Social\Entity as SocialEntity;
use BS\ExtendedSearch\Source\Job\UpdateTitleBase;
use MediaWiki\MediaWikiServices;
use Title;

class Entity extends UpdateTitleBase {

	/** @var string */
	protected $sSourceKey = 'socialentity';

	protected function doRun() {
		$oDP = $this->getSource()->getDocumentProvider();

		if ( !$this->getTitle()->exists() ) {
			$this->getSource()->deleteDocumentFromIndex(
				$this->getDocumentId( $this->getTitle()->getCanonicalURL() )
			);
			return [ 'id' => $this->getDocumentId( $this->getDocumentProviderUri() ) ];
		}

		$oDocumentProviderSource = $this->getDocumentProviderSource();
		if ( !$oDocumentProviderSource instanceof SocialEntity ) {
			return [ 'id' => $this->getDocumentId( $this->getDocumentProviderUri() ) ];
		}
		$aDC = $oDP->getDocumentData(
			$this->getDocumentProviderUri(),
			$this->getDocumentId( $this->getDocumentProviderUri() ),
			$oDocumentProviderSource
		);
		$this->getSource()->addDocumentToIndex( $aDC );
		return $aDC;
	}

	/**
	 *
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( 'updateEntityIndex', $title, $params );
	}

	/**
	 *
	 * @return SocialEntity
	 */
	protected function getDocumentProviderSource() {
		return MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $this->getTitle() );
	}
}
