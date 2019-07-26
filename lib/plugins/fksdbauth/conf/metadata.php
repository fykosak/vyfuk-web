<?php

/**
 * Options for the fksdbauth plugin
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
$meta['mysql_host'] = array('string');
$meta['mysql_user'] = array('string');
$meta['mysql_password'] = array('password');
$meta['mysql_database'] = array('string');
$meta['contest'] = array('multichoice', '_choices' => array('fykos', 'vyfuk'));
$meta['fallback_enabled'] = array('onoff');
$meta['fallback_plugin'] = array('string');
