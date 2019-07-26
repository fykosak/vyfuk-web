<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of fks_image
 *
 * @author miso
 */

class fksimage extends helper_plugin_fksimageshow {

    /**
     *
     * @var string path to style.ini
     */
    private $ini_file;

    /**
     *
     * @var int filetime style.ini
     */
    private $ini_time;

    /**
     *
     * @var array params of style.ini
     */
    private $ini_atr = array();

    /**
     * @var string name od season
     */
    public $season_name;

    /**
     *
     * @var string folder to save images
     */
    private $season_dir;

    /**
     *
     * @var string folder to default files
     */
    public $default_dir;

    /**
     *
     * @var string path of defaul file
     */
    private $default_file;

    /**
     *
     * @var int filetime of new file
     */
    private $file_time;

    /**
     * @var string path of new file
     */
    private $file_patch;

    /**
     *
     * @var string name of file
     */
    private $file_name;

    /**
     *
     * @var string type of file
     */
    private $file_ext;

    /**
     *
     * @var array of new color
     */
    private $color_new;

    /**
     *
     * @var array of old color
     */
    private $color_old;

    /**
     *
     * @var int new red value
     */
    private $new_red;

    /**
     *
     * @var int new green value
     */
    private $new_green;

    /**
     *
     * @var int new blue value
     */
    private $new_blue;

    /**
     *
     * @var int new alpha value
     */
    private $new_alpha;

    /**
     *
     * @var int old red value
     */
    private $old_red;

    /**
     *
     * @var int old rgreen value
     */
    private $old_green;

    /**
     *
     * @var int old blue value
     */
    private $old_blue;

    /**
     *
     * @var int old alpha value
     */
    private $old_alpha;

    public function __construct($file,$type = null) {

        global $conf;
        $this->ini_file = DOKU_INC.'lib/tpl/'.$conf['template'].'/style.ini';
        $this->ini_time = filemtime($this->ini_file);
        $this->ini_atr = parse_ini_file($this->ini_file);
        $this->season_name = $this->ini_atr['__season__'];
        $this->season_dir = 'lib/tpl/'.$conf['template'].'/images/season/'.$this->season_name.'/';
        $this->default_dir = 'lib/tpl/'.$conf['template'].'/images/season/default/';

        if($type){
            $this->file_ext = $type;
            $this->file_name = $file;
        }else{
            $path = pathinfo($file);
            $this->file_ext = $path['extension'];
            $this->file_name = $path['filename'];
        }

        $this->file_patch = DOKU_INC.$this->season_dir.$file.'.'.$type;
        $this->file_time = @filemtime($this->file_patch);
    }

    /**
     * 
     * @param type $file name of file without ext
     * @param string $type od file jpg/png
     * @return void
     */

    /**
     * @return void 
     */
    public function _colorize() {

        if(!file_exists(DOKU_INC.$this->season_dir)){
            mkdir(DOKU_INC.$this->season_dir);
        }

        if((!file_exists($this->file_patch) || ($this->file_time < $this->ini_time))){
            $this->_fks_colorize_img();
        }
        return;
    }

    /**
     * 
     * @return boolean
     */
    private function _fks_colorize_img() {

        $this->default_file = DOKU_INC.$this->default_dir.$this->file_name.'.'.$this->file_ext;
        if($this->file_ext == "png"){
            $im = imagecreatefrompng($this->default_file);
        }elseif($this->file_ext == 'jpg' || $this->file_ext == 'jpeg'){
            $im = imagecreatefromjpeg($this->default_file);
        }else{
            return;
        }

        if(preg_match('/radioactive/i',$this->file_name)){
            $style_rgb = hexdec($this->ini_atr['__vyfuk_back__']);
        }else{
            $style_rgb = hexdec($this->ini_atr['__vyfuk_head__']);
        }
        $this->_fks_repaint_img($im,$style_rgb);
        ob_start();
        if($this->file_ext == "png"){
            imagesavealpha($im,true);
            imagepng($im);
        }elseif($this->file_ext == 'jpg' || $this->file_ext == 'jpeg'){
            imagejpeg($im);
        }
        $contents = ob_get_contents();
        imagedestroy($im);
        ob_end_clean();
        io_saveFile(DOKU_INC.$this->season_dir.$this->file_name.'.'.$this->file_ext,$contents);

        return true;
    }

    /**
     * 
     * @global type $conf
     * @param string $file file name or full name of file
     * @param string $type extenson of file
     * @param bool $dis_scan fix for inf cycle
     * @return string path of repaint file
     */
    public static function _fks_season_image($file,$type = null,$dis_scan = false) {
        global $conf;
        $image = new fksimage($file,$type);

        $image->_colorize();
        if(!$dis_scan){
            foreach (scandir(DOKU_INC.$image->default_dir) as $value) {

                $more_file = pathinfo(DOKU_INC.$image->default_dir.$value);

                fksimage::_fks_season_image($more_file['filename'],$more_file['extension'],true);
            }
        }
        return DOKU_BASE.'lib/tpl/'.$conf['template'].'/images/season/'.$image->season_name.'/'.$file.'.'.$type;
    }

    /**
     * 
     * @param image $im
     * @param color $style_rgb
     * @return void
     */
    private function _fks_repaint_img(&$im,$style_rgb) {

        list($w,$h) = getimagesize($this->default_file);
        $this->color_new = imagecolorsforindex($im,$style_rgb);
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $rgb = imagecolorat($im,$i,$j);
                $this->color_old = imagecolorsforindex($im,$rgb);
                foreach (array('red','green','blue','alpha')as $value) {

                    $this->{"new_".$value} = $this->color_new[$value];
                    $this->{"old_".$value} = $this->color_old[$value];
                }
                if($this->_can_paint()){
                    $color = imagecolorallocate($im,$this->new_red,$this->new_green,$this->new_blue);
                    imagesetpixel($im,$i,$j,$color);
                }
            }
        }
        return;
    }

    /**
     * 
     * @return boolean
     */
    private function _can_paint() {
        $white = (
                ($this->old_red != 255) ||
                ($this->old_green != 255) ||
                ($this->old_blue != 255)
                );
        $same = (
                ($this->old_red != $this->new_red) ||
                ($this->old_green != $this->new_green) ||
                ($this->old_blue != $this->new_blue));

        return (boolean) ($this->old_alpha != 127) && $white && $same;
    }

}
