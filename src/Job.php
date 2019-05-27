<?php
namespace BlueSpice\Social;
abstract class Job extends \Job {
	protected $oEntity = null;

	const JOBCOMMAND = '';

	/**
	 *
	 * @param Title $oTitle
	 * @param array $params
	 */
	public function __construct( $oTitle, $params = [] ) {
		if( !$oTitle || $oTitle->getNamespace() !== Entity::NS ) {
			throw new BsException( 'Invalid Entity Title' );
		}
		parent::__construct(
			static::JOBCOMMAND,
			$oTitle,
			$params
		);
	}

	/**
	 * @return Entity
	 */
	public function getEntity() {
		if( !$this->oEntity ) {
			$this->oEntity = Entity::newFromTitle( $this->getTitle() );
		}
		if( !$this->oEntity ) {
			throw new \BsException( 'Invalid Entity' );
		}
		return $this->oEntity;
	}
}