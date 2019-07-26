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

class admin_plugin_fksmathengine extends DokuWiki_Admin_Plugin {

    public function __construct() {
        
    }

    public function getMenuSort() {
        return 230;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText($language) {
        $menutext = $this->getLang('name');
        return $menutext;
    }

    public function handle() {
        
    }

    public function html() {
        global $INPUT;
        helper_plugin_fkshelper::_returnMenu();

        $log = io_readFile(metaFN('fksmathengine:log_1', '.log'));
        $parse_log = explode("\n", $log);

        foreach ($parse_log as $value) {
            if (!empty($value)) {
                $log_lines[] = explode(';', $value);
            }
        }
        $sort = $INPUT->str('sort');

        if (!empty($sort)) {
            $id = (int) $sort;
            
            usort($log_lines, function($a, $b) use ($id) {
                return ($a[$id] < $b[$id]) ? -1 : 1;
            });
            if($INPUT->str('reverse')){
                $log_lines=array_reverse($log_lines);
            }
        }

        $NO = count($log_lines[0]);

        $form = new Doku_Form(array(), '?do=admin&page=fksmathengine', 'POST');
        $form->addHidden('do', 'admin');
        $form->addHidden('page', 'fksmathengine');
        $val = array($this->getLang('sort_by'));
        for ($i = 0; $i < $NO; $i++) {
            $val[] = $i;
        }
        $form->addElement(form_makeMenuField('sort', $val, null,''));
        $form->addElement(form_makeCheckboxField('reverse',1,$this->getLang('reverse')));
        $form->addElement(form_makeButton('submit', null, $this->getLang('sort')));
        
        echo html_open_tag('h1', array());
        echo $this->getLang('name').' -- '.$this->getLang('log_view');
        echo html_close_tag('h3');
        echo html_open_tag('div', array('class'=>'FKS_mathengine_sort'));
        
        html_form('nic', $form);
        echo html_close_tag('div');
        
        echo html_open_tag('table', array('class'=>'table table-striped FKS_mathengine_table'));
        
        echo html_open_tag('thead', array());
        echo html_open_tag('tr', array());
        
        for ($i = 0; $i < $NO - 2; $i++) {
            echo html_open_tag('th', array());
            echo $this->getLang('data');
            echo html_close_tag('th');
        }
        echo '<th>'.$this->getLang('time').'</th><th>'.$this->getLang('ip').'</th></tr></thead>';

        foreach ($log_lines as $value) {
            echo '<tr><td>' . implode('</td><td>', $value) . '</td></tr>';
        }

        echo '</table>';
    }

}
