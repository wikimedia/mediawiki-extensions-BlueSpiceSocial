<?php

namespace BlueSpice\Social\Data\Entity;

use \BlueSpice\Data\ISecondaryDataProvider;

class SecondaryDataProvider implements ISecondaryDataProvider {

	/**
	 *
	 * @var \MediaWiki\Linker\LinkRenderer
	 */
	protected $linkrenderer = null;

	/**
	 *
	 * @param \MediaWiki\Linker\LinkRenderer $linkrenderer
	 */
	public function __construct( $linkrenderer ) {
		$this->linkrenderer = $linkrenderer;
	}

	public function extend( $dataSets ){
		foreach( $dataSets as &$dataSet ) {

		}

		return $dataSets;
	}
}