<?php
/**
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */
namespace BlueSpice\Social;
/**
 * This view renders the a single item.
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class EntityAttachment {
	protected static $aAttachments = [
		'default' => "\\BlueSpice\\Social\\EntityAttachment",
		'file' => "\\BlueSpice\\Social\\EntityAttachment\\File",
		'image' => "\\BlueSpice\\Social\\EntityAttachment\\Image",
		'link' => "\\BlueSpice\\Social\\EntityAttachment\\Link",
		//'entity' TODO!
	];
	protected static $bAttachmentsRegistered = false;

	protected $oEntity = null;
	protected $mAttachment = null;
	protected $aArgs = [];
	protected $sType = 'default';

	/**
	 * @param \Entity $oEntity
	 * @param mixed $mAttachment
	 * @param type $sType
	 * @return \EntityAttachment
	 */
	public static function factory( Entity $oEntity, $mAttachment = null, $sType = 'default' ) {
		if( empty( $mAttachment ) ) {
			return null;
		}
		$aRegisteredAttachments = self::getRegisteredAttachments();
		if( !isset( $aRegisteredAttachments[$sType] ) ) {
			return null;
		}

		$oInstance = new $aRegisteredAttachments[$sType](
			$oEntity,
			$mAttachment
		);
		return $oInstance;
	}

	protected static function runRegister( $bForceReload = false ) {
		if( static::$bAttachmentsRegistered && !$bForceReload ) {
			return true;
		}

		$b = \Hooks::run( 'BSEntityAttachmentsRegister', array(
			&self::$aAttachments,
		));

		return $b ? static::$bAttachmentsRegistered = true : $b;
	}

	/**
	 * Returns all registered entities ( type => EntityConfigClass )
	 * @return array
	 */
	public static function getRegisteredAttachments() {
		if( !self::runRegister() ) {
			return array();
		}
		return self::$aAttachments;
	}

	/**
	 * Constructor
	 */
	protected function __construct( Entity $oEntity, $mAttachment ) {
		$this->oEntity = $oEntity;
		$this->mAttachment = $mAttachment;
		$this->aArgs['type'] = $this->sType;
		$this->aArgs['content'] = "&nbsp;";
	}

	/**
	 * @return string
	 */
	public function render() {
		if( !$this->mAttachment ) {
			return '';
		}
		$sOutput = \BSTemplateHelper::process(
			$this->getTemplateName(),
			$this->getArgs()
		);

		return $sOutput;
	}

	public function getTemplateName() {
		return 'BlueSpiceSocial.Entity.attachment.Default';
	}

	/**
	 * Default given attachment data is an array with params
	 * Overwrite this in your Attachment class
	 * @return array
	 */
	public function getArgs() {
		if( !$this->mAttachment ) {
			return [];
		}
		$this->aArgs = $this->mAttachment;
		return $this->aArgs;
	}

	/**
	 * @return Entity
	 */
	public function getEntity() {
		return $this->oEntity;
	}

	/**
	 * @return mixed
	 */
	public function getAttachment() {
		return $this->mAttachment;
	}
}