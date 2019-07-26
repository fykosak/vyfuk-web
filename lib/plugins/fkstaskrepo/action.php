<?php

/**
 * DokuWiki Plugin fkstaskrepo (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class action_plugin_fkstaskrepo extends DokuWiki_Action_Plugin {

    private $detFields = array('year', 'series', 'problem');
    private $modFields = array('name', 'origin', 'tags', 'task');

    /**
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fkstaskrepo');
    }

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('HTML_SECEDIT_BUTTON', 'BEFORE', $this, 'handle_html_secedit_button');
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'handle_html_edit_formselection');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_parser_cache_use');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_html_secedit_button(Doku_Event &$event, $param) {
        global $TEXT;
        if ($event->data['target'] !== 'plugin_fkstaskrepo') {
            return;
        }
        //$event->data['name'] = $this->getLang('Edit'); // it's set in redner()
    }

    public function handle_html_edit_formselection(Doku_Event &$event, $param) {
        global $TEXT;
        global $INPUT;
        if ($event->data['target'] !== 'plugin_fkstaskrepo') {
            return;
        }
        $event->preventDefault();

        unset($event->data['intro_locale']);

        // FIXME: Remove this if you want a media manager fallback link
        // You will probably want a media link if you want a normal toolbar
        $event->data['media_manager'] = false;

        echo $this->locale_xhtml('edit_intro');


        $form = $event->data['form'];

        if (array_key_exists('wikitext', $_POST)) {
            foreach ($this->detFields as $field) {
                $parameters[$field] = $_POST[$field];
            }
        } else {
            $parameters = syntax_plugin_fkstaskrepo_entry::extractParameters($TEXT, $this);
        }

        $data = $this->helper->getProblemData($parameters['year'], $parameters['series'], $parameters['problem']);
        $data = array_merge($data, $parameters);

        $globAttr = array();
        if (!$event->data['wr']) {
            $globAttr['readonly'] = 'readonly';
        }

        $form->startFieldset('Problem');
        // readonly fields
        foreach ($this->detFields as $field) {
            $attr = $globAttr;
            $attr['readonly'] = 'readonly';
            $value = $INPUT->post->str($field, $data[$field]);
            $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, $attr));
        }

        // editable fields
        foreach ($this->modFields as $field) {
            $attr = $globAttr;
            if ($field == 'task') {
                $value = $INPUT->post->str('wikitext', $data[$field]);
                $form->addElement(form_makeWikiText(cleanText($value), $attr));
            } else if ($field == 'tags') {
                $value = $INPUT->post->str($field, implode(', ', $data[$field]));
                $tags = array_map(function($it) {
                            return $it['tag'];
                        }, $this->helper->getTags()); // TODO default lang

                $attr['data-tags'] = json_encode($tags);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $this->getPluginName() . '-' . $field, null, $attr));
            } else {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, $attr));
            }
        }

        $form->endFieldset();
    }

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {
        if (!isset($_POST['do']) || !isset($_POST[reset($this->detFields)])) {
            return;
        }
        if (!isset($_POST['do']['save'])) {
            return;
        }
        global $TEXT;

        $TEXT = sprintf('<fkstaskrepo year="%s" series="%s" problem="%s"/>', $_POST['year'], $_POST['series'], $_POST['problem']);

        $data = array();
        foreach ($this->modFields as $field) {
            if ($field == 'task') {
                $data[$field] = cleanText($_POST['wikitext']);
            } else if ($field == 'tags') {
                $data[$field] = array_filter(array_map(function($it) {
                                    return trim($it);
                                }, explode(',', $_POST[$field])));
            } else {
                $data[$field] = $_POST[$field];
            }
        }
        $this->helper->updateProblemData($data, $_POST['year'], $_POST['series'], $_POST['problem']);
    }

    public function handle_parser_cache_use(Doku_Event &$event, $param) {
        $cache = & $event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) {
            return;
        }
        if ($cache->mode != 'xhtml') {
            return;
        }

        // get meta data
        $depends = p_get_metadata($cache->page, 'relation fkstaskrepo');
        if (!is_array($depends) || !count($depends)) {
            return; // nothing to do
        }
        $cache->depends['files'] = !empty($cache->depends['files']) ? array_merge($cache->depends['files'], $depends) : $depends;
    }

}

// vim:ts=4:sw=4:et:
