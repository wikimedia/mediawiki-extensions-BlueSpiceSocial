<?php

namespace BlueSpice\Social\Hook\MergeAccountFromTo;

use BlueSpice\Context;
use BlueSpice\DistributionConnector\Hook\MergeAccountFromTo;
use BlueSpice\Social\Data\Entity\Store;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext\SpecialTimeline;
use BlueSpice\Social\Job\ChangeOwner;
use IContextSource;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DataStore\Filter\Numeric;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class ChangeEntityOwner extends MergeAccountFromTo {

	protected function doProcess() {
		$context = $this->getContext();
		$filters = [ (object)[
			Numeric::KEY_PROPERTY => Entity::ATTR_OWNER_ID,
			Numeric::KEY_VALUE => $this->oldUser->getId(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric',
		] ];
		$params = new ReaderParams( [
			ReaderParams::PARAM_FILTER => $filters,
			ReaderParams::PARAM_SORT => $context->getSort(),
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
			ReaderParams::PARAM_START => 0,
		] );

		$res = ( new Store )->getReader( $context )->read( $params );
		foreach ( $res->getRecords() as $record ) {
			$entity = $this->getServices()->getService( 'BSEntityFactory' )
				->newFromObject( $record->getData() );
			if ( !$entity || !$entity->exists() ) {
				continue;
			}
			if ( !$entity->getConfig()->get( 'IsOwnerChangable' ) ) {
				continue;
			}
			$this->addJob( $entity );
		}
	}

	/**
	 *
	 * @return IContextSource
	 */
	protected function getContext() {
		$context = new Context(
			parent::getContext(),
			$this->getConfig()
		);
		$serviceUser = $this->getServices()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new SpecialTimeline(
			$context,
			$context->getConfig(),
			$serviceUser
		);
		return $listContext;
	}

	/**
	 *
	 * @param Entity $entity
	 */
	protected function addJob( Entity $entity ) {
		$job = new ChangeOwner(
			$entity->getTitle(),
			[ Entity::ATTR_OWNER_ID => $this->newUser->getId() ]
		);
		MediaWikiServices::getInstance()->getJobQueueGroup()->push(
			$job
		);
	}

}
