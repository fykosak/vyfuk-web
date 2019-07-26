<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_fkshelper_fl extends DokuWiki_Syntax_Plugin {
    /**
     * @var helper_plugin_fkshelper
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fkshelper');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{fl.*?\>.+?\|.+?}}', $mode, 'plugin_fkshelper_fl');
        $this->Lexer->addSpecialPattern('{{button.*?\>.+?\|.+?}}', $mode, 'plugin_fkshelper_fl');
        $this->Lexer->addSpecialPattern('{{media-button.*?\>.+?\|.+?}}', $mode, 'plugin_fkshelper_fl');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        preg_match('/{{\s*(media-)?(button|fl)(.*)>(.*)\|(.*)}}/', $match, $matchesButton);
        list(, $is_media, $button_type, $attributes, $link, $text) = $matchesButton;
        $type = 'link';

        /* Test if media */
        if ($is_media) {
            /* If the query contains question mark test if the page exists */
            if (substr($link, -1) === '?' && (!media_ispublic($link) || !file_exists(mediaFN($link)))) {
                return false;
            }

            $type = 'ml';
        } else {
            /* If the query contains question mark test if the page exists */
            if (substr($link, -1) === '?' && !page_exists($link)) {
                return false;
            }
        }

        $attributes = $this->helper->matchClassesNIDs($attributes);
        return [$state, $link, $text, $attributes, $type];
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;
        if (!$data) {
            return false;
        }
        if ($mode == 'xhtml') {
            list($state, $link, $text, $attributes, $type) = $data;
            switch ($state) {
                case DOKU_LEXER_SPECIAL:
                    $attributesString = ' class="fast_link ' . hsc($attributes['classes']) . '"';
                    $attributesString .= ' id="' . hsc($attributes['id']) . '"';
                    if ($type === 'ml') {
                        $renderer->doc .= '<a href="' . ml($link) . '"' . $attributesString . '>';
                    } else if (preg_match('|^http[s]?://|', $link)) {
                        $renderer->doc .= '<a href="' . htmlspecialchars($link) . '"' . $attributesString . '>';
                    } else {
                        /** FUCK dokuwiki  */
                        if (preg_match('/([^#]*)(#.*)/', $link, $ms)) {
                            list(, $id, $hash) = $ms;
                            $id = $id ?: $ID;
                            $renderer->doc .= '<a href="' . wl(cleanID($id)) . $hash . '"' . $attributesString . '>';
                        } else {
                            $renderer->doc .= '<a href="' . wl(cleanID($link)) . '"' . $attributesString . '>';
                        }
                    }
                    $renderer->doc .= htmlspecialchars(trim($text));
                    $renderer->doc .= '</a>';
                    return true;
            }
        }
        return false;
    }
}
