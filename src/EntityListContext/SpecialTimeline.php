<?php

namespace BlueSpice\Social\EntityListContext;

use BlueSpice\Social\Entity;

class SpecialTimeline extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListSpecialTimelineTypeSelected';

	public function getLimit() {
		return 20;
	}

	protected function getSortProperty() {
		return Entity::ATTR_TIMESTAMP_CREATED;
	}

	public function getPersistSettings() {
		return true;
	}
}
