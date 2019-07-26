<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/JpegMeta.php');

class syntax_plugin_fksimageshow_ilwrap extends DokuWiki_Syntax_Plugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksimageshow');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array();
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {

        $this->Lexer->addSpecialPattern('\{\{il-wrap\>.+?\}\}',$mode,'plugin_fksimageshow_ilwrap');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $matches = array();
        preg_match('/{{il-wrap>([\S\s]+)*}}/',$match,$matches);
        $ms = array();
        preg_match_all('/(.*)\s?/',$matches[1],$ms);
        $datas = array();
        foreach ($ms[1] as $m) {
            if($m == ''){
                continue;
            }
            $datas[] = $this->helper->parseIlData($m);
        }
        return array($state,array($datas));
    }

    public function render($mode,Doku_Renderer $renderer,$data) {
        if($mode == 'xhtml'){

            /** @var Do ku_Renderer_xhtml $renderer */
            list($state,$matches) = $data;
            list($datas) = $matches;
            $renderer->doc.='<div class="imageShowWrap">';
            foreach ($datas as $data) {
                $param = array('class' => 'imageShow imagelink');
                $img_size = 360;
                $renderer->doc .=$this->helper->printIlImageDiv($data['image']['id'],$data['label'],$data['href'],$img_size,$param);
            }
            $renderer->doc.='</div>';
        }



        return false;
    }

}
