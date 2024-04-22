<?php

namespace addons\addondev;

use addons\addondev\library\ClassLoader;
use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Addondev extends Addons
{

    /**
     * 插件安装方法
     *
     * @return bool
     */
    public function install()
    {
        $menu = require(__DIR__ . "/inc/backendmenu.php");
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     *
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('addondev');
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('addondev');
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('addondev');
    }

    /**
     * 升级
     */
    public function upgrade()
    {
        $upgradeClass = '\addons\addondev\inc\AddonUpgrade';
        if (class_exists($upgradeClass)) {
            call_user_func([$upgradeClass, 'exec']);
        }
    }

    /**
     * 应用初始化
     */
    public function appInit()
    {

        $libpath = ADDON_PATH . 'addondev' . DS . 'library' . DS;
        ClassLoader::addPsr0("Diff", $libpath . "PhpDiff" . DS, true);

        if (request()->isCli()) {
            \think\Console::addDefaultCommands([
                'addons\addondev\command\Addondev',
                'addons\addondev\command\Addoncrud'
            ]);
        }
    }
}
