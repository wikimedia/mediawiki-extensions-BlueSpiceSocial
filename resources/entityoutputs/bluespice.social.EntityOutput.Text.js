/**
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BluespiceSocial
 * @subpackage BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

bs.social.EntityOutputText = function( rawEntity ) {
	bs.social.EntityOutput.call( this, rawEntity );
	this.args['content'] = rawEntity['text'] || '';
};

OO.initClass( bs.social.EntityOutputText );
OO.inheritClass( bs.social.EntityOutputText, bs.social.EntityOutput );

bs.social.EntityOutputText.static.name = "\\BlueSpice\\Social\\EntityOutput\\Text";
bs.social.OutputFactory.register( bs.social.EntityOutputText );