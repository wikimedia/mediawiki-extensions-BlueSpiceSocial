<?php

/**
 * WikiText class for BlueSpiceSocial
 *
 * add desc
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */
namespace BlueSpice\Social\Parser;
/**
 * WikiText class for BlueSpiceSocial extension
 * @package BlueSpiceSocial
 * @subpackage BlueSpiceSocial
 */
class WikiText extends \Parser{
	public function parse($text, \Title $title, \ParserOptions $options, $linestart = true, $clearState = true, $revid = null) {
		global $wgUseTidy, $wgAlwaysUseTidy, $wgShowHostnames;

		/*if ( $clearState ) {
			$magicScopeVariable = $this->lock();
		}*/

		$this->setTitle( $title );
		$this->mOptions = $options;
		$this->setOutputType( self::OT_HTML );
		if ( $clearState ) {
			$this->clearState();
		}

		//$this->startParse( $title, $options, self::OT_HTML, $clearState );

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

		//Hooks::run( 'ParserBeforeStrip', array( &$this, &$text, &$this->mStripState ) );
		# No more strip!
		//Hooks::run( 'ParserAfterStrip', array( &$this, &$text, &$this->mStripState ) );
		$text = $this->internalParse( $text );
		//Hooks::run( 'ParserAfterParse', array( &$this, &$text, &$this->mStripState ) );

		$text = $this->mStripState->unstripGeneral( $text );

		# Clean up special characters, only run once, next-to-last before doBlockLevels
		$fixtags = array(
			# french spaces, last one Guillemet-left
			# only if there is something before the space
			'/(.) (?=\\?|:|;|!|%|\\302\\273)/' => '\\1&#160;',
			# french spaces, Guillemet-right
			'/(\\302\\253) /' => '\\1&#160;',
			'/&#160;(!\s*important)/' => ' \\1', # Beware of CSS magic word !important, bug #11874.
		);
		$text = preg_replace( array_keys( $fixtags ), array_values( $fixtags ), $text );

		$text = $this->doBlockLevels( $text, $linestart );

		$this->replaceLinkHolders( $text );

		/**
		 * The input doesn't get language converted if
		 * a) It's disabled
		 * b) Content isn't converted
		 * c) It's a conversion table
		 * d) it is an interface message (which is in the user language)
		 */
		/*if ( !( $options->getDisableContentConversion()
			|| isset( $this->mDoubleUnderscores['nocontentconvert'] ) )
		) {
			if ( !$this->mOptions->getInterfaceMessage() ) {
				# The position of the convert() call should not be changed. it
				# assumes that the links are all replaced and the only thing left
				# is the <nowiki> mark.
				$text = $this->getConverterLanguage()->convert( $text );
			}
		}*/

		/**
		 * A converted title will be provided in the output object if title and
		 * content conversion are enabled, the article text does not contain
		 * a conversion-suppressing double-underscore tag, and no
		 * {{DISPLAYTITLE:...}} is present. DISPLAYTITLE takes precedence over
		 * automatic link conversion.
		 */
		/*if ( !( $options->getDisableTitleConversion()
			|| isset( $this->mDoubleUnderscores['nocontentconvert'] )
			|| isset( $this->mDoubleUnderscores['notitleconvert'] )
			|| $this->mOutput->getDisplayTitle() !== false )
		) {
			$convruletitle = $this->getConverterLanguage()->getConvRuleTitle();
			if ( $convruletitle ) {
				$this->mOutput->setTitleText( $convruletitle );
			} else {
				$titleText = $this->getConverterLanguage()->convertTitle( $title );
				$this->mOutput->setTitleText( $titleText );
			}
		}

		$text = $this->mStripState->unstripNoWiki( $text );

		Hooks::run( 'ParserBeforeTidy', array( &$this, &$text ) );

		$text = $this->replaceTransparentTags( $text );
		$text = $this->mStripState->unstripGeneral( $text );
*/
		$text = \Sanitizer::normalizeCharReferences( $text );

		if ( ( $wgUseTidy && $this->mOptions->getTidy() ) || $wgAlwaysUseTidy ) {
			$text = MWTidy::tidy( $text );
		} else {
			# attempt to sanitize at least some nesting problems
			# (bug #2702 and quite a few others)
			$tidyregs = array(
				# ''Something [http://www.cool.com cool''] -->
				# <i>Something</i><a href="http://www.cool.com"..><i>cool></i></a>
				'/(<([bi])>)(<([bi])>)?([^<]*)(<\/?a[^<]*>)([^<]*)(<\/\\4>)?(<\/\\2>)/' =>
				'\\1\\3\\5\\8\\9\\6\\1\\3\\7\\8\\9',
				# fix up an anchor inside another anchor, only
				# at least for a single single nested link (bug 3695)
				'/(<a[^>]+>)([^<]*)(<a[^>]+>[^<]*)<\/a>(.*)<\/a>/' =>
				'\\1\\2</a>\\3</a>\\1\\4</a>',
				# fix div inside inline elements- doBlockLevels won't wrap a line which
				# contains a div, so fix it up here; replace
				# div with escaped text
				'/(<([aib]) [^>]+>)([^<]*)(<div([^>]*)>)(.*)(<\/div>)([^<]*)(<\/\\2>)/' =>
				'\\1\\3&lt;div\\5&gt;\\6&lt;/div&gt;\\8\\9',
				# remove empty italic or bold tag pairs, some
				# introduced by rules above
				'/<([bi])><\/\\1>/' => '',
			);

			$text = preg_replace(
				array_keys( $tidyregs ),
				array_values( $tidyregs ),
				$text );
		}

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
			$this->mOutput->setLimitReportData( 'limitreport-ppgeneratednodes',
				array( $this->mGeneratedPPNodeCount, $this->mOptions->getMaxGeneratedPPNodeCount() )
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

			if ( $this->mGeneratedPPNodeCount > $this->mOptions->getMaxGeneratedPPNodeCount() / 10 ) {
				wfDebugLog( 'generated-pp-node-count', $this->mGeneratedPPNodeCount . ' ' .
					$this->mTitle->getPrefixedDBkey() );
			}
		}*/
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
		parent::makeImage($title, $options, $holders);
		return '';
	}
}