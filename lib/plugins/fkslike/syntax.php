<?php

if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_fkslike extends DokuWiki_Syntax_Plugin {

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
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fkslike\}\}', $mode, 'plugin_fkslike');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
$to_page.= '<div class="fb-like-box" data-href="https://www.facebook.com/ksvyfuk" data-colorscheme="light" data-show-faces="false" data-header="false" data-stream="false" data-show-border="false"></div>';
        return array($state, array($to_page));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($to_page) = $match;
            $renderer->doc .= $to_page;
        }
        return false;
    }

}
