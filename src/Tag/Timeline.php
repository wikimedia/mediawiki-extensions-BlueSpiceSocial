<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\Services;
use BlueSpice\Tag\MarkerType\NoWiki;

class Timeline extends \BlueSpice\Tag\Tag {

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
		return [];
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		$cfg = \FormatJson::decode( $processedInput );

		if( !empty( $cfg ) ) {
			$processedArgs = array_merge( $processedArgs, (array) $cfg );
		} elseif ( !empty( $processedInput ) ) {
			//TODO: make generic TagError handler!
			throw new \MWException( 'Invalid JSON' );
		}
		
		$context = new \BlueSpice\Social\EntityListContext\Tag(
			\RequestContext::getMain(),
			Services::getInstance()->getConfigFactory()->makeConfig( 'bsg' )
		);

		return new TimelineHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame,
			$context
		);
	}

	public function getTagNames() {
		return [
			'bs:timeline',
			'bs:activitystream',
			'timeline'
		];
	}

}
