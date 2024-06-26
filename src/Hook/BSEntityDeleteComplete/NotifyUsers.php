<?php

namespace BlueSpice\Social\Hook\BSEntityDeleteComplete;

use BlueSpice\Hook\BSEntityDeleteComplete;
use BlueSpice\Social\Event\SocialEvent;
use MediaWiki\Extension\Notifications\EventFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\Events\Notifier;

class NotifyUsers extends BSEntityDeleteComplete {
	protected function skipProcessing() {
		if ( !$this->entity instanceof \BlueSpice\Social\Entity ) {
			return true;
		}

		if ( !$this->entity->getConfig()->get( 'HasNotifications' ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$isWatchEnabled = MediaWikiServices::getInstance()
			->getService( 'BSExtensionRegistry' )->hasName( "BlueSpiceSocialWatch" );

		$notifyAll = false;
		if ( !$isWatchEnabled ) {
			$notifyAll = true;
		}

		$notificationClasses = $this->entity->getConfig()->get( 'NotificationObjectClass' );
		if ( !is_array( $notificationClasses ) ) {
			$notificationClasses = [ $notificationClasses ];
		}

		/** @var EventFactory $eventFactory */
		$eventFactory = MediaWikiServices::getInstance()->getService( 'Notifications.EventFactory' );
		/** @var Notifier $notifier */
		$notifier = MediaWikiServices::getInstance()->getService( 'MWStake.Notifier' );
		foreach ( $notificationClasses as $notificationClass ) {
			if ( !$notificationClass ) {
				continue;
			}
			$event = $eventFactory->create( $notificationClass, [
				$this->user,
				$this->entity,
				SocialEvent::ACTION_DELETE
			] );
			if ( $event instanceof SocialEvent ) {
				$event->setNotifyAll( $notifyAll );
				$notifier->emit( $event );
			}
		}
	}

}
