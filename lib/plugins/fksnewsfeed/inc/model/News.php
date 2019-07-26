<?php

namespace PluginNewsFeed\Model;

class News extends AbstractModel {

    const SIMPLE_RENDER_PATTERN = '{{news-feed>id="@id@" even="@even@" editable="@editable@" stream="@stream@" page_id="@page_id@"}}';

    /**
     * @var integer
     */
    private $newsID;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $authorName;
    /**
     * @var string
     */
    private $authorEmail;
    /**
     * @var string
     */
    private $text;
    /**
     * @var string
     */
    private $newsDate;
    /**
     * @var string
     */
    private $image;
    /**
     * @var string
     */
    private $category;
    /**
     * @var string
     */
    private $linkHref;
    /**
     * @var string
     */
    private $linkTitle;

    /**
     * @var Priority
     */
    private $priority;

    /**
     * @return Priority
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getNewsID() {
        return $this->newsID;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAuthorName() {
        return $this->authorName;
    }

    /**
     * @return string
     */
    public function getAuthorEmail() {
        return $this->authorEmail;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getNewsDate() {
        return $this->newsDate;
    }

    public function getLocalDate() {
        $date = date('j\. F Y', strtotime($this->newsDate));
        $enMonth = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
        $langMonth = [
            $this->getLang('jan'),
            $this->getLang('feb'),
            $this->getLang('mar'),
            $this->getLang('apr'),
            $this->getLang('may'),
            $this->getLang('jun'),
            $this->getLang('jul'),
            $this->getLang('aug'),
            $this->getLang('sep'),
            $this->getLang('oct'),
            $this->getLang('now'),
            $this->getLang('dec')
        ];
        return (string)str_replace($enMonth, $langMonth, $date);
    }

    /**
     * @return string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getLinkHref() {
        return $this->linkHref;
    }

    /**
     * @return string
     */
    public function getLinkTitle() {
        return $this->linkTitle;
    }

    /**
     * @return bool
     */
    public function hasImage() {
        return $this->image != null;
    }

    /**
     * @return bool
     */
    public function hasLink() {
        return $this->linkHref != null;
    }

    public function create() {
        $this->sqlite->query('INSERT INTO news  (title,  author_name,  author_email , text,  news_date,  
image,  category, link_href,  link_title)  VALUES(?,?,?,?,?,?,?,?,?) ',
            $this->title,
            $this->authorName,
            $this->authorEmail,
            $this->text,
            $this->newsDate,
            $this->image,
            $this->category,
            $this->linkHref,
            $this->linkTitle);
        return $this->findMaxNewsID();
    }

    public function update() {
        $this->sqlite->query('UPDATE news SET title=?,  author_name=?,  author_email=? , text=?, news_date=?,  
image=?,  category=?, link_href=?,  link_title=? WHERE news_id=? ',
            $this->title,
            $this->authorName,
            $this->authorEmail,
            $this->text,
            $this->newsDate,
            $this->image,
            $this->category,
            $this->linkHref,
            $this->linkTitle,
            $this->newsID);
    }

    public function getToken($pageID = '') {
        return (string)wl($pageID, null, true) . '?news-id=' . $this->newsID;
    }

    public function getCacheFile() {
        return self::getCacheFileByID($this->newsID);
    }

    public static function getCacheFileByID($id) {
        return 'news-feed_news_' . $id;
    }

    public function render($even, $stream, $pageID = '', $editable = true) {
        $renderPattern = str_replace(['@id@', '@even@', '@editable@', '@stream@', '@page_id@'],
            [
                $this->newsID,
                $even,
                $editable ? 'true' : 'false',
                $stream,
                $pageID,
            ],
            self::SIMPLE_RENDER_PATTERN);
        $info = [];
        return p_render('xhtml', p_get_instructions($renderPattern), $info);
    }

    public function fill($data) {
        $this->newsID = $data['news_id'];
        $this->title = $data['title'];
        $this->authorName = $data['author_name'];
        $this->authorEmail = $data['author_email'];
        $this->text = $data['text'];
        $this->newsDate = $data['news_date'];
        $this->image = $data['image'];
        $this->category = $data['category'];
        $this->linkHref = $data['link_href'];
        $this->linkTitle = $data['link_title'];
    }

    public function setPriority(Priority $priority) {
        $this->priority = $priority;
    }

    public function fillFromDatabase() {
        $res = $this->sqlite->query('SELECT * FROM news WHERE news_id=?', $this->newsID);
        $row = $this->sqlite->res2row($res);
        $this->fill($row);
    }

    public function __construct($newsID = null) {
        parent::__construct();
        $this->newsID = $newsID;
    }
}
