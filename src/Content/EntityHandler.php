<?php

namespace BlueSpice\Social\Content;

use BlueSpice\Content\EntityHandler as EntityHanderBase;
use BlueSpice\Social\Entity;
use Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\Transform\PreSaveTransformParams;
use MediaWiki\MediaWikiServices;
use ParserOutput;
use Title;

class EntityHandler extends EntityHanderBase {

	/**
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_BSSOCIAL ) {
		parent::__construct( $modelId );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return "BlueSpice\\Social\\Content\\Entity";
	}

	/**
	 * @param Content $content
	 * @param ContentParseParams $cpoParams
	 * @param ParserOutput &$output The output object to fill (reference).
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output
	) {
		$dbKey = $cpoParams->getPage()->getDBkey();
		$title = Title::newFromDBkey( $dbKey );

		$oEntity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $title );
		if ( !$oEntity instanceof Entity ) {
			return;
		}
		$output->setDisplayTitle( strip_tags(
			$oEntity->getHeader()->parse()
		) );
		if ( $cpoParams->getGenerateHtml() ) {
			$output->setText( $oEntity->getRenderer()->render( 'Page' ) );
			$output->addModuleStyles( [ 'mediawiki.content.json' ] );
		} else {
			$output->setText( $oEntity->getRenderer()->render( 'Page' ) );
		}
	}

	/**
	 * Beautifies JSON prior to save.
	 *
	 * @param Content $content
	 * @param PreSaveTransformParams $pstParams
	 * @return JsonContent
	 */
	public function preSaveTransform(
		Content $content,
		PreSaveTransformParams $pstParams
	): Content {
		$contentClass = $this->getContentClass();
		return new $contentClass( $content->beautifyJSON() );
	}
}
