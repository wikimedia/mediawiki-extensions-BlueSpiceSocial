<?php
/**
 * ResourceCollector class for BlueSpiceSocial
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
 */
namespace BlueSpice\Social;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Services;

/**
 * ResourceCollector class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class ResourceCollector {
	protected static $oInstance = null;
	protected static $oContext = null;

	protected $oResourceLoaderContext = null;

	protected $aConfig = [];
	protected $aScripts = [];
	protected $aStyles = [];
	protected $aVarMsgKeys = [];

	/**
	 * @param \RequestContext|null $oContext
	 * @return ResourceCollector or null, when there is no
	 * valid request context given / main request (in cmd f.e)
	 */
	public static function getMain( \RequestContext $oContext = null ) {
		if ( static::$oInstance ) {
			return static::$oInstance;
		}
		static::$oContext = $oContext;
		if ( !static::$oContext ) {
			static::$oContext = \RequestContext::getMain();
		}
		if ( !static::$oContext ) {
			return null;
		}
		static::$oInstance = new static;
		return static::$oInstance;
	}

	protected function __construct() {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		$configFactory = Services::getInstance()->getBSEntityConfigFactory();
		foreach ( $registry->getAllKeys() as $sType ) {
			$oConfig = $configFactory->newFromType( $sType );
			if ( !$oConfig ) {
				continue;
			}
			if ( !$oConfig instanceof EntityConfig ) {
				continue;
			}
			$this->aConfig[$sType] = $oConfig->jsonSerialize();
			$moduleStyles = $oConfig->get( 'ModuleStyles' );
			if ( $moduleStyles ) {
				$this->aStyles = array_merge( $this->aStyles, $moduleStyles );
			}
			$moduleScripts = $oConfig->get( 'ModuleScripts' );
			if ( $moduleScripts ) {
				$this->aScripts = array_merge( $this->aScripts, $moduleScripts );
			}
			// isLoaded is not working correctly
			// also this modules can not be loaded within JS because stuff breaks
			$extensions = \ExtensionRegistry::getInstance()->getAllThings();
			if ( isset( $extensions[ 'MultimediaViewer' ] ) ) {
				$this->aScripts = array_merge(
					$this->aScripts,
					[ 'mmv.head', 'mmv.bootstrap.autostart' ]
				);
			}
			if ( empty( $oConfig->get( 'VarMessageKeys' ) ) ) {
				continue;
			}
			$this->aVarMsgKeys = array_merge(
				$this->aVarMsgKeys,
				$oConfig->get( 'VarMessageKeys' )
			);
		}

		\Hooks::run( 'BSSocialModuleDepths', [
			// deprecated
			$this->getContext()->getOutput(),
			// deprecated
			$this->getContext()->getSkin(),
			&$this->aConfig,
			&$this->aScripts,
			&$this->aStyles,
			&$this->aVarMsgKeys,
		] );
		// Make sure to have arrays in JS!
		$this->aScripts = array_values( array_unique( $this->aScripts ) );
		$this->aStyles = array_values( array_unique( $this->aStyles ) );
	}

	/**
	 * @return RequestContext
	 */
	public function getContext() {
		return static::$oContext;
	}

	/**
	 * @return array of serialized entitiy configs
	 */
	public function getConfig() {
		return $this->aConfig;
	}

	/**
	 * @return array of module scripts of all entities
	 */
	public function getModuleScripts() {
		return $this->aScripts;
	}

	/**
	 * @return array of module styles of all entities
	 */
	public function getModuleStyles() {
		return $this->aStyles;
	}

	/**
	 * @return array of message keys vor variable names
	 */
	public function getVarMessageKeys() {
		return $this->aVarMsgKeys;
	}

	/**
	 * @param string $sCss
	 * @return string
	 */
	public function getCombinedStylesFile( $sCss = '' ) {
		$oRLContext = $this->getResourceLoaderContext();
		foreach ( $this->getModuleStyles() as $sStyleMod ) {
			$oModule = \RequestContext::getMain()
				->getOutput()
				->getResourceLoader()
				->getModule( $sStyleMod );
			if ( !$oModule ) {
				wfDebugLog(
					'BSSocial',
					__CLASS__ . ":" . __METHOD__ . "invalid module: $sStyleMod"
				);
				continue;
			}
			$aStyle = $oModule->getStyles( $oRLContext );
			if ( empty( $aStyle['all'] ) ) {
				continue;
			}
			$sCss .= $aStyle['all'];
		}

		return $sCss;
	}

	/**
	 * @param string $sScript
	 * @return string
	 */
	public function getCombinedScriptsFile( $sScript = '' ) {
		$oRLContext = $this->getResourceLoaderContext();
		foreach ( $this->getModuleScripts() as $sScriptMod ) {
			$oModule = \RequestContext::getMain()
				->getOutput()
				->getResourceLoader()
				->getModule( $sScriptMod );
			$sScript .= $oModule->getScript( $oRLContext );
		}

		return $sScript;
	}

	/**
	 *
	 * @return \ResourceLoaderContext
	 */
	protected function getResourceLoaderContext() {
		if ( $this->oResourceLoaderContext ) {
			return $this->oResourceLoaderContext;
		}

		$aQuery = \ResourceLoader::makeLoaderQuery(
			[],
			$this->getContext()->getOutput()->getLanguage()->getCode(),
			$this->getContext()->getOutput()->getSkin()->getSkinName()
		);
		$this->oResourceLoaderContext = new \ResourceLoaderContext(
			$this->getContext()->getOutput()->getResourceLoader(),
			new \FauxRequest( $aQuery )
		);

		return $this->oResourceLoaderContext;
	}

}
