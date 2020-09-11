<?php
namespace BlueSpice\Social\Content;

use BlueSpice\Social\Entity as SocialEntity;
use MediaWiki\MediaWikiServices;

class Entity extends \BlueSpice\Content\Entity {

	/**
	 *
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_BSSOCIAL ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * Decodes the JSON into a PHP associative array.
	 * @return array
	 */
	public function getJsonData() {
		return \FormatJson::decode( $this->getNativeData(), true );
	}

	/**
	 * @return bool Whether content is valid JSON.
	 */
	public function isValid() {
		return $this->getJsonData() !== null;
	}

	/**
	 * Pretty-print JSON
	 *
	 * @return bool|null|string
	 */
	public function beautifyJSON() {
		$decoded = \FormatJson::decode( $this->getNativeData(), true );
		if ( !is_array( $decoded ) ) {
			return null;
		}
		return \FormatJson::encode( $decoded, true );
	}

	/**
	 * Beautifies JSON prior to save.
	 * @param Title $title Title
	 * @param User $user User
	 * @param ParserOptions $popts
	 * @return JsonContent
	 */
	public function preSaveTransform( \Title $title, \User $user, \ParserOptions $popts ) {
		return new static( $this->beautifyJSON() );
	}

	/**
	 * Set the HTML and add the appropriate styles
	 *
	 *
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 */
	protected function fillParserOutput( \Title $title, $revId,
		\ParserOptions $options, $generateHtml, \ParserOutput &$output ) {
		$oEntity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $title );
		if ( !$oEntity instanceof SocialEntity ) {
			return;
		}
		$output->setDisplayTitle( strip_tags(
			$oEntity->getHeader()->parse()
		) );
		if ( $generateHtml ) {
			$output->setText( $oEntity->getRenderer()->render( 'Page' ) );
			$output->addModuleStyles( 'mediawiki.content.json' );
		} else {
			$output->setText( $oEntity->getRenderer()->render( 'Page' ) );
		}
	}

	/**
	 * Constructs an HTML representation of a JSON object.
	 * @param array $mapping
	 * @return string HTML
	 */
	protected function objectTable( $mapping ) {
		$rows = [];

		foreach ( $mapping as $key => $val ) {
			$rows[] = $this->objectRow( $key, $val );
		}
		return \Xml::tags( 'table', [ 'class' => 'mw-json' ],
			\Xml::tags( 'tbody', [], implode( "\n", $rows ) )
		);
	}

	/**
	 * Constructs HTML representation of a single key-value pair.
	 * @param string $key
	 * @param mixed $val
	 * @return string HTML.
	 */
	protected function objectRow( $key, $val ) {
		$th = \Xml::elementClean( 'th', [], $key );
		if ( is_array( $val ) ) {
			$td = \Xml::tags( 'td', [], self::objectTable( $val ) );
		} else {
			if ( is_string( $val ) ) {
				$val = '"' . $val . '"';
			} else {
				$val = \FormatJson::encode( $val );
			}

			$td = \Xml::elementClean( 'td', [ 'class' => 'value' ], $val );
		}

		return \Xml::tags( 'tr', [], $th . $td );
	}
}
