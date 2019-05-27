<?php
/**
 * EntityConfig class for BlueSpiceSocial
 *
 * add desc
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
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
namespace BlueSpice\Social;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Data\FieldType;
use BlueSpice\Social\ExtendedSearch\Formatter\Internal\EntityFormatter;

/**
 * EntityConfig class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class EntityConfig extends \BlueSpice\EntityConfig {
	public function addGetterDefaults() {
		return array(
			'ModuleStyles' => array(),
			'ModuleScripts' => array(),
		);
	}
	protected function get_OutputClass() {
		return '';
	}

	protected function get_Renderer() {
		return "socialentity";
	}

	protected function get_StoreClass() {
		return "\\BlueSpice\\Social\\Data\\Entity\\Store";
	}
	protected function get_ChildListContextClass() {
		return "\\BlueSpice\\Social\\EntityListContext\\Children";
	}
	protected function get_TypeMessageKey() {
		return '';
	}
	protected function get_HeaderMessageKey() {
		return '';
	}
	protected function get_HeaderMessageKeyCreateNew() {
		return '';
	}
	protected function get_VarMessageKeys() {
		return [
			Entity::ATTR_ID => 'bs-social-var-id',
			Entity::ATTR_OWNER_ID => 'bs-social-var-ownerid',
			Entity::ATTR_TYPE => 'bs-social-var-type',
			Entity::ATTR_ARCHIVED => 'bs-social-var-archived',
			Entity::ATTR_TIMESTAMP_CREATED => 'bs-social-var-timestampcreated',
			Entity::ATTR_TIMESTAMP_TOUCHED => 'bs-social-var-timestamptouched',
		];
	}

	protected function get_DeleteConfirmMessageKey() {
		return 'bs-social-entityaction-delete-confirmtext';
	}

	protected function get_UnDeleteConfirmMessageKey() {
		return 'bs-social-entityaction-undelete-confirmtext';
	}

	protected function get_ContentClass() {
		return '\\BlueSpice\\Social\\Content\\Entity';
	}
	protected function get_AttributeDefinitions() {
		return array_merge(
			parent::get_AttributeDefinitions(),
			[
				Entity::ATTR_PARENT_ID => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::INT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
				Entity::ATTR_HEADER => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::TEXT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_RELATED_TITLE => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_OWNER_NAME => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_OWNER_REAL_NAME => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => true,
					Schema::STORABLE => false,
				],
				Entity::ATTR_ACTIONS => [
					Schema::FILTERABLE => false,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::LISTVALUE,
					Schema::INDEXABLE => false,
					Schema::STORABLE => false,
				],
				Entity::ATTR_PRELOAD => [
					Schema::FILTERABLE => false,
					Schema::SORTABLE => false,
					Schema::TYPE => FieldType::STRING,
					Schema::INDEXABLE => false,
					Schema::STORABLE => false,
				],
			]
		);
	}
	protected function get_EntityTemplateDefault() {
		return 'BlueSpiceSocial.Entity.Default';
	}
	protected function get_EntityTemplatePage() {
		return 'BlueSpiceSocial.Entity.Page';
	}
	protected function get_EntityTemplateShort() {
		return 'BlueSpiceSocial.Entity.Short';
	}
	protected function get_EntityTemplateList() {
		return 'BlueSpiceSocial.Entity.List';
	}
	protected function get_IsEditable() {
		return true;
	}
	protected function get_IsCreatable() {
		return true;
	}
	protected function get_IsDeleteable() {
		return true;
	}
	protected function get_IsSpawnable() {
		return $this->get_IsCreatable();
	}
	protected function get_CanHaveChildren() {
		return true;
	}
	protected function get_ModuleScripts() {
		return [
			'ext.bluespice.social',
			'ext.bluespice.social.entity',
		];
	}
	protected function get_ModuleStyles() {
		return [
			'ext.bluespice.social.styles',
		];
	}
	protected function get_PermissionTitleRequired() {
		return true;
	}
	protected function get_ReadPermission() {
		return 'read';
	}
	protected function get_CreatePermission() {
		return 'edit';
	}
	protected function get_EditPermission() {
		return 'edit';
	}
	protected function get_DeletePermission() {
		return 'edit';
	}
	protected function get_DeleteothersPermission() {
		return 'social-deleteothers';
	}
	protected function get_EditothersPermission() {
		return 'social-editothers';
	}
	protected function get_SourcePermission() {
		return 'social-source';
	}

	protected function get_AvailableAttachments() {
		return [];
	}

	protected function get_CanHaveAttachments() {
		return false;
	}

	protected function get_ExtendedSearchResultFormatter() {
		return EntityFormatter::class;
	}

	protected function get_ExtendedSearchListable() {
		return false;
	}

	protected function get_NotificationObjectClass() {
		return \BlueSpice\Social\Notifications\SocialNotification::class;
	}

	protected function get_HasNotifications() {
		return true;
	}

	protected function get_NotificationTypePrefix() {
		return 'bs-social-entity';
	}

	protected function get_EntityListOutputType() {
		return 'Default';
	}

	protected function get_EntityListTypeAllowed() {
		return true;
	}

	protected function get_EntityListTypeSelected() {
		return true;
	}

	protected function get_EntityListChildrenOutputType() {
		return 'Default';
	}

	protected function get_EntityListPreloadTitle() {
		return '';
	}

	protected function get_EntityListInitiallyHiddenChildrenDefault() {
		return true;
	}

	protected function get_EntityListInitiallyHiddenChildrenShort() {
		return true;
	}

	protected function get_EntityListInitiallyHiddenChildrenList() {
		return true;
	}

	protected function get_EntityListInitiallyHiddenChildrenPage() {
		return false;
	}

	protected function get_EntityListTypeChildrenAllowed() {
		return false;
	}

	protected function get_EntityListSpecialTimelineTypeSelected() {
		return false;
	}

	protected function get_EntityListPrivacyHandlerTypeAllowed() {
		return true;
	}
}
