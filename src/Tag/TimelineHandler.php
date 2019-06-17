<?php

namespace BlueSpice\Social\Tag;

use Parser;
use PPFrame;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Services;
use BlueSpice\Renderer\Params;
use BlueSpice\Tag\Handler;

class TimelineHandler extends Handler {
	/**
	 *
	 * @var EntityListContext
	 */
	protected $context = null;

	/**
	 *
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param EntityListContext $context
	 */
	public function __construct( $processedInput, array $processedArgs, Parser $parser,
		PPFrame $frame, EntityListContext $context ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->context = $context;
	}

	/**
	 *
	 * @return string
	 */
	public function handle() {
		$params = array_merge(
			$this->processedArgs,
			[ 'context' => $this->context ]
		);
		$renderer = Services::getInstance()->getBSRendererFactory()->get(
			'entitylist',
			new Params( $params )
		);
		return $renderer->render();
	}
}
