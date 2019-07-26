<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if(!defined('DOKU_INC')){
    die();
}
if(!defined('DOKU_PLUGIN')){
    define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
}
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeed extends DokuWiki_Syntax_Plugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array('formatting','substition','disabled');
    }

    public function getSort() {
        return 24;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed\>.+?\}\}',$mode,'plugin_fksnewsfeed_fksnewsfeed');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $text = str_replace(array("\n",'{{fksnewsfeed>','}}'),array('','',''),$match);
        /** @var id and even this NF $param */
        $param = $this->helper->FKS_helper->extractParamtext($text);
        return array($state,array($param));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {
        // $data is what the function handle return'ed.
        if($mode == 'xhtml'){
            /** @var Do ku_Renderer_xhtml $renderer */
            list(,list($param) ) = $data;
            $renderer->doc .= $this->rendernews($param);
        }
        return false;
    }

    private function rendernews($param = array()) {
        $data = $this->helper->load_news_simple($param["id"]);
        if(empty($data)){
            return '<div class="FKS_newsfeed_exist_msg">'.$this->getLang('news_non_exist').'</div>';
        }
        // if template not found use default 
        $tpl_path = wikiFN($this->getConf('tpl'));
        if(!file_exists($tpl_path)){
            $def_tpl = DOKU_PLUGIN.plugin_directory('fksnewsfeed').'/tpl.html';
            io_saveFile($tpl_path,io_readFile($def_tpl));
        }
        //load empty template 
        $tpl = io_readFile($tpl_path);


        $data['text'] = p_render('xhtml',p_get_instructions($data['text']),$info);
        $data['newsdate'] = $this->newsdate($data['newsdate']);
        $img_attr = array('style'=>'width:100%;');
        $data['image'] = '<img src="'.ml($data['image']).'" alt="newsfeed" '.buildAttributes($img_attr).'>';


        foreach (helper_plugin_fksnewsfeed::$Fields as $k) {
            $tpl = str_replace('@'.$k.'@',$data[$k],$tpl);
        }
        if(!isset($param['even'])){
            $param['even'] = 'FKS_newsfeed_even';
        }
        return '<div class="'.$param['even'].'" data-id="'.$param["id"].'">'.$tpl.'</div><div class="FKS_newsfeed_edit" data-id="'.$param["id"].'"></div>';
    }

    private function newsdate($date) {
        $enmonth = Array('January','February','March',
            'April','May','June',
            'July','August','September',
            'October','November','December');
        $langmonth = Array(
            $this->getLang('jan'),
            $this->getLang('feb'),
            $this->getLang('mar'),
            $this->getLang('apr'),
            $this->getLang('may'),
            $this->getLang('jun'),
            $this->getLang('jul'),
            $this->getLang('aug'),
            $this->getLang('sep'),
            $this->getLang('oct'),
            $this->getLang('now'),
            $this->getLang('dec')
        );
        return (string) str_replace($enmonth,$langmonth,$date);
    }

}
