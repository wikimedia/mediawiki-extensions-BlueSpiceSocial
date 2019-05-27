<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\Data\IPrimaryDataProvider;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Filter\Boolean;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\Filter\Date;
use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity;
use BlueSpice\Services;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 *
	 * @var \BlueSpice\Data\Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \BS\ExtendedSearch\Backend
	 */
	protected $searchBackend = null;

	/**
	 *
	 * @var Schema
	 */
	protected $schema = null;

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \BS\ExtendedSearch\Backend $searchBackend
	 * @param Schema
	 */
	public function __construct( \BS\ExtendedSearch\Backend $searchBackend, $factory, \IContextSource $context, Schema $schema ) {
		$this->searchBackend = $searchBackend;
		$this->schema = $schema;
		$this->factory = $factory;
		$this->context = $context;
	}

	/**
	 *
	 * @var \BS\ExtendedSearch\Backend
	 */
	public function getSearchBackend() {
		return $this->searchBackend;
	}

	/**
	 *
	 * @return \Elastica\Client
	 */
	public function getSearchClient() {
		return $this->getSearchBackend()->getClient();
	}

	/**
	 *
	 * @return \Elastica\Index
	 */
	public function getSearchIndex() {
		return $this->getSearchBackend()->getIndexByType( 'socialentity' );
	}

	protected function getSearch() {
		$search = new \Elastica\Search( $this->getSearchClient() );
		$search->addIndex( $this->getSearchIndex() );
		$search->addType(
			new \Elastica\Type( $this->getSearchIndex(), 'socialentity' )
		);
		return $search;
	}

	/**
	 *
	 * @param ReaderParams $params
	 */
	public function makeData( $params ) {
		$query = [];
		$query = $this->makePreFilterConds( $params, $query );
		$query = $this->makePreOptionConds( $params, $query );
		$queryJSON = \FormatJson::encode( $query, true );

		wfDebugLog( 'BSSocialEntities', __METHOD__.":\n".$queryJSON );
		do {
			try {
				$result = $this->getSearch()->search( $query );
			} catch ( \Exception $ex ) {
				//When there is no document in the index yet, a query may
				//crash with "Fielddata access on the _id field is disallowed"
				wfDebugLog(
					'BSSocialEntities',
					__METHOD__.":\nException during search - "
						. $ex->getMessage()
				);
				return $this->data;
			}

			if( $result->count() < 1 ) {
				return $this->data;
			}
			foreach( $result as $row ) {
				$this->appendRowToData( $row );
				if( $params->getLimit() === $params::LIMIT_INFINITE ) {
					continue;
				}
				if( count( $this->data ) >= $params->getLimit() ) {
					return $this->data;
				}
			}
			//because elastic search cant handle from + size larger than
			//10000 -.-
			//see: https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-search-after.html
			$query['from'] = -1;
			$query['search_after'] = $row->getHit()['sort'];
		} while( true );

		return $this->data;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @param array $query
	 * @return array
	 */
	protected function makePreFilterConds( $params, $query ) {
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach( $this->schema->getFilterableFields() as $fieldname ) {
			if( !$filter = $filterFinder->findByField( $fieldname ) ) {
				continue;
			}

			$value = $filter->getValue();

			if( $filter instanceof Numeric ) {
				$value = empty( $value ) ? 0 : (int) $value;
			}

			if( $filter instanceof Boolean ) {
				$value = empty( $value ) ? false : true;
			}

			if( $filter instanceof ListValue ) {
				if( is_object( $value ) ) {
					$value = (array) $value;
				}
				if( is_string( $value ) ) {
					$value = explode( '', $value );
				}
				if( !is_array( $value ) ) {
					$value = (array)$value;
				}
				$query['query']['bool']["filter"][] = [
					"terms" => [
						"entitydata.$fieldname" => array_values( $value )
					]
				];
				$filter->setApplied();
				continue;
			}

			if( $filter instanceof Date && $filter->getComparison() === Date::COMPARISON_LOWER_THAN ) {
				$query['query']['bool']['filter'][] = [
					"range" => [
						"entitydata.$fieldname" => [
							"format" => "yyyyMMddHHmmss",
							"lt" => $value,
						]
					]
				];
				continue;
			}
			if( $filter instanceof Date && $filter->getComparison() === Date::COMPARISON_GREATER_THAN ) {
				$query['query']['bool']['filter'][] = [
					"range" => [
						"entitydata.$fieldname" => [
							"format" => "yyyyMMddHHmmss",
							"gt" => $value,
						]
					]
				];
				continue;
			}
			if( $filter instanceof Numeric && $filter->getComparison() === Numeric::COMPARISON_LOWER_THAN ) {
				$query['query']['bool']['filter'][] = [
					"range" => [
						"entitydata.$fieldname" => [
							"lt" => $value,
						]
					]
				];
				continue;
			}
			if( $filter instanceof Numeric && $filter->getComparison() === Numeric::COMPARISON_GREATER_THAN ) {
				$query['query']['bool']['filter'][] = [
					"range" => [
						"entitydata.$fieldname" => [
							"gt" => $value,
						]
					]
				];
				continue;
			}
			
			$query['query']['bool']["filter"][] = [
				"term" => [
					"entitydata.$fieldname" => $value
				]
			];

			$filter->setApplied();
		}

		return $query;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @param array $query
	 * @return array
	 */
	protected function makePreOptionConds( $params, $query ) {
		$sort = $params->getSort();
		if( is_array( $sort ) ) {
			$sort = $sort[0];
		}
		$mapping = \BlueSpice\Social\ExtendedSearch\MappingProvider\Entity
			::getValueTypeMapping();

		$type = $this->schema[$sort->getProperty()][Schema::TYPE];
		if( isset( $mapping[$type] ) ) {
			$type = $mapping[$type];
		}
		$query['sort'] = [
			"entitydata.".$sort->getProperty() => [
				"order" => $sort->getDirection(),
				"unmapped_type" => $type,
				//"missing" => "_last"
			],
			"_id" => [
				"order" => "desc"
			]
		];
		$query['size'] = $params->getLimit();
		$query["from"] = $params->getStart();

		return $query;
	}


	protected function appendRowToData( \Elastica\Result $row ) {
		$record = new Record( $row );
		$entity = $this->factory->newFromObject( $record->getData() );
		if( !$entity instanceof Entity ) {
			return;
		}
		$user = $this->context->getUser();
		if( !$user ) {
			return;
		}

		if( !$this->isSystemUser( $user ) ) {
			if( !$entity->userCan( 'read', $user )->isOK() ) {
				return;
			}
		}
		$this->data[] = $record;
	}

	protected function isSystemUser( \User $user ) {
		return Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->isMaintenanceUser( $user );
	}
}
