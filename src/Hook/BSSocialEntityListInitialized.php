<?php
/**
 * Hook handler base class for BlueSpice hook BSSocialEntityListInitialized
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\Hook;

use BlueSpice\Social\Renderer\EntityList;
use BlueSpice\Renderer\Params;

abstract class BSSocialEntityListInitialized extends \BlueSpice\Hook {

	/**
	 * Instance of the entity list
	 * @var EntityList
	 */
	protected $entityList = null;

	/**
	 * Arguments the entity list was initialized with
	 * @var array
	 */
	protected $args = null;

	/**
	 * Params, the entity list was constructed with
	 * @var Params
	 */
	protected $params = null;

	/**
	 * Located in \BlueSpice\Social\Renderer\EntityList::initializeArgs. After
	 * the entity list processed the given params and finished initializing.
	 * @param EntityList $entityList
	 * @param array &$args
	 * @param Params $params
	 * @return bool
	 */
	public static function callback( $entityList, &$args, $params ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$entityList,
			$args,
			$params
		);
		return $hookHandler->process();
	}

	/**
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param EntityList $entityList
	 * @param array &$args
	 * @param Params $params
	 */
	public function __construct( $context, $config, $entityList, &$args, $params ) {
		parent::__construct( $context, $config );

		$this->entityList = $entityList;
		$this->args = &$args;
		$this->params = $params;
	}
}
