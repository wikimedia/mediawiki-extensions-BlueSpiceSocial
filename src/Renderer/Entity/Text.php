<?php

namespace BlueSpice\Social\Renderer\Entity;

use Config;
use IContextSource;
use Title;
use MediaWiki\Linker\LinkRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Utility\CacheHelper;
use BlueSpice\Social\Entity\Text as EntityText;
use BlueSpice\Social\EntityAttachment;

class Text extends \BlueSpice\Social\Renderer\Entity {

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper $cacheHelper
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$this->args['content'] = '';
		$this->args['attachments'] = $this->getEntity()->get(
			EntityText::ATTR_ATTACHMENTS
		);
	}

	protected function render_attachments( $mVal, $sType = 'Default' ) {
		$sOut = '';
		if( empty( $mVal ) || !is_array( $mVal ) ) {
			return $sOut;
		}
		$availableAttachments = $this->getEntity()->getConfig()->get(
			'AvailableAttachments'
		);
		foreach( $mVal as $sType => $aAttachments ) {
			if( !in_array( $sType, $availableAttachments ) ) {
				continue;
			}
			if( $sType === 'images' ) {
				foreach( $aAttachments as $sImage ) {
					if( !$oTitle = Title::makeTitle( NS_FILE, $sImage ) ) {
						continue;
					}

					if( !$oFile = wfFindFile( $oTitle ) ) {
						continue;
					}

					$sType = 'image';
					if( strpos( $oFile->getMimeType(), 'image' ) === false ) {
						$sType = 'file';
					}
					$entityAttachment = EntityAttachment::factory(
						$this->getEntity(),
						$oFile,
						$sType
					);
					$sOut .= $entityAttachment->render();
				}
			}
			if( $sType === 'links' ) {
				foreach( $aAttachments as $link ) {
					if( !$title = Title::newFromText( $link ) ) {
						continue;
					}

					$entityAttachment = EntityAttachment::factory(
						$this->getEntity(),
						$title,
						'link'					);
					$sOut .= $entityAttachment->render();
				}
			}
		}
		return $sOut;
	}

	protected function render_content( $val ) {
		return $this->getEntity()->get(
			EntityText::ATTR_PARSED_TEXT,
			''
		);
	}
}