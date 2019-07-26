<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . 'admin.php');

class admin_plugin_fksvalidpage extends DokuWiki_Admin_Plugin {

    public function __construct() {
        $this->helper = $this->loadHelper('fksvalidpage');
    }

    function getMenuSort() {
        return 228;
    }

    function forAdminOnly() {
        return false;
    }

    function getMenuText($language) {
        $menutext = $this->getLang('menu');
        return $menutext;
        //return "upload zadani";
    }

    function handle() {
        global $lang;
    }

    function html() {
        foreach ($this->helper->getallPages() as $key => $value) {
            $this->helper->ispagevalid($value, $key);
        }
        $form = new Doku_Form(array('onsubmit' => 'return false', 'id' => 'fkssearch'));
        $form->addElement(form_makeTextField('search', null, null, "search_input"));
        $form->addElement(form_makeListboxField('type', array('text'=>'text','nadpis'=>'head','metazanky'=>'meta'),null, 'type'));
        $form->addElement(form_makeButton('submit', null));
        html_form('search', $form);

        echo'<div id="search_div"></div>';
    }

}
