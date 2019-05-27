<?php

namespace BlueSpice\Social;

use IContextSource;

interface IEntityListContext extends IContextSource {
	public function getLimit();
	public function getStart();
	public function getSort();
	public function getFilters();
	public function getAvailableFilterFields();
	public function getAvailableSorterFields();
	public function getAllowedTypes();
	public function showEntityListMenu();
	public function showEntityListMore();
	public function showEntitySpawner();
	public function showHeadline();
	public function getHeadlineMessageKey();
	public function useEndlessScroll();
	public function getOutputTypes();
	public function getPreloadTitles();
	public function getLockedOptionNames();
	public function getLockedFilterNames();
	public function getSchema();
	public function getParent();
	public function getPreloadedEntities();
	public function getRendererName();
}
