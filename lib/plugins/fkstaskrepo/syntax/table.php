<?php

/**
 * DokuWiki Plugin fkstaskrepo (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_fkstaskrepo_table extends DokuWiki_Syntax_Plugin {
    /**
     *
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    function __construct() {
        $this->helper = $this->loadHelper('fkstaskrepo');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getSort() {
        return 165; // whatever
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<fkstaskrepotable\b.*?/>', $mode, 'plugin_fkstaskrepo_table');
    }

    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        preg_match('/lang="([a-z]+)"/', substr($match, 18, -2), $m);
        $lang = $m[1];
        return [$state, $lang];
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        list($state, $lang) = $data;
        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                if ($mode == 'xhtml') {
                    $renderer->nocache();
                    $this->showMainSearch($renderer, null, $lang);
                    $this->showTagSearch($renderer, null, $lang);
                    $this->showResults($renderer, $lang);
                    return true;
                } else if ($mode == 'metadata') {
                    return true;
                }
                break;
            default:
                return false;
        }
        return false;
    }

    private function showMainSearch(Doku_Renderer &$renderer, $data, $lang) {
        global $ID, $lang;
        // if(substr($ID,-1,1) == 's'){
        // $searchNS = substr($ID,0,-1);
        // }else{
        // $searchNS = $ID;
        // }
        // $form = new \dokuwiki\Form\Form();
        // form->setHiddenField('do',"search");
        // $form->addTextInput('tag',$this->getLang('Search!'));
        // $form->attr('id','taskrepo-search');
        // $R->doc .= $form->toHTML();
        // $R->doc .= '<form action="' . wl() . '" accept-charset="utf-8" class="fkstaskrepo-search" id="dw__search2" method="get"><div class="no">' . NL;
        // $R->doc .= '  <input type="hidden" name="do" value="search" />' . NL;
        // $R->doc .= '  <input type="hidden" id="dw__ns" name="ns" value="' . $searchNS . '" />' . NL;
        // $R->doc .= '  <input type="text" id="qsearch2__in" accesskey="f" name="id" class="edit" />' . NL;
        // $R->doc .= '  <input type="submit" value="' . $lang['btn_search'] . '" class="button" />' . NL;
        // $R->doc .= '  <div id="qsearch2__out" class="ajax_qsearch JSpopup"></div>' . NL;
        // $R->doc .= '</div></form>' . NL;
    }

    private function showTagSearch(&$renderer, $data, $lang) {
        global $INPUT;
        $renderer->doc .= '<p class="task-repo tag-cloud">';
        $tags = $this->helper->getTags();
        $max = array_reduce($tags, function ($max, $row) {
            return ($row['count'] > $max) ? $row['count'] : $max;
        }, 0);

        foreach ($tags as $row) {
            $max = $row['count'] > $max ? $row['count'] : $max;
        }
        $selectedTag = $INPUT->str(helper_plugin_fkstaskrepo::URL_PARAM);
        foreach ($tags as $row) {
            $size = ceil(10 * $row['count'] / $max);
            $renderer->doc .= $this->helper->getTagLink($row['tag'], $size, $lang, $row['count'], ($selectedTag == $row['tag']));
        }
    }

    private function showResults(&$renderer, $lang) {
        global $INPUT, $ID;
        $tag = $INPUT->str(helper_plugin_fkstaskrepo::URL_PARAM);
        if ($tag) {
            $problems = $this->helper->getProblemsByTag($tag);
            $total = count($problems);
            $problems = array_slice($problems, 10*($INPUT->int('p', 1)-1), 10);

            $renderer->doc .= '<h2> <span class="fa fa-tag"></span>' . hsc($this->getLang('tag__' . $tag)) . '</h2>';

            $renderer->doc .= $paginator = $this->helper->renderSimplePaginator(ceil($total/10), $ID, [helper_plugin_fkstaskrepo::URL_PARAM => $tag]);;

            foreach ($problems as $problemDet) {
                list($year, $series, $problem) = $problemDet;
                $renderer->doc .= p_render('xhtml', p_get_instructions('<fkstaskrepo lang="' . $lang . '" full="true" year="' . $year . '" series="' . $series . '" problem="' . $problem . '"/>'), $info);
            }

            $renderer->doc .= $paginator;
        }
    }
}
