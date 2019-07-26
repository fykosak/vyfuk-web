<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 6.8.2017
 * Time: 14:33
 */

namespace PluginNewsFeed\Model;


abstract class AbstractModel extends \helper_plugin_fksnewsfeed {

    public final function getPluginName() {
        return 'fksnewsfeed';
    }

    abstract public function fill($data);

    abstract public function fillFromDatabase();

    abstract public function create();

    abstract public function update();

}