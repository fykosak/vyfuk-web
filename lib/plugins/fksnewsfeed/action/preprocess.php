<?php

use \PluginNewsFeed\Model\Priority;
use \PluginNewsFeed\Model\News;
use \PluginNewsFeed\Model\Stream;

class action_plugin_fksnewsfeed_preprocess extends \DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'actPreprocess');
    }

    public function actPreprocess(Doku_Event &$event) {
        global $INPUT;
        if ($event->data !== helper_plugin_fksnewsfeed::FORM_TARGET) {
            return;
        }
        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();
        switch ($INPUT->param('news')['do']) {
            case 'create':
            case 'edit':
                return;
            case'save':
                $this->saveNews();
                return;
            case'priority':
                $this->savePriority();
                return;
            case'delete':
                $this->saveDelete();
                return;
            case'purge':
                $this->deleteCache();
                return;
            default:
                return;
        }
    }

    private function saveNews() {
        global $INPUT;

        $file = News::getCacheFileByID($INPUT->param('news')['id']);
        $cache = new cache($file, '');
        $cache->removeCache();

        $data = [];
        foreach (helper_plugin_fksnewsfeed::$fields as $field) {
            if ($field === 'text') {
                $data[$field] = cleanText($INPUT->str('text'));
            } else {
                $data[$field] = $INPUT->param($field);
            }
        }
        $news = new News();
        if ($INPUT->param('news')['id'] == 0) {
            $news->fill([
                'title' => $data['title'],
                'author_name' => $data['author-name'],
                'author_email' => $data['author-email'],
                'text' => $data['text'],
                'news_date' => $data['news-date'],
                'image' => $data['image'],
                'category' => $data['category'],
                'link_href' => $data['link-href'],
                'link_title' => $data['link-title'],
            ]);
            $newsID = $news->create();

            $this->saveIntoStreams($newsID);
        } else {
            $news->fill([
                'news_id' => $INPUT->param('news')['id'],
                'title' => $data['title'],
                'author_name' => $data['author-name'],
                'author_email' => $data['author-email'],
                'text' => $data['text'],
                'news_date' => $data['news-date'],
                'image' => $data['image'],
                'category' => $data['category'],
                'link_href' => $data['link-href'],
                'link_title' => $data['link-title'],
            ]);
            $news->update();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function saveIntoStreams($newsID) {
        global $INPUT;
        $stream = new Stream(null);
        $stream->fillFromDatabaseByName($INPUT->param('news')['stream']);
        $streamID = $stream->getStreamID();

        $streams = [$streamID];
        $this->helper->fullParentDependence($streamID, $streams);
        foreach ($streams as $stream) {
            $priority = new Priority(null, $newsID, $stream);
            $priority->create();
        }
    }

    private function savePriority() {
        global $INPUT;
        $file = News::getCacheFileByID($INPUT->param('news')['id']);

        $cache = new cache($file, '');
        $cache->removeCache();
        $stream = new \PluginNewsFeed\Model\Stream(null);
        $stream->fillFromDatabaseByName($INPUT->param('news')['stream']);
        $streamID = $stream->getStreamID();

        $priority = new Priority(null, $INPUT->param('news')['id'], $streamID);
        $data = $INPUT->param('priority');
        $priority->fill([
            'priority_from' => $data['from'],
            'priority_to' => $data['to'],
            'priority' => $data['value'],
        ]);
        if ($priority->update()) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    private function saveDelete() {
        global $INPUT;
        $stream = new Stream(null);
        $stream->fillFromDatabaseByName($INPUT->param('news')['stream']);
        $streamID = $stream->getStreamID();
        $priority = new Priority(null, $INPUT->param('news')['id'], $streamID);
        $priority->delete();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function deleteCache() {
        global $INPUT;
        if (!$INPUT->param('news')['id']) {
            $news = $this->helper->allNewsFeed();
            foreach ($news as $new) {
                $f = $new->getCacheFile();
                $cache = new cache($f, '');
                $cache->removeCache();
            }
        } else {
            $f = News::getCacheFileByID($INPUT->param('news')['id']);
            $cache = new cache($f, '');
            $cache->removeCache();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}
