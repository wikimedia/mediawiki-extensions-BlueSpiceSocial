<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\Social\EntityListContext;
use BlueSpice\Services;
use BlueSpice\Renderer\Params;
use BlueSpice\Tag\Handler;
use BlueSpice\Tag\Output;

class TimelineHandler extends Handler {
	/**
	 *
	 * @var EntityListContext
	 */
	protected $context = null;

	public function __construct( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame, EntityListContext $context ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->context = $context;
	}

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