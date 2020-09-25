/**
 *
 * @author     Stefan Kühn
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2020 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.social.EntityActionMenu = function( $el, entity ) {
	bs.social.El.call( this, $el );
	var me = this;
	me.entity = entity;
	me.actions = me.entity.data.get( 'actions', {} );
	me.buttons = {};
	me.showEntities = false;

	me.classes = {
		edit: bs.social.EntityActionMenu.Edit,
		delete: bs.social.EntityActionMenu.Delete
	};

	me.$actionsContainer = me.entity.getContainer( me.entity.ACTIONS_CONTAINER );
	var $dropDownContent = me.$actionsContainer.find( '.bs-social-entity-actions-menu-content' );
	var $highPrioElement = me.$actionsContainer.find( '.bs-social-entity-actions-menu-prio' );

	$(document).trigger( 'BSSocialEntityActionMenuInit', [ me, $el ] );

	var priorityActions = []
	for( var i in me.actions ) {
		if( !me.classes[i] ) {
			continue;
		}
		me.buttons[i] = new me.classes[i]( this, me.actions[i] );
		priorityActions.push( { action: me.buttons[i] ,priority: me.buttons[i].priority } );
	}

	priorityActions.sort( function( a, b ) {
		return a.priority < b.priority;
	});

	if( priorityActions.length === 0 ) {
		me.$actionsContainer.hide();
		return;
	}

	$highPrioElement.append( priorityActions[0].action.$element );

	for( var i = 1; i < priorityActions.length; i++ ) {
		$dropDownContent.append( priorityActions[i].action.$element );

		me.showEntities = true;
	}

	if( me.showEntities ) {
		me.$actionsContainer.show();
	}
	else {
		$dropDownContent.hide();
	}

	return;
};


OO.initClass( bs.social.EntityActionMenu );
OO.inheritClass( bs.social.EntityActionMenu, bs.social.El );