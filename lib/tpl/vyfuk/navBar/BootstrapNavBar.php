<?php

namespace fksTemplate\NavBar;

class BootstrapNavBar {

    /**
     * @var array
     */
    private $data = [];
    /**
     * @var string
     */
    private $brand = null;
    /**
     * @var string
     */
    private $html = '';
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $id;

    const USER_TOOLS_CONTAINER = 'div class="tools"';

    public function __construct($id) {
        $this->id = $id;
    }

    /**
     * @param $className string
     * @return $this
     */
    public function setClassName($className) {
        $this->className = $className;
        return $this;
    }

    /**
     * @param string $class
     * @param bool $allowLoggedInOnly
     * @return $this
     */
    public function addTools($class = '', $allowLoggedInOnly = false) {
        global $INFO;
        global $lang;
        if ($allowLoggedInOnly && !$INFO['userinfo']['name']) {
            return $this;
        }
        $data = [];
        $data[] = new NavBarItem([
            'id' => null,
            'level' => 1,
            'content' => '<span class="nav-item fa fa-cogs"></span>'
        ]);

        $userName = (($INFO['userinfo']['name'] != null) ? ($lang['loggedinas'] .
            $INFO['userinfo']['name']) : tpl_getLang('nologin'));
        $data[] = new NavBarItem([
            'id' => null,
            'level' => 2,
            'content' => '<div class="dropdown-item"><span class="fa fa-user"></span>
' . $userName . '</div>'
        ]);
        $data = array_merge($data, $this->getUserTools(), $this->getPageTools(), $this->getSiteTools());
        $this->data[] = [
            'class' => 'nav ' . $class,
            'data' => $data,
        ];
        return $this;
    }

    /**
     * @param string $href
     * @param string $text
     * @param string $imageSrc
     * @return $this
     */
    public function addBrand($href = '', $text = null, $imageSrc = null) {
        $html = '<a class="navbar-brand" href="' . wl(cleanID($href)) . '">';
        if ($imageSrc) {
            $html .= '<img src="' . DOKU_TPL . $imageSrc .
                '" width="30" height="30" class="d-inline-block align-top" alt="">';
        }
        if ($text) {
            $html .= $text;
        }
        $html .= '</a>';
        $this->brand = $html;
        return $this;
    }

    /**
     * @return $this
     */
    public function mainMenu() {
        $this->html .= '<nav class="navbar navbar-toggleable-md ' . $this->className . '">';
        if ($this->brand) {
            $this->html .= $this->brand;
        }
        $this->html .= '        
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="' . '#mainNavbar' . $this->id . '" 
    aria-controls="navbarSupportedContent" 
    aria-expanded="false" 
    aria-label="Toggle navigation">';

        $this->html .= '<span class="navbar-toggler-icon"></span>';
        $this->html .= '</button>   
         <div class="collapse navbar-collapse" id="mainNavbar' . $this->id . '">';
        foreach ($this->data as $item) {
            $this->renderNavBar($item['data'], $item['class']);
        }
        $this->html .= '</div>';
        $this->html .= '</nav>';
        return $this;
    }

    /**
     * @return string
     */
    public function render() {
        $this->mainMenu();
        echo $this->html;
        return $this;
    }

    /**
     * @param $filename string
     * @return NavBarItem[]
     */
    private function parseMenuFile($filename) {
        $filePath = wikiFN($filename);
        $data = [];
        if (file_exists($filePath)) {
            $lines = array_filter(file($filePath),
                function ($line) {
                    return preg_match('/^\s+\*/', $line);
                });

            $numLines = count($lines);
            for ($i = 0; $i < $numLines; $i++) {
                if (!$lines[$i]) {
                    continue;
                }
                list ($prefix, $content) = explode('*', $lines[$i]);
                $level = (int)strlen($prefix) / 2;
                $level = ($level > 2) ? 2 : $level;

                if (!preg_match('/\s*\[\[[^\]]+\]\]/', $content)) {
                    continue;
                }
                $content = str_replace([']', '['], '', trim($content));
                list($id, $content, $icon) = explode('|', $content);
                $data[] = new NavBarItem([
                    'id' => $id,
                    'icon' => $icon,
                    'level' => $level,
                    'content' => $content
                ]);
            }
        }
        return $data;
    }

