<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 5.8.2017
 * Time: 17:40
 */

namespace PluginNewsFeed\Renderer;

use PluginNewsFeed\Model\News;

class FykosRenderer extends AbstractRenderer {

    public function render($innerHtml, $formHtml, News $news) {
        $html = '<div class="col-12 row mb-3">';
        $html .= '<div class="col-12">';
        $html .= '<div class="bs-callout mb-3 bs-callout-' . $news->getCategory() . '">';
        $html .= $innerHtml;
        $html .= $formHtml;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
