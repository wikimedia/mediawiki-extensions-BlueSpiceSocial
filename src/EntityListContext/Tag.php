<?php

namespace BlueSpice\Social\EntityListContext;

class Tag extends \BlueSpice\Social\EntityListContext {

	public function getLimit() {
		return 5;
	}

	public function useEndlessScroll() {
		return false;
	}
}
