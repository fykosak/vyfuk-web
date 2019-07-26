<?php

namespace PluginNewsFeed\Renderer;

use \dokuwiki\Form\Form;
use \dokuwiki\Form\InputElement;
use \PluginNewsFeed\Model\News;
use \PluginNewsFeed\Model\Priority;
use \PluginNewsFeed\Model\Stream;

abstract class AbstractRenderer extends \helper_plugin_fksnewsfeed {

    public final function getPluginName() {
        return 'fksnewsfeed';
    }

    public function renderContent(News $data, $params) {
        $innerHtml = $this->getHeader($data);
        $innerHtml .= $this->getText($data);

        $innerHtml .= $this->getLink($data);
        $innerHtml .= $this->getSignature($data);
        $innerHtml .= $this->getShareFields($data);
        return $innerHtml;
    }

    public function renderEditFields($params) {

        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return '';
        }
        $html = '<button data-toggle="modal" data-target="#feedModal' . $params['id'] . '" class="btn btn-primary" >' .
            $this->getLang('btn_opt') . '</button>';
        $html .= '<div id="feedModal' . $params['id'] . '" class="modal" data-id="' . $params["id"] . '">';
        $html .= '<div class="modal-dialog">';
        $html .= '<div class="modal-content">';
        $html .= $this->getModalHeader();
        $html .= $this->getPriorityField($params['id'], $params['stream'], $params);
        $html .= $this->btnEditNews($params['id'], $params['stream']);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    abstract public function render($innerHtml, $formHtml, News $news);

    protected function getModalHeader() {
        $html = '';
        $html .= '<div class="modal-header">';
        $html .= '<h5 class="modal-title">' . $this->getLang('') . 'Upaviť novinku</h5>';
        $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        $html .= '<span aria-hidden="true">×</span>';
        $html .= '</button>';
        $html .= ' </div>';
        return $html;
    }

    /**
     * @param $id
     * @param $streamName
     * @param $params
     * @return string
     */
    protected function getPriorityField($id, $streamName, $params) {
        $html = '';
        if ($params['editable'] !== 'true') {
            return '';
        }
        if (!$params['stream']) {
            return '';
        }

        $html .= '<div class="modal-body">';
        $form = new Form();
        $form->addClass('block');

        $form->setHiddenField('do', \helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('news[id]', $id);
        $form->setHiddenField('news[stream]', $streamName);
        $form->setHiddenField('news[do]', 'priority');

        $stream = new Stream(null);
        $stream->fillFromDatabaseByName($streamName);
        $streamID = $stream->getStreamID();

        $priority = new Priority(null, $id, $streamID);
        $priority->fillFromDatabase();
        $form->addTagOpen('div')->addClass('form-group');
        $priorityValue = new InputElement('number', 'priority[value]', $this->getLang('valid_from'));
        $priorityValue->attr('class', 'form-control')->val($priority->getPriorityValue());
        $form->addElement($priorityValue);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('form-group');
        $priorityFromElement = new InputElement('datetime-local', 'priority[from]', $this->getLang('valid_from'));
        $priorityFromElement->val($priority->getPriorityFrom() ?: date('Y-m-d\TH:i:s', time()))
            ->attr('class', 'form-control');
        $form->addElement($priorityFromElement);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('form-group');
        $priorityToElement = new InputElement('datetime-local', 'priority[to]', $this->getLang('valid_to'));
        $priorityToElement->val($priority->getPriorityTo() ?: date('Y-m-d\TH:i:s', time()))
            ->attr('class', 'form-control');
        $form->addElement($priorityToElement);
        $form->addTagClose('div');

        $form->addButton('submit', $this->getLang('btn_save_priority'))->addClass('btn btn-success');
        $html .= $form->toHTML();
        $html .= ' </div > ';

        return $html;
    }

    protected function btnEditNews($id, $stream) {
        $html = '';

        $html .= '<div class="modal-footer"> ';
        $html .= '<div class="btn-group"> ';
        $editForm = new Form();
        $editForm->setHiddenField('do', \helper_plugin_fksnewsfeed::FORM_TARGET);
        $editForm->setHiddenField('news[id]', $id);
        $editForm->setHiddenField('news[do]', 'edit');
        $editForm->addButton('submit', $this->getLang('btn_edit_news'))->addClass('btn btn-info');
        $html .= $editForm->toHTML();

        if ($stream) {
            $deleteForm = new Form();
            $deleteForm->setHiddenField('do', \helper_plugin_fksnewsfeed::FORM_TARGET);
            $deleteForm->setHiddenField('news[do]', 'delete');
            $deleteForm->setHiddenField('news[stream]', $stream);
            $deleteForm->setHiddenField('news[id]', $id);
            $deleteForm->addButton('submit', $this->getLang('delete_news'))->attr('data-warning', true)
                ->addClass('btn btn-danger');
            $html .= $deleteForm->toHTML();
        }

        $purgeForm = new Form();
        $purgeForm->setHiddenField('do', \helper_plugin_fksnewsfeed::FORM_TARGET);
        $purgeForm->setHiddenField('news[do]', 'purge');
        $purgeForm->setHiddenField('news[id]', $id);
        $purgeForm->addButton('submit', $this->getLang('cache_del'))->addClass('btn btn-warning');
        $html .= $purgeForm->toHTML();
        $html .= ' </div > ';
        $html .= ' </div > ';

        return $html;
    }


    /**
     * @param $news News
     * @return string
     */
    protected function getShareFields(News $news) {
        global $ID;
        if (auth_quickaclcheck('start') < AUTH_READ) {
            return '';
        }
        $link = $news->getToken($news->getLinkHref());
        if (preg_match('|^https?://|', $news->getLinkHref())) {
            $link = $news->getToken($ID);
        }
        $html = '<div class="row mb-3" style="max-height: 2rem;">';

        $html .= '<div class="col-6">' .
            $this->social->facebook->createSend($link) .
            '</div>';

        $html .= '<div class="col-6">';
        $html .= $this->social->facebook->createShare($link);
        $html .= '</div>';

        //  $html .= '<div class="link">
        //  <span class="link-icon icon"></span>
        //  <span contenteditable="true" class="link_inp" >' . $link . '</span>
        //// </div>' ;

        $html .= '</div>';

        return $html;
    }

    /**
     * @param $news News
     * @return null|string
     */
    protected function getText($news) {
        return p_render('xhtml', p_get_instructions($news->getText()), $info);
    }

    /**
     * @param $news News
     * @return string
     */
    protected function getSignature($news) {
        return ' <div class="card-text text-right">
            <a href="mailto:' . hsc($news->getAuthorEmail()) . '" class="mail" title="' . hsc($news->getAuthorEmail()) .
            '"><span class="fa fa-envelope"></span>' . hsc($news->getAuthorName()) . '</a>
        </div>';
    }

    /**
     * @param $news News
     * @return string
     */
    protected function getHeader($news) {
        return '<h4>' . $news->getTitle() .
            '<small class="float-right">' . $news->getLocalDate() . '</small></h4>';
    }

    /**
     * @param $news News
     * @return string
     */
    protected function getLink($news) {
        if ($news->hasLink()) {
            if (preg_match('|^https?://|', $news->getLinkHref())) {
                $href = hsc($news->getLinkHref());
            } else {
                $href = wl($news->getLinkHref(), null, true);
            }
            return '<p><a class="btn btn-secondary" href="' . $href . '">' . $news->getLinkTitle() . '</a></p>';
        }
        return '';
    }

}