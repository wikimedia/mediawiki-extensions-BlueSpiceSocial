<?php

namespace BlueSpice\Social\EntityListContext;

class SpecialActivities extends \BlueSpice\Social\EntityListContext {
	public const CONFIG_NAME_OUTPUT_TYPE = 'EntityListSpecialActivitiesOutputType';
	public const CONFIG_NAME_TYPE_ALLOWED = 'EntityListSpecialActivitiesTypeAllowed';
	public const CONFIG_NAME_TYPE_SELECTED = 'EntityListSpecialActivitiesTypeSelected';

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 20;
	}
}
