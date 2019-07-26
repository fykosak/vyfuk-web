<?php

class syntax_plugin_fkshelper_person extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getAllowedTypes() {
        return ['substition'];
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addEntryPattern('<person\b.*?>(?=.*?</person>)', $mode, 'plugin_fkshelper_person');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</person>', 'plugin_fkshelper_person');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                preg_match('|<person\s+id="(.+)">|', $match, $matches);
                list(, $id) = $matches;
                return [$state, ['id' => $id]];
                break;
            case DOKU_LEXER_UNMATCHED:
                return [$state, $match];
            default:
                return [$state];
        }
    }

    public function render($mode, Doku_Renderer $renderer, $data) {

        list($state, $payload) = $data;
        if ($mode == 'xhtml') {
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    list(, $personInfo) = $data;
                    $link = wl($this->getConf('person-page-link'), null, true) . '#' . $personInfo['id'];
                    $imgSrc = ml(str_replace('@id@', $personInfo['id'], $this->getConf('person-image-src')),
                        ['w' => 140]);
                    $renderer->doc .= '<a href="' . $link . '" ><span class="person" data-src="' . $imgSrc . '">';
                    break;
                case DOKU_LEXER_UNMATCHED:
                    $renderer->doc .= $renderer->_xmlEntities($payload);
                    break;
                case DOKU_LEXER_EXIT:
                    $renderer->doc .= '</span></a>';
                    break;
            }
            return true;
        } else if ($mode == 'metadata') {
            return true;
        }
        return false;
    }
}
