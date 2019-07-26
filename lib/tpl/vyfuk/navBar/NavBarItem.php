<?php

namespace fksTemplate\NavBar;

class NavBarItem {

    private $id;
    private $icon;
    private $level;
    private $content;

    public function __construct($parameters) {
        $this->id = $parameters['id'];
        $this->icon = $parameters['icon'];
        $this->level = $parameters['level'];
        $this->content = $parameters['content'];
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIcon() {
        if ($this->icon) {
            return ' <span class="' . $this->icon . '" ></span > ';
        }
        return '';
    }

    /**
     * @return bool
     */
    private function isExternal() {
        return preg_match('#https?://#', $this->id);
    }

    /**
     * @return integer
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getLink() {
        if ($this->isExternal()) {
            return htmlspecialchars($this->id);
        }
        return wl(cleanID($this->id));
    }

    /**
     * @return bool
     */
    public function hasId() {
        return !is_null($this->id);
    }
}