<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . 'admin.php');

class admin_plugin_fkshelper extends DokuWiki_Admin_Plugin {

    public $pages = array(
        array('system:fksfootbar','zápatí'),
        array('system:fkssidebar','boční panel'),
        array('system:menu','menu')
        
    );

    public function __construct() {
    }

    function getMenuSort() {
        return 229;
    }

    function forAdminOnly() {
        return false;
    }

    function getMenuText($language) {
        $menutext = 'FKS_helper:' . 'Upravit zvláštní stránky';
        return $menutext;
        
    }

    function handle() {
        
    }

    function html() {

        foreach ($this->pages as $value) {
            @list($id,$name) = $value;

            if(!$name){
                $name= p_get_first_heading($id);
                 if(!$name){
                     $name = $id;
                 }
            }
            $form = new Doku_Form(array());
            $form->startFieldset($name);
            $form->addHidden('id', $id);
            $form->addElement(form_makeButton('submit', 'edit', $this->getLang('fkseditpage')));

            $form->endFieldset();
            $form->printForm();
        }
    }


}
