<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_fksmathengine extends DokuWiki_Syntax_Plugin {

    private $data = array();

    public function __construct() {
        $this->helper = $this->loadHelper('fksmathengine');
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
        $this->Lexer->addSpecialPattern('\{\{fksmathengine>.+?\}\}', $mode, 'plugin_fksmathengine');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        $match = substr($match, 16, -2);



        $data = self::parsedata($match);

        $params = array();

        $param = $this->addvisible($data['vars']);
        $params = array_merge($params, $param);
        unset($param);


        $param = $this->addhidden($data['consts']);
        $params = array_merge($params, $param);
        unset($param);


        $param = $this->addoutput($data['math']);
        $params = array_merge($params, $param);
        unset($param);


        return array($state, array($params));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($params) = $match;
            
            $renderer->doc .='<div class="FKS_mathengine">';

/*
 * and now add to page visible 
 */

            foreach ($params['visible'] as $k => $v) {
                $renderer->doc .='<p>';
                $renderer->doc .='<span>' . $v['legend'][0] . ':</span>';
                $renderer->doc .='<input ' . buildAttributes($v['input']) . ' />';
                $renderer->doc .='<span>$' . $v['legend'][1] . '$</span>';
                $renderer->doc .= '</p>';
            }
            
            foreach ($params['hidden'] as $k => $v) {
                
                $renderer->doc .='<input ' . buildAttributes($v['input']) . ' />';
                
            }
            foreach ($params['output'] as $k => $v) {
                $renderer->doc.= $v['script'];
               
                $renderer->doc .='<button ' . buildAttributes($v['button']) . ' >'.$this->getLang('calculate').'</button>';
                
                
                $renderer->doc .='<p>';
                $renderer->doc .='<span>' . $v['legend'][0] . ':</span>';
                $renderer->doc .='<input ' . buildAttributes($v['input']) . ' />';
                $renderer->doc .='<span>$' . $v['legend'][1] . '$</span>';
                $renderer->doc .= '</p>';
                
            }

            $renderer->doc .='</div>';
        }
        return false;
    }

    private static function parsedata($match) {
        $match = str_replace("\n", '', $match);
        $data = explode(';', $match);

        foreach ($data as $key => $value) {
            list($k, $v) = preg_split('/:/', $value);
            $data[$k] = $v;
            unset($data[$key]);
        }

        foreach (array('var', 'const') as $value) {
            $data[$value . 's'] = preg_split('/,/', $data[$value]);
        }
        return $data;
    }

    private function addvisible($vars) {
        $param = array();
        foreach ($vars as $key => $value) {
            list($name, $values) = preg_split('/=/', $value);
            $name = str_replace(' ', '', $name);

            $param['visible'][] = array(
                'legend' => $this->getlabel($values),
                'input' => array('type' => 'text', 'id' => $name, 'name' => $name, 'value' => null, 'class' => 'edit FKS_mathengine_input')
            );
        }
        return $param;
    }

    private function addhidden($consts) {
        $param = array();
        foreach ($consts as $key) {
            list($name, $values) = preg_split('/=/', $key);
            $param['hidden'][] = array(
                'legend' => null,
                'input' => array('type' => 'hidden', 'id' => $name, 'name' => $name, 'value' => $values,'class' => 'FKS_mathengine_input')
            );
   
        }
        return $param;
    }
/**
 * 
 * @param type $mathem
 * @return type
 */
    private static function addoutput($mathem) {
         $param = array();
        list($name, $label, $math) = preg_split('/=/', $mathem);
        $script = self::getscript($math);
        $param['output'][] = array(
            'script' => $script,
            'legend' => self::getlabel($label),
            'button' => array('type' => 'submit', /*'onclick' => 'engine()',*/ 'class' => 'btn FKS_mathengine_btn'),
            'input' => array('id' => 'results', 'readonly' => 'redonly', 'class' => 'edit')
        );
       
        return $param;
    }
/**
 * 
 * @param type $math
 * @return string
 */
    private static function getscript($math) {
        $scr = '<script>
                function engine(){
                return ';
        $scr .= str_replace(array('\\', '{', '}'), array('Math.', 'document.getElementById("', '").value'), $math);
        $scr.=';};
                </script>';
        return $scr;
    }
/**
 * 
 * @param type $text
 * @return array
 */
    private static function getlabel($text) {
        return preg_split('/\|/', $text);
    }

}
