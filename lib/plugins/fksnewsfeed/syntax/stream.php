<?php

class syntax_plugin_fksnewsfeed_stream extends DokuWiki_Syntax_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getSort() {
        return 3;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{news-stream>.+?}}', $mode, 'plugin_fksnewsfeed_stream');
    }

    public function handle($match, $state, $pos, \Doku_Handler $handler) {
        preg_match_all('/([a-z]+)="([^".]*)"/', substr($match, 14, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, [$parameters]];
    }

    public function render($mode, \Doku_Renderer $renderer, $data) {
        if ($mode !== 'xhtml') {
            return true;
        }
        list(, $match) = $data;
        list($param) = $match;
        $attributes = [];
        foreach ($param as $key => $value) {
            $attributes['data-' . $key] = $value;
        }
        $renderer->doc .= '<div class="news-stream">
<div class="stream row" ' . buildAttributes($attributes) . '>
</div>
</div>';
        return false;
    }

}
