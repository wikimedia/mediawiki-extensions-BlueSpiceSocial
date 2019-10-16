<?php

/**
 * Title class for BlueSpiceSocial
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
namespace BlueSpice\Social\Entity;

use Message;
use Status;
use User;
use Title;
use BsNamespaceHelper;

/**
 * Title class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class ActionTitle extends Action {
	const ATTR_NAMESPACE = 'namespace';
	const ATTR_TITLE_TEXT = 'titletext';

	/**
	 * @deprecated since version 2.27.2
	 * @var mixed
	 */
	protected $vActionRef = null;
	/**
	 * @deprecated since version 2.27.2
	 * @var string
	 */
	protected $sText = '';

	/**
	 *
	 * @param Message|null $oMsg
	 * @return Message
	 */
	public function getHeader( $oMsg = null ) {
		return parent::getHeader( $oMsg )->params(
			$this->getRelatedTitle()->getPrefixedText(),
			$this->get( static::ATTR_TITLE_TEXT, '' ),
			$this->get( static::ATTR_NAMESPACE, 0 ),
			BsNamespaceHelper::getNamespaceName(
				$this->get( static::ATTR_NAMESPACE, 0 )
			)
		);
	}

	/**
	 * Returns the titletext attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return string
	 */
	public function getTitleText() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_TITLE_TEXT, '' );
	}

	/**
	 * Returns the namespace attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return integer
	 */
	public function getNamespace() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_NAMESPACE, 0 );
	}

	/**
	 * @deprecated since version 2.27.2
	 * @return mixed
	 */
	public function getActionRef() {
		return $this->vActionRef;
	}

	/**
	 *
	 * @param bool $bForceInvalidateFirst
	 * @return string
	 */
	public function getParsedText( $bForceInvalidateFirst = false ) {
		// Deprecated!!!!
		if ( !empty( $this->getActionRef() ) ) {
			// Make sure, the action text content does not get parsed
			// (possible tag injection)!
			$sText = strip_tags( $this->sText );
			return "<nowiki>$sText</nowiki>";
		}
		return parent::getParsedText( $bForceInvalidateFirst );
	}

	/**
	 * @deprecated since version 2.27.2
	 */
	public function setActionRef( $vActionRef ) {
		$this->vActionRef = $vActionRef;
		return $this->setUnsavedChanges();
	}

	/**
	 * Sets the titletext attribute
	 * @deprecated since version 3.0.0 - use set( $attrName, $value ) instead
	 * @return ActionTitle
	 */
	public function setTitleText( $sTitleText ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_TITLE_TEXT, $sTitleText );
	}

	/**
	 * Sets the namespace attribute
	 * @deprecated since version 3.0.0 - use set( $attrName, $value ) instead
	 * @return ActionTitle
	 */
	public function setNamespace( $iNamespace ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_NAMESPACE, $iNamespace );
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
				static::ATTR_NAMESPACE => $this->get(
					static::ATTR_NAMESPACE,
					0
				),
				static::ATTR_TITLE_TEXT => $this->get(
					static::ATTR_TITLE_TEXT,
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
		if ( isset( $o->{static::ATTR_NAMESPACE} ) ) {
			$this->set(
				static::ATTR_NAMESPACE,
				$o->{static::ATTR_NAMESPACE}
			);
		}
		if ( isset( $o->{static::ATTR_TITLE_TEXT} ) ) {
			$this->set(
				static::ATTR_TITLE_TEXT,
				$o->{static::ATTR_TITLE_TEXT}
			);
		}

		// Deprecated!
		if ( isset( $o->actionref ) ) {
			$this->setActionRef( $o->actionref );
		}
		// Deprecated
		if ( isset( $o->text ) ) {
			$this->sText = $o->text;
		}
		parent::setValuesByObject( $o );
	}

	/**
	 *
	 * @return Title
	 */
	public function getRelatedTitle() {
		if ( $this->relatedTitle ) {
			return $this->relatedTitle;
		}
		if ( !empty( $this->getActionRef() ) ) {
			$this->relatedTitle = Title::newFromText( $this->getActionRef() );
			return $this->relatedTitle;
		}

		$this->relatedTitle = Title::makeTitle(
			$this->get( static::ATTR_NAMESPACE, 0 ),
			$this->get( static::ATTR_TITLE_TEXT, '' )
		);
		if ( !$this->relatedTitle ) {
			return parent::getRelatedTitle();
		}
		return $this->relatedTitle;
	}

	/**
	 *
	 * @param User|null $oUser
	 * @param array $aOptions
	 * @return Status
	 */
	public function save( User $oUser = null, $aOptions = [] ) {
		if ( empty( $this->get( static::ATTR_TITLE_TEXT, '' ) ) ) {
			return Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_TITLE_TEXT )->plain()
			) );
		}
		return parent::save( $oUser, $aOptions );
	}
}
