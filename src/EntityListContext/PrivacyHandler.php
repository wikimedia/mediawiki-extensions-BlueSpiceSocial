<?php

namespace BlueSpice\Social\EntityListContext;

class PrivacyHandler extends \BlueSpice\Social\EntityListContext {
	const CONFIG_NAME_TYPE_ALLOWED = 'EntityListPrivacyHandlerTypeAllowed';
	const CONFIG_NAME_TYPE_SELECTED = 'EntityListPrivacyHandlerTypeSelected';

	public function getLimit() {
		return -1;
	}
}
