<?php

namespace BlueSpice\Social\Task\EntityList;

use Status;
use BlueSpice\Task;
use BlueSpice\ParamProcessor\ParamDefinition;

class Reload extends Task {

	/**
	 * @return Status
	 */
	protected function doExecute() {
		return Status::newGood();
	}

	/**
	 * @return ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [];
	}

}
