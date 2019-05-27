<?php

namespace BlueSpice\Social\Notifications;

use BlueSpice\Social\Entity as SocialEntity;
use BlueSpice\BaseNotification;

class SocialNotification extends BaseNotification {
	const ACTION_EDIT = 'edit';
	const ACTION_CREATE = 'create';
	const ACTION_DELETE = 'delete';

	/**
	 *
	 * @var string
	 */
	protected $key;
	protected $action;

	/**
	 *
	 * @var \User
	 */
	protected $user;
	protected $realname;

	/**
	 *
	 * @var BlueSpice\Social\Entity
	 */
	protected $entity;

	protected $notifyAll = false;
	
	public function __construct( $key, SocialEntity $entity, \User $agent, $action = self::ACTION_EDIT ) {
		$this->key = $key;
		$this->entity = $entity;
		$this->user = $agent;
		$this->action = $action;

		$realname = \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getUserHelper( $this->user )->getDisplayName();

		$this->realname = $realname;
	}

	public function setNotifyAll( $value = true ) {
		$this->notifyAll = $value;
	}

	public function getAudience() {
		return $this->getUsersWatching();
	}

	public function getKey() {
		return $this->key . '-' . $this->action;
	}

	public function getParams() {
		return [
			'entitytype' => wfMessage( $this->entity->getConfig()->get( 'TypeMessageKey' ) )->plain(),
			'realname' => $this->realname,
			'primary-link-label' => wfMessage( 'bs-social-notification-primary-link-label' )->plain(),
			//Todo: Find better way to diferentiate between social notifs and other
			'social-notification' => true
		];
	}

	public function getUser() {
		return $this->user;
	}

	/**
	 * Returns SocialEntity this notifiaction is about
	 *
	 * @return SocialEntity
	 */
	public function getEntity() {
		return $this->entity;
	}

	public function getTitle() {
		$title = $this->entity->getTitle();
		if( $title instanceof \Title && $title->exists() ) {
			return $title;
		}
		return null;
	}

	/**
	 * Gets all users watching the given entity page
	 *
	 * @return array
	 */
	protected function getUsersWatching() {
		$users = [];

		$title = $this->getWatchedTitle();
		if( !$title instanceof \Title || !$title->exists() ) {
			return $users;
		}

		$userIdProperty = $this->getUserIdProperty();

		$res = $this->runQuery( $title );

		foreach( $res as $row ) {
			$user = \User::newFromId( $row->$userIdProperty );
			if( $user instanceof \User ) {
				if( in_array( $user->getId(), $this->getUserIdsToSkip() ) ) {
					continue;
				}

				if( $title->userCan( 'read', $user ) == false ) {
					continue;
				}
				$users[] = $user->getId();
			}
		}

		return $users;
	}

	protected function getWatchedTitle() {
		return $this->entity->getTitle();
	}

	protected function runQuery( $title ) {
		if( $this->notifyAll ){
			return wfGetDB( DB_SLAVE )->select(
				'user',
				'user_id'
			);
		} else {
			return wfGetDB( DB_SLAVE )->select(
				'watchlist',
				'wl_user',
				[
					'wl_namespace' => $title->getNamespace(),
					'wl_title' => $title->getText()
				]
			);
		}
	}

	protected function getUserIdProperty() {
		if( $this->notifyAll ){
			return 'user_id';
		}
		return 'wl_user';
	}

	protected function getUserIdsToSkip() {
		return [$this->user->getId()];
	}
}