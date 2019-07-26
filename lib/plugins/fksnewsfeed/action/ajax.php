<?php

use \dokuwiki\Form\Form;
use \PluginNewsFeed\Model\Stream;
use \PluginNewsFeed\Model\News;

class action_plugin_fksnewsfeed_ajax extends \DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'stream');
    }

    public function stream(Doku_Event &$event) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }

        if ($INPUT->param('news')['do'] == 'stream' || $INPUT->param('news')['do'] == 'more') {
            header('Content-Type: application/json');
            $event->stopPropagation();
            $event->preventDefault();

            $htmlHead = null;
            if ($INPUT->param('news')['do'] == 'stream') {
                if (auth_quickaclcheck('start') >= AUTH_EDIT) {
                    $htmlHead .= '<div class="btn-group-vertical">';
                    $htmlHead .= '<div class="mb-3">';
                    $htmlHead .= $this->printCreateBtn($INPUT->param('news')['stream']);
                    $htmlHead .= '</div>';
                    $htmlHead .= '<div class="mb-3">';
                    $htmlHead .= $this->printPullBtn($INPUT->param('news')['stream']);
                    $htmlHead .= '</div>';
                    $htmlHead .= '<div class="mb-3">';
                    $htmlHead .= $this->printCacheBtn();
                    $htmlHead .= '</div>';
                    $htmlHead .= '</div>';
                }
                $htmlHead .= $this->printRSS($INPUT->param('news')['stream']);
            }

            $stream = new Stream(null);
            $stream->fillFromDatabaseByName($INPUT->param('news')['stream']);
            $news = $stream->getNews();
            $data = $this->printStream($news, (int)$INPUT->param('news')['start'], (int)$INPUT->param('news')['length'], $INPUT->param('news')['stream'], $INPUT->str('page_id'));
            $json = new JSON();
            $data['html']['head'] = $htmlHead;
            echo $json->encode($data);
        } else {
            return;
        }
    }

    /**
     * @param $news News[]
     * @param int $start
     * @param int $length
     * @param string $stream
     * @param string $page_id
     * @return array
     */
    private function printStream($news, $start = 0, $length = 5, $stream = "", $page_id = "") {
        $htmlNews = [];
        $htmlButton = null;
        global $INPUT;
        for ($i = $start; $i < min([$start + $length, (count($news))]); $i++) {
            $e = $i % 2 ? 'even' : 'odd';
            $htmlNews[] = $news[$i]->render($e, $stream, $page_id);
        }
        if ($length + $start >= count($news)) {
            $htmlButton .= '<div class="alert alert-warning">' . $this->getLang('no_more') . '</div>';
        } else {
            $htmlButton = '<button class="more-news btn btn-info w-100" data-stream="' . $INPUT->param('news')['stream'] . '" data-view="' . ($length + $start) . '">
            ' . $this->getLang('btn_more_news') . '
                </button>';
        }
        return ['html' => ['button' => $htmlButton, 'news' => $htmlNews]];
    }

    private function getPullBtnForm($stream) {
        $form = new Form();
        $form->setHiddenField('target', helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'fksnewsfeed_push');
        $form->setHiddenField('news[stream]', $stream);
        $form->addButton('submit', $this->getLang('btn_push_stream'))
            ->addClass('btn btn-info');
        return $form;
    }

    private function printPullBtn($stream) {
        return $this->getPullBtnForm($stream)
            ->toHTML();
    }

    private function printRSS($stream) {
        $html = '';
        $html .= '<div class="rss">';
        $html .= '<a href="' . DOKU_URL . 'feed.php?stream=' . $stream . '"><span class="icon small-btn rss-icon"></span><span class="btn-big">RSS</span></a>';
        $html .= '<span class="link" contenteditable="true" >' . DOKU_URL . 'feed.php?stream=' . $stream . '</span>';
        $html .= '</div>';
        return $html;
    }

    private function getCreateButtonForm($stream) {
        $form = new Form();
        $form->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('news[do]', 'create');
        $form->setHiddenField('news[id]', 0);
        $form->setHiddenField('news[stream]', $stream);
        $form->addButton('submit', $this->getLang('btn_create_news'))
            ->addClass('btn btn-primary');
        return $form;
    }

    private function printCreateBtn($stream) {
        return $this->getCreateButtonForm($stream)
            ->toHTML();
    }

    private function printCacheBtn() {
        $form = new Form();
        $form->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('news[do]', 'purge');
        $form->addButton('submit', $this->getLang('cache_del_full'))
            ->addClass('btn btn-warning');
        return $form->toHTML();
    }
}
