<?php
/**
 * ResourceLoader class for BlueSpiceSocialMessages resource module for BlueSpice
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
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
namespace BlueSpice\Social\ResourceLoader;
use BlueSpice\Social\EntityConfig;
use BlueSpice\EntityRegistry;

class Messages extends \ResourceLoaderModule {
	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 *
	 * @return array List of message keys. Keys may occur more than once
	 */
	public function getMessages() {
		$aMessages = $aVarMsgKeys = [];
		foreach( EntityRegistry::getRegisterdTypeKeys() as $sType ) {
			if( !$oConfig = EntityConfig::factory( $sType ) ) {
				continue;
			}
			if( !$oConfig instanceof EntityConfig ) {
				continue;
			}
			if( !empty( $oConfig->get( 'HeaderMessageKey' ) ) ) {
				$aMessages[] = $oConfig->get( 'HeaderMessageKey' );
			}
			if( !empty( $oConfig->get( 'HeaderMessageKeyCreateNew' ) ) ) {
				$aMessages[] = $oConfig->get( 'HeaderMessageKeyCreateNew' );
			}
			if( !empty( $oConfig->get( 'TypeMessageKey' ) ) ) {
				$aMessages[] = $oConfig->get( 'TypeMessageKey' );
			}
			if( empty( $oConfig->get( 'VarMessageKeys' ) ) ) {
				continue;
			}
			$aVarMsgKeys = array_merge(
				$aVarMsgKeys,
				$oConfig->get( 'VarMessageKeys' )
			);
		}
		foreach( $aVarMsgKeys as $sVarName => $sMsgKey ) {
			if( empty( $sMsgKey ) ) {
				continue;
			}
			$aMessages[] = $sMsgKey;
		}
		return $aMessages;
	}

	/**
	 * Get target(s) for the module, eg ['desktop'] or ['desktop', 'mobile']
	 *
	 * @return array Array of strings
	 */
	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}
}