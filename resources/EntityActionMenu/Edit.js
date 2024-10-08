/*
* @author     Stefan Kühn
* @package    BluespiceSocial
* @subpackage BlueSpiceSocial
* @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
*/

bs.social = bs.social || {};
bs.social.EntityActionMenu = bs.social.EntityActionMenu || {};

bs.social.EntityActionMenu.Edit = function ( entityActionMenu ) {
	OO.EventEmitter.call( this );
	var me = this;
	me.entityActionMenu = entityActionMenu;
	me.$element = null;
	me.priority = 4;
	me.$element = $( '<li><a class="dropdown-item bs-social-entity-action-edit" tabindex="0" role="button"'
		+ 'aria-label=' + mw.message( "bs-social-entityaction-edit" ).plain() + '>'
		+ mw.message( "bs-social-entityaction-edit" ).plain() +
		+ '</a></li>'
	);
	me.$element.on( 'click', function( e ) { me.click( e ); } );
};

OO.initClass( bs.social.EntityActionMenu.Edit );
OO.mixinClass( bs.social.EntityActionMenu.Edit, OO.EventEmitter );

bs.social.EntityActionMenu.Edit.prototype.click = function ( e ) {
	if( this.entityActionMenu.entity.editmode ) {
		e.preventDefault();
		return false;
	}
	this.entityActionMenu.entity.makeEditMode();
	e.preventDefault();
	return false;
};
