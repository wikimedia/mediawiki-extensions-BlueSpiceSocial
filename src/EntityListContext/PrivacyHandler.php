<?php

namespace BlueSpice\Social\EntityListContext;

class PrivacyHandler extends \BlueSpice\Social\EntityListContext {
	public const CONFIG_NAME_TYPE_ALLOWED = 'EntityListPrivacyHandlerTypeAllowed';
	public const CONFIG_NAME_TYPE_SELECTED = 'EntityListPrivacyHandlerTypeSelected';

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return -1;
	}
}
