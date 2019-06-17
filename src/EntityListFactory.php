<?php

namespace BlueSpice\Social;

use Config;
use BlueSpice\RendererFactory;
use BlueSpice\IRenderer;
use BlueSpice\Renderer\Params;

class EntityListFactory {
	/**
	 *
	 * @var RendererFactory
	 */
	protected $rendererFactory = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param RendererFactory $rendererFactory
	 * @param Config $config
	 */
	public function __construct( RendererFactory $rendererFactory, Config $config ) {
		$this->rendererFactory = $rendererFactory;
		$this->config = $config;
	}

	/**
	 * @param Params $params
	 * @param IEntityListContext|null $context | null
	 * @return IRenderer
	 */
	public function newFromEntityListContext( Params $params, IEntityListContext $context = null ) {
		if ( !$context ) {
			$context = new EntityListContext( $context, $context->getConfig() );
		}
		return $this->rendererFactory->get(
			$context->getRendererName(),
			$params,
			$context
		);
	}
}
