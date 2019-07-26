<?php

class action_plugin_fkstimer extends DokuWiki_Action_Plugin {

    /**
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'serverTime');
    }

    public function serverTime(Doku_Event &$event) {
        /*
         * correction to server time + user can set different between server and display time.
         */
        $date = date('Y-m-d\TH:i:s', time() + ($this->getConf('server-correction')));

        $event->data['meta'][] = [
            'name' => 'fks-timer',
            'content' => $date
        ];
    }
}
