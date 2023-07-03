<?php

namespace BlueSpice\Social\ExtendedSearch;

use BlueSpice\EntityFactory;
use BlueSpice\Social\ExtendedSearch\Crawler\Entity as EntityCrawler;
use BlueSpice\Social\ExtendedSearch\DocumentProvider\Entity as EntityDocumentProvider;
use BlueSpice\Social\ExtendedSearch\Formatter\EntityFormatter;
use BlueSpice\Social\ExtendedSearch\LookupModifier\AddHighlighters;
use BlueSpice\Social\ExtendedSearch\LookupModifier\AutocompleteSourceFields;
use BlueSpice\Social\ExtendedSearch\LookupModifier\EntitySimpleQSFields;
use BlueSpice\Social\ExtendedSearch\LookupModifier\EntityTypeAggregation;
use BlueSpice\Social\ExtendedSearch\LookupModifier\FilterOutActionEntities;
use BlueSpice\Social\ExtendedSearch\MappingProvider\Entity as EntityMappingProvider;
use BlueSpice\Social\ExtendedSearch\Updater\Entity as EntityUpdater;
use BS\ExtendedSearch\ISearchCrawler;
use BS\ExtendedSearch\ISearchDocumentProvider;
use BS\ExtendedSearch\ISearchMappingProvider;
use BS\ExtendedSearch\ISearchResultFormatter;
use BS\ExtendedSearch\ISearchUpdater;
use BS\ExtendedSearch\Lookup;
use BS\ExtendedSearch\Source\WikiPages;
use IContextSource;
use MediaWiki\MediaWikiServices;

class Entities extends WikiPages {

	/**
	 *
	 * @return ISearchCrawler
	 */
	public function getCrawler(): ISearchCrawler {
		return $this->makeObjectFromSpec( [
			'class' => EntityCrawler::class,
			'args' => [ $this->config ],
			'services' => [ 'DBLoadBalancer', 'JobQueueGroup' ]
		] );
	}

	/**
	 *
	 * @return ISearchDocumentProvider
	 */
	public function getDocumentProvider(): ISearchDocumentProvider {
		return $this->makeObjectFromSpec( [
			'class' => EntityDocumentProvider::class,
			'services' => [
				'HookContainer', 'ContentRenderer', 'RevisionLookup', 'PageProps', 'Parser',
				'RedirectLookup', 'UserFactory'
			]
		] );
	}

	/**
	 *
	 * @return \BS\ExtendedSearch\Source\MappingProvider\WikiPage
	 */
	public function getMappingProvider(): ISearchMappingProvider {
		return new EntityMappingProvider();
	}

	/**
	 *
	 * @return ISearchUpdater
	 */
	public function getUpdater(): ISearchUpdater {
		return new EntityUpdater( $this );
	}

	/**
	 *
	 * @return ISearchResultFormatter
	 */
	public function getFormatter(): ISearchResultFormatter {
		/** @var EntityFactory $entityFactory */
		$entityFactory = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );

		return new EntityFormatter( $this,
			$entityFactory );
	}

	/**
	 * @return bool
	 */
	public function isSortable() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getLookupModifiers( Lookup $lookup, IContextSource $context ): array {
		return [
			new AddHighlighters( $lookup, $context ),
			new AutocompleteSourceFields( $lookup, $context ),
			new EntitySimpleQSFields( $lookup, $context ),
			new EntityTypeAggregation( $lookup, $context ),
			new FilterOutActionEntities( $lookup, $context ),
		];
	}
}
