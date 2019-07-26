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
 * @id news with path
 * @news_feed how many newsfeed need display
 * @news_view how many news is display
 */
class action_plugin_fksmathengine extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'fksmathengine_ajax');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function fksmathengine_ajax(Doku_Event &$event, $param) {
        global $INPUT;
        if ($INPUT->str('target') != 'fksmathengine') {
            return;
        }

        $event->stopPropagation();
        $event->preventDefault();
        require_once DOKU_INC . 'inc/JSON.php';
        header('Content-Type: application/json');
        $json = new JSON();
        $param = $INPUT->param('param');
        array_push($param, $INPUT->param('result'));
        array_push($param, date("Y-m-d H:i:s"));
        array_push($param, $_SERVER['REMOTE_ADDR']);
        $log = implode(';', $param);
        $url_log = metaFN('fksmathengine:log_1', '.log');
        $old_log = io_readFile($url_log);
        $new_log = $old_log . "\n" . $log;
        io_saveFile($url_log, $new_log);


        echo $json->encode(array('s' => true));
    }

}
