<?php

/**
 * DokuWiki Plugin fkstaskrepo (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class admin_plugin_fkstaskrepo extends DokuWiki_Admin_Plugin {

    const DEFAULT_LANGUAGE = 'cs';

    /**
     *
     * @var helper_plugin_fkstaskrepo
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fkstaskrepo');
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
        
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $ID;
        global $INPUT;

        $year = $INPUT->post->int('year', null);
        $series = $INPUT->post->int('series', null);
        $language = $INPUT->post->str('language', null);

        ptln('<h1>' . $this->getLang('menu') . '</h1>');

        $form = new Doku_Form(array('class' => $this->getPluginName(), 'enctype' => 'multipart/form-data'));
        $form->startFieldset($this->getLang('update'));
        $form->addHidden('id', $ID);
        $form->addHidden('do', 'admin');
        //$form->addHidden('page', 'sqlite'); TODO
        //$form->addHidden('db', $_REQUEST['db']); TODO
        //$form->addHidden('version', $_REQUEST['version']); TODO

        $form->addElement(form_makeTextField('year', $year, $this->getLang('year')));
        $form->addElement(form_makeTextField('series', $series, $this->getLang('series')));
        $form->addElement(form_makeMenuField('language', array('cs', 'en'), $language, $this->getLang('language')));
        $form->addElement(form_makeFileField('xml_file', '<span title="' . $this->getLang('xml_source_help') . '">' . $this->getLang('xml_file') . '</span>'));

        $form->addElement(form_makeButton('submit', 'admin', $this->getLang('update')));
        $form->endFieldset();
        $form->printForm();

        if ($year !== null && $series !== null && $language !== null) {
            // obtain file
            if ($_FILES['xml_file'] && $_FILES['xml_file']['name']) {
                if ($_FILES['xml_file']['error'] > 0) {
                    msg('Upload failed.', -1);
                    return;
                }
                $dst = $this->helper->getSeriesFilename($year, $series, $language);
                move_uploaded_file($_FILES['xml_file']['tmp_name'], $dst);
                $content = file_get_contents($dst);
            } else {
                $content = $this->helper->getSeriesData($year, $series, $language, helper_plugin_fksdownloader::EXPIRATION_FRESH);
                if (!$content) {
                    return;
                }
            }

            $this->processSeries($content, $year, $series, $language);
        }
    }

    private function processSeries($content, $year, $series, $language) {
        $pagePath = sprintf($this->getConf('page_path_mask'), $year, $series);
        $pageTemplate = io_readFile(wikiFN($this->getConf('series_template')));

        // series template
        $seriesXML = simplexml_load_string($content);
        $parameters = array(
            'year' => $year,
            'series' => $series,
            'language' => $language,
            'deadline' => (string) $seriesXML['deadline'],
            'deadline-post' => (string) $seriesXML['deadline-post'],
            'label' => '@label@', // workaround so that we do not overwrite the placeholder with an empty string (before second replacement)
        );

        $pageContent = $this->replaceVariables($parameters, $pageTemplate);
        $that = $this;
        $pageContent = preg_replace_callback('/--\s*problem\s--(.*)--\s*endproblem\s*--/is', function($match) use($seriesXML, $that) {
                    $result = '';
                    $problemTemplate = $match[1];
                    foreach ($seriesXML as $problem) {
                        $problemParameters = array();
                        foreach ($problem as $field => $value) {
                            $problemParameters[$field] = (string) $value;
                        }

                        $result .= $that->replaceVariables($problemParameters, $problemTemplate);
                    }
                    return $result;
                }, $pageContent);

        io_saveFile(wikiFN($pagePath), $pageContent);

        msg(sprintf('Updated <a href="%s">%s</a>.', wl($pagePath), $pagePath));
    }

    private function replaceVariables($parameters, $template) {
        $that = $this;
        $result = preg_replace_callback('/@([^@]+)@/', function($match) use($parameters, $that) {
                    $key = $match[1];
                    if (!isset($parameters[$key])) {
                        msg(sprintf($that->getLang('undefined_template_variable'), $key));
                        return '';
                    } else {
                        return $parameters[$key];
                    }
                }, $template);
        return $result;
    }

}

// vim:ts=4:sw=4:et: