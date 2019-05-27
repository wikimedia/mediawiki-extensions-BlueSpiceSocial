<?php

namespace BlueSpice\Social\Tag;

use BlueSpice\Services;
use BlueSpice\Tag\Handler;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Renderer\Entity as EntityRenderer;

class SocialEntityHandler extends Handler {
	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	public function __construct( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		$this->entity = Services::getInstance()->getBSEntityFactory()->newFromID(
			$processedArgs[Entity::ATTR_ID],
			NS_SOCIALENTITY
		);
		if( !$this->entity instanceof Entity ) {
			new \MWException(
				"Non existent or invalid entity for '". Entity::ATTR_ID . "'"
			);
		}

	}

	public function handle() {
		if( !$this->entity->userCan( 'read', $this->parser->getUser() ) ) {
			return "";
		}
		return $this->entity->getRenderer()->render(
			$this->processedArgs[EntityRenderer::RENDER_TYPE]
		);
	}
}