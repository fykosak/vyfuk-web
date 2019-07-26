<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 5.11.2017
 * Time: 20:49
 */

namespace PluginFKSHelper\Form;


use dokuwiki\Form\InputElement;

class DateTimeInputElement extends InputElement {

    public function __construct($name, $label = '') {
        parent::__construct('datetime-local', $name, $label);
    }

    public function setStep($step) {
        return $this->attr('step', $step);
    }

    public function val($value = null) {
        $value = date('Y-m-d\TH:i:s', strtotime($value));
        return parent::val($value);
    }

}
