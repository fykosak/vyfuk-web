<?php

/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
if (!defined('DOKU_INC')) {
    die();
}

/** $INPUT 
 * @news_do add/edit/
 * @news_id no news
 * @news_strem name of stream
 * @id news with path same as doku @ID
 * @news_feed how many newsfeed need display
 * @news_view how many news is display
 */
class action_plugin_fksnewsfeed_rss extends DokuWiki_Action_Plugin {

    public $helper;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    /**
     * 
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        /**
         * RSS
         */
        $controller->register_hook('FEED_OPTS_POSTPROCESS', 'BEFORE', $this, 'rss_generate');
    }

    /**
     * 
     * @global type $conf
     * @global DokuWikiFeedCreator $rss
     * @global UniversalFeedCreator $data
     * @global type $opt
     * @global type $INPUT
     * @param Doku_Event $event
     * @param type $param
     */
    public function rss_generate() {
        if (!$this->getConf('rss_allow')) {
            return;
        }
        global $conf;
        global $rss;
        global $data;
        global $opt;
        global $image;

        global $INPUT;
        $set_stream = $INPUT->str('stream');
        if (empty($set_stream)) {
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

        foreach (helper_plugin_fksnewsfeed::loadstream($INPUT->str('stream')) as $value) {
            $ntext = syntax_plugin_fksnewsfeed_fksnewsfeed::loadnewssimple($value);
            list($param, $text) = helper_plugin_fksnewsfeed::_extract_param_news($ntext);
            $data = new UniversalFeedCreator();
            $data->pubDate = $param['newsdate'];
            $data->title = $param['name'];

            $data->link = $this->helper->_generate_token($value);
            $data->description = p_render('text', p_get_instructions($text), $info);
            $data->editor = $param['author'];
            $data->editorEmail = $param['email'];
            $data->webmaster = 'miso@fykos.cz';
            $data->category = $INPUT->str('stream');
            $rss->addItem($data);
        }
        $feeds = $rss->createFeed($opt['feed_type'], 'utf-8');
        print $feeds;
        exit;
    }

}
