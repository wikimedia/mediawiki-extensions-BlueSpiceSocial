<?php

/**
 * WikiPage class for BlueSpiceSocial
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
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Entity;

use Status;
use User;

/**
 * WikiPage class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class ActionWikiPage extends ActionTitle {
	const ATTR_WIKI_PAGE_ID = 'wikipageid';
	const ATTR_REVISION_ID = 'revisionid';

	/**
	 * Returns the wikipageid attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return integer
	 */
	public function getWikiPageID() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_WIKI_PAGE_ID, 0 );
	}

	/**
	 * Returns the revisionid attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return integer
	 */
	public function getRevisionID() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_REVISION_ID, 0 );
	}

	/**
	 * Sets the wikipageid attribute
	 * @param integer $iWikiPageID
	 * @deprecated since version 3.0.0 - use set( $attrName, $variable ) instead
	 * @return ActionWikiPage
	 */
	public function setWikiPageID( $iWikiPageID ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_WIKI_PAGE_ID, $iWikiPageID );
	}

	/**
	 * Sets the revisionid attribute
	 * @param integer $iRevisionID
	 * @deprecated since version 3.0.0 - use set( $attrName, $variable ) instead
	 * @return ActionWikiPage
	 */
	public function setRevisionID( $iRevisionID ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_REVISION_ID, $iRevisionID );
	}

	/**
	 *
	 * @param array $a
	 * @return array
	 */
	public function getFullData( $a = [] ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_WIKI_PAGE_ID => $this->get(
					static::ATTR_WIKI_PAGE_ID,
					0
				),
				static::ATTR_REVISION_ID => $this->get(
					static::ATTR_REVISION_ID,
					0
				),
			]
		) );
	}

	/**
	 *
	 * @param string $attrName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $attrName, $default = null ) {
		if ( $attrName !== static::ATTR_SUMMARY ) {
			return parent::get( $attrName, $default );
		}
		if ( empty( $this->get( static::ATTR_REVISION_ID, 0 ) ) ) {
			return parent::get( $attrName, $default );
		}
		$revision = \Revision::newFromId( $this->get(
			static::ATTR_REVISION_ID,
			0
		) );
		if ( !$revision ) {
			return parent::get( $attrName, $default );
		}
		if ( !empty( $revision->getComment() ) ) {
			return $revision->getComment();
		}
		return wfMessage(
			'bs-socialactionsmw-autoeditsummaray'
		)->plain();
	}

	/**
	 *
	 * @param \stdClass $o
	 */
	public function setValuesByObject( \stdClass $o ) {
		if ( isset( $o->{static::ATTR_WIKI_PAGE_ID} ) ) {
			$this->set(
				static::ATTR_WIKI_PAGE_ID,
				$o->{static::ATTR_WIKI_PAGE_ID}
			);
		}
		if ( isset( $o->{static::ATTR_REVISION_ID} ) ) {
			$this->set(
				static::ATTR_REVISION_ID,
				$o->{static::ATTR_REVISION_ID}
			);
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @param User|null $oUser
	 * @param array $aOptions
	 * @return Status
	 */
	public function save( User $oUser = null, $aOptions = [] ) {
		if ( empty( $this->get( static::ATTR_WIKI_PAGE_ID, 0 ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_WIKI_PAGE_ID )->plain()
			) );
		}
		if ( empty( $this->get( static::ATTR_REVISION_ID, 0 ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_REVISION_ID )->plain()
			) );
		}
		return parent::save( $oUser, $aOptions );
	}
}
