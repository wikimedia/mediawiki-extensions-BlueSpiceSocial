<?php

namespace BlueSpice\Social\Content;

use BlueSpice\Content\EntityHandler as EntityHanderBase;
use Content;
use MediaWiki\Content\Transform\PreSaveTransformParams;

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
