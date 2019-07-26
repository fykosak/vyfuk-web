<?php

/**
 * DokuWiki Plugin fkstaskrepo (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

require_once 'tex_preproc.php';

require_once 'inc/Task.php';

class helper_plugin_fkstaskrepo extends DokuWiki_Plugin {

    /**
     * @var helper_plugin_fksdownloader
     */
    private $downloader;

    /**
     * @var helper_plugin_sqlite
     */
    private $sqlite;

    const URL_PARAM = 'tasktag';

    const XMLNamespace = 'http://www.w3.org/XML/1998/namespace';

    public function __construct() {
        $this->downloader = $this->loadHelper('fksdownloader');

// initialize sqlite
        $this->sqlite = $this->loadHelper('sqlite', false);
        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
            return;
        }
        if (!$this->sqlite->init($pluginName,
            DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)
        ) {
            msg($pluginName . ': Cannot initialize database.');
            return;
        }
    }

    /*     * **************
     * XML data
     */
    private function getPath($year, $series) {
        $mask = $this->getConf('remote_path_mask');
        return sprintf($mask, $year, $series);
    }

    public function getSeriesData($year, $series, $expiration = helper_plugin_fksdownloader::EXPIRATION_FRESH) {
        $path = $this->getPath($year, $series);
        return $this->downloader->downloadWebServer($expiration, $path);
    }

    public function getSeriesFilename($year, $series) {
        return $this->downloader->getCacheFilename($this->downloader->getWebServerFilename($this->getPath($year,
            $series)));
    }

    /**
     * Downloads document from the web server and saves it according the path mask as a media.
     * @param $year
     * @param $series
     * @param $remotePathMask
     * @param $localPathMask
     * @param int $expiration
     * @return bool|string
     */
    public function downloadDocument($year, $series, $remotePathMask, $localPathMask, $expiration = helper_plugin_fksdownloader::EXPIRATION_FRESH) {
        $content = $this->downloader->downloadWebServer($expiration, sprintf($remotePathMask, $year, $series));

        $res = io_saveFile(mediaFN($fileID = sprintf($localPathMask, $year, $series)), $content);

        return ($content && $res) ? $fileID : false;
    }

    /**
     * For now only Czech version is supported.
     * @param int $year
     * @param int $series
     * @param string $task IT IS LABEL, NOT A NUMBER
     * @param int $expiration
     * @return bool|string
     */
    public function downloadSolution($year, $series, $task, $expiration = helper_plugin_fksdownloader::EXPIRATION_FRESH) {
        $content = $this->downloader->downloadWebServer($expiration, vsprintf($this->getConf('remote_task_solution_path_mask'), [$year, $series, null, null, $this->labelToNumber($task)]));

        $res = io_saveFile(
            mediaFN($fileID = vsprintf(
                $this->getConf('solution_path_cs'),
                [$year, $series, strtolower($task)]
            )), $content
        );

        return ($content && $res) ? $fileID : false;
    }

    /*
     * Tags
     */

    public function storeTags($year, $series, $problem, $tags) {
        // const tableProblem="problem";
        // allocate problem ID
        $sql = 'SELECT problem_id FROM problem WHERE year = ? AND series = ? AND problem = ?';
        $res = $this->sqlite->query($sql, $year, $series, $problem);
        $problemId = $this->sqlite->res2single($res);
        if (!$problemId) {
            $this->sqlite->query('INSERT INTO problem (year, series, problem) VALUES(?, ?, ?)',
                $year,
                $series,
                $problem);
            $res = $this->sqlite->query($sql, $year, $series, $problem);
            $problemId = $this->sqlite->res2single($res);
        }
        // flush and insert tags
        $this->sqlite->query('begin transaction');

        $this->sqlite->query('DELETE FROM problem_tag WHERE problem_id = ?', $problemId);

        foreach ($tags as $tag) {
            // allocate tag ID
            $sql = 'SELECT tag_id FROM tag WHERE tag_cs = ?';
            $res = $this->sqlite->query($sql, $tag);
            $tagId = $this->sqlite->res2single($res);
            if (!$tagId) {
                $this->sqlite->query('INSERT INTO tag (tag_cs) VALUES(?)', $tag);
                $res = $this->sqlite->query($sql, $tag);
                $tagId = $this->sqlite->res2single($res);
            }
            $this->sqlite->query('INSERT INTO problem_tag (problem_id, tag_id) VALUES(?, ?)', $problemId, $tagId);
        }

        $this->sqlite->query('DELETE FROM tag WHERE tag_id NOT IN (SELECT tag_id FROM problem_tag)'); // garbage collection
        $this->sqlite->query('commit transaction');
    }

    /**
     * @param $year
     * @param $series
     * @param $problem
     * @return array
     */
    public function loadTags($year, $series, $problem) {
        $sql = 'SELECT problem_id FROM problem WHERE year = ? AND series = ? AND problem = ?';
        $res = $this->sqlite->query($sql, $year, $series, $problem);
        $problemId = $this->sqlite->res2single($res);

        if (!$problemId) {
            return [];
        }

        $res = $this->sqlite->query('SELECT t.tag_cs FROM tag t LEFT JOIN problem_tag pt ON pt.tag_id = t.tag_id WHERE pt.problem_id =?',
            $problemId);
        $result = [];
        foreach ($this->sqlite->res2arr($res) as $row) {
            $result[] = $row['tag_cs'];
        }
        return $result;
    }

    public function getTags() {
        $sql = 'SELECT t.tag_cs AS tag, count(pt.problem_id) AS count FROM tag t LEFT JOIN problem_tag pt ON pt.tag_id = t.tag_id GROUP BY t.tag_id ORDER BY 1';
        $res = $this->sqlite->query($sql);
        return $this->sqlite->res2arr($res);
    }

    public function getProblemsByTag($tag) {
        $sql = 'SELECT tag_id FROM tag WHERE tag_cs = ?';
        $res = $this->sqlite->query($sql, $tag);
        $tagId = $this->sqlite->res2single($res);
        if (!$tagId) {
            return [];
        }

        $res = $this->sqlite->query('SELECT DISTINCT p.year, p.series, p.problem FROM problem p LEFT JOIN problem_tag pt ON pt.problem_id = p.problem_id WHERE pt.tag_id = ? ORDER BY 1 DESC, 2 DESC, 3 ASC',
            $tagId);
        $result = [];
        foreach ($this->sqlite->res2arr($res) as $row) {
            $result[] = [$row['year'], $row['series'], $row['problem']];
        }
        return $result;
    }

    final public function getSpecLang($id, $lang = null) {
        global $conf;
        if (!$lang || $lang == $conf['lang']) {
            return $this->getLang($id);
        }
        $this->localised = false;
        $confLang = $conf['lang'];
        $conf['lang'] = $lang;
        $l = $this->getLang($id);
        $conf['lang'] = $confLang;
        $this->localised = false;
        return $l;
    }

    public function getTagLink($tag, $size = 5, $lang = 'cs', $count = 0, $active = false) {
        $page = $this->getConf('archive_path_' . $lang);
        $html = '<a data-tag="' . $tag . '" href="' . wl($page, [self::URL_PARAM => $tag]) . '" class="tag size' .
            $size . ' ' . ($active ? '' : '') . '">';
        $html .= '<span class="fa fa-tag"></span>';
        $html .= hsc($this->getSpecLang('tag__' . $tag, $lang));
        if ($count) {
            $html .= ' (';
            $html .= $count;
            $html .= ')';
        }
        $html .= '</a>';
        return $html;
    }


    public function labelToNumber ($label) {
        $dictionary = explode(',', $this->getConf('label_number_tasks_used'));
        foreach ($dictionary as $pair) {
            $pair = explode('/', $pair);
            if ($pair[0] == $label) {
                return $pair[1];
            }
        }
        return null;
    }

    public function numberToLabel ($number) {
        $dictionary = explode(',', $this->getConf('label_number_tasks_used'));
        foreach ($dictionary as $pair) {
            $pair = explode('/', $pair);
            if ($pair[1] == $number) {
                return (int)$pair[0];
            }
        }
        return null;
    }

    public function getSupportedTasks () {
        $dictionary = explode(',', $this->getConf('label_number_tasks_used'));
        $list = [];
        foreach ($dictionary as $pair) {
            $pair = explode('/', $pair);
            $list[(int)$pair[1]] = $pair[0];
        }
        return $list;
    }

    public function getSupportedLanguages () {
        return explode(',', $this->getConf('languages_used'));
    }

    public function getDefaultLanguage () {
        return $this->getSupportedLanguages()[0];
    }

    /**
     * Creates table, where you can select specific tasks to download
     * @param \dokuwiki\Form\Form $form
     * @param array $languages Preferred languages
     */
    public function addTaskSelectTable (\dokuwiki\Form\Form $form, array $languages = null) {
        $form->addTagOpen('table')->addClass('table');
        $form->addTagOpen('thead');
        $form->addTagOpen('tr');
        $form->addHTML('<td>#</td>');
        foreach ($this->getSupportedTasks() as $taskNumber => $taskLabel) {
            $form->addHTML('<td>' . $taskLabel . '</td>');
        }
        $form->addTagClose('tr');
        $form->addTagClose('thead');

        $form->addTagOpen('tbody');
        foreach ($languages ?: $this->getSupportedLanguages() as $language) {
            $form->addTagOpen('tr');
            $form->addHTML('<td><b>' . $language . '</b></td>');
            foreach ($this->getSupportedTasks() as $taskNumber => $taskLabel) {
                $form->addTagOpen('td');
                $form->addCheckbox('taskselect[' . $language . '][' . $taskNumber . ']')->attr('checked', 'checked');
                $form->addTagClose('td');
            }
            $form->addTagClose('tr');
        }
        $form->addTagClose('tbody');
        $form->addTagClose('table');
    }

    /**
     * @param int $total
     * @param string $page
     * @param array $urlParameters
     * @param string $pageNumberParamName
     * @return string
     */
    public function renderSimplePaginator($total, $page, $urlParameters, $pageNumberParamName = 'p' ) {
        global $INPUT;
        if ($total < 2) return null;

        $actual = $INPUT->int($pageNumberParamName,1);

        $html = '<ul class="pagination justify-content-end">';
        $html .=  '<li class="page-item' . ($actual === 1 ? ' disabled' : '') . '"><a class="page-link" href="' . wl($page, array_merge($urlParameters,[$pageNumberParamName => $actual-1])) . '">' . $this->getLang('prev') . '</a></li>';
        for ($i = 1; $i <= $total; $i++) {
            $html .= '<li class="page-item' . ($actual === $i ? ' active' : '') . '"><a class="page-link" href="' . wl($page, array_merge($urlParameters,[$pageNumberParamName => $i])) . '">' . $i . '</a></li>';
        }
        $html .=  '<li class="page-item' . ($actual === $total ? ' disabled' : '') . '"><a class="page-link" href="' . wl($page, array_merge($urlParameters,[$pageNumberParamName => $actual+1])) . '">' . $this->getLang('next') . '</a></li>';
        $html .= '</ul>';

        return $html;
    }
}

class fkstaskrepo_exception extends RuntimeException {

}
