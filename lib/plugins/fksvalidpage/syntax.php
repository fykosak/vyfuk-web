<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if (!defined('DOKU_INC')) {
    die();
}


class syntax_plugin_fksvalidpage extends DokuWiki_Syntax_Plugin {
    
     public function __construct() {
        $this->helper = $this->loadHelper('fksvalidpage');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fkssearch\}\}', $mode, 'plugin_fksvalidpage');
    }

    //public function postConnect() { $this->Lexer->addExitPattern('</fkstimer>','plugin_fkstimer'); }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        //$match = substr($match, 12, -13);
        //$newsauthor=preg_split('/-/', $match);
        //$to_page.=$newsauthor[1];
        //$to_page.=$newsauthor[0];
        ob_start();        
        
        foreach ($this->helper->getallPages() as $key=>$value){
            $to_page.= $value;
          $this->helper->istruehedaline($value) ;
        }
        $to_page.=ob_get_contents();
        ob_end_clean();
        echo $to_page;
        
        
        
        $form = new Doku_Form(array('onsubmit' => 'return false', 'id' => 'fkssearch'));
        $form->addElement(form_makeTextField('search', null, null, "search_input"));
        $form->addElement(form_makeButton('submit', null));
        ob_start();
        html_form('search', $form);
        $to_page.= ob_get_contents();
        ob_end_clean();
        
        $to_page.='<div id="search_div"></div>';
        //$pages= $this->helper->getallPages();
        //global $retH;
        //$this->helper->getH($pages);
        //$to_page.='<span style="White-space:pre">';
//print_r($retH);
        //print_r($this->helper->getallPages());  
        //$this->helper->countH($retH["h1"]);
        //ob_start();
        //print_r($retH);
        //$to_page.=ob_get_contents();
        //ob_end_clean();

        //$to_page.='</span>';
        //print_r($Hall);
        //foreach ($pages as $key => $value) {
        //    $data[$value] = preg_split('/\ /', io_readFile($value));
        //}


        //print_r($data);
        return array($state, array($to_page));
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($to_page) = $match;

            $renderer->doc .= $to_page;
        }
        return false;
    }

   /* private function extraxtH($page, $H,$type) {
        global $retH;
        foreach ($H as $K => $V) {
            if (!($K % 2)) {
                //echo $K;

                $H[$K] = null;
            } else {
                $retH[$type][$page] = $V;
            }
        }
        return true;
    }
*/
}
