<?php

namespace BlueSpice\Social\Api\Store;

use BlueSpice\Social\Entity as SocialEntity;
use BlueSpice\Timestamp;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\Filter\Date;
use MWStake\MediaWiki\Component\DataStore\FilterFactory;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class Entity extends \BlueSpice\Api\Store {

	protected function makeDataStore() {
		return new \BlueSpice\Social\Data\Entity\Store( $this->getContext() );
	}

	/**
	 *
	 * @return ReaderParams
	 */
	protected function getReaderParams() {
		$readerParams = parent::getReaderParams();
		$filters = $readerParams->getFilter();
		foreach ( $filters as &$filter ) {
			if ( $filter->{Date::KEY_PROPERTY} === SocialEntity::ATTR_TIMESTAMP_CREATED ) {
				// reset creation filter timestamp to UTC
				$filter->{Date::KEY_COMPARISON} = Date::COMPARISON_GREATER_THAN;
				$ts = new Timestamp( $filter->{Date::KEY_VALUE} );
				$ts->unOffsetForUser( $this->getUser() );
				$filter->{Date::KEY_VALUE} = $ts->format( 'YmdHis' );
				break;
			}
		}
		$invertedTypeMap = array_flip( FilterFactory::getTypeMap() );
		$newFilters = [];
		foreach ( $filters as $filter ) {
			$newFilters[] = [
				Filter::KEY_TYPE => $invertedTypeMap[get_class( $filter )],
				Filter::KEY_FIELD => $filter->getField(),
				Filter::KEY_COMPARISON => $filter->getComparison(),
				Filter::KEY_VALUE => $filter->getValue()
			];
		}

		return new ReaderParams( [
			ReaderParams::PARAM_QUERY => $readerParams->getQuery(),
			ReaderParams::PARAM_START => $readerParams->getStart(),
			ReaderParams::PARAM_LIMIT => $readerParams->getLimit(),
			ReaderParams::PARAM_SORT => $readerParams->getSort(),
			ReaderParams::PARAM_FILTER => $newFilters,
		] );
	}
}
