<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 23.8.2017
 * Time: 16:09
 */

namespace PluginFKSTaskRepo;

/**
 * Class Task
 * @package PluginFKSTaskRepo
 */

class Task {

    public static $editableFields = [
        'name',
        'points',
        'origin',
        'task',
        'authors',
        'solution-authors',
        'figures',
    ];

    public static $readonlyFields = [
        'year',
        'number',
        'series',
        'label',
        'lang',
    ];
    private $number;
    private $label;
    private $name;
    private $lang;
    private $origin;
    private $task;
    private $points;
    private $figures;
    private $authors;
    private $solutionAuthors;
    private $year;
    private $series;

    /**
     * @var \PluginFKSTaskRepo\TexPreproc;
     */
    private $texPreproc;

    /**
     * name, origin, task, figures
     * @var array localized data stored in the file
     */
    private $taskLocalizedData = [];

    /**
     * @var \helper_plugin_fkstaskrepo Helper plugin
     */
    private $helper;

    public function __construct(\helper_plugin_fkstaskrepo $helper, $year, $series, $label, $lang = 'cs') {
        $this->texPreproc = new TexPreproc();
        $this->year = $year;
        $this->series = $series;
        $this->label = strtoupper($label);
        $this->lang = $lang;
        $this->helper = $helper;
    }

    /**
     * @param $figures
     */
    public function saveFiguresRawData($figures) {
        $this->figures = [];

        foreach ($figures as $figure) {
            $figureGoodForm = [];
            $figureGoodForm['caption'] = $figure['caption'];

            foreach ($figure as $ext => $data) {
                $name = $this->getAttachmentPath($figure['caption'], $ext);
                if (io_saveFile(mediaFN($name), (string)trim($data))) {
                    msg('Figure "' . $figure['caption'] . '" for language ' . $this->lang . ' has been saved', 1);
                } else {
                    msg('Figure "' . $figure['caption'] . '" for language ' . $this->lang . ' has not saved properly!', -1);
                }
                $this->figures[] = ['path' => $name, 'caption' => $figure['caption']];
            }
        }
    }

    /**
     * Returns ID path of the Attachment based on its caption
     * @param $caption string Attachment Caption
     * @param $type string File type
     * @return string ID
     */
    private function getAttachmentPath($caption, $type) {
        $name = substr(preg_replace("/[^a-zA-Z0-9_-]+/", '-', $caption), 0, 30) . '_' . substr(md5($caption.$type),0,5);
        return vsprintf($this->helper->getConf('attachment_path_' . $this->lang), [$this->year, $this->series, $this->label]) . ':' . $name . '.' . $type;
    }

    /**
     * Returns the path of .json file with task data.
     * @return string path of file
     */
    private function getFileName() {
        return MetaFN(vsprintf($this->helper->getConf('task_data_meta_path'), [$this->year, $this->series, $this->label]), null);
    }

    /**
     * Saves task
     */
    public function save() {
        $data = [
            'year' => $this->year,
            'series' => $this->series,
            'label' => $this->label,
            'number' => $this->number,
            'points' => $this->points,
            'authors' => $this->authors,
            'solution-authors' => $this->solutionAuthors,
            'localization' => $this->taskLocalizedData, // Includes old data
        ];

        $data['localization'][$this->lang] = [
            'name' => $this->name,
            'origin' => $this->origin,
            'task' => $this->task,
            'figures' => $this->figures,
        ];

        io_saveFile($this->getFileName(), json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Loads task
     * @return bool Success
     */
    public function load() {
        $content = io_readFile($this->getFileName(), false);
        if (!$content) {
            return false;
        }
        $data = json_decode($content, true);

        $this->taskLocalizedData = $data['localization'];

        if (!key_exists($this->lang, $data['localization'])) {
            return false;
        }

        $this->number = $data['number'];
        $this->name = $data['localization'][$this->lang]['name'];
        $this->origin = $data['localization'][$this->lang]['origin'];
        $this->task = $data['localization'][$this->lang]['task'];
        $this->points = $data['points'];
        $this->figures = $data['localization'][$this->lang]['figures'];
        $this->authors = $data['authors'];
        $this->solutionAuthors = $data['solution-authors'];

        return true;
    }

    /**
     * @return mixed
     */
    public function getYear() {
        return $this->year;
    }

    /**
     * @return mixed
     */
    public function getSeries() {
        return $this->series;
    }
    /**
     * @return mixed
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * Number is not an ID of the task
     * @return mixed
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * Number is readonly field, but it is editable during XML import
     * @param mixed $number
     */
    public function setNumber($number) {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getPoints() {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points) {
        $this->points = $points;
    }

    /**
     * @return array
     */
    public function getAuthors() {
        return $this->authors;
    }

    /**
     * @param array $authors
     */
    public function setAuthors($authors) {
        $this->authors = $authors;
    }

    /**
     * @return mixed
     */
    public function getSolutionAuthors() {
        return $this->solutionAuthors;
    }

    /**
     * @param mixed $solutionAuthors
     */
    public function setSolutionAuthors($solutionAuthors) {
        $this->solutionAuthors = $solutionAuthors;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getOrigin() {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin) {
        $this->origin = $origin;
    }

    /**
     * @return mixed
     */
    public function getTask() {
        return $this->task;
    }

    /**
     * @param string $task
     * @param boolean $preProc
     */
    public function setTask($task, $preProc = true) {
        if ($preProc) {
            $this->task = $this->texPreproc->preproc($task);
        } else {
            $this->task = $task;
        }
    }

    /**
     * @return array
     */
    public function getFigures() {
        return $this->figures;
    }

    /**
     * @param mixed $figures
     */
    public function setFigures($figures) {
        $this->figures = $figures;
    }

}
