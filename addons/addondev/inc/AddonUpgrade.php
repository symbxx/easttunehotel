<?php
namespace addons\addondev\inc;

use app\common\library\Menu;

class AddonUpgrade {

    public static function exec(){
        $newMenus = require(__DIR__ . "/backendmenu.php");
        Menu::upgrade('addondev',$newMenus);
    }
}