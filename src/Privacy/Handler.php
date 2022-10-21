<?php

namespace BlueSpice\Social\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Social\Privacy\Job\DeleteEntity;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DataStore\Filter\Numeric;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Record;
use Wikimedia\Rdbms\IDatabase;

class Handler implements IPrivacyHandler {
	/**
	 * @var \Language
	 */
	protected $language;

	/**
	 * @var \User
	 */
	protected $user;

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->language = \RequestContext::getMain()->getLanguage();
		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return \Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$this->user = $this->services->getUserFactory()->newFromName( $oldUsername );
		$entityRecords = $this->getAllEntities();

		$services = MediaWikiServices::getInstance();
		$entityFactory = $services->getService( 'BSEntityFactory' );
		$jobs = [];
		foreach ( $entityRecords as $record ) {
			$data = $record->getData();

			$entity = $entityFactory->newFromObject( $data );
			if ( !$entity instanceof Entity ) {
				continue;
			}

			// Add job to update search index after the process has completed
			$jobs[] = new \BlueSpice\Social\ExtendedSearch\Job\Entity(
				$entity->getTitle()
			);
		}
		$services->getJobQueueGroup()->push( $jobs );

		return \Status::newGood();
	}

	/**
	 * @param \User $userToDelete
	 * @param \User $deletedUser
	 * @return \Status
	 */
	public function delete( \User $userToDelete, \User $deletedUser ) {
		$this->user = $userToDelete;
		$entityRecords = $this->getAllEntities();

		$services = MediaWikiServices::getInstance();
		$entityFactory = $services->getService( 'BSEntityFactory' );
		$jobs = [];
		foreach ( $entityRecords as $record ) {
			$data = $record->getData();

			$entity = $entityFactory->newFromObject( $data );
			if ( !$entity instanceof Entity ) {
				continue;
			}

			// Do changing owner ID in a job, since it edits the page
			$jobs[] = new DeleteEntity(
				$entity->getTitle(), [
					'deletedUserId' => $deletedUser->getId()
				]
			);
		}
		$services->getJobQueueGroup()->push( $jobs );

		return \Status::newGood();
	}

	/**
	 * Very minimalistic data export - until we know more on what
	 * we should export and what not
	 *
	 * @param array $types Types of info users wants to retrieve
	 * @param string $format Requested output format
	 * @param \User $user User to export data from
	 * @return \Status
	 */
	public function exportData( array $types, $format, \User $user ) {
		$this->user = $user;
		$entityRecords = $this->getAllEntities();

		$exportData = [];
		foreach ( $entityRecords as $record ) {
			$data = $record->getData();

			$entity = $this->services->getService( 'BSEntityFactory' )->newFromObject( $data );
			if ( !$entity instanceof Entity\Text ) {
				continue;
			}

			$timestamp = $this->language->userTimeAndDate(
				$entity->getTimestampCreated(),
				$user
			);
			$exportData[] = "$timestamp: {$data->header}: {$data->text}";
		}

		return \Status::newGood( [
			Transparency::DATA_TYPE_CONTENT => $exportData
		] );
	}

	/**
	 *
	 * @return Record[]
	 */
	protected function getAllEntities() {
		$context = new \BlueSpice\Context(
			\RequestContext::getMain(),
			$this->services->getConfigFactory()->makeConfig( 'bsg' )
		);
		$serviceUser = $this->services->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new EntityListContext\PrivacyHandler(
			$context,
			$context->getConfig(),
			$serviceUser,
			null
		);
		$filters = $listContext->getFilters();
		$filters[] = (object)[
			Numeric::KEY_PROPERTY => Entity::ATTR_OWNER_ID,
			Numeric::KEY_VALUE => $this->user->getId(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];

		$params = new ReaderParams( [
			'filter' => $filters,
			'sort' => $listContext->getSort(),
			'limit' => $listContext->getLimit(),
			'start' => 0,
		] );

		$store = new \BlueSpice\Social\Data\Entity\Store();
		$res = $store->getReader( $listContext )->read( $params );

		return $res->getRecords();
	}
}
