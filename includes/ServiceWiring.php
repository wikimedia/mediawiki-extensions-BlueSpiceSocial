<?php

use MediaWiki\MediaWikiServices;

return [

	'BSSocialEntityListFactory' => static function ( MediaWikiServices $services ) {
		return new \BlueSpice\Social\EntityListFactory(
			$services->getService( 'BSRendererFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

];
