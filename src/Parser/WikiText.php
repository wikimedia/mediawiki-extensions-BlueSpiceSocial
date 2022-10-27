<?php

namespace BlueSpice\Social\Parser;

use BlockLevelPass;
use MWTidy;
use Sanitizer;

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
	public function parse( $text, \MediaWiki\Page\PageReference $title, \ParserOptions $options, $linestart = true,
		$clearState = true, $revid = null
	) {
		$this->setPage( $title );
		$this->setOptions( $options );
		$this->setOutputType( self::OT_HTML );
		if ( $clearState ) {
			$this->clearState();
		}

		$this->mInputSize = strlen( $text );
		if ( $this->getOptions()->getEnableLimitReport() ) {
			$this->getOutput()->resetParseStartTime();
		}

		# Remove the strip marker tag prefix from the input, if present.
		if ( $clearState ) {
			$text = strtr( $text, "\x7f", "?" );
		}

		$oldRevisionId = $this->getRevisionId();
		$oldRevisionObject = $this->getRevisionRecordObject();
		$oldRevisionTimestamp = $this->getRevisionTimestamp();
		$oldRevisionUser = $this->getRevisionUser();
		$oldRevisionSize = $this->getRevisionSize();
		if ( $revid !== null ) {
			$this->mRevisionId = $revid;
			$this->mRevisionObject = null;
			$this->mRevisionTimestamp = null;
			$this->mRevisionUser = null;
			$this->mRevisionSize = null;
		}

		# No more strip!
		$text = $this->internalParse( $text );
		$text = $this->internalParseHalfParsed( $text, true, $linestart );
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

		/*if ( $this->mExpensiveFunctionCount > $this->mOptions->getExpensiveParserFunctionLimit() ) {
			$this->limitationWarn( 'expensive-parserfunction',
				$this->mExpensiveFunctionCount,
				$this->mOptions->getExpensiveParserFunctionLimit()
			);
		}

		Hooks::run( 'ParserAfterTidy', array( &$this, &$text ) );
*/
		# Information on include size limits, for the benefit of users who try to skirt them
		/*if ( $this->mOptions->getEnableLimitReport() ) {
			$max = $this->mOptions->getMaxIncludeSize();

			$cpuTime = $this->mOutput->getTimeSinceStart( 'cpu' );
			if ( $cpuTime !== null ) {
				$this->mOutput->setLimitReportData( 'limitreport-cputime',
					sprintf( "%.3f", $cpuTime )
				);
			}

			$wallTime = $this->mOutput->getTimeSinceStart( 'wall' );
			$this->mOutput->setLimitReportData( 'limitreport-walltime',
				sprintf( "%.3f", $wallTime )
			);

			$this->mOutput->setLimitReportData( 'limitreport-ppvisitednodes',
				array( $this->mPPNodeCount, $this->mOptions->getMaxPPNodeCount() )
			);
			$this->mOutput->setLimitReportData( 'limitreport-postexpandincludesize',
				array( $this->mIncludeSizes['post-expand'], $max )
			);
			$this->mOutput->setLimitReportData( 'limitreport-templateargumentsize',
				array( $this->mIncludeSizes['arg'], $max )
			);
			$this->mOutput->setLimitReportData( 'limitreport-expansiondepth',
				array( $this->mHighestExpansionDepth, $this->mOptions->getMaxPPExpandDepth() )
			);
			$this->mOutput->setLimitReportData( 'limitreport-expensivefunctioncount',
				array( $this->mExpensiveFunctionCount, $this->mOptions->getExpensiveParserFunctionLimit() )
			);
			Hooks::run( 'ParserLimitReportPrepare', array( $this, $this->mOutput ) );

			$limitReport = "NewPP limit report\n";
			if ( $wgShowHostnames ) {
				$limitReport .= 'Parsed by ' . wfHostname() . "\n";
			}
			foreach ( $this->mOutput->getLimitReportData() as $key => $value ) {
				if ( Hooks::run( 'ParserLimitReportFormat',
					array( $key, &$value, &$limitReport, false, false )
				) ) {
					$keyMsg = wfMessage( $key )->inLanguage( 'en' )->useDatabase( false );
					$valueMsg = wfMessage( array( "$key-value-text", "$key-value" ) )
						->inLanguage( 'en' )->useDatabase( false );
					if ( !$valueMsg->exists() ) {
						$valueMsg = new RawMessage( '$1' );
					}
					if ( !$keyMsg->isDisabled() && !$valueMsg->isDisabled() ) {
						$valueMsg->params( $value );
						$limitReport .= "{$keyMsg->text()}: {$valueMsg->text()}\n";
					}
				}
			}
			// Since we're not really outputting HTML, decode the entities and
			// then re-encode the things that need hiding inside HTML comments.
			$limitReport = htmlspecialchars_decode( $limitReport );
			Hooks::run( 'ParserLimitReport', array( $this, &$limitReport ) );

			// Sanitize for comment. Note '‐' in the replacement is U+2010,
			// which looks much like the problematic '-'.
			$limitReport = str_replace( array( '-', '&' ), array( '‐', '&amp;' ), $limitReport );
			$text .= "\n<!-- \n$limitReport-->\n";

		}*/
		$this->getOutput()->setText( $text );

		$this->mRevisionId = $oldRevisionId;
		$this->mRevisionObject = $oldRevisionObject;
		$this->mRevisionTimestamp = $oldRevisionTimestamp;
		$this->mRevisionUser = $oldRevisionUser;
		$this->mRevisionSize = $oldRevisionSize;
		$this->mInputSize = false;

		return $this->getOutput();
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

	/**
	 * Helper function for parse() that transforms half-parsed HTML into fully
	 * parsed HTML.
	 *
	 * @param string $text
	 * @param bool $isMain
	 * @param bool $linestart
	 * @return string
	 */
	protected function internalParseHalfParsed( $text, $isMain = true, $linestart = true ) {
		$text = $this->getStripState()->unstripGeneral( $text );

		$text = BlockLevelPass::doBlockLevels( $text, $linestart );

		$this->replaceLinkHolders( $text );

		/**
		 * The input doesn't get language converted if
		 * a) It's disabled
		 * b) Content isn't converted
		 * c) It's a conversion table
		 * d) it is an interface message (which is in the user language)
		 */
		if ( !( $this->getOptions()->getDisableContentConversion()
			|| isset( $this->mDoubleUnderscores['nocontentconvert'] ) )
			&& !$this->getOptions()->getInterfaceMessage()
		) {
			# The position of the convert() call should not be changed. it
			# assumes that the links are all replaced and the only thing left
			# is the <nowiki> mark.
			$text = $this->getTargetLanguageConverter()->convert( $text );
		}

		$text = $this->getStripState()->unstripNoWiki( $text );

		$text = $this->getStripState()->unstripGeneral( $text );

		# Clean up special characters, only run once, after doBlockLevels
		$text = Sanitizer::armorFrenchSpaces( $text );

		$text = Sanitizer::normalizeCharReferences( $text );

		$text = MWTidy::tidy( $text );

		return $text;
	}

}
