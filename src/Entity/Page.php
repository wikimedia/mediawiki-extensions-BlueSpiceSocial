<?php

/**
 * Page class for BlueSpiceSocial
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Entity;

use Status;
use User;
use BlueSpice\Social\Entity;
use BlueSpice\Services;

/**
 * Page class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class Page extends Entity {
	const ATTR_DESCRIPTION = 'description';

	/**
	 * Gets the Page attributes formated for the api
	 * @param array $a
	 * @return object
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_DESCRIPTION => $this->get(
					static::ATTR_DESCRIPTION,
					''
				),
			]
		) );
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( isset( $o->{static::ATTR_DESCRIPTION} ) ) {
			$this->set(
				static::ATTR_DESCRIPTION,
				$o->{static::ATTR_DESCRIPTION}
			);
		}
		parent::setValuesByObject( $o );
	}

	/**
	 * Returns the description
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return string
	 */
	public function getDescription() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_DESCRIPTION, '' );
	}

	/**
	 * Sets the description
	 * @deprecated since version 3.0.0 - use set( $attrName, $variable ) instead
	 * @param string
	 * @return Page
	 */
	public function setDescription( $sDescription ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		$this->set( static::ATTR_DESCRIPTION, $sDescription );
	}

	/**
	 *
	 * @param User|null $user
	 * @param array $options
	 * @return Status
	 */
	public function save( User $user = null, $options = [] ) {
		// always use the maintenance user for page entities to prevent
		// unrealistic edit statistics for users
		$user = Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->getUser();
		return parent::save( $user, $options );
	}
}
