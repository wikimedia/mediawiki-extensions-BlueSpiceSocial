<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\Tag\MarkerType\NoWiki;
use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Renderer\Entity as EntityRenderer;

class SocialEntity extends \BlueSpice\Tag\Tag {

	public function needsDisabledParserCache() {
		return true;
	}

	public function getContainerElementName() {
		return 'div';
	}

	public function needsParsedInput() {
		return false;
	}

	public function needsParseArgs() {
		return true;
	}

	public function getMarkerType() {
		return new NoWiki();
	}

	public function getInputDefinition() {
		return null;
	}

	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::INTEGER,
				Entity::ATTR_ID,
				0
			),
			new ParamDefinition(
				ParamType::STRING,
				EntityRenderer::RENDER_TYPE,
				EntityRenderer::RENDER_TYPE_SHORT
			),
		];
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		return new SocialEntityHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	public function getTagNames() {
		return [
			'bs:socialentity',
		];
	}

}
