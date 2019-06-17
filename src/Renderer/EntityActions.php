<?php

namespace BlueSpice\Social\Renderer;

use Exception;
use Config;
use IContextSource;
use Html;
use MediaWiki\Linker\LinkRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Entity;

class EntityActions extends \BlueSpice\Renderer {

	const PARAM_ENTITY = 'entity';

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
	 * @param string $name | ''
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
		$content .= Html::element( 'a', [
			'class' => 'bs-social-entity-actions-btn',
			'title' => $this->msg( 'bs-social-entityactions-label' )->plain(),
			'href' => '#',
		] );
		$content .= Html::element( 'div', [
			'class' => 'bs-social-entity-actions-content',
		] );
		return $content;
	}
}
