<?php

class syntax_plugin_fkshelper_clearer extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'formatting';
    }

    public function getPType() {
        return 'normal';
    }

    public function getAllowedTypes() {
        return [];
    }

    public function getSort() {
        return 1000;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~clear~~', $mode, 'plugin_fkshelper_clearer');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        return [$state];
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode == 'xhtml') {
            list($state) = $data;
            switch ($state) {
                case DOKU_LEXER_SPECIAL :
                    $renderer->doc .= '<div style="clear:both"></div>';
                    break;
            }
            return true;
        } else {
            return false;
        }
    }
}
