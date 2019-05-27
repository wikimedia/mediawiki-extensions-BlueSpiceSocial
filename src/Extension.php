<?php
/**
 * BlueSpiceSocial base extension for BlueSpice
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
use BlueSpice\Social\ResourceCollector;
use BlueSpice\Services;

class Extension extends \BlueSpice\Extension {

	public static function onRegistration() {
		global $wgExtraNamespaces, $wgContentNamespaces,
			$wgNamespacesWithSubpages, $wgNamespacesToBeSearchedDefault,
			$wgContentHandlers, $wgNamespaceContentModels, $bsgSystemNamespaces;

		if( !defined( 'NS_SOCIALENTITY' ) ) {
			define( "NS_SOCIALENTITY", 1506 );
			$wgExtraNamespaces[NS_SOCIALENTITY] = 'SocialEntity';
			$wgNamespacesWithSubpages[NS_SOCIALENTITY] = false;
			$wgNamespacesToBeSearchedDefault[NS_SOCIALENTITY] = false;
			$bsgSystemNamespaces[1506] = 'NS_SOCIALENTITY';
		}
		if( !defined( 'NS_SOCIALENTITY_TALK' ) ) {
			define( 'NS_SOCIALENTITY_TALK', 1507 );
			$wgExtraNamespaces[NS_SOCIALENTITY_TALK] = 'SocialEntity_talk';
			$bsgSystemNamespaces[1507] = 'NS_SOCIALENTITY_TALK';
		}
		if( !defined( 'CONTENT_MODEL_BSSOCIAL' ) ) {
			define( 'CONTENT_MODEL_BSSOCIAL', 'BSSocial' );
			$wgContentHandlers[CONTENT_MODEL_BSSOCIAL]
				= 'BlueSpice\\Social\\Content\\EntityHandler';
			$wgNamespaceContentModels[NS_SOCIALENTITY] = CONTENT_MODEL_BSSOCIAL;
		}

		// Add social namespaces to "noindex" list for ExtendedSearch
		if ( isset( $GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'] ) ) {
			$GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'] = array_merge(
				$GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'],
				[ 1506, 1507 ]
			);
		} else {
			$GLOBALS['bsgESSourceConfig']['wikipage']['skip_namespaces'] = [
				1506, 1507
			];
		}

	}

	/**
	 * @deprecated since version 3.0.0 - use Services::getInstance()
	 * ->getBSUtilityFactory()->getMaintenanceUser()->getUser() instead
	 * @return \User
	 */
	public static function getMaintenanceUser() {
		wfDeprecated( __METHOD__, '3.0.0' );
		return Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->getUser();
	}

	public static function getDefaultRelatedTitle() {
		//TODO: make changeable!
		return \Title::newMainPage();
	}

	/**
	 * Embeds CSS into pdf export
	 * @param array $aTemplate
	 * @param array $aStyleBlocks
	 * @return boolean Always true to keep hook running
	 */
	public static function onBSUEModulePDFBeforeAddingStyleBlocks( &$aTemplate, &$aStyleBlocks ) {
		$oCollector = ResourceCollector::getMain();
		$aStyleBlocks[ 'SocialEntity' ] = $oCollector->getCombinedStylesFile();
		$aStyleBlocks[ 'SocialEntity' ] .= <<<HEREDOC
.bs-social-entity-aftercontent, .bs-social-entity-actions,
.bs-social-entity-content-more, .bs-social-entitylist-menu {
	display: none !important;
}
.bs-social-entity-userimage {
	float: left;
	position: absolute;
}
HEREDOC;
		return true;
	}

	/**
	 * Removes watchlist notifications for entities
	 * @param User $watchingUser
	 * @param Title $title
	 * @param UserMailer $userMailer
	 * @param type $this
	 */
	public static function onSendWatchlistEmailNotification( $watchingUser, $title, $userMailer ) {
		if( !$title || $title->getNamespace() !== NS_SOCIALENTITY ) {
			return true;
		}
		$entity = Services::getInstance()->getBSEntityFactory()
			->newFromSourceTitle( $title );
		if( !$entity || !$entity->exists() ) {
			return true;
		}

		return false;
	}

}
