<?php

use \PluginNewsFeed\Model\Stream;
use \dokuwiki\Form\Form;

class admin_plugin_fksnewsfeed_dependence extends DokuWiki_Admin_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 291;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText($lang) {
        $menuText = $this->getLang('dependence_menu');
        return $menuText;
    }

    public function handle() {
        global $INPUT;
        $dep = $INPUT->param('dep');
        if ($dep['child'] == '' || $dep['parent'] == '') {
            return;
        }
        $childStream = new Stream();
        $childStream->fillFromDatabaseByName($dep['child']);
        $childID = $childStream->getStreamID();

        $parentStream = new Stream();
        $parentStream->fillFromDatabaseByName($dep['parent']);
        $parentID = $parentStream->getStreamID();

        $d = $this->helper->allParentDependence($parentID);
        if (in_array($childID, $d)) {
            msg($this->getLang('dep_exist'), -1);
        } else {
            if ($this->helper->createDependence($parentID, $childID)) {
                msg($this->getLang('dep_created'), 1);
            }
        }
    }

    public function html() {
        echo '<h1>' . $this->getLang('dependence_menu') . '</h1>';

        $streams = $this->helper->getAllStreams();
        echo $this->createDependenceFrom($streams);

        echo '<h2>' . $this->getLang('dep_list') . ':</h2>';

        foreach ($streams as $stream) {
            echo '<h3>' . $this->getLang('stream') . ': <span class="badge badge-primary">' .
                $stream->getName() . '</span></h3>';

            $this->renderParentDependence($stream);
            $this->renderChildDependence($stream);
            echo '<hr class="clearfix">';
        }
    }

    private function renderChildDependence(Stream $stream) {
        $childDependence = $this->helper->allChildDependence($stream->getStreamID());
        echo '<h4>' . $this->getLang('dep_list_child') . '</h4>';
        if (!empty($childDependence)) {
            echo '<ul>';
            foreach ($childDependence as $dependence) {
                $dependenceStream = new Stream($dependence);
                $dependenceStream->fillFromDatabase();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }

        $fullChildDependence = [];
        $this->helper->fullChildDependence($stream->getStreamID(), $fullChildDependence);
        echo '<h4>' . $this->getLang('dep_list_child_full') . '</h4>';
        if (!empty($fullChildDependence)) {
            echo '<ul>';
            foreach ($fullChildDependence as $dependence) {
                $dependenceStream = new Stream($dependence);
                $dependenceStream->fillFromDatabase();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }
    }

    private function renderParentDependence(Stream $stream) {
        echo '<h4>' . $this->getLang('dep_list_parent') . '</h4>';

        $parentDependence = $this->helper->allParentDependence($stream->getStreamID());
        if (!empty($parentDependence)) {
            echo '<ul>';
            foreach ($parentDependence as $dependence) {
                $dependenceStream = new Stream($dependence);
                $dependenceStream->fillFromDatabase();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }

        $fullParentDependence = [];
        $this->helper->fullParentDependence($stream->getStreamID(), $fullParentDependence);
        echo '<h4>' . $this->getLang('dep_list_parent_full') . '</h4>';
        if (!empty($fullParentDependence)) {
            echo '<ul>';
            foreach ($fullParentDependence as $dependence) {
                $dependenceStream = new Stream($dependence);
                $dependenceStream->fillFromDatabase();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }
    }

    /**
     * @param Stream[] $streams
     * @return string
     */
    private function createDependenceFrom(array $streams) {
        global $lang;
        $html = '<h2>' . $this->getLang('dep_create') . '</h2>';
        $html .= '<div class="info">' . $this->getLang('dep_full_info') . '</div>';
        $streamNames = array_map(function (Stream $stream) {
            return $stream->getName();
        }, $streams);

        $form = new Form();
        $form->addClass('block');
        $form->setHiddenField('news[do]', 'dependence');
        $form->addDropdown('dep[parent]', $streamNames, $this->getLang('dep_parent_info'));
        $form->addDropdown('dep[child]', $streamNames, $this->getLang('dep_child_info'));
        $form->addButton('submit', $lang['btn_save']);
        $html .= $form->toHTML();
        return $html;
    }
}
