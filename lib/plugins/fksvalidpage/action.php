<?php

/**
 * DokuWiki Plugin fksdbexport (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_fksvalidpage extends DokuWiki_Action_Plugin {

    private $helper;
    private $fks_valid = array('fks_valid' => false);
    

    // private $modFields = array('name', 'email', 'author', 'newsdate', 'text');

    public function __construct() {
        $this->helper = $this->loadHelper('fksvalidpage');
    }

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_action_ajax_request');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act');
        $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'handle_act_render');
        //$controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'handle_linenumbers');
        //print_r($controller);
    }

    public function handle_act_render(Doku_Event &$event, $param) {
        global $ID;
        if (!$this->fks_valid['fks_valid']) {
            return;
        }
        $this->helper->ispagevalid(DOKU_INC . 'data/pages/' . str_replace(':', '/', $ID) . '.txt', 'this');
    }

    public function handle_action_act(Doku_Event &$event, $param) {
        
        global $ACT;

        if ($ACT != 'fks_valid') {
            return;
        }

        $this->fks_valid['fks_valid'] = true;
    }

    
   

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_action_ajax_request(Doku_Event &$event, $param) {


        //e.g. access additional request variables
        global $INPUT; //available since release 2012-10-13 "Adora Belle"
        $name = $INPUT->str('key');
        if ($INPUT->str('target') != 'search') {
            return;
        }

        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();




        $pages = $this->helper->getallPages();
        $D = array();


        $dokuP = array('===', '====', '=====' . '======', '\\', '/', '**', '*', '?', '!', ':', '.', ',', '[[', ']]', '}', '}', '|');
        foreach ($pages as $key => $value) {
            $k = false;

            $k = array_search($name, preg_split('/ /', str_replace($dokuP, ' ', str_replace("\n", " ", io_readFile($value)))));
            if ($k) {
                $D[] = $value;
            }
        }
        $data['links'] = '<span>výsledky pre: ' . $name . '</span>';
        foreach ($D as $key => $value) {
            $link = wl(str_replace('/', ':', str_replace(DOKU_INC . 'data/pages/', '', substr($value, 0, -4))));
            $data['links'].='<p><a href="' . $link . '">' . $link . '</a></p>';
        }


        require_once DOKU_INC . 'inc/JSON.php';
        $json = new JSON();

        //set content type
        header('Content-Type: application/json');
        echo $json->encode($data);
    }

    private function getLower($text) {
        return strtolower($text);
    }

}

// vim:ts=4:sw=4:et: