<?php

/**
 * DokuWiki Plugin fkstaskrepo (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_fkstaskrepo_batchselect extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('<fkstaskreposelect\s.*?/>', $mode, 'plugin_fkstaskrepo_batchselect');
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
        preg_match('/lang="([a-z]+)"/', substr($match, 19, -2), $m);
        $lang = $m[1];

        $path = $this->getRegExpPath($lang);
        search($data, $conf['datadir'], 'search_allpages', [], '', -1);

        $data = array_filter($data, function ($a) use ($path) {
            return preg_match('/' . $path . '/', $a['id']);
        });
        $data = array_map(function ($a) use ($path, $lang) {
            list($a['year'], $a['series']) = $this->extractPathParameters($a['id'], $lang);
            return $a;
        }, $data);

        $pages = [];
        foreach ($data as $page) {
            $pages[$page['year']][$page['series']] = $page['id'];
        }
        return [$state, [$pages, $lang]];
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        $renderer->nocache();
        global $ID;
        list($state, list($pages, $lang)) = $data;
        list($currentYear, $currentSeries) = $this->extractPathParameters($ID, $lang);

        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                $renderer->nocache();
                $renderer->doc .= '<div class="task-repo batch-select col-xl-3 col-lg-4 col-md-5 col-sm-12 pull-right">';
                $this->renderHeadline($renderer, $lang);
                $this->renderYearSelect($renderer, $pages, $lang, $currentYear);
                $this->renderSeries($renderer, $pages, $lang, $currentYear, $currentSeries);
                $renderer->doc .= '</div>';
                return true;
            default:
                return false;
        }
    }

    private function renderHeadline(Doku_Renderer &$renderer, $lang) {
        $renderer->doc .= '<h4>' . $this->helper->getSpecLang('batch_select', $lang) . '</h4>';
    }

    private function renderSeries(Doku_Renderer &$renderer, $pages, $lang, $currentYear = null, $currentSeries = null) {
        foreach ($pages as $year => $batches) {
            $renderer->doc .= '<div class="year" ' . ($currentYear == $year ? '' : 'style="display:none"') . ' data-year="' . $year . '">';
            //$renderer->doc .= $this->helper->getSpecLang('series', $lang);
            $renderer->doc .= '<ul class="pagination">';
            foreach ($batches as $batch => $page) {
                $renderer->doc .= '<li class="page-item ' . ($currentSeries == $batch && $currentYear == $year ? 'active' : '') . '"><a class="page-link" href="' . wl($page) . '" >' . $batch . '</a></li>';
            }
            $renderer->doc .= '</ul>';
            $renderer->doc .= '</div>';
        }
    }

    private function renderYearSelect(Doku_Renderer &$renderer, $pages, $lang, $currentYear = null) {
        $renderer->doc .= '<select class="form-control mb-2" size="">';
        foreach ($pages as $year => $batches) {
            $renderer->doc .= ' <option value="' . $year . '" ' . ($year == $currentYear ? 'selected' : '') . '>' . $this->helper->getSpecLang('year', $lang) . ' ' . $year . '</option>';
        }
        $renderer->doc .= '</select>';
    }

    private function getRegExpPath($lang) {
        return preg_replace('/%[0-9]\$s/', '([0-9]+)', $this->getConf('page_path_mask_' . $lang));
    }

    private function extractPathParameters($id, $lang) {
        preg_match('/' . $this->getRegExpPath($lang) . '/', $id, $m);
        $currentYear = $m[1];
        $currentSeries = $m[2];
        return [$currentYear, $currentSeries];
    }
}
