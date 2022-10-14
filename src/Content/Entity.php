<?php
namespace BlueSpice\Social\Content;

class Entity extends \BlueSpice\Content\Entity {

	/**
	 * @param string $text
	 * @param string $modelId
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_BSSOCIAL ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * Decodes the JSON into a PHP associative array.
	 *
	 * @return array
	 */
	public function getJsonData() {
		return \FormatJson::decode( $this->getText(), true );
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
		$decoded = \FormatJson::decode( $this->getText(), true );
		if ( !is_array( $decoded ) ) {
			return null;
		}
		return \FormatJson::encode( $decoded, true );
	}

	/**
	 * Constructs an HTML representation of a JSON object.
	 *
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
	 *
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
