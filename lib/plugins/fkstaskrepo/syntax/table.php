<?php

/**
 * DokuWiki Plugin fkstaskrepo (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class syntax_plugin_fkstaskrepo_table extends DokuWiki_Syntax_Plugin {

    const URL_PARAM = 'tasktag';

    /**
     * @var helper_plugin_fksdownloader
     */
    private $downloader;

    /**
     *
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    function __construct() {
        $this->downloader = $this->loadHelper('fksdownloader');
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
        return 165; // whatever
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<fkstaskrepotable\b.*?/>', $mode, 'plugin_fkstaskrepo_table');
    }

    /**
     * Handle matches of the fkstaskrepo syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        // TODO parameters?
        return array();
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {
        if ($mode == 'xhtml') {
            $renderer->nocache();
            $this->showMainSearch($renderer, $data);
            $this->showTagSearch($renderer, $data);
            $this->showResults($renderer, $data);
            return true;
        } else if ($mode == 'metadata') {
            return true;
        }

        return false;
    }

    private function showMainSearch(&$R, $data) {
        global $ID, $lang;
        if (substr($ID, -1, 1) == 's') {
            $searchNS = substr($ID, 0, -1);
        } else {
            $searchNS = $ID;
        }

        $R->doc .= '<form action="' . wl() . '" accept-charset="utf-8" class="fkstaskrepo-search" id="dw__search2" method="get"><div class="no">' . NL;
        $R->doc .= '  <input type="hidden" name="do" value="search" />' . NL;
        $R->doc .= '  <input type="hidden" id="dw__ns" name="ns" value="' . $searchNS . '" />' . NL;
        $R->doc .= '  <input type="text" id="qsearch2__in" accesskey="f" name="id" class="edit" />' . NL;
        $R->doc .= '  <input type="submit" value="' . $lang['btn_search'] . '" class="button" />' . NL;
        $R->doc .= '  <div id="qsearch2__out" class="ajax_qsearch JSpopup"></div>' . NL;
        $R->doc .= '</div></form>' . NL;
    }

    private function showTagSearch(&$R, $data) {
        global $ID;
        $R->doc .= '<p class="fkstaskrepo-tagcloud">';

        $tags = $this->helper->getTags(); // TODO lang
        $max = 0;
        foreach ($tags as $row) {
            $max = $row['count'] > $max ? $row['count'] : $max;
        }

        foreach ($tags as $row) {
            $size = ceil(10 * $row['count'] / $max);
            $R->doc .= '<a href="' . wl($ID, array(self::URL_PARAM => $row['tag'])) . '" class="size' . $size . '">' . hsc($row['tag']) . '</a> ';
        }
    }

    private function showResults(&$R, $data) {
        global $INPUT;

        $tag = $INPUT->get->str(self::URL_PARAM);
        if ($tag) {
            $problems = $this->helper->getProblems($tag); // TODO lang
            foreach ($problems as $problemDet) {
                list($year, $series, $problem) = $problemDet;
                try {
                    $data = $this->helper->getProblemData($year, $series, $problem);
                    $R->doc .= p_render('xhtml', syntax_plugin_fkstaskrepo_entry::prepareContent($data, $this->getConf('task_template_search')), $info);
                } catch (fkstaskrepo_exception $e) {
                    msg($e->getMessage(), -1);
                }
            }
        }
    }

}

// vim:ts=4:sw=4:et:
