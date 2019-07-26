<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeedstream extends DokuWiki_Syntax_Plugin {

    private $helper;

   


    //array('indic' => array(), 'items' => array(), 'img' => array(), 'html_indic' => '', 'html_items' => '');

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    public function getSort() {
        return 3;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed-stream>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeedstream');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state) {
        $param = helper_plugin_fkshelper::extractParamtext(substr($match, 21, -2));
        return array($state, array($param));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        if ($mode !== 'xhtml') {
            return;
        }
        list(, $match) = $data;
        list($param) = $match;
        $atr = array();
        foreach ($param as $key => $value) {
            $atr['data-' . $key] = $value;
        }

        $renderer->doc .='<noscript>' .
                helper_plugin_fkshelper::returnmsg('<h1>O RLY?</h1>
        <p>Good luck without JavaScript</p>', -1) .
                '</noscript>';

        $renderer->doc .='<div class="FKS_newsfeed_stream" ' . buildAttributes($atr) . '></div>';
        return false;
    }

}
