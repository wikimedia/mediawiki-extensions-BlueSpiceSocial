<?php
/**
 * Provides the base api for BlueSpiceSocial.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BluespiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
namespace BlueSpice\Social\Api\Task;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Services;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Renderer\EntityList;
use BlueSpice\Social\ResourceCollector;

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpiceSocial
 */
class Entities extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'getEntities',
		'getEntity',
		'editEntity',
		'deleteEntity',
		'getConfigs',
	);

	protected $aReadTasks = array(
		'getEntities',
		'getConfigs',
	);

	protected function getRequiredTaskPermissions() {
		return array(
			'getEntities' => array( 'read' ),
			'getEntity' => array( 'read' ),
			'editEntity' => array( 'read', 'edit' ),
			'deleteEntity' => array( 'read', 'edit' ),
			'getConfigs' => array( 'read' ),
		);
	}

	public function task_getEntity( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$entity = $this->getEntityFactory()->newFromObject(
			$taskData
		);
		if( !$entity instanceof Entity || !$entity->exists() ) {
			return $result;
		}

		$status = $entity->userCan( 'read', $this->getUser() );
		if( !$status->isOK() ) {
			$result->message = $status->getWikiText();
			return $result;
		}
		$result->success = true;
		$result->payload['entity'] = \FormatJson::encode( $entity );
		$result->payload['actions'] = $entity->getActions(
			[],
			$this->getUser()
		);

		$renderer = $entity->getRenderer( $this->getContext() );
		if( empty( $taskData->outputtype ) ) {
			$result->payload['view'] = $renderer->render();
		} else {
			$result->payload['view'] = $renderer->render(
				$taskData->outputtype
			);
		}
		return $result;
	}

	public function task_getConfigs( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		$resourceCollector = ResourceCollector::getMain();

		$result->success = true;
		$result->payload = $resourceCollector->getConfig();

		return $result;
	}

	public function task_getEntities( $vTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		$context = $this->getContext();

		if( !$context instanceof EntityListContext ) {
			$class = "\\BlueSpice\\Social\\EntityListContext";
			if( isset( $vTaskData->EntityListContext ) ) {
				$class = $vTaskData->EntityListContext;
			}
			$entity = null;
			if( isset( $vTaskData->parentid ) ) {
				$entity = $this->getEntityFactory()->newFromID(
					$vTaskData->parentid,
					Entity::NS
				);
			}
			$context = new $class(
				$this->getContext(),
				$this->getConfig(),
				$this->getUser(),
				$entity
			);
		}

		$params = array_merge(
			(array) $vTaskData,
			[ 'context' => $context ]
		);
		$renderer = $this->getServices()->getBSRendererFactory()->get(
			'entitylist',
			new Params( $params )
		);

		$oResult->success = true;
		$oResult->payload['entities'] = [];
		$args = $renderer->getArgs();
		$renderTypes = $args[EntityList::PARAM_OUTPUT_TYPES];
		if( $args[EntityList::PARAM_OFFSET] < 1  ) {

			foreach( $args[EntityList::PARAM_PRELOADED_ENTITIES] as $raw ) {
				$entity = $this->getEntityFactory()->newFromObject(
					(object)$raw
				);
				if( !$entity instanceof Entity ) {
					continue;
				}
				$renderType = 'Default';
				if( isset( $renderTypes[$entity->get( Entity::ATTR_TYPE )] ) ) {
					$renderType = $renderTypes[$entity->get( Entity::ATTR_TYPE )];
				}
				$oResult->payload['entities'][] = [
					'entity' => \FormatJson::encode( $entity ),
					'view' => $entity->getRenderer( $context )->render(
						$renderType
					),
				];
			}
		}

		foreach( $renderer->getEntities() as $entity ) {
			if( !$entity ) {
				continue;
			}
			$renderType = 'Default';
			if( isset( $renderTypes[$entity->get( Entity::ATTR_TYPE )] ) ) {
				$renderType = $renderTypes[$entity->get( Entity::ATTR_TYPE )];
			}
			$oResult->payload['entities'][] = [
				'entity' => \FormatJson::encode( $entity ),
				'view' => $entity->getRenderer( $context )->render(
					$renderType
				),
			];
		}

		return $oResult;
	}

	public function task_editEntity( $vTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		$oEntity = $this->getEntityFactory()->newFromObject(
			$vTaskData
		);
		if( !$oEntity instanceof Entity ) {
			return $oResult;
		}

		$sAction = $oEntity->exists()
			? 'edit'
			: 'create'
		;
		if( $sAction == 'edit' && !$oEntity->userIsOwner( $this->getUser() ) ) {
			$sAction = 'editothers';
		}

		$oStatus = $oEntity->userCan( $sAction, $this->getUser() );
		if( !$oStatus->isOK() ) {
			$oResult->message = $oStatus->getWikiText();
			return $oResult;
		}
		$oEntity->setValuesByObject( $vTaskData );
		$oStatus = $oEntity->save( $this->getUser() );
		if( $oStatus->isOk() ) {
			$oResult->success = true;
		} else {
			$oResult->message = $oStatus->getWikiText();
		}
		$oResult->payload['entity'] = \FormatJson::encode( $oEntity );
		$oResult->payload['actions'] = $oEntity->getActions(
			[], 
			$this->getUser()
		);
		$oResult->payload['entityconfig'][$oEntity->get( Entity::ATTR_TYPE )]
			= \FormatJson::encode( $oEntity->getConfig() );

		$renderer = $oEntity->getRenderer( $this->getContext() );
		if( empty( $vTaskData->outputtype ) ) {
			$oResult->payload['view'] = $renderer->render();
		} else {
			$oResult->payload['view'] = $renderer->render(
				$vTaskData->outputtype
			);
		}
		return $oResult;
	}

	public function task_deleteEntity( $vTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		$undelete = isset( $vTaskData->undelete ) && $vTaskData->undelete
			? true
			: false;

		$oEntity = $this->getEntityFactory()->newFromID(
			$vTaskData->id,
			Entity::NS
		);
		if( !$oEntity instanceof Entity ) {
			$oResult->message = "entity $vTaskData->id not found";
			return $oResult;
		}

		$sAction = 'delete';
		if( !$oEntity->userIsOwner( $this->getUser() ) ) {
			$sAction = 'deleteothers';
		}

		$oStatus = $oEntity->userCan( $sAction, $this->getUser() );
		if( !$oStatus->isOK() ) {
			$oResult->message = $oStatus->getWikiText();
			return $oResult;
		}
		if( $undelete ) {
			$oStatus = $oEntity->undelete( $this->getUser() );
		} else {
			$oStatus = $oEntity->delete( $this->getUser() );
		}
		if( $oStatus->isOK() ) {
			$oResult->success = true;
		} else {
			$oResult->message = $oStatus->getHTML();
		}
		$oResult->payload['entity'] = \FormatJson::encode( $oEntity );
		$oResult->payload['actions'] = $oEntity->getActions(
			[],
			$this->getUser()
		);
		$oResult->payload['entityconfig'][$oEntity->get( Entity::ATTR_TYPE )]
			= \FormatJson::encode( $oEntity->getConfig() );
		$oResult->payload['view'] = $oEntity->getRenderer()->render();
		return $oResult;
	}

	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		return parent::getAllowedParams() + array(

		);
	}

	/**
	 * Returns the basic param descriptions
	 * @return array
	 */
	public function getParamDescription() {
		return parent::getParamDescription() + array(

		);
	}

	/**
	 * Returns the bsic description for this module
	 * @return type
	 */
	public function getDescription() {
		return array(
			'BSApiTasksBase: This should be implemented by subclass'
		);
	}

	/**
	 * Returns the basic example
	 * @return type
	 */
	public function getExamples() {
		return array(
			'api.php?action='.$this->getModuleName().'&task='.$this->aTasks[0].'&taskData={someKey:"someValue",isFalse:true}',
		);
	}

	/**
	 *
	 * @return \BlueSpice\EntityFactory
	 */
	protected function getEntityFactory() {
		return $this->getServices()->getBSEntityFactory();
	}

	/**
	 *
	 * @return Services
	 */
	protected function getServices() {
		return Services::getInstance();
	}
}