<?php

namespace BlueSpice\Social\Event;

use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity;
use MediaWiki\Permissions\GroupPermissionsLookup;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use Message;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\EventLink;
use MWStake\MediaWiki\Component\Events\TitleEvent;
use stdClass;
use Title;
use Wikimedia\Rdbms\ILoadBalancer;

class SocialEvent extends TitleEvent {

	public const ACTION_EDIT = 'edit';
	public const ACTION_CREATE = 'create';
	public const ACTION_DELETE = 'delete';

	/** @var Entity */
	protected $entity;
	/** @var string */
	protected $action;
	/** @var ILoadBalancer */
	protected $lb;
	/** @var UserFactory */
	protected $userFactory;
	/** @var GroupPermissionsLookup */
	protected $groupPermissionsLookup;
	/** @var bool */
	protected $notifyAll = false;

	/**
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param GroupPermissionsLookup $gpl
	 * @param EntityFactory $entityFactory
	 * @param UserIdentity $agent
	 * @param stdClass $entityData
	 * @param string $action
	 */
	public function __construct(
		ILoadBalancer $lb, UserFactory $userFactory, GroupPermissionsLookup $gpl,
		EntityFactory $entityFactory, UserIdentity $agent, stdClass $entityData, string $action = self::ACTION_EDIT
	) {
		$this->entity = $entityFactory->newFromObject( $entityData );
		parent::__construct( $agent, $this->entity->getRelatedTitle() ?? $this->entity->getTitle() );

		$this->action = $action;
		$this->lb = $lb;
		$this->userFactory = $userFactory;
		$this->groupPermissionsLookup = $gpl;
	}

	/**
	 *
	 * @return Title
	 */
	protected function getWatchedTitle() {
		return $this->entity->getRelatedTitle();
	}

	/**
	 * @return Message
	 */
	public function getEntityTypeMessage(): Message {
		return Message::newFromKey(
			$this->entity->getConfig()->get( 'TypeMessageKey' )
		);
	}

	/**
	 * @return Message
	 */
	public function getKeyMessage(): Message {
		return Message::newFromKey( "bs-social-event-$this->action-desc" );
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage( IChannel $forChannel ): Message {
		return Message::newFromKey( "bs-social-event-$this->action" )->params(
			$this->getEntityTypeMessage()->text()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getLinks( IChannel $forChannel ): array {
		return [
			new EventLink(
				$this->entity->getTitle()->getFullURL(),
				Message::newFromKey( 'bs-social-notification-primary-link-label' )
			)
		];
	}

	/**
	 * @return UserIdentity[]|null
	 */
	public function getPresetSubscribers(): ?array {
		$users = [];

		$title = $this->getWatchedTitle();

		if ( !( $title instanceof Title ) || !$title->exists() ) {
			return $users;
		}

		$userIdProperty = $this->getUserIdProperty();
		$res = $this->runQuery( $title );

		foreach ( $res as $row ) {
			$user = $this->userFactory->newFromId( $row->$userIdProperty );
			if ( in_array( $user->getId(), $this->getUserIdsToSkip() ) ) {
				continue;
			}
			$users[$user->getId()] = $user;
		}
		return array_values( $users );
	}

	/**
	 *
	 * @param bool $value
	 */
	public function setNotifyAll( $value = true ) {
		$this->notifyAll = $value;
	}

	/**
	 *
	 * @param \Title $title
	 * @return bool|\Wikimedia\Rdbms\IResultWrapper
	 */
	protected function runQuery( $title ) {
		$groups = $this->groupPermissionsLookup->getGroupsWithPermission( 'read' );
		if ( in_array( '*', $groups ) || in_array( 'user', $groups ) ) {
			$allowedGroups = null;
		} else {
			$allowedGroups = $groups;
		}
		if ( $this->notifyAll ) {
			$tables = [ 'user' ];
			$conds = [];
			if ( $allowedGroups ) {
				$tables[] = 'user_groups';
				$conds = [
					'user_id = ug_user',
					'ug_group IN (' . $this->lb->getConnection( DB_REPLICA )->makeList( $allowedGroups ) . ')'
				];
			}
			return $this->lb->getConnection( DB_REPLICA )->select(
				$tables,
				[ 'user_id' ],
				$conds,
				__METHOD__
			);
		} else {
			$tables = [ 'watchlist' ];
			$conds = [
				'wl_namespace' => $title->getNamespace(),
				'wl_title' => $title->getDBkey(),
			];
			if ( $allowedGroups ) {
				$tables[] = 'user_groups';
				$conds = array_merge( $conds, [
					'wl_user = ug_user',
					'ug_group IN (' . $this->lb->getConnection( DB_REPLICA )->makeList( $allowedGroups ) . ')'
				] );
			}
			return $this->lb->getConnection( DB_REPLICA )->select(
				$tables,
				[ 'wl_user' ],
				$conds,
				__METHOD__
			);
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function getUserIdProperty() {
		if ( $this->notifyAll ) {
			return 'user_id';
		}
		return 'wl_user';
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-social-event';
	}

	/**
	 *
	 * @return array
	 */
	protected function getUserIdsToSkip() {
		return [ $this->agent->getId() ];
	}
}
