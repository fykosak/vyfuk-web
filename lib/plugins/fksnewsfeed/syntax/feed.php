<?php

use \PluginNewsFeed\Model\News;
use \PluginNewsFeed\Renderer;

class syntax_plugin_fksnewsfeed_feed extends DokuWiki_Syntax_Plugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;
    /**
     * @var \PluginNewsFeed\Renderer\AbstractRenderer
     */
    private $renderer;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
        switch ($this->getConf('contest')) {
            default;
            case 'fykos':
                $this->renderer = new Renderer\FykosRenderer($this->helper);
                break;
            case 'vyfuk':
                $this->renderer = new Renderer\VyfukRenderer($this->helper);
                break;
        }
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return [];
    }

    public function getSort() {
        return 24;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{news-feed>.+?}}', $mode, 'plugin_fksnewsfeed_feed');
    }

    public function handle($match, $state, $pos, \Doku_Handler $handler) {
        preg_match_all('/([a-z-_]+)="([^".]*)"/', substr($match, 12, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, $parameters];
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode == 'xhtml') {

            list($state, $param) = $data;
            switch ($state) {
                case DOKU_LEXER_SPECIAL:
                    $renderer->nocache();
                    $news = new News($param['id']);
                    $news->fillFromDatabase();
                    if (is_null($news) || ($param['id'] == 0)) {
                        $renderer->doc .= '<div class="alert alert-danger">' . $this->getLang('news_non_exist') .
                            '</div>';
                        return true;
                    }
                    $renderer->doc .= $this->getContent($news, $param);

                    return false;
                default:
                    return true;
            }
        }
        return false;
    }

    /**
     * @param $data News
     * @param $params array
     * @return string
     */
    private function getContent(News $data, $params) {
        $f = $data->getCacheFile();
        $cache = new cache($f, '');
        $json = new JSON();
        if ($cache->useCache()) {
            $innerHtml = $json->decode($cache->retrieveCache());
        } else {
            $innerHtml = $this->renderer->renderContent($data, $params);

            $cache->storeCache($json->encode($innerHtml));
        }
        $formHtml = $this->renderer->renderEditFields($params);
        $html = $this->renderer->render($innerHtml, $formHtml, $data);
        return $html;
    }
}
