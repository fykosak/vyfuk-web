<?php
use \dokuwiki\Form\Form;
use \dokuwiki\Form\InputElement;

class action_plugin_fksnewsfeed_form extends \DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'tplEditNews');
    }

    public function tplEditNews(Doku_Event &$event) {

        global $ACT;
        global $INPUT;
        if ($ACT !== helper_plugin_fksnewsfeed::FORM_TARGET) {
            return;
        }
        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();
        switch ($INPUT->param('news')['do']) {
            case'edit':
            case'create':
                $this->getEditForm($event);
                return;
            default:
                return;
        }
    }

    private function createDefault() {
        global $INFO;
        return [
            'author-name' => $INFO['userinfo']['name'],
            'news-date' => date('Y-m-d\TH:i:s'),
            'author-email' => $INFO['userinfo']['mail'],
            'text' => $this->getLang('news_text'),
            'category' => '',
        ];
    }

    private function getEditForm(Doku_Event &$event) {
        global $INPUT;
        global $ID;

        $form = new Form();
        if ($INPUT->param('news')['id'] != 0) {
            $data = $this->helper->loadSimpleNews($INPUT->param('news')['id']);
        } else {
            $data = $this->createDefault();
        }

        $form->setHiddenField('page_id', $ID);
        $form->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('news[id]', $INPUT->param('news')['id']);
        $form->setHiddenField('news[do]', 'save');
        $form->setHiddenField('news[stream]', $INPUT->param('news')['stream']);

        $form->addFieldsetOpen('News Feed');

        foreach (helper_plugin_fksnewsfeed::$fields as $field) {
            $input = null;
            $form->addTagOpen('div')->addClass('form-group');

            switch ($field) {
                case'text':
                    $input = $form->addTextarea('text', $this->getLang($field), -1)->attr('class', 'form-control');
                    break;
                case 'news-date':
                    $input = new InputElement('datetime-local', $field, $this->getLang($field));
                    $input->attr('class', 'form-control');
                    $form->addElement($input);
                    $input->val(date('Y-m-d\TH:i:s', strtotime($data[$field])));
                    break;
                case'category':

                    $input = $form->addDropdown('category',
                        [
                            'primary',
                            'info',
                            'success',
                            'warning',
                            'danger',
                            'deprecated',
                            'fykos-blue',
                            'fykos-pink',
                            'fykos-line',
                            'fykos-purple',
                            'fykos-orange',
                            'fykos-green',
                        ],
                        $this->getLang($field))->attr('class', 'form-control');
                    break;
                case'image':
                    $input = $form->addTextInput($field, $this->getLang($field))->attr('class', 'form-control');
                    break;
                case 'link-href':
                case 'link-title':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'class' => 'form-control',
                    ]);
                    break;
                default:
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
            }
            if ($field !== 'news-date') {
                $input->val($data[$field]);
            }
            $form->addTagClose('div');
        }

        $form->addFieldsetClose();
        $form->addButton('submit', 'Uložiť')->addClass('btn btn-suceess');
        echo $form->toHTML();
    }
}
