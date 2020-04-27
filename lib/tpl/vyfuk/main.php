<?php
if (!defined('DOKU_INC')) {
    die();
}

// include custom template functions stolen from arctic template 
require_once(dirname(__FILE__) . '/tpl_functions.php');


require_once (DOKU_PLUGIN . plugin_directory('fksimageshow') . '/helper.php');
global $ACT;
global $ID;
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']; ?>" lang="<?php echo $conf['lang']; ?>" dir="<?php echo $lang['direction'] ?>">

    <head>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
        <script type="text/javascript" src="//use.edgefonts.net/gloria-hallelujah:n4:all;source-sans-pro.js" >
        </script>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, target-densityDpi=device-dpi" />
        <title>
            <?php
            if (preg_match('/výfuk/i', tpl_pagetitle($ID, true))) {
                echo tpl_pagetitle($ID, true);
            } else {
                echo tpl_pagetitle($ID, true), '::', strip_tags($conf['title']);
            }
            ?>

        </title>
        <?php
        tpl_metaheaders();
        echo tpl_favicon(array('favicon', 'mobile'));
        ?>
       
        <script>
            (function(i, s, o, g, r, a, m) {
                i["GoogleAnalyticsObject"] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, "script", "//www.google-analytics.com/analytics.js", "ga");
            ga("create", "UA-51523765-1", "cuni.cz");
            ga("send", "pageview");</script>
        
        <script src="<?php echo DOKU_BASE . 'lib/tpl/' . $conf['template'] . '/bootstrap.min.js'; ?>">
        </script>
 <meta name="google-site-verification" content="Z__tiJ79t7gWSoTBODkD_FP3YXja-tL4myNJSh5JFKc" />
    </head>
    <body data-do="<?php echo $ACT ?>">
        <div class="clearer">
        </div>
        <div class="dokuwiki" id="dokuwiki" data-do="<?php echo $ACT ?>">
            <div class="fksmenu">
                <?php _wp_tpl_mainmenu(); ?>
            </div>

            <div class="clearer">
            </div>
            <div class="wrap">

                <div class="header">
                    <div class="clearer">
                    </div>
                    <div class="fkspagename" style="text-align:center">
                        <div class="fks_vyfuk_logo" >
                            <a href="<?php echo DOKU_BASE ?>">
                                <img src="<?php echo '/lib/tpl/vyfuk/images/logo-o.svg' ?>" style="height:150px;" alt="logo_vyfuk" />
                            </a>
                        </div>
                        <div class="fks_mrak_1">
                            <img src="<?php echo '/lib/tpl/vyfuk/images/season/default/vyfuk_mrak_1.png'?>" alt="logo_vyfuk"  style="height:90px" />
                        </div>
                        <div class="fks_mrak_2">
                            <img src="<?php echo '/lib/tpl/vyfuk/images/season/default/vyfuk_mrak_2.png' ?>" alt="logo_vyfuk"  style="height:80px" />
                        </div>
                        <div class="fks_vyfucek">
                            <img src="<?php echo '/lib/tpl/vyfuk/images/season/default/vyfuk_tabule.png' ?>" alt="logo_vyfuk"  style="height:150px" />
                        </div>
                    </div>

                    <div class="clearer">
                    </div>
                </div>
                <?php
                flush();
                ?>
                <div class="clearer">
                </div>
                <div class="page" >
                    <?php
                    _fks_noscript();
                    tpl_content();
                    ?>
                </div>
                <?php
                flush();
                if ($ACT == 'show') {
                    ?>
                    <div class="fkssidebar_R">
                        <?php echo p_render("xhtml", p_cached_instructions('data/pages/system/fkssidebar.txt'), $info); ?>
                    </div>
                    <div class="clearer">
                    </div>
                    <?php
                }
                tpl_flush();
                ?>
                <div class="no">
                    <?php
                    tpl_indexerWebBug();
                    ?>
                </div>
            </div>
            <div class="fksfootbar">
                <?php echo p_render("xhtml", p_cached_instructions("data/pages/system/fksfootbar.txt"), $info); ?>
                <a href="http://wiki.splitbrain.org/wiki:dokuwiki" title="Driven by DokuWiki">
                    <img src="<?php echo DOKU_TPL . 'images/button-dw.png'; ?>" width="80" height="15" alt="Driven by DokuWiki" />
                </a>
                <a href="<?php echo DOKU_BASE . 'feed.php'; ?>" title="Recent changes RSS feed">
                    <img src="<?php echo DOKU_TPL . 'images/button-rss.png'; ?>" width="80" height="15" alt="Recent changes RSS feed" />
                </a>
            </div>
        </div>
    </body>
</html>



