<?php

class syntax_plugin_fkstimer extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getAllowedTypes() {
        return [];
    }

    public function getSort() {
        return 225;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{timer>.+?}}', $mode, 'plugin_fkstimer');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {

        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                $match = substr($match, 8, -2);
                $dateString = date('Y-m-d\TH:i:s', strtotime($match));
                return [$state, ['date' => $dateString]];
            default:
                return [$state, []];
        }
    }

    public function render($mode, Doku_Renderer $renderer, $data) {

        if ($mode == 'xhtml') {
            list($state, $params) = $data;
            switch ($state) {
                case DOKU_LEXER_SPECIAL:
                    $renderer->doc .= '<span class="fks-timer" data-date="' . $params['date'] . '">';
                    $renderer->doc .= '</span>';
                    return true;
                default:
                    return true;
            }
        }
        return false;
    }
}