    /**
     * @param string $file
     * @param string $class
     * @return $this
     */
    public function addMenuText($file = 'menu', $class = '') {
        global $conf;
        $pageLang = $conf['lang'];
        $menuFileName = 'system/' . $file . '_' . $pageLang;

        $this->data[] = [
            'class' => 'nav ' . $class,
            'data' => $this->parseMenuFile($menuFileName),
        ];
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function addLangSelect($class = '') {
        global $conf;
        $data = [];
        if (count($conf['available_lang']) == 0) return $this;
        $data[] = new NavBarItem([
            'id' => null,
            'level' => 1,
            'content' => '<span class="fa fa-language"></span>'
        ]);

        foreach ($conf['available_lang'] as $currentLang) {

            $data[] = new NavBarItem([
                'id' => null,
                'level' => 2,
                'content' => '<a 
                href="' . $currentLang['content']['url'] . '" 
                class="dropdown-item ' . $currentLang['content']['class'] . ' ' .
                    ($currentLang['code'] == $conf['lang'] ? 'active' : '') . '"
                ' . $currentLang['content']['more'] . '
                >' . $currentLang['content']['text'] . ' </a> '
            ]);
        }
        $this->data[] = [
            'class' => 'nav ' . $class,
            'data' => $data,
        ];
        return $this;
    }

    /**
     * @return NavBarItem[]
     */
    private function getUserTools() {
        global $lang;
        $data = [];
        $data[] = new NavBarItem([
            'id' => null,
            'level' => 2,
            'content' => ' <div class="dropdown-header" ><span class="glyphicon glyphicon-user" ></span> ' .
                $lang['user_tools'] . ' .</div> '
        ]);

        $userTools = [
            'view' => 'main',
            'items' => [
                'admin' => tpl_action('admin', true, self::USER_TOOLS_CONTAINER, 1),
                //   'userpage' => tpl_action('userpage',1,'li',1),
                'profile' => tpl_action('profile', true, self::USER_TOOLS_CONTAINER, 1),
                'register' => tpl_action('register', true, self::USER_TOOLS_CONTAINER, 1),
                'login' => tpl_action('login', true, self::USER_TOOLS_CONTAINER, 1)
            ]
        ];
        $evt = new \Doku_Event('TEMPLATE_USERTOOLS_DISPLAY', $userTools);

        if ($evt->advise_before()) {
            foreach ($evt->data['items'] as $k => $html) {
                $data[] = new NavBarItem([
                    'id' => null,
                    'level' => 2,
                    'content' => $html
                ]);
            }
        }
        $evt->advise_after();
        return $data;
    }

    /**
     * @return NavBarItem[]
     */
    private function getSiteTools() {
        global $lang;
        $data = [];
        $data[] = new NavBarItem([
            'id' => null,
            'level' => 2,
            'content' => ' <div class="dropdown-header" ><span class="glyphicon glyphicon-user" ></span> ' .
                $lang['site_tools'] . ' .</div > '
        ]);

        ob_start();
        tpl_searchform();
        $search_form = ob_get_contents();
        ob_end_clean();

        $siteTools = [
            'view' => 'main',
            'items' => [
                'recent' => tpl_action('recent', 1, self::USER_TOOLS_CONTAINER, 1),
                'media' => tpl_action('media', 1, self::USER_TOOLS_CONTAINER, 1),
                'index' => tpl_action('index', 1, self::USER_TOOLS_CONTAINER, 1),
                'search' => $search_form
            ]
        ];

        $event = new \Doku_Event('TEMPLATE_USERTOOLS_DISPLAY', $siteTools);

        if ($event->advise_before()) {
            foreach ($event->data['items'] as $k => $html) {
                $data[] = new NavBarItem([
                    'id' => null,
                    'level' => 2,
                    'content' => $html
                ]);
            }
        }

        $event->advise_after();
        return $data;
    }

    /**
     * @return NavBarItem[]
     */
    private function getPageTools() {
        global $lang;
        $data = [];
        $data[] = new NavBarItem([
            'id' => null,
            'level' => 2,
            'content' => ' <div class="dropdown-header"><span class="fa fa-user-o"></span> ' . $lang['page_tools'] .
                ' </div> '
        ]);
        $pageTools = [
            'view' => 'main',
            'items' => [
                'edit' => tpl_action('edit', 1, self::USER_TOOLS_CONTAINER, 1),
                'revert' => tpl_action('revert', 1, self::USER_TOOLS_CONTAINER, 1),
                'revisions' => tpl_action('revisions', 1, self::USER_TOOLS_CONTAINER, 1),
                'backlink' => tpl_action('backlink', 1, self::USER_TOOLS_CONTAINER, 1),
                'subscribe' => tpl_action('subscribe', 1, self::USER_TOOLS_CONTAINER, 1)
            ]
        ];
        $event = new \Doku_Event('TEMPLATE_PAGETOOLS_DISPLAY', $pageTools);

        if ($event->advise_before()) {
            foreach ($event->data['items'] as $k => $html) {
                $data[] = new NavBarItem([
                    'id' => null,
                    'level' => 2,
                    'content' => $html
                ]);
            }
        }

        $event->advise_after();
        return $data;
    }

    /**
     * @param $data NavBarItem[]
     * @param string $class
     */
    private function renderNavBar($data, $class = '') {
        $inLI = false;
        $inUL = false;

        $this->html .= ' <div class="nav navbar-nav ' . $class . '" > ';

        foreach ($data as $k => $item) {

            $link = $item->getLink();
            $title = $item->getIcon() . $item->getContent();
            if ($item->getLevel() == 1) {
                if ($inUL) {
                    $inUL = false;
                    $this->html .= '</div>';
                }
                if ($inLI) {
                    $inLI = false;
                    $this->html .= '</div>';
                }
                /* is next level 2? */
                if ($data[$k + 1] && $data[$k + 1]->getLevel() == 2) {
                    $inLI = true;
                    $this->html .= '<div class="dropdown nav-item"><a href="' . $link .
                        '" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >' .
                        $title . '<span class="caret"></span></a>';
                } else {
                    $this->html .= '<a class="nav-item nav-link" href="' . $link . '">' . $title . '</a>';
                }
            } elseif ($item->getLevel() == 2) {
                if (!$inUL) {
                    $inUL = true;
                    $this->html .= '<div class="dropdown-menu" role="menu">' . "\n";
                }

                if ($item->hasId()) {
                    $this->html .= '<a class="dropdown-item" href="' . $link . '">' . $title . '</a>';
                } else {
                    $this->html .= $title;
                }
            }
        }
        if ($inUL) {
            $this->html .= '</div>';
        }
        if ($inLI) {
            $this->html .= '</div>';
        }
        $this->html .= '</div>';
    }
}
