<?php

/* Facebook Like-Button Plugin for Dokuwiki
 * 
 * Copyright (C) 2012 Marvin Thomas Rabe (marvinrabe.de)
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, see <http://www.gnu.org/licenses/>. */

/**
 * Embed facebook javascript onto any page
 * @license GNU General Public License 3 <http://www.gnu.org/licenses/>
 * @author Marvin Thomas Rabe <mrabe@marvinrabe.de>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_facebooklike extends DokuWiki_Action_Plugin {

	/**
	 * General information about the plugin.
	 */
	function getInfo(){
		return array(
			'author' => 'Marvin Thomas Rabe',
			'email'  => 'mrabe@marvinrabe.de',
			'date'	 => '2012-06-06',
			'name'	 => 'Facebook Like-Button',
			'desc'	 => 'Adds Facebook like-buttons',
			'url'	 => 'https://github.com/marvinrabe/dokuwiki-facebook',
		);
	}
	
	/**
	 * Register its handlers.
	 */
	function register(Doku_Event_Handler $controller) {
	    $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE',  $this, '_addHeaders');
	}

	/**
	 * Adds the FBML JavaScript file to the header.
	 */
	function _addHeaders (&$event, $param) {
		global $lang;

		$event->data["script"][] = array (
		  "type" => "text/javascript",
		  "src" => '//connect.facebook.net/'.$this->getLang('fbml_language').'/all.js#xfbml=1',
		);
	}

}
