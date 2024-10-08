<?php

namespace BlueSpice\Social\Renderer;

use BlueSpice\Renderer\Params;
use BlueSpice\Social\Entity;
use Config;
use Exception;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use Message;

class EntityActions extends \BlueSpice\Renderer {

	public const PARAM_ENTITY = 'entity';

	/**
	 *
	 * @var Entity
	 */
	protected $entity = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '' ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );

		$this->entity = $params->get(
			static::PARAM_ENTITY,
			false
		);
		if ( !$this->entity instanceof Entity ) {
			throw new Exception(
				"param '" . static::PARAM_ENTITY . "' needs to be an instance of \\BlueSpice\\Social\\Entity"
			);
		}

		if ( empty( $this->args[static::PARAM_CLASS] ) ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS] .= ' bs-social-entity-actions';
	}

	public function render() {
		$out = $this->getOpenTag();
		$out .= $this->makeTagContent();
		$out .= $this->getCloseTag();
		return $out;
	}

	protected function makeTagContent() {
		$content = '';
		$content .= Html::element( 'ul', [
			'class' => 'bs-social-entity-actions-menu-prio'
		] );
		$content .= Html::openElement( 'div', [
			'class' => 'bs-social-entity-actions-menu'
		] );
		$content .= Html::openElement( 'div', [ 'class' => 'dropdown' ] );

		$ariaLabel = Message::newFromKey( 'bs-social-entity-actions-menu-button-aria-label' );
		$content .= Html::openElement( 'button', [
			'class' => 'btn btn-secondary dropdown-toggle',
			'type' => 'button',
			'data-toggle' => 'dropdown',
			'data-bs-toggle' => 'dropdown',
			'aria-haspopup' => 'true',
			'aria-expanded' => 'false',
			'aria-label' => $ariaLabel->plain(),
		] );
		$content .= Html::element( 'span', [
			'class' => 'caret'
		] );
		$content .= Html::closeElement( 'button' );
		$content .= Html::element( 'ul', [
			'class' => 'dropdown-menu bs-social-entity-actions-menu-content'
		] );
		$content .= Html::closeElement( 'div' );
		$content .= Html::closeElement( 'div' );

		return $content;
	}
}
