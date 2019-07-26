<?php
/**
 * Options for the filelink plugin
 *
 * @author Lukas Timko <lukast@fykos.cz>
 */


$meta['filesystem_root'] = array('string');
$meta['web_root'] = array('string');
$meta['expiration'] = array('numeric');
$meta['behavior'] = array('multichoice','_choices' => array('hide', 'message'));
$meta['message_text'] = array('string');
$meta['message_class'] = array('string');

