<?php

/**
 * DokuWiki Plugin fksdbexport (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class action_plugin_fksdbexport extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this, 'handle_parser_cache_use');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_parser_cache_use(Doku_Event &$event, $param) {
        $cache = & $event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) {
            return;
        }
        if ($cache->mode != 'xhtml') {
            return;
        }

        // get meta data
        $depends = p_get_metadata($cache->page, 'relation fksdbexport');
        if (!is_array($depends) || !count($depends)) {
            return; // nothing to do
        }
        $cache->depends['files'] = !empty($cache->depends['files']) ? array_merge($cache->depends['files'], $depends) : $depends;
    }

}

// vim:ts=4:sw=4:et: