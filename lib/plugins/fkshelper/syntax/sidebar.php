<?php

class syntax_plugin_fkshelper_sidebar extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('~~SIDEBAR\|.*?~~', $mode, 'plugin_fkshelper_sidebar');
    }

    public function handle($match, $state, $pos, \Doku_Handler $dokuHandler) {
        preg_match('/~~SIDEBAR\|(.*?)~~/', $match, $matches);
        list (, $pageId) = $matches;
        $pageId = trim($pageId);
        return [$state, $pageId];
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode === 'metadata') {
            list(, $pageId) = $data;
            $renderer->meta['sidebar'] = $pageId;
            return true;
        } else {
            return false;
        }
    }
}
