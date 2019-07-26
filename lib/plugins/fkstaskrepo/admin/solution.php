<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class admin_plugin_fkstaskrepo_solution extends DokuWiki_Admin_Plugin {

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
        return 'Stáhnout vzorová řešení z Astrid';
    }

    public function getMenuIcon() {
        $plugin = $this->getPluginName();
        return DOKU_PLUGIN . $plugin . '/solution.svg';
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

        // Process solution download
        if ($INPUT->bool('downloadsolutions') && $year && $series) {
            foreach (['cs'] as $language) { // For now, only czech language is supported
                foreach ($this->helper->getSupportedTasks() as $taskNumber => $task) {
                    // Test, if the task is selected
                    if ($taskSelect[$language][$taskNumber]) {
                        $st = $this->helper->downloadSolution($year, $series, $task);
                        msg(($st ? '<a href="' . ml($st) . '">' : null) . 'Řešení úlohy ' . $task . ($st ? '</a>' : null), $st ? 1 : -1);
                    }
                }
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

        $this->helper->addTaskSelectTable($form, ['cs']); // For now, only czech language is supported

        $form->addHTML('<hr/>');

        $form->addButton('downloadsolutions', 'Stáhnout a zobrazit na webu řešení této série.')->addClass('btn btn-danger');
        $form->addHTML('<small class="form-text text-danger">Stáhne z Astridu řešení k jednotlivým příkladům v PDF a zobrazí je na webu.</small>');

        $form->setHiddenField('id', $ID);
        $form->setHiddenField('do', 'admin');

        echo $form->toHTML();
    }

}
