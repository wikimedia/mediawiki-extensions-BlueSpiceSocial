<?php
/**
 * EntitiesResult class for BlueSpiceSocial
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
 */
namespace BlueSpice\Social;
/**
 * EntitiesResult class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class Result extends \Status {
	protected $aFilter = array();
	protected $sQuery = '';
	protected $aOptions = array();

	static function newFatal( $message /*, parameters...*/ ) {
		$params = func_get_args();
		$result = new self;
		call_user_func_array( array( &$result, 'error' ), $params );
		$result->ok = false;
		return $result;
	}

	/**
	 * Factory function for good results
	 *
	 * @param mixed $value
	 * @return Status
	 */
	static function newGood( $value = null ) {
		$result = new self;
		$result->value = $value;
		return $result;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	public function setValue( $mValue ) {
		$this->value = $mValue;
		return $this;
	}

	public function getFilter() {
		return $this->aFilter;
	}

	public function getOptions() {
		return $this->aOptions;
	}

	public function getQuery() {
		return $this->sQuery;
	}

	public function setQuery( $sQuery ) {
		$this->sQuery = $sQuery;
		return $this;
	}

	public function setFilter( $aFilter ) {
		$this->aFilter = $aFilter;
		return $this;
	}

	public function setOptions( $aOptions ) {
		$this->aOptions = $aOptions;
		return $this;
	}
}