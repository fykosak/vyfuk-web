<?php

namespace PluginNewsFeed\Model;


class Stream extends AbstractModel {
    /**
     * @var integer
     */
    private $streamID;
    /**
     * @var string
     */
    private $name;

    public function __construct($streamID = null) {
        parent::__construct();
        $this->streamID = $streamID;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return integer
     */
    public function getStreamID() {
        return $this->streamID;
    }

    /**
     * @return News[]
     */
    public function getNews() {
        ;
        $res = $this->sqlite->query('SELECT * FROM priority o JOIN news n ON o.news_id=n.news_id WHERE stream_id=? ',
            $this->streamID);
        $ars = $this->sqlite->res2arr($res);
        $news = [];
        foreach ($ars as $ar) {
            $priority = new Priority();
            $priority->fill($ar);

            $feed = new News();
            $feed->fill($ar);
            $feed->setPriority($priority);
            $news[] = $feed;
        }
        return $this->sortNews($news);
    }

    private function sortNews($news) {
        usort($news,
            function (News $a, News $b) {
                if ($a->getPriority()->getPriorityValue() > $b->getPriority()->getPriorityValue()) {
                    return -1;
                } elseif ($a->getPriority()->getPriorityValue() < $b->getPriority()->getPriorityValue()) {
                    return 1;
                } else {
                    return strcmp($b->getNewsDate(), $a->getNewsDate());
                }
            });
        return $news;
    }

    public function update() {
        msg('not implement', -1);
        return;
    }

    public function fill($data) {
        $this->name = $data['name'];
        $this->streamID = $data['stream_id'];
    }

    public function fillFromDatabaseByName($name) {
        $res = $this->sqlite->query('SELECT * FROM stream WHERE name=?', $name);
        $this->fill($this->sqlite->res2row($res));
    }

    public function fillFromDatabase() {
        $res = $this->sqlite->query('SELECT name FROM stream WHERE stream_id=?', $this->streamID);
        $this->name = $this->sqlite->res2single($res);
    }

    public function create() {
        $this->sqlite->query('INSERT INTO stream (name) VALUES(?)', $this->name);
        $this->fillFromDatabaseByName($this->name);
        return $this->name;
    }
}
