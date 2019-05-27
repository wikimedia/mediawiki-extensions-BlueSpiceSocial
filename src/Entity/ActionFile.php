<?php

/**
 * File class for BlueSpiceSocial
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
namespace BlueSpice\Social\Entity;
/**
 * File class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
abstract class ActionFile extends ActionTitle {
	const ATTR_FILE_NAME = 'filename';
	const ATTR_FILE_TIMESTAMP = 'filetimestamp';

	protected $iNamespace = NS_FILE;

	/**
	 * Returns the filename attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return string
	 */
	public function getFileName() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_FILE_NAME, '' );
	}

	/**
	 * Returns the filetimestamp attribute
	 * @deprecated since version 3.0.0 - use get( $attrName, $default ) instead
	 * @return string
	 */
	public function getFileTimestamp() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->get( static::ATTR_FILE_TIMESTAMP, '' );
	}

	/**
	 * Sets the filename attribute
	 * @deprecated since version 3.0.0 - use set( $attrName, $value ) instead
	 * @param string $sFileName
	 * @return string
	 */
	public function setFileName( $sFileName ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_FILE_NAME, $sFileName );
	}

	public function get( $attrName, $default = null ) {
		//files must be in file namespace
		if( $attrName == static::ATTR_NAMESPACE ) {
			return NS_FILE;
		}
		return parent::get( $attrName, $default );
	}

	public function set( $attrName, $value ) {
		//files must be in file namespace
		if( $attrName == static::ATTR_NAMESPACE ) {
			return parent::set( $attrName, NS_FILE );
		}
		return parent::set( $attrName, $value );
	}

	public function getParsedText( $bForceInvalidateFirst = false ) {
		//Deprecated!!!!
		if( !empty( $this->getActionRef() ) ) {
			//Make sure, the action text content does not get parsed
			//(possible tag injection)!
			$sText = strip_tags( $this->sText );
			$sText = "<nowiki>$sText</nowiki>";
		} else {
			$sText = parent::getParsedText( $bForceInvalidateFirst );
		}
		$oFile = $this->getRelatedFile();
		if( !$oFile ) {
			return $sText;
		}
		$sText .= \Html::element( 'img', [
			'src' => $oFile->createThumb( 200 ),
			'title' => $oFile->getName(),
		]);
		return $sText;
	}

	public function setNamespace( $iNamespace ) {
		$this->iNamespace = NS_FILE;
		return $this->setUnsavedChanges();
	}

	/**
	 * Sets the filetimestamp attribute
	 * @deprecated since version 3.0.0 - use set( $attrName, $value ) instead
	 * @param string $sFileTimestamp
	 * @return string
	 */
	public function setFileTimestamp( $sFileTimestamp ) {
		wfDeprecated( __METHOD__, '3.0.0' );
		return $this->set( static::ATTR_FILE_TIMESTAMP, $sFileTimestamp );
	}

	public function getFullData( $a = array() ) {
		return parent::getFullData( array_merge(
			$a,
			[
				static::ATTR_FILE_NAME => $this->get(
					static::ATTR_FILE_NAME,
					''
				),
				static::ATTR_FILE_TIMESTAMP => $this->get(
					static::ATTR_FILE_TIMESTAMP,
					''
				),
			]
		));
	}

	public function setValuesByObject( \stdClass $o ) {
		if( isset( $o->{static::ATTR_FILE_NAME} ) ) {
			$this->set(
				static::ATTR_FILE_NAME,
				$o->{static::ATTR_FILE_NAME}
			);
		}
		if( isset( $o->{static::ATTR_FILE_TIMESTAMP} ) ) {
			$this->set(
				static::ATTR_FILE_TIMESTAMP,
				$o->{static::ATTR_FILE_TIMESTAMP}
			);
		}
		parent::setValuesByObject( $o );
	}

	public function getRelatedFile() {
		return wfFindFile( $this->getRelatedTitle(), [
			'time' => $this->get( static::ATTR_FILE_TIMESTAMP, '' )
		]);
	}

	public function save( \User $oUser = null, $aOptions = array() ) {
		if( empty( $this->get( static::ATTR_FILE_NAME, '' ) ) ) {
			return \Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_FILE_NAME )->plain()
			));
		}
		if( empty( $this->get( static::ATTR_FILE_TIMESTAMP, '' ) ) ) {
			return \Status::newFatal( wfMessage(
				'bs-social-entity-fatalstatus-save-emptyfield',
				$this->getVarMessage( static::ATTR_FILE_TIMESTAMP )->plain()
			));
		}
		return parent::save( $oUser, $aOptions );
	}
}