<?php

/**
 * DokuWiki Plugin fksdbexport (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class syntax_plugin_fksdbexport extends DokuWiki_Syntax_Plugin {

    const REFRESH_AUTO = 'auto';
    const REFRESH_MANUAL = 'manual';
    const TEMPLATE_DOKUWIKI = 'dokuwiki';
    const TEMPLATE_XSLT = 'xslt';
    const TEMPLATE_JS = 'js';
    const SOURCE_EXPORT = 'export';
    const SOURCE_EXPORT1 = 'export1';
    const SOURCE_EXPORT2 = 'export2';
    const SOURCE_RESULT_DETAIL = 'results.detail';
    const SOURCE_RESULT_CUMMULATIVE = 'results.cummulative';
    const SOURCE_RESULT_SCHOOL_CUMMULATIVE = 'results.school-cummulative';

    /**
     * @var helper_plugin_fksdownloader
     */
    private $downloader;

    function __construct() {
        $this->downloader = $this->loadHelper('fksdownloader');
    }

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
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 165; //just copied Doodle or whatever
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<fksdbexport\b.*?>.*?</fksdbexport>',$mode,'plugin_fksdbexport');
    }

    /**
     * Handle matches of the fksdbexport syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match,$state,$pos,Doku_Handler $handler) {
        $match = substr($match,13,-14);              // strip markup (including space after "<fksdbexport ")
        list($parameterString,$templateString) = preg_split('/>/u',$match,2);

        $params = $this->parseParameters($parameterString);

        $qid = $params['qid'];
        $queryParameters = $params['parameters'];
        $exportId = helper_plugin_fksdownloader::getExportId($qid,$queryParameters);

        if($params['refresh'] == self::REFRESH_AUTO){
            $source = $this->autoRefresh($params);
        }else if($params['refresh'] == self::REFRESH_MANUAL){
            $source = $this->manualRefresh($params);
        }

        $content = $this->prepareContent($params,$source,$templateString);

        $instructions = array($params,$templateString,$exportId,$content);
        return $instructions;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode,Doku_Renderer $renderer,$data) {
        list($params,$template,$exportId,$content) = $data;

        if($mode == 'xhtml'){
            if($params['refresh'] == self::REFRESH_AUTO){
                $content = $this->prepareContent($params,$this->autoRefresh($params),$template);
            }

            if($content === null){
                $renderer->doc .= $this->getLang('missing_data');
                $renderer->nocache();
                return true;
            }

            if($params['template'] == self::TEMPLATE_DOKUWIKI){
                $renderer->doc .= p_render($mode,$content,$info);
            }else if($params['template'] == self::TEMPLATE_XSLT || $params['template'] == self::TEMPLATE_JS){
                $renderer->doc .= $content;
            }

            return true;
        }else if($mode == 'metadata'){
            if($params['refresh'] == self::REFRESH_MANUAL && $content !== null){
                $renderer->meta[$this->getPluginName()][$exportId]['version'] = $params['version'];
            }else if($params['refresh'] == self::REFRESH_AUTO){
                $expiration = $params['expiration'] !== null ? $params['expiration'] : $this->getConf('expiration');
                if(isset($renderer->meta['date']['valid']['age'])){
                    $renderer->meta['date']['valid']['age'] = min($renderer->meta['date']['valid']['age'],$expiration);
                }else{
                    $renderer->meta['date']['valid']['age'] = $expiration;
                }
            }
            if($params['template_file']){
                $templateFile = wikiFN($params['template_file']);
                if(isset($renderer->meta['relation']['fksdbexport'])){
                    $renderer->meta['relation']['fksdbexport'][] = $templateFile;
                }else{
                    $renderer->meta['relation']['fksdbexport'] = array($templateFile);
                }
            }

            return true;
        }
        return false;
    }

    /**
     * @note Modified Doodle2 plugin.
     * 
     * @param type $parameterString
     */
    private function parseParameters($parameterString) {
        //----- default parameter settings
        $params = array(
            'qid' => null,
            'parameters' => array(
                'contest' => $this->getConf('contest'),
            ),
            'refresh' => self::REFRESH_AUTO,
            'version' => 0,
            'expiration' => null,
            'template' => self::TEMPLATE_DOKUWIKI,
            'template_file' => null,
            'source' => self::SOURCE_EXPORT1
        );

        //----- parse parameteres into name="value" pairs  
        preg_match_all("/(\w+?)=\"(.*?)\"/",$parameterString,$regexMatches,PREG_SET_ORDER);
        //debout($parameterStr);
        //debout($regexMatches);
        for ($i = 0; $i < count($regexMatches); $i++) {
            $name = strtolower($regexMatches[$i][1]);  // first subpattern: name of attribute in lowercase
            $value = $regexMatches[$i][2];              // second subpattern is value
            if(strcmp($name,"qid") == 0){
                $params['qid'] = trim($value);
            }else if(strcmp(substr($name,0,6),"param_") == 0){
                $key = substr($name,6);
                $params['parameters'][$key] = $value;
            }else if(strcmp($name,"refresh") == 0){
                if($value == self::REFRESH_AUTO){
                    $params['refresh'] = self::REFRESH_AUTO;
                }else if($value == self::REFRESH_MANUAL){
                    $params['refresh'] = self::REFRESH_MANUAL;
                }else{
                    msg(sprintf($this->getLang('unexpected_value'),$value),-1);
                }
            }else if(strcmp($name,"version") == 0){
                $params['version'] = trim($value);
                $params['refresh'] = self::REFRESH_MANUAL; // implies manual refresh
            }else if(strcmp($name,"template_file") == 0){
                $params['template_file'] = trim($value);
                $params['template'] = self::TEMPLATE_XSLT; // implies XSL transformation
            }else if(strcmp($name,"expiration") == 0){
                if(!is_numeric($value)){
                    msg($this->getLang('expected_number'),-1);
                }
                $params['expiration'] = trim($value);
            }else if(strcmp($name,"template") == 0){
                if($value == self::TEMPLATE_DOKUWIKI){
                    $params['template'] = self::TEMPLATE_DOKUWIKI;
                }else if($value == self::TEMPLATE_XSLT){
                    $params['template'] = self::TEMPLATE_XSLT;
                }else if($value == self::TEMPLATE_JS){
                    $params['template'] = self::TEMPLATE_JS;
                }else{
                    msg(sprintf($this->getLang('unexpected_value'),$value),-1);
                }
            }else{
                $found = false;
                foreach ($params as $paramName => $default) {
                    if(strcmp($name,$paramName) == 0){
                        $params[$name] = trim($value);
                        $found = true;
                        break;
                    }
                }
                if(!$found){
                    msg(sprintf($this->getLang('unexpected_value'),$name),-1);
                }
            }
        }
        // check validity
        if(in_array($params['source'], array(self::SOURCE_RESULT_CUMMULATIVE, self::SOURCE_RESULT_DETAIL, self::SOURCE_RESULT_SCHOOL_CUMMULATIVE))){
            foreach (array('contest','year','series') as $paramName) {
                if(!isset($params['parameters'][$paramName])){
                    msg(sprintf($this->getLang('missing_parameter'),$paramName),-1);
                }
            }
            if($params['source'] == self::SOURCE_RESULT_CUMMULATIVE || $params['source'] == self::SOURCE_RESULT_SCHOOL_CUMMULATIVE){
                $params['series'] = explode(' ',$params['series']);
            }
        }
        return $params;
    }

    private function prepareContent($params,$content,$templateString) {
        global $ID;
        if($content === null){
            return null;
        }

        $xml = new DomDocument;
        $xml->loadXML($content);

        if($params['template'] == self::TEMPLATE_DOKUWIKI){
            $xpath = new DOMXPath($xml);
            $needles = array();
            //preg_match('#\s*(<header\s*>(.*)</header>)?(.*?)(<footer\s*>(.*)</footer>)?#', $templateString, $matches);

            $m = preg_match('#^\s*(<header>(.*)</header>)?(.+)(<footer>(.*)</footer>)?\s*$#s', $templateString, $matches);
            $rowTemplate = trim($matches[3]);

            $header = $matches[2];
            $footer = $matches[5];

            foreach ($xpath->query('//column-definitions/column-definition') as $iter) {
                $name = $iter->getAttribute('name');
                $needles[] = '@'.$name.'@';
            }
            $needles[] = '@iterator0@';
            $needles[] = '@iterator@';

            $source = $header."\n";
            $iterator = 0;
            foreach ($xpath->query('//data/row') as $row) {
                $replacements = array();
                foreach ($row->childNodes as $child) {
                    if(isset($child->tagName)){ /* XML content may be interleaved with text nodes */
                        $replacements[] = $child->textContent;
                    }
                }
                $replacements[] = $iterator++;
                $replacements[] = $iterator;

                $source .= str_replace($needles,$replacements,$rowTemplate)."\n";
            }
            $source .= $footer."\n";

            return p_get_instructions($source);
        }else if($params['template'] == self::TEMPLATE_XSLT){
            if($params['template_file']){
                $templateFile = wikiFN($params['template_file']);
                $templateString = io_readFile($templateFile);
            }

            if(!class_exists('XsltProcessor')){
                msg($this->getLang('xslt_missing'),-1);
                return null;
            }

            $xsltproc = new XsltProcessor();
            $xsl = new DomDocument;
            $xsl->loadXML(trim($templateString));
            //$xsltproc->registerPHPFunctions(); // TODO verify need of this
            $xsltproc->importStyleSheet($xsl);
            $result = $xsltproc->transformToXML($xml);

            if($result === false){
                foreach (libxml_get_errors() as $e) {
                    msg($e->message,-1);
                }
                $e = libxml_get_last_error();
                if($e){
                    msg($e->message,-1);
                }
                $result = null;
            }
            return $result;
        }elseif($params['template'] == self::TEMPLATE_JS){
            /** @TODO just for debuging */
            //var_dump($content);
            $xpath = new DOMXPath($xml);


            $json = [];

            foreach ($xpath->query('//data/row') as $row) {
                $jsonRow = [];

                foreach ($row->childNodes as $child) {
                    $jsonRow[$child->tagName] = $child->textContent;
                }
                $json[] = $jsonRow;
            }
            // var_dump($templateString);

            $e = json_encode($json);
            $cashe = new cache($this->getPluginName()."_".md5($params.$ID),'.js');
            if(!$cashe->useCache()){

                $cashe->storeCache($templateString);
            }
          
            return '<div class="fksdbexport js-renderer" data="'.htmlspecialchars($e).'" data-js="'.htmlspecialchars($templateString).'"></div>';

        }
    }

    private function autoRefresh($params) {
        $expiration = $params['expiration'] !== null ? $params['expiration'] : $this->getConf('expiration');
        return $this->download($expiration,$params);
    }

    private function manualRefresh($params) {
        global $ID;
        $desiredVersion = $params['version'];
        $key = $this->getPluginName().' '.helper_plugin_fksdownloader::getExportId($params['qid'],$params['parameters']);
        $metadata = p_get_metadata($ID,$key);
        $downloadedVersion = $metadata['version'];

        if($downloadedVersion === null || $desiredVersion > $downloadedVersion){
            return $this->download(helper_plugin_fksdownloader::EXPIRATION_FRESH,$params);
        }else{
            return $this->download(helper_plugin_fksdownloader::EXPIRATION_NEVER,$params);
        }
    }

    private function download($expiration,$params) {
        $parameters = $params['parameters'];

        switch ($params['source']) {
            case self::SOURCE_EXPORT:
            case self::SOURCE_EXPORT1:
            case self::SOURCE_EXPORT2:
                $version = ($params['source'] === self::SOURCE_EXPORT) ? 1 : (int) substr($params['source'],strlen(self::SOURCE_EXPORT));
                return $this->downloader->downloadExport($expiration,$params['qid'],$params['parameters'],$version);
                break;
            case self::SOURCE_RESULT_DETAIL:
                return $this->downloader->downloadResultsDetail($expiration,$parameters['contest'],$parameters['year'],$parameters['series']);
                break;
            case self::SOURCE_RESULT_CUMMULATIVE:
                return $this->downloader->downloadResultsCummulative($expiration,$parameters['contest'],$parameters['year'],explode(' ',$parameters['series']));
                break;
            case self::SOURCE_RESULT_SCHOOL_CUMMULATIVE:
                return $this->downloader->downloadResultsSchoolCummulative($expiration,$parameters['contest'],$parameters['year'],explode(' ',$parameters['series']));
                break;
            default:
                msg(sprintf($this->getLang('unexpected_value'),$params['source']),-1);
                break;
        }
    }

}

// vim:ts=4:sw=4:et:
