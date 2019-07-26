<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class helper_plugin_fkshelper extends DokuWiki_Plugin {

    /**
     * talčítko pre návrat do menu z admin prostredia 
     * 
     * @author Michal Červeňák <miso@fykos.cz>
     * @return void
     * @param null
     */
    public function returnMenu() {

        $form = new Doku_Form(array(
            'id' => "returntomenu",
            'method' => 'POST',
            'action' => DOKU_BASE . "?do=admin"
        ));

        $form->addElement(form_makeButton('submit', '', $this->getLang('returntomenu')));
        html_form('returntomenu', $form);
    }

    /**
     * call form not class
     * @return void
     * @param null
     */
    public static function _returnMenu() {
        $helper = new helper_plugin_fkshelper;
        $helper->returnMenu();
    }

    /**
     * msg return html not print
     * @author = Michal Červeňák
     * @return string html of msg
     * @param string $text 
     * @param int $lvl
     * 
     */
    public static function returnmsg($text, $lvl) {


        ob_start();
        msg($text, $lvl);
        $msg = ob_get_contents();
        ob_end_clean();
        return $msg;
    }

    /**
     * extract param from text
     * @author Lukáš Ledvina
     * @param string $text for parsing
     * @return array parameters
     * 
     *
     */
    public static function extractParamtext($text, $delimiter = ';', $sec_delimiter = '=', $packer = '"') 
    {
        $param = array();
        $k = $v = "";
// state:
//  0: init
//  1: wait for value
//  2: wait for "end value
//  3: final state
//  4: error state
        $index = 0;
        $state = 0;
        while ( true ){
            list($nindex, $nActChar) = self::getNextActiveChar($text, $index, $delimiter, $sec_delimiter, $packer);
            switch ( $state ){
                case 0:
                    switch ( $nActChar ){
                        case 0: // ;
                        case 3: // null
                            $k = trim(substr($text, $index, $nindex-$index));
                            $v = true;
                            if ( !self::testKey($k) ) $state     = 4;
                            else                $param[$k] = $v;
                            break;
                        case 1: // =
                            $k = trim(substr($text, $index, $nindex-$index));
                            if ( !self::testKey($k) ) $state = 4;
                            else                $state = 1;
                            break;
                        case 2: // "
                            $state = 4; // error
                            break;
                        case 4: // white only
                            $state = 3; // end
                            break;
                    }
                    break;
                case 1:
                    switch ( $nActChar ){
                        case 0: // ;
                        case 3: // null
                        case 4: // white only
                            $v = trim(substr($text, $index, $nindex-$index));
                            if ($v == "" ){
                                msg("extractParamtext: parse warning: empty value after =.",-1);
                                $v = true;
                            }
                            $param[$k] = $v;
                            $state = 0;
                            break;
                        case 1: // =
                            msg("extractParamtext: parse error: 2x = in one expr.",-1);
                            $state = 4;
                            break;
                        case 2: // "
                            if ( trim(substr($text, $index, $nindex-$index)) == "" ) {
                                $state = 2;
                            } else {
                                msg("extractParamtext: parse error: chars between = and \".",-1);
                                $state = 4;
                            }
                            break;
                    }
                    break;
                case 2:
                    $nindex = strlen($text)+1;
                    for ( $i=$index; $i < strlen($text); $i++ ) {
                        if ( $text[$i] == $packer ){
                            $nindex = $i + 1;
                            break;
                        }
                    }
                    $v = substr($text, $index, $nindex-$index-1);
                    if ($v == "" ) $v = true;
                    $param[$k] = $v;
                    $state = 0;
                    break;
                case 3:
                    return $param;
                case 4:
                    return $param;
            }
            $index = $nindex + 1;
        }
    }

    /**
     * test if key is valid (for extractParamtext)
     * @author Lukáš Ledvina
     * @param string $key
     * @return bool
     * 
     *
     */
    private static function testKey( $text )
    {
        $ret = ctype_alnum($text);
        if ( !$ret ) msg("extractParamtext: parse error: Key \"".$text."\" is not valid",-1);
        return $ret;
    }

    /**
     * get next active char from text (for extractParamtext)
     * @author Lukáš Ledvina
     * @param string $text for parsing, $begin of parsing
     * @return array (position,type)
     * 
     *
     */
    private static function getNextActiveChar($text, $begin, $delimiter = ';', $sec_delimiter = '=', $packer = '"')
    {
        if ( trim( substr( $text, $begin ) ) == "" )
            return array(strlen($text), 4);
        for ( $i=$begin; $i < strlen($text); $i++ ) {
            switch ( $text[$i] ) {
                case $delimiter:
                    return array($i, 0);
                case $sec_delimiter:
                    return array($i, 1);
                case $packer:
                    return array($i, 2);
            }
        }
        return array(strlen($text), 3);
    }


    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param array $arr atributes 
     * @return string
     */
    public static function buildStyle($arr) {
        $r = "";
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                $r.=rawurldecode($key) . ':';
                $r.=rawurldecode($value) . ';';
            }
        } else {
            $r.=str_replace(',', ';', $arr);
        }

        return $r;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $dir
     * @param bool $subdir
     * @return array
     */
    public static function filefromdir($dir, $subdir = true) {
        if ($subdir) {
            $result = array();
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, array(".", ".."))) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                        $result = array_merge($result, self::filefromdir($dir . DIRECTORY_SEPARATOR . $value));
                    } else {
                        $result[] = $dir . DIRECTORY_SEPARATOR . $value;
                    }
                }
            }
        } else {
            $result = array();
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, array(".", ".."))) {
                    if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                        $result[] = $dir . DIRECTORY_SEPARATOR . $value;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 
     * @param int $l lenght of string
     * @return string 
     */
    public static function _generate_rand($l = 5) {

        $r = '';
        $seed = str_split('1234567890abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        shuffle($seed);
        foreach (array_rand($seed, $l) as $k) {
            $r .= $seed[$k];
        }
        return (string) $r;
    }

    public static function _is_even($i) {
        if ($i % 2) {
            return 'even';
        } else {
            return 'odd';
        }
    }

}

/**
 * extend Doku html.php
 */

/**
 * @author Michal Červeňák <miso@fykos.cz>
 * @param string $name
 * @param string $class
 * @param array $params
 * @return string
 */
function html_facebook_btn($name = 'Share on FaceBook', $class = 'btn-social btn-facebook', $params = array()) {
    $r.= '<button  ' . buildAttributes($params) . ' class="' . $class . '">';
    $r.= '<i class="fa fa-facebook"></i>';
    $r.= $name . '</button>';
    return $r;
}

/**
 * @author Michal Červeňák <miso@fykos.cz>
 * @param string $name
 * @param string $class
 * @param array $params
 * @return string
 */
function html_button($name = 'btn', $class = 'btn', $params = array()) {
    $r.='<button ' . buildAttributes($params) . ' class="' . $class . '">';
    $r.=$name;
    $r.= '</button>';
    return $r;
}

function FKS_html_open_tag($tag, $attr = array()) {
    
    return '<' . $tag . ' ' . buildAttributes($attr) . '>';
}

function html_open_tag($tag, $attr = array()) {
    
    return FKS_html_open_tag($tag, $attr );
}

function html_close_tag($tag) {
    
    return FKS_html_close_tag($tag);
}

function FKS_html_close_tag($tag) {
    return '</' . $tag . '>';
}

function html_make_tag($tag, $attr = array()) {
   
    return FKS_html_make_tag($tag, $attr);
    
}
function FKS_html_make_tag($tag, $attr = array()) {
   
    return '<' . $tag . ' ' . buildAttributes($attr) . '/>';
}
