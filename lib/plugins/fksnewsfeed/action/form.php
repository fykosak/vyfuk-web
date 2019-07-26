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
class action_plugin_fksnewsfeed_form extends DokuWiki_Action_Plugin {

    private $modFields = array('name', 'email', 'author', 'newsdate', 'text');
    private $helper;
    private $delete;

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

        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'form_to_news');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'save_news');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'add_to_stream');
        $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'stream_delete');
    }

    /**
     * 
     * @global type $TEXT
     * @global type $INPUT
     * @global type $ID
     * @param Doku_Event $event
     * @param type $param
     * @return type
     */
    public function form_to_news(Doku_Event &$event) {
        global $TEXT;
        global $INPUT;

        global $INFO;
        if ($INPUT->str('target') !== 'plugin_fksnewsfeed') {
            return;
        }
        $event->preventDefault();
        unset($event->data['intro_locale']);
        echo $this->locale_xhtml('edit_intro');
        $form = $event->data['form'];

        if (array_key_exists('wikitext', $_POST)) {
            foreach ($this->modFields as $field) {
                $data[$field] = $INPUT->param($field);
            }
        } else {
            $max_id=  $this->helper->findimax();  
            
            
            if ($max_id > $INPUT->str('news_id')) {
                $data = $this->helper->load_news_simple($INPUT->str("news_id"));
                $TEXT = $data['text'];
            } else {
                $data = array('author' => $INFO['userinfo']['name'],
                    'newsdate' => dformat(),
                    'email' => $INFO['userinfo']['mail'],
                    'text' => 'Tady napiš text aktuality',
                    'name' => 'Název aktuality');
                $TEXT = 'Tady napiš text aktuality';
            }
        }

        $form->startFieldset('Newsfeed');
        $form->addHidden('news_id', $INPUT->str("news_id"));
        $form->addHidden('target', 'plugin_fksnewsfeed');

        $form->addHidden('news_do', $INPUT->str('news_do'));
        if (is_array($INPUT->param('news_stream'))) {
            foreach ($INPUT->param('news_stream') as $k => $v) {
                if ($v == 1) {
                    $form->addHidden('news_stream[' . $k . ']', 1);
                }
            }
        } else {
            $form->addHidden('news_stream[' . $INPUT->param('news_stream') . ']', 1);
        }


        foreach ($this->modFields as $field) {
            if ($field == 'text') {
                $value = $INPUT->post->str('wikitext', $data[$field]);
                $form->addElement(form_makeWikiText($TEXT, array()));
            } else {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, array()));
            }
        }
        $form->endFieldset();
    }

    public function save_news() {
        global $INPUT;
        global $ACT;

        if ($INPUT->str("target") == "plugin_fksnewsfeed") {
            global $TEXT;
            global $ID;
            if (isset($_POST['do']['save'])) {
                $data = array();
                foreach ($this->modFields as $field) {
                    if ($field == 'text') {
                        $data[$field] = cleanText($INPUT->str('wikitext'));
                        unset($_POST['wikitext']);
                    } else {
                        $data[$field] = $INPUT->param($field);
                    }
                }
                
                if ($INPUT->str('news_do') == 'add') {
                    $this->helper->saveNewNews($data, $INPUT->str('news_id'), FALSE);
                    
                    if (is_array($INPUT->param('news_stream'))) {
                        foreach ($INPUT->param('news_stream') as $k => $v) {
                            if ($v == 1) {
                                $arr[] = $k;
                            }
                        }
                    } else {
                        $arr[] = $INPUT->str('news_stream');
                    }

                    foreach ($arr as $value) {
                        $c = '';
                        $c.=';' . $INPUT->str('news_id') . ";";
                        $c.=io_readFile(metaFN('fksnewsfeed/streams/' . $value, ".csv"), FALSE);
                        if (io_saveFile(metaFN('fksnewsfeed/streams/' . $value, ".csv"), $c)) {
                            msg(' written successful', 1);
                        } else {
                            msg("written failure", -1);
                        }
                    }
                }else{
                    $this->helper->saveNewNews($data, $INPUT->str('news_id'), true);
                }
                unset($TEXT);
                unset($_POST['wikitext']);
                $ACT = "show";
                $ID = 'start';
            }
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
    public function add_to_stream() {
        global $INPUT;
       
        if ($INPUT->str("target") == "plugin_fksnewsfeed") {
            

            if ($INPUT->str('news_do') == 'delete') {
                $this->delete['delete'] = true;


                global $INPUT;
              

                $this->delete['data']['news_stream-data'] = $INPUT->str('news_stream-data');
                if ($this->delete['data']['news_stream-data']) {
                    $old_data = io_readFile(metaFN('fksnewsfeed:old-streams:' . $INPUT->str('news_stream'), '.csv'));
                    $new_data = $old_data . "\n" . $this->delete['data']['news_stream-data'];
                    $old_stream_path = metaFN('fksnewsfeed:old-streams:' . $INPUT->str('news_stream'), '.csv');

                    io_saveFile($old_stream_path, $new_data);
                    $set_save_stream = $INPUT->str('news_stream-save');
                    if (!empty($set_save_stream)) {
                        $new_stream_path = metaFN('fksnewsfeed:streams:' . $INPUT->str('news_stream'), '.csv');
                        io_saveFile($new_stream_path, $this->delete['data']['news_stream-data']);
                    }
                    $display = $INPUT->str('news_stream-data');
                } else {
                    $display = io_readFile(metaFN('fksnewsfeed:streams:' . $INPUT->str('news_stream'), '.csv'));
                }
                $this->delete['data']['news_stream'] = $INPUT->str('news_stream');
                $this->delete['data']['news_stream-data'] = $display;
            }
        }
    }

    public function stream_delete(Doku_Event &$event) {
        if (!$this->delete['delete']) {
            return;
        }
        $event->preventDefault();
        global $INPUT;
        global $lang;

        echo '<h1>' . $this->getLang('permut_menu') . ':' . $this->delete['data']['news_stream'] . '</h1>';

        echo html_open_tag('legend');
        echo $this->getLang('btn_delete_news');
        echo html_close_tag('legend');


        $form = new Doku_Form(array('id' => "save",
            'method' => 'POST', 'action' => null));
        $form->startFieldset(null);
        $form->addHidden('stream', $INPUT->str('news_stream'));

        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addHidden('news_do', 'delete');
        $form->addElement('<textarea name="news_stream-data" class="wikitext">' . $this->delete['data']['news_stream-data'] . '</textarea>');
        $form->addElement(form_makeButton('submit', '', $lang['btn_preview'], array()));
        $form->endFieldset();
        html_form('nic', $form);


        $set_stream_data = $this->delete['data']['news_stream-data'];
        if (!empty($set_stream_data)) {
            echo html_open_tag('legend');
            echo $lang['btn_save'];
            echo html_close_tag('legend');


            $form = new Doku_Form(array(
                'id' => "save",
                'method' => 'POST',
                'action' => null));
            $form->startFieldset(null);
            $form->addHidden('news_stream', $this->delete['data']['news_stream']);
            $form->addHidden('news_stream-save', true);
            $form->addHidden('news_stream-data', $this->delete['data']['news_stream-data']);
            $form->addElement($this->delete['data']['news_stream-data']);
            $form->addElement(form_makeButton('submit', '', $lang['btn_save'], array()));
            $form->endFieldset();

            html_form('nic', $form);
        }

        echo html_open_tag('legend');
        echo $lang['btn_preview'];
        echo html_close_tag('legend');

        foreach (preg_split('/;;/', substr($this->delete['data']['news_stream-data'], 1, -1)) as $value) {
            $e = 'FKS_newsfeed_odd';
            $n = str_replace(array('@id@', '@even@'), array($value, $e), $this->helper->simple_tpl);
            echo p_render("xhtml", p_get_instructions($n), $info);
        }
    }

   

}
