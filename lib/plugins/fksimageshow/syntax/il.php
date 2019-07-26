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

class syntax_plugin_fksimageshow_il extends DokuWiki_Syntax_Plugin {

    /**
     *
     * @var helper_plugin_fksimageshow 
     */
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
        $this->Lexer->addSpecialPattern('\{\{il\>.+?\}\}',$mode,'plugin_fksimageshow_il');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $matches = array();
        preg_match('/\{\{il\>(.+?)\}\}/',$match,$matches);
        list(,$p) = $matches;
        $data = $this->helper->parseIlData($p);
        return array($state,array($data));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {
        global $ID;
        if($mode == 'xhtml'){

            /** @var Do ku_Renderer_xhtml $renderer */
            list($state,$matches) = $data;
            list($data) = $matches;

            $param = array('class' => 'imageShow imagelink');

            if(!page_exists($data['href'])){
                if(auth_quickaclcheck($ID) >= AUTH_EDIT){
                    $renderer->nocache();
                    msg('page not exist: '.$data['href'],-1,null,null,MSG_EDIT);
                    $param['class'].=' naught';
                }
            }
            $img_size = 360;
            switch ($data['position']) {
                case "left":
                    $param['class'].=' left';
                    break;
                case "right":
                    $param['class'].=' right';
                    break;
                default :
                    $param['class'].=' center';
                    break;
            }
            if($data['image'] == null){
                $renderer->nocache();
                $renderer->doc .='<a href="'.(preg_match('|^http[s]?://|',trim($data['href'])) ? htmlspecialchars($data['href']) : wl(cleanID($data['href']))).'">'.htmlspecialchars($data['label']).'</a>';
            }else{

                $renderer->doc .=$this->helper->printIlImageDiv($data['image']['id'],$data['label'],$data['href'],$img_size,$param);
            }
        }

        return false;
    }

}
