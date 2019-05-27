<?php

namespace BlueSpice\Social\ExtendedSearch;

use BlueSpice\Social\ExtendedSearch\Formatter;
use BlueSpice\Social\ExtendedSearch\LookupModifier;
use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;

class Entities extends \BS\ExtendedSearch\Source\DecoratorBase {

	protected $lookupModifiers = [
		LookupModifierBase::TYPE_SEARCH => [
			'filteroutactionentities' => LookupModifier\FilterOutActionEntities::class,
			'addhighlighters' => LookupModifier\AddHighlighters::class,
			'entitytypeaggregation' => LookupModifier\EntityTypeAggregation::class,
			'entitysimpleqsfields' => LookupModifier\EntitySimpleQSFields::class
		],
		LookupModifierBase::TYPE_AUTOCOMPLETE => [
			'filteroutactionentities' => LookupModifier\FilterOutActionEntities::class,
			'autocompletesourcefields' => LookupModifier\AutocompleteSourceFields::class
		]
	];

	/**
	 * @param \BS\ExtendedSearch\Source\Base $base
	 * @return Entities
	 */
	public static function create( $base ) {
		return new self( $base );
	}

	/**
	 *
	 * @return Entity
	 */
	public function getCrawler() {
		return new \BlueSpice\Social\ExtendedSearch\Crawler\Entity( $this->getConfig() );
	}

	/**
	 *
	 * @return Entity
	 */
	public function getDocumentProvider() {
		return new \BlueSpice\Social\ExtendedSearch\DocumentProvider\Entity(
			$this->oDecoratedSource->getDocumentProvider()
		);
	}

	/**
	 *
	 * @return Entity
	 */
	public function getMappingProvider() {
		return new \BlueSpice\Social\ExtendedSearch\MappingProvider\Entity(
			$this->oDecoratedSource->getMappingProvider()
		);
	}

	/**
	 *
	 * @return Entity
	 */
	public function getUpdater() {
		return new \BlueSpice\Social\ExtendedSearch\Updater\Entity( $this->oDecoratedSource );
	}

	public function getFormatter() {
		return new Formatter\EntityFormatter( $this );
	}
}