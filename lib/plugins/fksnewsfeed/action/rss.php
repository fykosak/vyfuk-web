<?php
use \PluginNewsFeed\Model\Stream;

class action_plugin_fksnewsfeed_rss extends \DokuWiki_Action_Plugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
    public $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('FEED_OPTS_POSTPROCESS', 'BEFORE', $this, 'rss_generate');
    }

    public function rss_generate() {
        global $conf;
        global $rss;
        global $data;
        global $opt;
        global $image;

        global $INPUT;
        $streamName = $INPUT->str('stream');
        if (empty($streamName)) {
            return;
        }
        unset($rss, $data);
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex');
        $rss = new DokuWikiFeedCreator();
        $rss->title = $conf['title'];
        $rss->link = DOKU_URL;
        $rss->syndicationURL = DOKU_URL . 'lib/plugins/fksnewsfeed/rss.php';
        $rss->cssStyleSheet = DOKU_URL . 'lib/exe/css.php?s=feed';
        $rss->image = $image;

        $stream = new Stream();
        $stream->fillFromDatabaseByName($streamName);
        $streamID = $stream->getStreamID();

        $stream = new Stream($streamID);
        $allNews = $stream->getNews();

        foreach ($allNews as $news) {
            $data = new UniversalFeedCreator();
            $data->pubDate = $news->getNewsDate();
            $data->title = $news->getTitle();
            $data->link = $news->getToken();

            $data->description = p_render('text', p_get_instructions($news->getText()), $info);
            $data->editor = $news->getAuthorName();
            $data->editorEmail = $news->getAuthorEmail();
            $data->category = $news->getCategory();
            $rss->addItem($data);
        }
        $feeds = $rss->createFeed($opt['feed_type'], 'utf-8');
        print $feeds;
        exit;
    }
}
