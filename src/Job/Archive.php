<?php
namespace BlueSpice\Social\Job;

class Archive extends \BlueSpice\Social\Job {
	public const JOBCOMMAND = 'socialentityarchive';

	public function run() {
		$oEntity = $this->getEntity();
		$oStatus = $oEntity->delete();
	}
}
