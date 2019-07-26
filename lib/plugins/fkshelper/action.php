<?php

class action_plugin_fkshelper extends DokuWiki_Action_Plugin {
    /**
     * @var helper_plugin_fkshelper
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fkshelper');
    }

    public function register(Doku_Event_Handler $controller) {
        // $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'antiSpam');
        // $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'maintenance');
    }

    public function antiSpam() {
        global $INPUT;
        $html_out = $this->getConf('deny_html_out');

        $deny_ip = [''];

        foreach ($deny_ip as $value) {
            if (($_SERVER['REMOTE_ADDR'] == $value) || ($INPUT->str('i_am_spamer') == 1)) {
                header($_SERVER["SERVER_PROTOCOL"] . " 418 HTCPCP Coffee not found");
                die($html_out);
            }
        }
    }

    public function maintenance() {
        global $INPUT;
        if ($_COOKIE['test-user'] == 'fykosak') {
            return;
        }
        if ($INPUT->str('test-user') == 'fykosak' && $INPUT->str('password') == 'sakra_chcem_pristup') {
            setcookie('test-user', 'fykosak');
            return;
        }
        $date = time();
        $maintenanceFrom = strtotime('2017-05-20T00:00:00');
        $maintenanceTo = strtotime('2017-05-19T18:00:00');
        if ($date > $maintenanceTo) {
            return;
        }
        if ($date < $maintenanceFrom) {
            return;
        }
        // TODO jazykové korektury
        require_once __DIR__ . '/maintenance.html';
        die();
    }
}
