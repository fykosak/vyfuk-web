<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$meta['newsfolder'] = array('string', '_caution' => 'danger');
$meta['newsfile'] = array('string', '_caution' => 'danger');


$meta['more_news'] = array('numeric');

$meta['hash_no'] = array('numeric', '_caution' => 'warning');
$meta['no_pref'] = array('numeric', '_caution' => 'warning');





$meta['perm_link'] = array('multichoice', '_choices' => array(1, 2, 4, 8, 16, 32, 64, 128, 255,1000));


$meta['perm_fb'] = array('multichoice', '_choices' => array(1, 2, 4, 8, 16, 32, 64, 128, 255,1000));


$meta['perm_add'] = array('multichoice', '_choices' => array(1, 2, 4, 8, 16, 32, 64, 128, 255,1000));
$meta['perm_manage'] = array('multichoice', '_choices' => array(1, 2, 4, 8, 16, 32, 64, 128, 255,1000));
$meta['perm_rss'] = array('multichoice', '_choices' => array(1, 2, 4, 8, 16, 32, 64, 128, 255,1000));
