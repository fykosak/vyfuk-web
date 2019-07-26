<?php
/**
 * This syntax plugin replaces <fkstaskrepobatchpdf year="" series="" lang="" /> with link to brochure, serial and
 * yearbook if exists.
 * Path is defined in the config and it is capable to show the czech brochure on the english site.
 *
 * DokuWiki Plugin fkstaskrepo (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Štěpán Stenchlák <stenchlak@fykos.cz>
 */

class syntax_plugin_fkstaskrepo_batchpdf extends DokuWiki_Syntax_Plugin {

    /**
     *
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    function __construct() {
        $this->helper = $this->loadHelper('fkstaskrepo');
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
        return 164; // whatever
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<fkstaskrepobatchpdf\s.*?/>', $mode, 'plugin_fkstaskrepo_batchpdf');
    }

    /**
     * Handle matches of the fkstaskrepo syntax
     *
     * @param string $match The match of the syntax
     * @param int $state The state of the handler
     * @param int $pos The position in the document
     * @param Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        global $conf;

        // Extract attributes from $match to $attr
            $attr = [];
            // @see https://stackoverflow.com/a/38305337
            preg_match_all("/((?:(?!\\s|=).)*)\\s*?=\\s*?[\"']?((?:(?<=\")(?:(?<=\\\\)\"|[^\"])*|(?<=')(?:(?<=\\\\)'|[^'])*)|(?:(?!\"|')(?:(?!\\/>|>|\\s).)+))/", $match, $rawattributes);
            foreach ($rawattributes[1] as $i => $j) {
                $attr[$j] = $rawattributes[2][$i];
            }

        $attr['lang'] = $attr['lang'] ? $attr['lang'] : $conf['lang']; // Modify lang

        if (!$attr['prefer'] || $attr['prefer'] === 'brochure') {
            $attr['brochure_path'] = vsprintf($this->getConf('brochure_path_' . $attr['lang']), [$attr['year'], $attr['series']]); // Add path
            $attr['brochure_path'] = file_exists(mediaFN( $attr['brochure_path'])) ?  $attr['brochure_path'] : null; // Remove path if not exists
            // Include original cs brochure to en (if exists obviously)
            $attr['brochure_original'] = vsprintf($this->getConf('brochure_path_cs'), [$attr['year'], $attr['series']]);
            $attr['brochure_original'] = file_exists(mediaFN( $attr['brochure_original'])) && $attr['lang'] !== 'cs' ?  $attr['brochure_original'] : null; // Remove path to original brochure if not exists, or in case lang == cs
        }

        // Czech Yearbook
        if (!$attr['prefer'] || $attr['prefer'] === 'yearbook') {
            $attr['yearbook_original'] = vsprintf($this->getConf('yearbook_path_cs'), [$attr['year']]);
            $attr['yearbook_original'] = file_exists(mediaFN($attr['yearbook_original'])) ? $attr['yearbook_original'] : null; // Remove path to if not exists
        }

        // Czech Serial
        if (!$attr['prefer'] || $attr['prefer'] === 'serial') {
            $attr['serial_original'] = vsprintf($this->getConf('serial_path_cs'), [$attr['year'], $attr['series']]);
            $attr['serial_original'] = file_exists(mediaFN($attr['serial_original'])) ? $attr['serial_original'] : null; // Remove path to if not exists
        }

        return $attr;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, \Doku_Renderer $renderer, $data) {
        switch ($mode) {
            case 'xhtml':
                // Year book and serial are only in Czech
                if ($data['yearbook_original']) {
                    $renderer->doc .= '<div class="seriespdf yearbook">';
                    $renderer->internalmedia($data['yearbook_original'], vsprintf($this->helper->getSpecLang('year_book',$data['lang']), [$data['year'], $data['series']]),null,null,null,null,'linkonly');
                    $renderer->doc .= '</div>';
                }

                if ($data['brochure_original']) {
                    $renderer->doc .= '<div class="seriespdf brochure brochure-original">';
                    $renderer->internalmedia($data['brochure_original'], vsprintf($this->helper->getSpecLang('brochure_original',$data['lang']), [$data['year'], $data['series']]),null,null,null,null,'linkonly');
                    $renderer->doc .= '</div>';
                }
                if ($data['brochure_path']) {
                    $renderer->doc .= '<div class="seriespdf brochure brochure-default">';
                    $renderer->internalmedia($data['brochure_path'], vsprintf($this->helper->getSpecLang('brochure',$data['lang']), [$data['year'], $data['series']]),null,null,null,null,'linkonly');
                    $renderer->doc .= '</div>';
                }

                if ($data['serial_original']) {
                    $renderer->doc .= '<div class="seriespdf serial">';
                    $renderer->internalmedia($data['serial_original'], vsprintf($this->helper->getSpecLang('serial',$data['lang']), [$data['year'], $data['series']]),null,null,null,null,'linkonly');
                    $renderer->doc .= '</div>';
                }
                return true;
            default:
                return true;
        }
    }
}

