<?php

/**
 * Options for the fksdownloader plugin
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
$meta['wsdl'] = array('string');
$meta['fksdb_login'] = array('string');
$meta['fksdb_password'] = array('password');
$meta['http_host'] = array('string');
$meta['http_scheme'] = array('multichoice', '_choices' => array('http', 'https'));
$meta['http_login'] = array('string');
$meta['http_password'] = array('password');
$meta['tmp_dir'] = array('string');

