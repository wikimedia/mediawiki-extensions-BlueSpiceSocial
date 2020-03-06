<?php

namespace BlueSpice\Social\Hook\BSEntityDeleteComplete;

use BlueSpice\Hook\BSEntityDeleteComplete;
use BlueSpice\Social\Notifications\SocialNotification;

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
		$isWatchEnabled = \BlueSpice\Services::getInstance()
			->getService( 'BSExtensionRegistry' )->hasName( "BlueSpiceSocialWatch" );

		$notifyAll = false;
		if ( !$isWatchEnabled ) {
			$notifyAll = true;
		}

		$notificationsManager = \BlueSpice\Services::getInstance()->getService( 'BSNotificationManager' );

		$notifier = $notificationsManager->getNotifier();

		$notificationClasses = $this->entity->getConfig()->get( 'NotificationObjectClass' );
		if ( !is_array( $notificationClasses ) ) {
			$notificationClasses = [ $notificationClasses ];
		}
		$notificationTypePrefix = $this->entity->getConfig()->get( 'NotificationTypePrefix' );

		foreach ( $notificationClasses as $notificationClass ) {
			$notification = new $notificationClass(
				$notificationTypePrefix,
				$this->entity,
				$this->user,
				SocialNotification::ACTION_DELETE
			);
			$notification->setNotifyAll( $notifyAll );

			$notifier->notify( $notification );
		}
	}

}
