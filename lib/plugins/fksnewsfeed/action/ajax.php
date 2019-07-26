<?php

/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
if (!defined('DOKU_INC')) {
    die;
}

/** $INPUT 
 * @news_do add/edit/
 * @news_id no news
 * @news_strem name of stream
 * @id news with path same as doku @ID
 * @news_feed how many newsfeed need display
 * @news_view how many news is display
 */
class action_plugin_fksnewsfeed_ajax extends DokuWiki_Action_Plugin {

    private $helper;

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


        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'ajax_stream');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'ajax_more');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'ajax_edit');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function ajax_stream(Doku_Event &$event, $param) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }


        require_once DOKU_INC . 'inc/JSON.php';
        header('Content-Type: application/json');
        if ($INPUT->str('news_do') == 'stream') {

            $event->stopPropagation();
            $event->preventDefault();

            $feed = (int) $INPUT->str('news_feed');
            $r = (string) "";
            if (auth_quickaclcheck('start') >= $this->getConf('perm_add')) {
                $r.=html_button($this->getLang('btn_manage_stream'), 'btn btn-warning FSK_newsfeed_manage_btn', array());

                $r.=html_open_tag('div', array('class' => 'FKS_newsfeed_manage', 'style' => 'display:none'));
                $r.=html_open_tag('div', array('class' => 'alert alert-warning alert-dismissible', 'role' => 'alert'));

                $r.=$this->getLang('info_add_news');
                $r.=html_close_tag('div');
                $form = new Doku_Form(array('id' => 'addnews', 'method' => 'GET', 'class' => 'fksreturn'));
                $form->addHidden('do', 'edit');
                $form->addHidden('target', 'plugin_fksnewsfeed');
                $form->addHidden('news_do', 'add');
                $form->addHidden('news_id', $this->helper->findimax());
                $form->addHidden('news_stream', $INPUT->str('news_stream'));
                $form->addElement(form_makeButton('submit', '', $this->getLang('btn_add_news')));
                ob_start();
                html_form('addnews', $form);
                $r .= ob_get_contents();
                ob_end_clean();
            }
            if (auth_quickaclcheck('start') >= $this->getConf('perm_manage')) {
                $r.=html_open_tag('div', array('class' => 'alert alert-info', 'role' => 'alert'));
                $r.=$this->getLang('info_delete_news');
                $r.=html_close_tag('div');
                $form2 = new Doku_Form(array('id' => 'addnews', 'method' => 'GET', 'class' => 'fksreturn'));
                $form2->addHidden('target', 'plugin_fksnewsfeed');
                $form2->addHidden('news_do', 'delete');
                $form2->addHidden('news_stream', $INPUT->str('news_stream'));
                $form2->addElement(form_makeButton('submit', '', $this->getLang('btn_delete_news')));
                ob_start();
                html_form('addnews', $form2);
                $r .= ob_get_contents();
                ob_end_clean();
                $r.=html_close_tag('div');
            }
            if (auth_quickaclcheck('start') >= $this->getConf('perm_rss')) {


                $r.=html_open_tag('div', array('class' => 'form-group FKS_newsfeed_rss'));
                $r.=html_open_tag('div', array('class' => 'input-group'));
                $r.=html_open_tag('span', array('class' => 'input-group-addon'));
                $r.='RSS' . html_close_tag('span');
                $r.=html_make_tag('input', array(
                    'class' => 'form-control',
                    'data-id' => 'rss',
                    'type' => 'text',
                    'value' => DOKU_URL . 'feed.php?stream=' . $INPUT->str('news_stream')));
                $r.=html_close_tag('div') . html_close_tag('div');
            }


            foreach ($this->helper->loadstream($INPUT->str('news_stream'), true) as $key => $value) {
                if ($feed) {
                    $e = $this->helper->_is_even($key);

                    $n = str_replace(array('@id@', '@even@'), array($value, $e), $this->helper->simple_tpl);
                    $r.= p_render("xhtml", p_get_instructions($n), $info);

                    $feed --;
                } else {
                    break;
                }
            }
            $r.=$this->_add_button_more($INPUT->str('news_stream'), $INPUT->str('news_feed'));

            $json = new JSON();

            echo $json->encode(array("r" => $r));
        } else {
            return;
        }
    }

    public function ajax_more(Doku_Event &$event, $param) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }

        require_once DOKU_INC . 'inc/JSON.php';
        header('Content-Type: application/json');

        if ($INPUT->str('news_do') == 'more') {
            $event->stopPropagation();
            $event->preventDefault();

            $f = $this->helper->loadstream($INPUT->str('news_stream'));
            (int) $max = (int) $this->getConf('more_news') + (int) $INPUT->str('news_view');
            $more = false;
            for ($i = (int) $INPUT->str('news_view'); $i < $max; $i++) {
                if (array_key_exists($i, $f)) {
                    $e = $this->helper->_is_even($i);

                    $n = str_replace(array('@id@', '@even@'), array($f[$i], $e), $this->helper->simple_tpl);
                    $r.= p_render("xhtml", p_get_instructions($n), $info);
                } else {
                    $more = true;
                    //$r.= html_open_tag('div', array('class' => 'FKS_newsfeed_more_msg'));
                    //$r.=$this->getLang('no_more');
                    //$r.=html_close_tag('div');
                    break;
                }
            }
            $r.= $this->_add_button_more($INPUT->str('news_stream'), $max);
            $json = new JSON();

            echo $json->encode(array('news' => $r, 'more' => $more));
        } else {
            return;
        }
    }

    public function ajax_edit(Doku_Event &$event, $param) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }

        require_once DOKU_INC . 'inc/JSON.php';
        header('Content-Type: application/json');

        if ($INPUT->str('news_do') == 'edit') {
            $event->stopPropagation();
            $event->preventDefault();
            $r = '';
           
            
            
            if (auth_quickaclcheck('start') >= AUTH_EDIT) {
                $form = new Doku_Form(array('id' => 'editnews', 'method' => 'POST', 'class' => 'fksreturn'));
                $form->addHidden("do", "edit");
                $form->addHidden('news_id', $INPUT->str('news_id'));
                $form->addHidden("target", "plugin_fksnewsfeed");
                $form->addElement(form_makeButton('submit', '', $this->getLang('btn_edit_news')));

                ob_start();
                html_form('editnews', $form);
                $r.=html_open_tag('div', array('class' => 'secedit FKS_newsfeed_secedit'));
                $r.= ob_get_contents();
                $r.=html_close_tag('div');
                ob_end_clean();
            }
            if (auth_quickaclcheck('start') >= $this->getConf('perm_fb')) {
                $fb_class = 'fb-share-button btn btn-small btn-social btn-facebook';
                $fb_atr = array('data-href' => $this->helper->_generate_token((int) $INPUT->str('news_id')));
                $r.= html_facebook_btn('Share on FB', $fb_class, $fb_atr);
            }
            if (auth_quickaclcheck('start') >= $this->getConf('perm_link')) {
                $r.=html_button($this->getLang('btn_newsfeed_link'), 'btn btn-info FKS_newsfeed_button FKS_newsfeed_link_btn', array('data-id' => $INPUT->str('news_id')));
                $link = $this->helper->_generate_token((int) $INPUT->str('news_id'));
                $r.=html_make_tag('input', array(
                    'class' => 'FKS_newsfeed_link_inp',
                    'data-id' => $INPUT->str('news_id'),
                    'style' => 'display:none',
                    'type' => 'text',
                    'value' => $link));
            }
            $json = new JSON();

            echo $json->encode(array("r" => $r));
        } else {
            return;
        }
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $stream
     * @param int $more
     * @return string
     */
    private function _add_button_more($stream, $more) {

        return html_open_tag('div', array(
                    'class' => 'FKS_newsfeed_more',
                    'data-stream' => (string) $stream,
                    'data-view' => (int) $more)) .
                html_button($this->getLang('btn_more_news'), 'button', array('title' => 'fksnewsfeed'))
                . html_close_tag('div');
    }

}
