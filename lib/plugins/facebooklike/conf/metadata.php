<?php
/**
 * Options for the like button
 *
 * @license GNU General Public License 3 <http://www.gnu.org/licenses/>
 * @author Marvin Thomas Rabe <mrabe@marvinrabe.de>
 */

$meta['layout'] = array('multichoice','_choices' => array('standard','button_count','box_count'));
$meta['show_faces'] = array('onoff');
$meta['width'] = array('string');
$meta['action'] = array('multichoice','_choices' => array('like','recommend'));
$meta['font']  = array('multichoice','_choices' => array('arial','lucida grande','segoe ui','tahoma','trebuchet ms','verdana'));
$meta['colorscheme']  = array('multichoice','_choices' => array('light','dark'));
