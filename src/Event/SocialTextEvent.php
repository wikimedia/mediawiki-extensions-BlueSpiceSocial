<?php

namespace BlueSpice\Social\Event;

use BlueSpice\Social\Entity\Text as TextEntity;

class SocialTextEvent extends SocialEvent {

	/**
	 * @return string
	 */
	protected function getEntityText(): string {
		return $this->entity->get( TextEntity::ATTR_PARSED_TEXT );
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'bs-social-text-event';
	}
}
