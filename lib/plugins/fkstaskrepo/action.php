<?php
/**
 * Action plugin for exiting tasks on the web
 * DokuWiki Plugin fkstaskrepo (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class action_plugin_fkstaskrepo extends DokuWiki_Action_Plugin {

    private static $tags = [
        'mechHmBodu',
        'mechTuhTel',
        'hydroMech',
        'mechPlynu',
        'gravPole',
        'kmitani',
        'vlneni',
        'molFyzika',
        'termoDyn',
        'statFyz',
        'optikaGeom',
        'optikaVln',
        'elProud',
        'elPole',
        'magPole',
        'relat',
        'kvantFyz',
        'jadFyz',
        'astroFyz',
        'matematika',
        'chemie',
        'biofyzika',
        'other',
    ];
    /**
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fkstaskrepo');
    }

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'tplEditForm');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'editTask');

        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_parser_cache_use');
    }

    public function tplEditForm(Doku_Event &$event) {
        global $INPUT, $conf;
        if ($event->data !== 'plugin_fkstaskrepo' || !$this->isLogged()) {
            return;
        }
        $event->preventDefault();
        echo '<h1>Úprava úlohy</h1>';

        $problem = new \PluginFKSTaskRepo\Task(
            $this->helper,
            $INPUT->param('task')['year'],
            $INPUT->param('task')['series'],
            $INPUT->param('task')['problem'],
            $INPUT->param('task')['lang']
        );
        $problem->load();

        $form = new \dokuwiki\Form\Form();
        $form->addClass('task-repo-edit');
        $form->setHiddenField('task[do]', 'update');
        $form->setHiddenField('do', 'plugin_fkstaskrepo');

        foreach (\PluginFKSTaskRepo\Task::$readonlyFields as $field) {
            $form->addTagOpen('div')->addClass('form-group');
            switch ($field) {
                case 'year':
                    $this->addStaticField($form, $field, $problem->getYear());
                    break;
                case 'number':
                    $this->addStaticField($form, $field, $problem->getNumber());
                    break;
                case 'series':
                    $this->addStaticField($form, $field, $problem->getSeries());
                    break;
                case 'label':
                    $this->addStaticField($form, $field, $problem->getLabel());
                    break;
                case 'lang':
                    $this->addStaticField($form, $field, $problem->getlang());
                    break;
            }
            $form->addTagClose('div');
        }
        foreach (\PluginFKSTaskRepo\Task::$editableFields as $field) {
            $form->addTagOpen('div')->addClass('form-group');
            switch ($field) {
                case 'task':
                    $form->addTextarea('problem[task]', $this->helper->getSpecLang($field, 'cs'))->attrs(['class' => 'form-control', 'rows' => 15])
                        ->val($problem->getTask());
                    $form->addHTML('<small class="form-text">Přílohy přidávejte externě, samy se zobrazí.</small>');
                    break;
                case 'figures':
                    $form->addFieldsetOpen($this->helper->getSpecLang('figures', 'cs'));
                    $form->addTag('div')->addClass('figures mb-3')->attr('data-value', json_encode($problem->getFigures()));
                    $form->addFieldsetClose();
                    $mediaLink = vsprintf($this->getConf('attachment_path_' . $problem->getLang()), [$problem->getYear(), $problem->getSeries(), $problem->getLabel()]);
                    $form->addHTML('<button type="button" class="btn btn-primary btn-small" id="addmedia" data-folder-id="' . $mediaLink . '">Otevřít / Nahrát přílohy</a></button>');
                    $form->addHTML('<small class="form-text">Defaultní adresa pro ukládání je <code>' . $mediaLink . '</code></small>');
                    break;
                case 'name':
                    $form->addTextInput('problem[name]', $this->helper->getSpecLang($field, 'cs'))
                        ->attrs(['class' => 'form-control'])->val($problem->getName());
                    $form->addHTML('<small class="form-text">Podle konvence začíná název úlohy malým písmenem pokud se nejedná o vlastní jméno. Taktéž nekončí tečkou. Jméno úlohy bude potřeba opravit i ve FKSDB.</small>');
                    break;
                case 'origin':
                    $form->addTextInput('problem[origin]', $this->helper->getSpecLang($field, 'cs'))
                        ->attrs(['class' => 'form-control'])->val($problem->getOrigin());
                    break;
                case 'authors':
                    $value = implode(', ', $problem->getAuthors());
                    $form->addTextInput('problem[authors]', $this->helper->getSpecLang($field, 'cs'))
                        ->attrs(['class' => 'form-control'])->val($value);
                    $form->addHTML('<small class="form-text">Autory oddělujte čárkou.</small>');
                    break;

                case 'solution-authors':
                    $value = implode(', ', $problem->getSolutionAuthors());
                    $form->addTextInput('problem[solution-authors]', $this->helper->getSpecLang($field, 'cs'))
                        ->attrs(['class' => 'form-control'])->val($value);
                    $form->addHTML('<small class="form-text">Autory oddělujte čárkou.</small>');
                    break;
                case 'points':
                    $inputElement = new dokuwiki\Form\InputElement('number', 'problem[points]', $this->helper->getSpecLang($field, 'cs'));
                    $inputElement->val($problem->getPoints() ?: '');
                    $inputElement->attrs(['class' => 'form-control']);
                    $form->addElement($inputElement);
                    $form->addHTML('<small class="form-text">V případě 0 se počet bodů nezobrazí (u starších příkladů se nezachovalo). Body za úlohu budou potřeba opravit i ve FKSDB.</small>');
                    break;
            }
            $form->addTagClose('div');
        }
        $this->addTagsField($form, $problem);
        $form->addHTML('<hr>');

        $solutionFilename = vsprintf($this->getConf('solution_path_' . $problem->getLang()), [$problem->getYear(), $problem->getSeries(), $problem->getLabel()]);
        preg_match('/^(.*):[^:]*/', $solutionFilename, $solutionPath);
        $solutionPath = $solutionPath[1];

        $brochureFilename = vsprintf($this->getConf('brochure_path_' . $problem->getLang()), [$problem->getYear(), $problem->getSeries()]);
        preg_match('/^(.*):[^:]*/', $brochureFilename, $brochurePath);
        $brochurePath = $brochurePath[1];

        // Only in Czech
        $serialFilename = vsprintf($this->getConf('serial_path_cs'), [$problem->getYear(), $problem->getSeries()]);
        preg_match('/^(.*):[^:]*/', $serialFilename, $serialPath);
        $serialPath = $serialPath[1];


        $form->addHTML('<p>Název, zadání, origin a figures jsou pro každý překlad unikátní, proto nezapomeň upravit všechny jazykové mutace.</p>');
        $form->addHTML('<p>Řešení této úlohy v PDF nahrajte jako <code><a href="#" class="dwmediaselector-open" data-media-path="' . $solutionPath . '">' . $solutionFilename . '</a></code>. Brožurku celé této série jako <code><a href="#" class="dwmediaselector-open" data-media-path="' . $brochurePath . '">' . $brochureFilename . '</a></code>.</p>');
        $form->addHTML('<p class="font-italic">Případnou seriálovou úlohu této série nahrajte jako <code><a href="#" class="dwmediaselector-open" data-media-path="' . $serialPath . '">' . $serialFilename . '</a></code>.</p>');
        $form->addButton('submit', 'Uložit')->addClass('btn btn-primary');
        echo $form->toHTML();
    }

    private function addStaticField(\dokuwiki\Form\Form &$form, $field, $value) {
        $form->addTextInput('problem[' . $field . ']', $this->helper->getSpecLang($field, 'cs'))
            ->attrs(['class' => 'form-control', 'readonly' => 'readonly'])->val($value);
    }

    private function addTagsField(\dokuwiki\Form\Form $form, \PluginFKSTaskRepo\Task $data) {
        $form->addFieldsetOpen($this->helper->getSpecLang('tags', 'cs'));

        $form->addTagOpen('div')->addClass('row');
        $topics = $this->helper->loadTags($data->getYear(), $data->getSeries(), $data->getLabel());
        foreach (self::$tags as $tag) {
            $form->addTagOpen('div')->addClass('form-check col-lg-4 col-md-6 col-sm-12');
            $isIn = false;
            if (is_array($topics)) {
                $isIn = in_array($tag, $topics);
            }
            $input = $form->addCheckbox('problem[topics][]', $this->helper->getSpecLang('tag__' . $tag, 'cs'))->val($tag);
            if ($isIn) {
                $input->attr('checked', 'checked');
            }
            $form->addTagClose('div');
        }
        $form->addTagClose('div');
        $form->addFieldsetClose();
    }

    public function editTask(Doku_Event &$event) {
        global $INPUT;
        if ($event->data !== 'plugin_fkstaskrepo' || !$this->isLogged()) {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();
        switch ($INPUT->param('task')['do']) {
            case 'update':
                $this->updateProblem($event);
                break;
            case 'edit':
                break;
        }
    }

    private function updateProblem(Doku_Event &$event) {
        global $INPUT;

        if (!$this->isLogged()) {
            return false;
        }

        $problemData = $INPUT->param('problem');


        $problem = new \PluginFKSTaskRepo\Task($this->helper, (int)$problemData['year'], (int)$problemData['series'], (string)$problemData['label'], $problemData['lang']);

        $problem->load();

        //$problem->setNumber((int)$INPUT->param('problem')['number']);
        $problem->setPoints((int)$INPUT->param('problem')['points'] ?: null);
        $problem->setAuthors(array_map('trim', explode(',', $INPUT->param('problem')['authors'])));
        $problem->setSolutionAuthors(array_map('trim', explode(',', $INPUT->param('problem')['solution-authors'])));

        $problem->setName(trim($INPUT->param('problem')['name']));
        $problem->setOrigin(trim($INPUT->param('problem')['origin']));
        $problem->setTask(cleanText($INPUT->param('problem')['task']), false);
        $problem->setFigures($this->processFigures($INPUT->param('problem')['figures']));
        $problem->save();
        $this->helper->storeTags($problem->getYear(), $problem->getSeries(), $problem->getLabel(), $INPUT->param('problem')['topics']);
        $event->data = 'show';
    }


    private function processFigures($figures) {
        $out = [];
        foreach ($figures as $figure) {
            $path = trim($figure['path']);
            $caption = trim($figure['caption']);
            if ($path == '') continue; // $caption can be omitted
            $out[] = [
                'path' => $path,
                'caption' => $caption,
            ];
        }

        return $out;
    }

    public function handle_parser_cache_use(Doku_Event &$event) {
        $cache = &$event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) {
            return;
        }
        if ($cache->mode != 'xhtml') {
            return;
        }

        // get meta data
        $depends = p_get_metadata($cache->page, 'relation fkstaskrepo');
        if (!is_array($depends) || !count($depends)) {
            return; // nothing to do
        }
        $cache->depends['files'] = !empty($cache->depends['files']) ? array_merge($cache->depends['files'],
            $depends) : $depends;
    }

    /**
     * @return bool
     */
    private function isLogged() {
        global $ID;
        return auth_quickaclcheck($ID) >= AUTH_EDIT;
    }
}

