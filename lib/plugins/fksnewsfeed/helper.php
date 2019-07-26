<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_LF')) {
    define('DOKU_LF', "\n");
}
if (!defined('DOKU_TAB')) {
    define('DOKU_TAB', "\t");
}

class helper_plugin_fksnewsfeed extends DokuWiki_Plugin {

    public static $Fields = array('name', 'email', 'author', 'newsdate', 'text');
    public $FKS_helper;
    public $simple_tpl;
    public $sqlite;

    const simple_tpl = "{{fksnewsfeed>id=@id@; even=@even@}}";
    
    const db_table_feed = "fks_newsfeed_news";

    public function __construct() {
        $this->simple_tpl = self::simple_tpl;
        $this->FKS_helper = $this->loadHelper('fkshelper');

        $this->sqlite = $this->loadHelper('sqlite', false);
        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
            return;
        }
        if (!$this->sqlite->init('fksnewsfeed', DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)) {
            msg($pluginName . ': Cannot initialize database.');
            return;
        }
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $s 
     * @param bool $o
     * @return void
     * load file with configuration
     * and load old configuration file 
     */
    public static function loadstream($s, $o = true) {
        if ($o) {
            return (array) preg_split('/;;/', substr(io_readFile(metaFN("fksnewsfeed:streams:" . $s, ".csv"), FALSE), 1, -1));
        } else {

            $arr = preg_split("/\n/", substr(io_readFile(metaFN("fksnewsfeed:old-streams:" . $s, ".csv"), FALSE), 1, -1));
            $l = count($arr);
            return (array) preg_split('/;;/', substr($arr[$l - 1], 1, -1));
        }
    }

    /**
     * Find no news 
     * @author Michal Červeňák <miso@fykos.cz>
     * @return int
     */
    public function findimax() {
        $sql2 = 'select max(id) from '.self::db_table_feed;
        $res = $this->sqlite->query($sql2);
        $imax = $this->sqlite->res2single($res);
        $imax++;
        return (int) $imax;
    }

    

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $name
     * @param string $dir
     * @param flag $flag
     * @param int $type
     * @return string
     */
    public static function shortfilename($name, $dir = '', $flag = 'ID_ONLY', $type = 4) {
        if (!preg_match('/\w*\/\z/', $dir)) {
            //$dir = $dir . DIRECTORY_SEPARATOR;
        }
        $doku = pathinfo(DOKU_INC);

        $rep_dir_base = $doku['dirname'] . DIRECTORY_SEPARATOR . $doku['filename'] . DIRECTORY_SEPARATOR;
        $rep_dir_base_full = $doku['dirname'] . DIRECTORY_SEPARATOR . $doku['filename'] . '.' . $doku['extension'] . DIRECTORY_SEPARATOR;
        $rep_dir = "data/meta/";
        switch ($flag) {
            case 'ID_ONLY':
                $rep_dir.=$dir . "/news";
                break;
            case 'NEWS_W_ID':
                $rep_dir.=$dir . "/";
                break;
            case 'DIR_N_ID':
                $rep_dir.='';
                break;
        }
        $n = str_replace(array($rep_dir_base_full, $rep_dir, $rep_dir_base), '', $name);
        
        return (string) substr($n, 0, -$type);
    }

    /**
     * save a new news or rewrite old
     * @author Michal Červeňák <miso@fykos.cz>
     * @return bool is write ok
     * @param array $Rdata params to save
     * @param string $id path to news
     * @param bool $rw rewrite?
     * 
     */
    public function saveNewNews($Rdata, $id = 0, $rw = false) {

        foreach (self::$Fields as $v) {
            if (array_key_exists($v, $Rdata)) {
                $data[$v] = $Rdata[$v];
            } else {
                $data[$v] = $this->getConf($v);
            }
        }

        $image = ':';
        $date = $data['newsdate'];
        $author = $data['author'];
        $email = $data['email'];
        $name = $data['name'];
        $text = $data['text'];
        
        
        
        if (!$rw) {
            $sql = 'insert into '.self::db_table_feed.' (id,name, author, email,newsdate,text,image) values(?,?,?,?,?,?,?)';
            
            $this->sqlite->query($sql, $id, $name, $author, $email, $date, $text, $image);
           
        } else {
            $sql = 'update '.self::db_table_feed.' set name=?, author=?, email=?, newsdate=?, text=?, image=? where id=? ';
           
            $this->sqlite->query($sql, $name, $author, $email, $date, $text, $image,$id);
             
        }
        return;
    }

    /**
     * short name of news and add dots
     * 
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $name text to short
     * @param int $l length of output
     * @return string shorted text
     * 
     * 
     */
    public static function shortName($name = "", $l = 25) {
        if (strlen($name) > $l) {
            $name = mb_substr($name, 0, $l - 3) . '...';
        }
        return (string) $name;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @return array all stream from dir
     */
    public static function allstream() {
        foreach (glob(DOKU_INC . 'data/meta/fksnewsfeed/streams/*.csv') as $key => $value) {
            $sh = self::shortfilename($value, 'fksnewsfeed/streams', 'NEWS_W_ID', 4);

            $streams[$key] = $sh;
            //$streams[$key] = str_replace(array(DOKU_INC . 'data/meta/fksnewsfeed/streams/', '.csv'), array("", ''), $value);
        }
        return (array) $streams;
    }


    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @global type $INFO
     * @param string $type action 
     * @param string $newsid
     * @return void
     */
    public static function _log_event($type, $newsid) {
        global $INFO;

        $log = io_readFile(metaFN('fksnewsfeed:log', '.log'));
        $news_id = preg_replace('/[A-Z]/', '', $newsid);
        $log.= "\n" . date("Y-m-d H:i:s") . ' ; ' . $news_id . ' ; ' . $type . ' ; ' . $INFO['name'] . ' ; ' . $_SERVER['REMOTE_ADDR'] . ';' . $INFO['ip'] . ' ; ' . $INFO['user'];

        io_saveFile(metaFN('fksnewsfeed:log', '.log'), $log);
        return;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param int $i
     * @return string
     */
    public static function _is_even($i) {

        return 'FKS_newsfeed_' . helper_plugin_fkshelper::_is_even($i);
    }


    /**
     * 
     * @param type $id
     * @return type
     */
    public function _generate_token($id) {
        $hash_no = (int) $this->getConf('hash_no');
        $l = (int) $this->getConf('no_pref');
        $this->hash['pre'] = helper_plugin_fkshelper::_generate_rand($l);
        $this->hash['pos'] = helper_plugin_fkshelper::_generate_rand($l);
        $this->hash['hex'] = dechex($hash_no + 2 * $id);
        $this->hash['hash'] = $this->hash['pre'] . $this->hash['hex'] . $this->hash['pos'];
        return (string) DOKU_URL . '?do=fksnewsfeed_token&token=' . $this->hash['hash'];
    }

    /**
     * load news @i@ and return text
     * @author     Michal Červeňák <miso@fykos.cz>
     * @param int $id
     * @return string
     */
    public function load_news_simple($id) {
        $sql = 'SELECT * from fks_newsfeed_news where id=' . $id . '';
        $res = $this->sqlite->query($sql);
        foreach ($this->sqlite->res2arr($res) as $row) {
           
            return $row;
        }
    }

}
