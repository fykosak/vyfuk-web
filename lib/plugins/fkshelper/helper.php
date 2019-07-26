<?php

require_once __DIR__.'/inc/Form/DateTimeInputElement.php';

class helper_plugin_fkshelper extends DokuWiki_Plugin {

    public function matchClassesNIDs($attributes) {
        // match classes
        preg_match_all('/\.([a-zA-z0-9-_]*)/', $attributes, $classes);
        // match ID
        preg_match('/\#([a-zA-z0-9-_]*)/', $attributes, $id);

        return ['classes' => implode(" ", $classes[1]), 'id' => $id[1]];
    }
}
