<?php

namespace BlueSpice\Social\Hook\BSEntitySaveComplete;

use BlueSpice\Hook\BSEntitySaveComplete;
use BlueSpice\Social\Event\SocialEvent;
use MediaWiki\Extension\Notifications\EventFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\Events\Notifier;

class NotifyUsers extends BSEntitySaveComplete {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->entity instanceof \BlueSpice\Social\Entity ) {
			return true;
		}
		if ( !$this->entity->getConfig()->get( 'HasNotifications' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	protected function doProcess() {
		$action = SocialEvent::ACTION_EDIT;
		if ( $this->entity->getTitle()->isNewPage() ) {
			$action = SocialEvent::ACTION_CREATE;
		}

		$notifyAll = false;

		$services = $this->getServices();

		if ( $services->hasService( 'BSSocialAutoWatcherFactory' ) ) {
			$autoWatcherFactory = $services->getService( 'BSSocialAutoWatcherFactory' );
			$autoWatcher = $autoWatcherFactory->factory( $this->entity, $this->getContext() );
			$autoWatcher->autoWatch();
		} else {
			// Service not defined
			$notifyAll = true;
		}

		$eventKeys = $this->entity->getConfig()->get( 'NotificationObjectClass' );
		if ( !is_array( $eventKeys ) ) {
			$eventKeys = [ $eventKeys ];
		}
		/** @var EventFactory $eventFactory */
		$eventFactory = MediaWikiServices::getInstance()->getService( 'Notifications.EventFactory' );
		/** @var Notifier $notifier */
		$notifier = MediaWikiServices::getInstance()->getService( 'MWStake.Notifier' );
		foreach ( $eventKeys as $eventKey ) {
			if ( !$eventKey ) {
				continue;
			}
			$event = $eventFactory->create( $eventKey, [
				$this->user,
				$this->entity->jsonSerialize(),
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
