<?php

namespace BlueSpice\Social\Hook;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Event\SocialEvent;
use MediaWiki\Extension\NotifyMe\EventFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Notifier;
use RequestContext;
use Status;

class NotifyUsers {

	/** @var array */
	private $deleted = [];

	/**
	 *
	 * @param Entity $entity
	 * @return bool
	 */
	protected function skipProcessing( Entity $entity ) {
		return !$entity instanceof Entity || !$entity->getConfig()->get( 'HasNotifications' );
	}

	/**
	 * @param Entity $entity
	 * @param Status $status
	 * @param UserIdentity $user
	 * @return true|void
	 */
	public function onBSEntityDelete( Entity $entity, Status $status, UserIdentity $user ) {
		if ( $this->skipProcessing( $entity ) ) {
			return true;
		}
		$this->deleted[] = $entity->get( Entity::ATTR_ID );
	}

	/**
	 * @param Entity $entity
	 * @param Status $status
	 * @param UserIdentity $user
	 * @return bool
	 * @throws \Exception
	 */
	public function onBSEntitySaveComplete( Entity $entity, Status $status, UserIdentity $user ) {
		if ( $this->skipProcessing( $entity ) ) {
			return true;
		}
		$action = SocialEvent::ACTION_EDIT;
		if ( $entity->getTitle()->isNewPage() ) {
			$action = SocialEvent::ACTION_CREATE;
		}
		if ( in_array( $entity->get( Entity::ATTR_ID ), $this->deleted ) ) {
			$action = SocialEvent::ACTION_DELETE;
		}

		$notifyAll = false;

		$services = MediaWikiServices::getInstance();
		// Cannot inject, as it would error if the service is not defined
		if ( $services->hasService( 'BSSocialAutoWatcherFactory' ) ) {
			$autoWatcherFactory = $services->getService( 'BSSocialAutoWatcherFactory' );
			$autoWatcher = $autoWatcherFactory->factory( $entity, RequestContext::getMain() );
			$autoWatcher->autoWatch();
		} else {
			// Service not defined
			$notifyAll = true;
		}

		$eventKeys = $entity->getConfig()->get( 'NotificationObjectClass' );
		if ( !is_array( $eventKeys ) ) {
			$eventKeys = [ $eventKeys ];
		}
		/** @var EventFactory $eventFactory */
		$eventFactory = MediaWikiServices::getInstance()->getService( 'NotifyMe.EventFactory' );
		/** @var Notifier $notifier */
		$notifier = MediaWikiServices::getInstance()->getService( 'MWStake.Notifier' );
		foreach ( $eventKeys as $eventKey ) {
			if ( !$eventKey ) {
				continue;
			}
			$event = $eventFactory->create( $eventKey, [
				$user,
				$entity->jsonSerialize(),
				$action
			] );
			if ( $event instanceof SocialEvent ) {
				$event->setNotifyAll( $notifyAll );
				$notifier->emit( $event );
			}
		}

		return true;
	}
}
