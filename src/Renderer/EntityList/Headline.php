<?php
namespace BlueSpice\Social\Renderer\EntityList;

use BlueSpice\Renderer\Params;
use BlueSpice\Social\Renderer\EntityList;
use BlueSpice\Utility\CacheHelper;
use Config;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MWException;

class Headline extends \BlueSpice\TemplateRenderer {
	const PARAM_ENTITY_LIST = 'entitylist';

	/**
	 *
	 * @var EntityList
	 */
	protected $entityList = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name
	 * @param CacheHelper|null $cacheHelper
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$this->entityList = $params->get( static::PARAM_ENTITY_LIST, null );
		if ( !$this->entityList ) {
			throw new MWException(
				'EntityListMenu requires an EntityListRenderer'
			);
		}
		$args = $this->entityList->getArgs();

		$this->args[static::PARAM_TAG] = 'div';
		if ( empty( $this->args[static::PARAM_CLASS] ) ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS] .= ' bs-social-entitylist-headline';

		$this->args[EntityList::PARAM_HEADLINE_MESSAGE_KEY]
			= $args[EntityList::PARAM_HEADLINE_MESSAGE_KEY];

		$this->args[static::PARAM_CONTENT]
			= $this->args[ EntityList::PARAM_HEADLINE_MESSAGE_KEY ];

		$this->args[EntityList::PARAM_HIDDEN]
			= $args[EntityList::PARAM_HIDDEN];

		if ( $this->args[EntityList::PARAM_HIDDEN] ) {
			$this->args[static::PARAM_CLASS] .= ' initiallyhidden';
		}

		$msg = $this->msg(
			$this->args[ EntityList::PARAM_HEADLINE_MESSAGE_KEY ]
		);
		if ( $msg && $msg->exists() ) {
			$this->args[static::PARAM_CONTENT] = $msg->plain();
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpiceSocial.EntityListHeadline";
	}

}
