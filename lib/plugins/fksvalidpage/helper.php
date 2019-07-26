<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class helper_plugin_fksvalidpage extends DokuWiki_Plugin {

    public function __construct() {
        
        $this->FKS_helper = $this->loadHelper('fkshelper');
    }

    static public function FKS_valid_btn() {
        global $ID;
        $form = new Doku_Form(array(
            'class' => "button btn_valid_page",
            'method' => 'GET',
            'action' => wl($ID)
        ));
        $form->addElement(form_makeOpenTag('div', array('class' => 'no')));
        $form->addHidden('do', 'fks_valid');
        $form->addElement(form_makeButton('submit', null, 'FKS-valid', array('class' => 'button')));
        $form->addElement(form_makeCloseTag('div'));
        ob_start();
        html_form('button', $form);
        $r = ob_get_contents();
        ob_end_clean();
        return $r;
    }

    function extraxtH($page, $H, $type) {

        foreach ($H as $K => $V) {
            if (!($K % 2)) {
                //echo $K;
                $H[$K] = null;
            } else {
                $retH[$type][$page][] = $V;
            }
        }
        return true;
    }

    function extraxtbyH($H) {
        if (is_array($H)) {
            foreach ($H as $K => $V) {
                $arr[] = $V;
            }
        }
        return $arr;
    }

    function searchkey($s, $text) {
        $A = array();
        foreach ($text as $key => $value) {
            if (preg_match('/' . $s . '/', $value)) {
                $A[] = $key;
            }
        }
        return $A;
    }

    function deleteDiakr($text) {
        $cs = array('ě', 'é', 'ř', 'ŕ', 'ť', 'ž', 'ú', 'í', 'ó', 'ô', 'á', 'ä', 'š', 'ď', 'ľ', 'ĺ', 'ý', 'č', 'ň');
        $en = array('e', 'e', 'r', 'r', 't', 'z', 'u', 'i', 'o', 'o', 'a', 'a', 's', 'd', 'l', 'l', 'y', 'c', 'n');
        return str_replace($cs, $en, $text);
    }

    function wikilinfromfile($file) {

        return '<a href="' . wl(str_replace(array(DOKU_INC . 'data/pages/', '/', '.txt'), array('', ':', ''), $file)) . '">' . str_replace(array(DOKU_INC . 'data/pages/', '/', '.txt'), array('', ':', ''), $file) . '</a>';
    }

    function getallPages() {
        return $this->filefromdir(DOKU_INC . 'data/pages');
    }

    function filefromdir($dir) {
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result = array_merge($result, $this->filefromdir($dir . DIRECTORY_SEPARATOR . $value));
                } else {
                    $result[] = $dir . DIRECTORY_SEPARATOR . $value;
                }
            }
        }
        return $result;
    }

    function filetreedir($dir) {
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = $this->filetreedir($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[$value] = $dir . DIRECTORY_SEPARATOR . $value;
                }
            }
        }
        return $result;
    }

    function shortfilename($name, $dir, $flag = 'ID_ONLY') {
        switch ($flag) {
            case 'ID_ONLY':
                $n = substr($name, strlen(DOKU_INC . "data/pages/" . $dir . "/news"), -4);
                break;
            case 'NEWS_W_ID':
                $n = substr($name, strlen(DOKU_INC . "data/pages/" . $dir . "/"), -4);
                break;
            case 'DIR_N_ID':
                $n = substr($name, strlen(DOKU_INC . "data/pages/"), -4);
                break;
        }
        return $n;
    }

    function ispagevalid($page, $id) {
       
        list($validHglobal, $msgHglobal) = $this->Hglobal($page);
        list($validHEven, $msgHEven) = $this->HEven($page);
        list($validTable, $msgTable) = $this->VTable($page);
        if ($id % 2) {
            $even = 'even';
        } else {
            $even = 'odd';
        }
        echo '<div class="valid_' . $even . '">'
        . '<label>NO:' . $id . ' ' . $this->shortfilename($page, null, 'DIR_N_ID') . '</label>';

        if (!($validHEven && $validHglobal && $validTable)) {

            $AT = 3;
            $TT = $AT - ($validHEven + $validHglobal + $validTable);
            echo $this->FKS_helper->returnmsg($this->getLang('page') . ' ' . $this->wikilinfromfile($page) . '  ' . str_replace(array('@non@', '@all@'), array($TT, $AT), $this->getLang('nonvalid')), -1);

            echo ' '
            . '<label class="fks_valid_page_label" id="fks_valid_page_label_' . $id . '">' . $this->getLang('showtest') . '</label>'
            . '<div style="display:none" class="level3" id="fks_valid_page_label_' . $id . '_page">'
            . $msgHEven . $msgHglobal . $msgTable
            . '</div>';
        } else {
            echo$this->FKS_helper->returnmsg($this->getLang('page') . ' ' . $this->wikilinfromfile($page) . '  ' . $this->getLang('valid'), 1);
        }
        echo '</div>';
    }

    function HEven($page) {
        list($valid1, $msg1, $text) = $this->countH($text, 1);
        list($valid2, $msg2, $text) = $this->countH($text, 2);
        list($valid3, $msg3, $text) = $this->countH($text, 3);
        list($valid4, $msg4, $text) = $this->countH($text, 4);
        list($valid5, $msg5, $text) = $this->countH($text, 5);
        $valid = $valid1 && $valid2 && $valid3 && $valid4 && $valid5;
        $msg = $msg1 . $msg2 . $msg3 . $msg4 . $msg5;
        return array($valid, $msg);
    }

    function countH($text, $lvl) {
        switch ($lvl) {
            case 1:
                $Hwiki = '======';
                break;
            case 2:
                $Hwiki = '=====';
                break;
            case 3:
                $Hwiki = '====';
                break;
            case 4:
                $Hwiki = '===';
                break;
            case 5:
                $Hwiki = '==';
                break;
        }
        $valid = true;
        if (preg_match_all('/' . $Hwiki . '/', $text) % 2) {
            $valid = false;
            $msg = $this->FKS_helper->returnmsg(str_replace('@lvl@', $lvl, $this->getLang('herror')), -1);
        }
        $text = str_replace($Hwiki, '', $text);

        return array($valid, $msg, $text);
    }

    function Hglobal($page) {
        $valid = false;
        if (preg_match('/.?======.?/i', io_readFile($page))) {
            $valid = true;
        } else {
            $valid = false;
            $msg = $this->FKS_helper->returnmsg($this->getLang('page') . ' ' . $this->wikilinfromfile($page) . ' ' . $this->getLang('h1error'), -1);
        }
        return array($valid, $msg);
    }

    function VTable($page) {
        //$table = null;
        $tableid = null;
        foreach (preg_split('/\n/', io_readFile($page)) as $key => $value) {
            if (preg_match('/\|/', $value)) {
                if (substr($value, 0, 1) === '|') {
                    $tableid[$key] = array('id' => $key, 'match' => preg_match_all('/[\^\|]/', $value, $match));
                }
            }
        }
        $valid = true;
        $msg = '';
        if (is_array($tableid)) {
            foreach ($tableid as $key => $value) {
                if (array_key_exists($key + 1, $tableid)) {
                    $key2 = $key + 1;
                    if ($tableid[$key2]['match'] != $tableid[$key]['match']) {
                        $valid = false;
                        $msg .= $this->FKS_helper->returnmsg(str_replace(array('@1line@', '@2line@'), array($key, $key2), $this->getLang('nonvalidlanes')), -1);
                    }
                }
            }
        }
        return array($valid, $msg);
    }

}
