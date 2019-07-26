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
 * Embed a Facebook like-button onto any page
 * @license GNU General Public License 3 <http://www.gnu.org/licenses/>
 * @author Marvin Thomas Rabe <mrabe@marvinrabe.de>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/auth.php');

class syntax_plugin_facebooklike extends DokuWiki_Syntax_Plugin {

	private $data;

	/**
	 * General information about the plugin.
	 */
	public function getInfo(){
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
	 * What kind of syntax are we?
	 */
	public function getType(){
		return 'container';
	}

	/**
	 * What about paragraphs?
	 */
	public function getPType(){
		return 'block';
	}

	/**
 	 * Where to sort in?
 	 */
	public function getSort(){
		return 309;
	}

	/**
 	 * Connect pattern to lexer.
 	 */
	public function connectTo($mode) {
		$this->Lexer->addSpecialPattern('\{\{like>[^}]*\}\}',$mode,'plugin_facebooklike');
	}

	/**
	 * Handle the match
	 */
	public function handle($match, $state, $pos, &$handler){
		if (isset($_REQUEST['comment']))
		    return false;

		$match = substr($match,7,-2); //strip markup from start and end

		$data = array();

		//handle params
		$params = explode('|',$match);
		foreach($params as $param){
			$splitparam = explode('=',$param);
			$data[$splitparam[0]] = $splitparam[1];
		}
		return $data;
	}

	/**
	 * Create output.
	 */
	public function render($mode, &$renderer, $data) {
		if($mode == 'xhtml'){
			// Next line just for developing purposes.
			// Disables Dokuwiki cache.
			// $renderer->info['cache'] = false;
			$renderer->doc .= $this->_button($data);
			return true;
		}
		return false;
	}

	/**
	 * Does the contact form XHTML creation. Adds some JavaScript to validate the form
	 * and creates the input form.
	 */
	protected function _button($data){
		global $ID;
		global $conf;
		$this->data = $data;

		$ret = '<fb:like href="'.(empty($data['url'])?wl($ID,'',true):$data['url']).'" '
			. 'layout="'.$this->_setting('layout', $conf).'" '
			. 'show_faces="'.$this->_setting('faces', $conf).'" '
			. 'width="'.$this->_setting('width', $conf).'" '
			. 'action="'.$this->_setting('action', $conf).'" '
			. 'font="'.$this->_setting('font', $conf).'" '
			. 'colorscheme="'.$this->_setting('colorscheme', $conf).'"'
			. '></fb:like>';

		return $ret;
	}

	/**
	 * Returns valid setting.
	 */
	protected function _setting($name, $gconf) {
		include dirname(__FILE__).'/conf/default.php';

		if(strtolower($name) == 'faces') {
			if(empty($this->data['show_faces'])) {
				if(!isset($gconf['plugin']['facebooklike']['show_faces']))
					return 'true';
				else
					return ($gconf['plugin']['facebooklike']['show_faces'] == 1)?'true':'false';
			} else
				return $this->data['show_faces'];
		} else {
			if(empty($this->data[$name])) {
				if(!isset($gconf['plugin']['facebooklike'][$name]))
					return $conf[$name];
				else
					return $gconf['plugin']['facebooklike'][$name];
			} else
				return $this->data[$name];
		}
	}

}
