<?php

namespace BlueSpice\Social\Parser;

use MWTidy;

/**
 * WikiText class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class WikiText extends \Parser {
	/**
	 *
	 * @param string $text
	 * @param \Title $title
	 * @param \ParserOptions $options
	 * @param int $linestart
	 * @param bool $clearState
	 * @param int|null $revid
	 * @return string
	 */
	public function parse( $text, \Title $title, \ParserOptions $options, $linestart = true,
		$clearState = true, $revid = null
	) {
		$this->setTitle( $title );
		$this->mOptions = $options;
		$this->setOutputType( self::OT_HTML );
		if ( $clearState ) {
			$this->clearState();
		}

		$this->mInputSize = strlen( $text );
		if ( $this->mOptions->getEnableLimitReport() ) {
			$this->mOutput->resetParseStartTime();
		}

		# Remove the strip marker tag prefix from the input, if present.
		if ( $clearState ) {
			$text = strtr( $text, "\x7f", "?" );
		}

		$oldRevisionId = $this->mRevisionId;
		$oldRevisionObject = $this->mRevisionObject;
		$oldRevisionTimestamp = $this->mRevisionTimestamp;
		$oldRevisionUser = $this->mRevisionUser;
		$oldRevisionSize = $this->mRevisionSize;
		if ( $revid !== null ) {
			$this->mRevisionId = $revid;
			$this->mRevisionObject = null;
			$this->mRevisionTimestamp = null;
			$this->mRevisionUser = null;
			$this->mRevisionSize = null;
		}

		# No more strip!
		$text = $this->internalParse( $text );

		$text = $this->getStripState()->unstripGeneral( $text );

		# Clean up special characters, only run once, next-to-last before doBlockLevels
		$fixtags = [
			# french spaces, last one Guillemet-left
			# only if there is something before the space
			'/(.) (?=\\?|:|;|!|%|\\302\\273)/' => '\\1&#160;',
			# french spaces, Guillemet-right
			'/(\\302\\253) /' => '\\1&#160;',
			# Beware of CSS magic word !important, bug #11874.
			'/&#160;(!\s*important)/' => ' \\1',
		];
		$text = preg_replace( array_keys( $fixtags ), array_values( $fixtags ), $text );

		// FIXME: This function is deprecated in MediaWiki and will
		// be removed in a future release.
		$text = $this->doBlockLevels( $text, $linestart );

		$this->replaceLinkHolders( $text );

		$text = \Sanitizer::normalizeCharReferences( $text );

		$text = MWTidy::tidy( $text );

		$this->mOutput->setText( $text );

		$this->mRevisionId = $oldRevisionId;
		$this->mRevisionObject = $oldRevisionObject;
		$this->mRevisionTimestamp = $oldRevisionTimestamp;
		$this->mRevisionUser = $oldRevisionUser;
		$this->mRevisionSize = $oldRevisionSize;
		$this->mInputSize = false;

		return $this->mOutput;
	}

	/**
	 * Parse image options text and use it to make an image
	 *
	 * @param Title $title
	 * @param string $options
	 * @param LinkHolderArray|bool $holders
	 * @return string HTML
	 */
	public function makeImage( $title, $options, $holders = false ) {
		parent::makeImage( $title, $options, $holders );
		return '';
	}
}
