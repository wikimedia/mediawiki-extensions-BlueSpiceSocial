<?php

/**
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
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\EntityConfig;

use BlueSpice\Social\Data\Entity\Schema;
use BlueSpice\Social\Entity\Page as Entity;
use BlueSpice\Social\EntityConfig;
use BlueSpice\Social\ExtendedSearch\Formatter\Internal\PageFormatter;
use MWStake\MediaWiki\Component\DataStore\FieldType;

/**
 *  class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class Page extends EntityConfig {

	/**
	 *
	 * @return bool
	 */
	protected function get_IsSpawnable() {
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function get_Renderer() {
		return 'socialentitypage';
	}

	/**
	 *
	 * @return array
	 */
	protected function get_AttributeDefinitions() {
		return array_merge(
			parent::get_AttributeDefinitions(),
			[
				Entity::ATTR_DESCRIPTION => [
					Schema::FILTERABLE => true,
					Schema::SORTABLE => true,
					Schema::TYPE => FieldType::TEXT,
					Schema::INDEXABLE => true,
					Schema::STORABLE => true,
				],
			]
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ExtendedSearchResultFormatter() {
		return PageFormatter::class;
	}

	/**
	 *
	 * @return bool
	 */
	protected function get_HasNotifications() {
		return false;
	}
}
