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
class action_plugin_fksnewsfeed_token extends DokuWiki_Action_Plugin {

    private $hash = array('pre' => null, 'pos' => null, 'hex' => null, 'hash' => null);
  
    private $helper;
    private $token = array('show' => false, 'id' => null);

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
         * to render by token
         */
        $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'render_by_tocen');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'encript_token');
    }

    
    /**
     * 
     * @param Doku_Event $event
     * @param type $param
     */
    public function render_by_tocen(Doku_Event &$event) {
        if ($this->token['show']) {
            $e = $this->helper->_is_even($this->token['id']);
            $event->preventDefault();
            echo p_render('xhtml', p_get_instructions(str_replace(array('@id@', '@even@'), array($this->token['id'], $e), $this->helper->simple_tpl)), $info);
        }
    }
    /**
     * 
     * @global type $INPUT
     * @global string $ACT
     * @global type $TEXT
     * @global type $ID
     * @global type $INFO
     * @param Doku_Event $event
     * @param type $param
     */
    public function encript_token() {
        global $ACT;
        global $INPUT;
        
        if ($ACT != 'fksnewsfeed_token') {
            return;
        }
        $token = $INPUT->str('token');
        $this->token['id'] = self::_encript_token($token, $this->getConf('no_pref'), $this->getConf('hash_no'));
        $this->token['show'] = true;
    }

    /**
     * 
     * @param type $hash
     * @param type $l
     * @param type $hash_no
     * @return type
     */
    private static function _encript_token($hash, $l, $hash_no) {
        $enc_hex = substr($hash, $l, -$l);
        $enc_dec = hexdec($enc_hex);
        $id = ($enc_dec - $hash_no) / 2;
        return (int) $id;
    }

}
