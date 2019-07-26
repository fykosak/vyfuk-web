<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class admin_plugin_fkstaskrepo_task extends DokuWiki_Admin_Plugin {

    static $availableVersions = [1];

    /**
     *
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fkstaskrepo');
    }

    public function getMenuText($language)
    {
        return 'Stáhnout zadání série z Astrid';
    }

    public function getMenuIcon() {
        $plugin = $this->getPluginName();
        return DOKU_PLUGIN . $plugin . '/task.svg';
    }

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 10;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        global $INPUT;
        $year = $INPUT->int('year', null);
        $series = $INPUT->int('series', null);

        $taskSelect = $INPUT->arr('taskselect', null);

        // Process XML upload
        if ($INPUT->bool('uploadxml')) {
            if ($_FILES['xml_file'] && $_FILES['xml_file']['name']) {
                if ($_FILES['xml_file']['error'] > 0) {
                    msg('Nepodařilo se nahrát XML soubor.', -1);
                    return;
                }
                $this->processSeries(
                    file_get_contents($_FILES['xml_file']['tmp_name']),
                    $INPUT->bool('uploadxmlhard'),
                    $taskSelect
                );
            }
        }

        // Process Astrid download XML
        if ($INPUT->bool('download') && $year && $series) {
            // Tasks
            if ($INPUT->bool('downloadtasks')) {
                // Task XML
                $data = $this->helper->getSeriesData($year, $series, helper_plugin_fksdownloader::EXPIRATION_FRESH);

                if ($data) {
                    $this->processSeries(
                        $data,
                        $INPUT->bool('downloadtaskshard'),
                        $taskSelect
                    );
                } else {
                    msg('Nepodařilo se nahrát XML soubor.', -1);
                }
            }

            // Documents
            foreach ($INPUT->arr('documentselect', null) ?: [] as $ID => $document) {
                $st = $this->helper->downloadDocument($year, $series, $this->getSupportedDocuments()[$ID]['remotepathmask'], $this->getSupportedDocuments()[$ID]['localpathmask']);
                msg(($st ? '<a href="' . ml($st) . '">' : null) . $this->getSupportedDocuments()[$ID]['name'] . ($st ? '</a>' : null), $st ? 1 : -1);
            }
        }
    }

    public function html() {
        global $ID;
        ptln('<h1>' . $this->getMenuText('cs') . '</h1>');
        $form = new \dokuwiki\Form\Form();
        $form->addClass('task-repo-edit');
        $form->attrs(['class' => $this->getPluginName(), 'enctype' => 'multipart/form-data']);

        $form->addHTML('<p>Vyberte číslo série a ročníku a klikněte na příslušné tlačítko. Dojde ke stažení, popřípadě aktualizaci, dat na webu z <a href="astrid.fykos.cz">astrid.fykos.cz</a>. Pokud přidáváte novou sérii, nezapomeňte upravit odkazy v hlavním menu jak v české, tak i v anglické verzi. Vše ostatní by mělo být automatické.</p>');

        $form->addTagOpen('div')->addClass('form-group');
        $inputElement = new dokuwiki\Form\InputElement('number', 'year', 'Číslo ročníku');
        $inputElement->attrs(['class' => 'form-control']);
        $form->addElement($inputElement);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('form-group');
        $inputElement = new dokuwiki\Form\InputElement('number', 'series', 'Číslo série');
        $inputElement->attrs(['class' => 'form-control']);
        $form->addElement($inputElement);
        $form->addTagClose('div');

        // List of tasks to download
        $this->helper->addTaskSelectTable($form);

        $form->addHTML('<hr/>');

        $form->addButton('download', 'Importovat zadání, brožurku a seriál této série.')->addClass('btn btn-primary d-block mb-3');

        // Some stuff to decide what do to...
            $form->addTagOpen('div');
            $form->addCheckbox('downloadtasks', 'Stahovat vůbec zadání?')->attr('checked', 'checked');
            $form->addTagClose('div');

            $form->addTagOpen('div');
            $form->addCheckbox('downloadtaskshard', 'Přepsat existující příklady na webu.');
            $form->addTagClose('div');

            $this->addDocumentSelectList($form);

        $form->addHTML('<small class="form-text">Stáhne z Astridu české a anglické zadání, brožurku v PDF a seriál v PDF.</small>');

        $form->addHTML('<hr/>');

        $form->addButton('uploadxml', 'Nahrát XML ručně ze souboru.')->addClass('btn btn-warning d-block mb-3');
        $form->addCheckbox('uploadxmlhard', 'Přepsat existující příklady na webu.');
        $form->addElement((new \dokuwiki\Form\InputElement('file', 'xml_file'))->addClass('d-block mt-3'));

        $form->addHTML('<small class="form-text">Tuto možnost používejte pouze tehdy, pokud není možné automaticky importovat z Astrid. Vyberte prosím pouze z tabulky příklady, které chcete importovat.</small>');

        echo $form->toHTML();
    }

    /**
     * Process XML and creates tasks
     * @param $content string XML content
     * @param $hard bool overwrite existing tasks
     * @param $taskSelect @see $this->helper->addTaskSelectTable()
     */
    private function processSeries($content, $hard, $taskSelect) {
        $seriesXML = simplexml_load_string($content);

        $deadline = $seriesXML->deadline;
        $deadline_post = $seriesXML->{'deadline-post'};

        $m = [];
        preg_match('/[0-9]+/', $seriesXML->contest, $m);
        $year = (int)$m[0]; // FYKOSXX

        $series = (int)$seriesXML->number;

        foreach ($this->helper->getSupportedLanguages() as $lang) {
            // Test if any task in current language is selected
            $somethingChosen = false;
            foreach ($taskSelect[$lang] ?: [] as $taskSelected) {
                if ($taskSelected) {
                    $somethingChosen = true;
                    break;
                }
            }
            if (!$somethingChosen) {
                continue;
            }

            $pagePath = sprintf($this->getConf('page_path_mask_' . $lang), $year, $series);
            if ($pagePath == "") {
                msg('No page path defined for language ' . $lang, -1);
                continue;
            }

            // Loads template for page
            $pageTemplate = io_readFile(wikiFN($this->getConf('series_template')));

            // Replace data in template
            $pageContent = $this->replaceVariables([
                'human-deadline' => date($this->helper->getSpecLang('deadline-format', $lang), strtotime($deadline)),
                'human-deadline-post' => date($this->helper->getSpecLang('deadline-post-format', $lang), strtotime($deadline_post)),
                'lang' => $lang,
                'year' => $year,
                'series' => $series,
            ],
                $pageTemplate);

            // Saves problems
            foreach ($seriesXML->problems->children() as $problem) {
                $this->createTask($problem, $year, $series, $lang, $hard, $taskSelect);
            }

            // Saves pages with problems
            io_saveFile(wikiFN($pagePath), $pageContent);

            msg(sprintf('<a href="%s">Stránka série v jazyce ' . $lang . '</a>.', wl($pagePath, null, true), $pagePath), 1);
        }
    }

    /**
     * Saves specific problem from XMLElement
     * @param SimpleXMLElement $problem
     * @param $year
     * @param $series
     * @param $lang
     * @param bool $hard overwrite existing task
     * @param $taskSelect @see $this->helper->addTaskSelectTable()
     * @return bool
     */
    private function createTask(SimpleXMLElement $problem, $year, $series, $lang, $hard, $taskSelect) {
        // Test, if the task is selected
        if (!$taskSelect[$lang][$this->helper->labelToNumber($problem->label)]) {
            return true;
        }

        $task = new \PluginFKSTaskRepo\Task($this->helper, $year, $series, (string)$problem->label, $lang);
        $exists = $task->load();

        if (!$hard && $exists) {
            msg("{$task->getName()} ($year-$series-{$task->getLabel()}-$lang) byla přeskočena.", 0);
            return true;
        }

        // Save figures
        $task->saveFiguresRawData($this->extractFigures($problem, $lang));

        /**
         * @var $child SimpleXMLElement
         */
        foreach ($problem->children() as $k => $child) {
            if ($this->hasLang($child, $lang)) {
                switch ($k) {
                    case 'number':
                        $task->setNumber((int)$child);
                        break;
                    case'name':
                        $task->setName((string)$child);
                        break;
                    case 'origin':
                        $task->setOrigin((string)$child);
                        break;
                    case'points':
                        $task->setPoints((int)$child);
                        break;
                    case 'task':
                        $task->setTask((string)$child);
                        break;
                    case 'authors':
                        $authors = (array)$child->children();
                        if ($authors['author']) {
                            if (is_scalar($authors['author'])) {
                                $task->setAuthors([$authors['author']]);
                            } else {
                                $task->setAuthors($authors['author']);
                            }
                        };
                        break;
                    case 'solution-authors':
                        $solutionAuthors = (array)$child->children();
                        if ($solutionAuthors['solution-author']) {
                            if (is_scalar($solutionAuthors['solution-author'])) {
                                $task->setSolutionAuthors([$solutionAuthors['solution-author']]);
                            } else {
                                $task->setSolutionAuthors($solutionAuthors['solution-author']);
                            }
                        };
                        break;
                }
            }
        }
        $task->save();

        msg("{$task->getName()} ($year-$series-{$task->getLabel()}-$lang)", 1);

        $this->helper->storeTags($task->getYear(), $task->getSeries(), $task->getLabel(), (array)$problem->topics->topic);
        return true;
    }

    /**
     * Checks if the SimpleXMLElement $e has set the specific lang, or nothing
     * @param SimpleXMLElement $e
     * @param string $lang
     * @return bool
     */
    private function hasLang(\SimpleXMLElement $e, $lang) {
        return (($lang == (string)$e->attributes(\helper_plugin_fkstaskrepo::XMLNamespace)->lang) ||
            (string)$e->attributes(\helper_plugin_fkstaskrepo::XMLNamespace)->lang == "");
    }

    /**
     * @param SimpleXMLElement $problem
     * @param $lang
     * @return array
     * @todo Solve languages
     */
    private function extractFigures(\SimpleXMLElement $problem, $lang) {
        $figuresData = [];
        if ((string)$problem->figures != "") {
            foreach ($problem->figures->figure as $figure) {
                if ($this->hasLang($figure, $lang)) {
                    $simpleFigure = [];
                    $simpleFigure['caption'] = (string)$figure->caption;
                    /**
                     * @var $data \SimpleXMLElement
                     */
                    foreach ($figure->data as $data) {
                        $type = (string)$data->attributes()->extension;
                        $simpleFigure['data'][$type] = trim((string)$data);
                    }
                    $figuresData[] = $simpleFigure;
                }
            }
        }

        return $figuresData;
    }

    /**
     * Replaces data in string
     * @param array $parameters
     * @param string $template
     * @return string
     */
    private function replaceVariables($parameters, $template) {
        $that = $this;

        $result = preg_replace_callback('/@([^@]+)@/',
            function ($match) use ($parameters, $that) {
                $key = $match[1];
                if (!isset($parameters[$key])) {
                    msg(sprintf($that->getLang('undefined_template_variable'), $key));
                    return '';
                } else {
                    return $parameters[$key];
                }
            },
            $template);
        return $result;
    }

    private function addDocumentSelectList(\dokuwiki\Form\Form $form) {
        foreach ($this->getSupportedDocuments() as $ID => $document) {
            $form->addTagOpen('div');
            $form->addCheckbox('documentselect[' . $ID . ']', $document['name'])->attr('checked', 'checked');
            $form->addTagClose('div');
        }
    }

    private function getSupportedDocuments()
    {
        return [
            [
                'name' => 'Brožurka série v PDF',
                'remotepathmask' => $this->getConf('remote_brochure_path_mask'),
                'localpathmask' => $this->getConf('brochure_path_cs'),
            ],
            [
                'name' => 'Zadání seriálu v PDF',
                'remotepathmask' => $this->getConf('remote_serial_path_mask'),
                'localpathmask' => $this->getConf('serial_path_cs'),
            ],
        ];
    }
}
