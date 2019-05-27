/**
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

bs.social.EntityText = function( $el, type, data ) {
	bs.social.Entity.call( this, $el, type, data );
	var me = this;
	me.ATTACHMENTS_CONTAINER = 'bs-social-entity-content-attachments';
};
OO.initClass( bs.social.EntityText );
OO.inheritClass( bs.social.EntityText, bs.social.Entity );

bs.social.EntityText.prototype.makeEditMode = function() {
	bs.social.EntityText.super.prototype.makeEditMode.apply( this );

	var $attachments =  this.getContainer( this.ATTACHMENTS_CONTAINER );

	if( $attachments.length > 0 ) {
		$attachments.hide();
	}
};

bs.social.EntityText.prototype.removeEditMode = function() {
	bs.social.EntityText.super.prototype.removeEditMode.apply( this );

	var $attachments =  this.getContainer( this.ATTACHMENTS_CONTAINER );

	if( $attachments.length > 0 ) {
		$attachments.show();
	}
};

bs.social.EntityText.prototype.makeEditor = function() {
	return new bs.social.EntityEditorText( this.getEditorConfig(), this );
};