<?php

require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/AbstractModel.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/News.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/Priority.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/Stream.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/renderer/AbstractRenderer.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/renderer/VyfukRenderer.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/renderer/FykosRenderer.php';

use \PluginNewsFeed\Model\Stream;
use \PluginNewsFeed\Model\News;

class helper_plugin_fksnewsfeed extends \DokuWiki_Plugin {

    public static $fields = [
        'title',
        'author-name',
        'author-email',
        'news-date',
        'image',
        'category',
        'link-href',
        'link-title',
        'text',
    ];
    /**
     * @var helper_plugin_sqlite
     */
    public $sqlite;
    /**
     * @var helper_plugin_social
     */
    public $social;

    const FORM_TARGET = 'plugin_news-feed';

    public function __construct() {
        $this->social = $this->loadHelper('social');

        $this->sqlite = $this->loadHelper('sqlite', false);

        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
        }
        if (!$this->sqlite->init('fksnewsfeed',
            DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)
        ) {
            msg($pluginName . ': Cannot initialize database.');
        }
    }

    public function findMaxNewsID() {
        $res = $this->sqlite->query('SELECT max(news_id) FROM news');
        return (int)$this->sqlite->res2single($res);
    }

    public function allStream() {
        $streams = [];
        $res = $this->sqlite->query('SELECT s.name FROM stream s');
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $row['name'];
        }
        return $streams;
    }

    /**
     * @return Stream[]
     */
    public function getAllStreams() {
        $streams = [];
        $res = $this->sqlite->query('SELECT * FROM stream');
        foreach ($this->sqlite->res2arr($res) as $row) {
            $stream = new Stream();
            $stream->fill($row);
            $streams[] = $stream;
        }
        return $streams;
    }

    /**
     * @param $id integer
     * @return array
     */
    public function loadSimpleNews($id) {
        $res = $this->sqlite->query('SELECT * FROM news WHERE news_id=?', $id);
        foreach ($this->sqlite->res2arr($res) as $row) {
            return $this->prepareRow($row);
        }
        return null;
    }

    private function prepareRow($row) {
        $values = [];
        foreach ($row as $key => $value) {
            $values[str_replace('_', '-', $key)] = $value;
        }
        return $values;
    }

    public function allParentDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['child'];
        }
        return $streamIDs;
    }

    public function allChildDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence  WHERE child=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['parent'];
        }
        return $streamIDs;
    }

    public function fullParentDependence($streamIDs, &$arr) {
        foreach ($this->allParentDependence($streamIDs) as $newStreamID) {
            if (!in_array($newStreamID, $arr)) {
                $arr[] = $newStreamID;
                $this->fullParentDependence($newStreamID, $arr);
            }
        }
    }

    public function fullChildDependence($streamIDs, &$arr) {
        foreach ($this->allChildDependence($streamIDs) as $newStreamID) {
            if (!in_array($newStreamID, $arr)) {
                $arr[] = $newStreamID;
                $this->fullChildDependence($newStreamID, $arr);
            }
        }
    }

    public function createDependence($parent, $child) {
        return (bool)$this->sqlite->query('INSERT INTO dependence (parent,child) VALUES(?,?);', $parent, $child);
    }

    /**
     * @return News[]
     */
    public function allNewsFeed() {
        $res = $this->sqlite->query('SELECT * FROM news');
        $news = [];
        foreach ($this->sqlite->res2arr($res) as $row) {
            $feed = new News();
            $feed->fill($row);
            $news[] = $feed;
        };
        return $news;
    }
}
