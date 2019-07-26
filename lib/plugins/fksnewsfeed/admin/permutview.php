<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz> 
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . 'admin.php');

class admin_plugin_fksnewsfeed_permutview extends DokuWiki_Admin_Plugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 229;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText() {
        $menutext = $this->getLang('permut_menu');
        return $menutext;
    }

    public function handle() {
        
    }

    public function html() {


        ptln('<h1>' . $this->getLang('permut_menu') . '</h1>', 0);
        $this->helper->FKS_helper->returnMenu('permut_menu');

        $this->changedstream();

        $this->getpermutnews();
    }

    private function getpermutnews() {
        global $INPUT;
        global $lang;
        $set_stream_data = $INPUT->str('stream-data');
        if (!empty($set_stream_data)) {
            $old_data = io_readFile(metaFN('fksnewsfeed:old-streams:' . $INPUT->str('stream'), '.csv'));
            $new_data = $old_data . "\n" . $INPUT->str('stream-data');
            $old_stream_path = metaFN('fksnewsfeed:old-streams:' . $INPUT->str('stream'), '.csv');

            io_saveFile($old_stream_path, $new_data);
            $set_save_stream = $INPUT->str('stream-save');
            if (!empty($set_save_stream)) {
                $new_stream_path = metaFN('fksnewsfeed:streams:' . $INPUT->str('stream'), '.csv');
                io_saveFile($new_stream_path, $INPUT->str('stream-data'));
            }
            $display = $INPUT->str('stream-data');
        } else {
            $display = io_readFile(metaFN('fksnewsfeed:streams:' . $INPUT->str('stream'), '.csv'));
        }


        $form = new Doku_Form(array('id' => "save",
            'method' => 'POST', 'action' => null));
        $form->addHidden('stream', $INPUT->str('stream'));
        $form->startFieldset('edit-stream');
        $form->addElement('<textarea name="stream-data" class="wikitext">' . $display . '</textarea>');
        $form->addElement(form_makeButton('submit', '', $lang['btn_preview'], array()));
        $form->endFieldset();
        html_form('nic', $form);


        $set_stream_data = $INPUT->str('stream-data');
        if (!empty($set_stream_data)) {
            $form = new Doku_Form(array(
                'id' => "save",
                'method' => 'POST',
                'action' => null));
            $form->addHidden('stream', $INPUT->str('stream'));
            $form->addHidden('stream-save', true);
            $form->addHidden('stream-data', $display);

            $form->startFieldset('save-stream');

            $form->addElement($display);

            $form->addElement(form_makeButton('submit', '', $lang['btn_save'], array()));


            $form->endFieldset();
            html_form('nic', $form);
        }
        foreach (preg_split('/;;/', substr($display, 1, -1)) as $value) {
            $e = 'FKS_newsfeed_odd';
            $n = str_replace(array('@id@', '@even@'), array($value, $e), $this->helper->simple_tpl);
            echo p_render("xhtml", p_get_instructions($n), $info);
        }
    }

    private function changedstream() {

        $form = new Doku_Form(array(
            'id' => "changedir",
            'method' => 'POST',
        ));
        $form->startFieldset($this->getLang('change_stream'));


        $form->addElement(form_makeListboxField('stream', array_merge(array(' '), helper_plugin_fksnewsfeed::allstream())));
        $form->addElement(form_makeButton('submit', '', $this->getLang('change_stream')));
        $form->endFieldset();
        html_form('FKS_newsfeed_change_stream', $form);
    }

}
