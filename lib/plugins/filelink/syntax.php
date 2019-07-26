<?php
/**
 * DokuWiki Plugin filelink (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Lukas Timko <lukast@fykos.cz>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_filelink extends DokuWiki_Syntax_Plugin {
    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'normal';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 1; //must be low to override {{.*?}} link
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{filelink>.+?}}',$mode,'plugin_filelink');
    }

    /**
     * Handle matches of the filelink syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){
        $raw_data = $this->tokenize($match);
            

        /* load configuration TODO make safe for public usage */
        $config = array(
            'icon' => ($raw_data['params']['icon'] == false)?$raw_data['params']['icon']:true,
            'size' => ($raw_data['params']['size'] == true)?$raw_data['params']['size']:false,
            'filesystem_root' => ($raw_data['params']['filesystem_root'] != null)?$raw_data['params']['filesystem_root']:$this->getConf('filesystem_root'),
            'web_root' => ($raw_data['params']['web_root'] != null)?$raw_data['params']['web_root']:$this->getConf('web_root'),
            'expiration' => is_numeric($raw_data['params']['expiration'])?$raw_data['params']['expiration']:$this->getConf('expiration'),
            'behavior' => (($raw_data['params']['behavior'] == 'hide')||($raw_data['params']['behavior'] == 'message'))?$raw_data['params']['behavior']:$this->getConf('behavior'),
            'message_text' => ($raw_data['params']['message_text'] != null)?$raw_data['params']['message_text']:$this->getConf('message_text'),
            'message_class' => ($raw_data['params']['message_class'] != null)?$raw_data['params']['message_class']:$this->getConf('message_class')
        );

        if($raw_data['url'] == null) return array('message_text' => ($config['behavior'] == 'message')?$config['message_text']:null, ($config['behavior'] == 'message')?'message_class':null => $config['message_class'], 'expiration' => $config['expiration']);

        if(!(file_exists($config['filesystem_root'].$raw_data['url']) && is_readable($config['filesystem_root'].$raw_data['url']))) return array('message_text' => ($config['behavior'] == 'message')?$config['message_text']:null, ($config['behavior'] == 'message')?'message_class':null => $config['message_class'], 'expiration' => $config['expiration']);

        $filesize = filesize($config['filesystem_root'].$raw_data['url']);

        return array('url' => $config['web_root'].$raw_data['url'], 'label' => $raw_data['label'], 'expiration' => $config['expiration'], 'size' => ($config['size'])?$this->filesizeToReadable($filesize):null, 'icon' => ($config['icon'])?$this->getIconUrl($config['filesystem_root'].$raw_data['url']):null);
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'xhtml'){
            if($data['url'] != null){
	            $renderer->doc .= "<a href=\"".$data['url']."\">"
                                .(($data['icon']!=null)?"<img src=\"".$data['icon']."\" />":"")
                                .$data['label'].(($data['size']!=null)?" (".$data['size'].")":"")
                                ."</a>";
            }
            else if($data['message_text'] != null){
                $renderer->doc .= $data['message_text'];
            }
            else{
                //$renderer->doc .= "vubec nic"; //jen testovaci
            }
            return true;
        }
        return false;
    }

/**************own methods********************/
    private function tokenize($match) {
        $match = substr($match, 11, -2); //trim '{{filelink>' and '}}'

        $url_pat = '[\/\.a-zA-Z0-9_-]+';
	    $param_name_pat = '[a-z][a-z0-9_]*';
	    $simple_value_pat = '[a-zA-Z0-9]+';
	    $quoted_value_pat = '"[^"]*"';
	    $param_pat = $param_name_pat.'=(?:'.$simple_value_pat.'|'.$quoted_value_pat.')';
        $params_pat = $param_pat.'(?:&'.$param_pat.')*';
        $label_pat = '(.+)';
        $pattern = '/^('.$url_pat.')(\?'.$params_pat.')?(?:\|'.$label_pat.')?$/';

        if(!preg_match($pattern, $match, $matches))
		    return array('url' => '', 'label' => '', 'params' => array());

	    $data['url'] = $matches[1];
	    $data['label'] = $matches[3];
	    $data['params'] = array();

	    if($matches[2]){
		    $pattern = '/(?P<names>'.$param_name_pat.')=(?P<values>'.$simple_value_pat.'|'.$quoted_value_pat.')/';
		    preg_match_all($pattern, $matches[2], $par_matches);
		    for($i=0; $i<count($par_matches['names']); $i++)
		    {
			    $data['params'][$par_matches['names'][$i]] = $par_matches['values'][$i];
		    }
	    }

        return $data;

    }

    private function getIconUrl($file){
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if(file_exists(DOKU_INC.'lib/images/fileicons/'.$extension.'.png')) {
            return DOKU_URL.'lib/images/fileicons/'.$extension.'.png';
        }
        else{
            return DOKU_URL.'lib/images/fileicons/file.png';
        }
    }

    private function filesizeToReadable($bytes){
        $unit = array('B', 'kB', 'MB', 'GB');
        $power = floor((strlen($bytes)-1)/3.0);
        return sprintf("%d ", $bytes/pow(1024, $power)).$unit[$power];
    }

}

// vim:ts=4:sw=4:et:
