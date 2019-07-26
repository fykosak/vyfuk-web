<?php
if (!defined('DOKU_INC')) {
    die();
}

// include custom template functions stolen from arctic template 
require_once(dirname(__FILE__) . '/tpl_functions.php');
require_once(dirname(__FILE__) . '/navBar/BootstrapNavBar.php');
require_once(dirname(__FILE__) . '/navBar/NavBarItem.php');


global $ACT;
global $ID;
global $conf;
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']; ?>" lang="<?php echo $conf['lang']; ?>"
      dir="<?php echo $lang['direction'] ?>">

<head>
    <link href="http://fonts.googleapis.com/css?family=Shadows+Into+Light+Two&amp;subset=latin,latin-ext"
          rel="stylesheet" type="text/css"/>
    <link
            href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,300,400italic,400,600italic,600,700italic,700,800italic,800"
            rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="//use.edgefonts.net/gloria-hallelujah:n4:all;source-sans-pro.js">
    </script>


    <meta name="viewport" content="width=device-width, target-densityDpi=device-dpi"/>
    <title>
        <?php
        echo tpl_pagetitle($ID, true) . (preg_match('/výfuk/i', tpl_pagetitle($ID, true)) ?: '::' . strip_tags($conf['title']))
        ?>

    </title>
    <?php
    tpl_metaheaders();
    echo tpl_favicon(['favicon', 'mobile']);
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"
            integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb"
            crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"
            integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn"
            crossorigin="anonymous"></script>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta name="google-site-verification" content="Z__tiJ79t7gWSoTBODkD_FP3YXja-tL4myNJSh5JFKc"/>
</head>
<body data-act="<?php echo $ACT; ?>"
      data-namespace="<?php echo getNS($ID); ?>"
      data-page_id="<?php echo $ID; ?>">

<div class="dokuwiki" id="dokuwiki" data-do="<?php echo $ACT ?>">
    <div class="bg-vyfuk-green">
        <?php
        $fullMenu = new \fksTemplate\NavBar\BootstrapNavBar('full');
        $fullMenu->setClassName('container navbar-inverse ')
            ->addMenuText('menu')
            ->addTools('ml-auto justify-content-end', true)
            ->render();
        ?>
    </div>
    <div class="container main-container">

        <header class="header">
            <div class="row justify-content-around pt-3 pb-3">
                <div class="col-lg-4 col-md-12 header-logo">
                    <a
                            href="<?php echo DOKU_BASE ?>">
                        <img
                                src="<?php echo DOKU_TPL . '/images/logo.png'; ?>"
                                alt="logo_vyfuk"/>
                    </a>
                </div>

                <div class="col-lg-2 hidden-md-down header-image-1">
                    <img
                            src="<?php echo DOKU_TPL . '/images/header-1.png'; ?>"
                            alt="logo_vyfuk"
                    />
                </div>
                <div class="col-lg-2 hidden-md-down header-image-2">
                    <img
                            src="<?php echo DOKU_TPL . '/images/header-2.png'; ?>"
                            alt="logo_vyfuk"
                    />
                </div>
                <div class="col-lg-3 hidden-md-down header-image-3">
                    <img
                            src="<?php echo DOKU_TPL . '/images/header-3.png'; ?>"
                            alt="logo_vyfuk"
                    />
                </div>
            </div>
        </header>
        <?php
        flush();
        ?>
        <div class="row">
            <main class="col-lg-8 col-md-12">
                <?php
                tpl_content();
                ?>
            </main>
            <?php
            flush();
            if ($ACT === 'show') {
                ?>
                <aside class="col-lg-4 col-md-12">
                    <?php echo tpl_include_page('system:fkssidebar'); ?>
                </aside>
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
        <footer class="text-center pt-3 mt-3">
            <?php echo p_render('xhtml', p_cached_instructions('data/pages/system/fksfootbar.txt'), $info); ?>
            <div class="row">
                <a class="col-6"
                   href="http://wiki.splitbrain.org/wiki:dokuwiki" title="Driven by DokuWiki">
                    <img src="<?php echo DOKU_TPL . 'images/button-dw.png'; ?>" width="80" height="15"
                         alt="Driven by DokuWiki"/>
                </a>
                <a class="col-6"
                   href="<?php echo DOKU_BASE . 'feed.php'; ?>" title="Recent changes RSS feed">
                    <img src="<?php echo DOKU_TPL . 'images/button-rss.png'; ?>" width="80" height="15"
                         alt="Recent changes RSS feed"/>
                </a>
            </div>
        </footer>

    </div>

</div>
</body>
</html>



