<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 5.8.2017
 * Time: 17:40
 */

namespace PluginNewsFeed\Renderer;

use PluginNewsFeed\Model\News;

class VyfukRenderer extends AbstractRenderer {
    public function render($innerHtml, $formHtml, News $news) {
        $html = '<div class="col-12 row mb-3">';
        $html .= '<div class="col-12">';
        $html .= '<div class="card card-outline-' . $news->getCategory() . ' card-outline-vyfuk-orange">';
        $html .= '<div class="card-block">';
        $html .= $innerHtml;
        $html .= $formHtml;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    protected function getHeader($news) {
        return '<h4 class="card-title">' . $news->getTitle() . '</h4>' .
            '<p class="card-text">' .
            '<small class="text-muted">' . $news->getLocalDate() . '</small>' .
            '</p>';
    }
}
