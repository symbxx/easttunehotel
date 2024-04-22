<?php

namespace addons\addondev\library;

use app\admin\model\addondev\AddondevGen;
use think\Config;

class GenHelper
{

    public static function systemTables()
    {
        $dbPrefix = config('database.prefix');
        $tables = array_map(function ($name) use ($dbPrefix) {
            return $dbPrefix . $name;
        }, [
            'admin',
            'admin_log',
            'auth_group',
            'auth_group_access',
            'auth_rule',
            'attachment',
            'config',
            'category',
            'ems',
            'sms',
            'user',
            'user_group',
            'user_rule',
            'user_score_log',
            'user_token'
        ]);

        $placeholders = implode(',', array_fill(0, count($tables), '?'));
        return [$tables, $placeholders];
    }

    /**
     * 重新构建菜单数组
     */
    public static function rebuildArrayForMenu($addon, $data = [])
    {
        $string = "<?php\n\n//插件" . $addon . "后台管理的菜单\n//先在后台生成和配置好菜单，再导出复制到插件文件中 \n\$menu = " . var_export($data, TRUE) . ";";
        $string = str_replace("\t", "\s\s\s\s", $string);
        $string = preg_replace("/\s+\d+\s+=>\s/", "", $string);
        $string = preg_replace("/\s+\'id\'\s=>.*?,\n/", "", $string);
        $string = preg_replace("/\s+\'pid\'\s=>.*?,/\n", "", $string);
        $string = preg_replace("/\s+\'spacer\'\s=>.*?,/\n", "", $string);
        $string = str_replace("),", "],", $string);
        $string = str_replace(");", "];", $string);
        $string = str_replace("array (", "[", $string);
        $string = str_replace("childlist", "sublist", $string);
        return $string;
    }

    public static function arrayRemoveEmpty($arr)
    {
        $narr = array();
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $val = self::arrayRemoveEmpty($val);
                if (count($val) != 0) {
                    $narr[$key] = $val;
                }
            } else {
                if (trim($val) !== "") {
                    $narr[$key] = $val;
                }
            }
        }
        unset($arr);
        return $narr;
    }

    public static function backupGen($addon)
    {
        $genList = collection(AddondevGen::all(['addon' => $addon]))->each(function ($gen) {
            $gen->append([], true);
        })->toArray();
        if ($genList) {
            foreach ($genList as &$gen) {
                unset($gen['id']);
                $gen = array_filter($gen, function ($val) {
                    return !($val === '' || $val === null);
                });
            }
            $path = self::getAddondevFile($addon);
            $data = ['gen' => $genList, '__remark__' => '[FastAdmin插件开发辅助增强插件]使用的代码生成模板备份文件'];
            file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    public static function recoverGen($addon)
    {
        $path = self::getAddondevFile($addon);
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (isset($data['gen'])) {
                foreach ($data['gen'] as $gen) {
                    unset($gen["__remark__"]);
                    $genObj = AddondevGen::where([
                        'name' => $gen['name'],
                        'addon' => $addon,
                        'mtable' => $gen['mtable'],
                        'controller' => $gen['controller']
                    ])->find();
                    if (empty($genObj)) {
                        $genObj = new AddondevGen($gen);
                    }
                    $genObj->save($gen);
                }
            }
        }
    }

    private static function getAddondevFile($addon)
    {
        return ADDON_PATH . $addon . DS . 'addondev.json';
    }

    public static function getAddonList()
    {
        $addonList = array_keys(get_addon_list());
        $addonList = array_combine($addonList, $addonList);
        $gitDir = ADDON_PATH . 'addondev' . DS . '.git';
        if (!is_dir($gitDir)) {
            unset($addonList['addondev']);
        }
        return $addonList;
    }

    /**
     * 获得插件列表
     * @return array
     */
    public static function addonList()
    {
        $results = scandir(ADDON_PATH);
        $list = [];
        foreach ($results as $name) {
            if ($name === '.' or $name === '..') {
                continue;
            }
            if (is_file(ADDON_PATH . $name)) {
                continue;
            }
            $addonDir = ADDON_PATH . $name . DS;
            if (!is_dir($addonDir)) {
                continue;
            }

            if (!is_file($addonDir . ucfirst($name) . '.php')) {
                continue;
            }

            //这里不采用get_addon_info是因为会有缓存
            //$info = get_addon_info($name);
            $info_file = $addonDir . 'info.ini';
            if (!is_file($info_file)) {
                continue;
            }

            $info = Config::parse($info_file, '', "addon-info-{$name}");
            if (!isset($info['name'])) {
                continue;
            }
            $info['url'] = addon_url($name);
            if ($name == 'addondev') {
                $info['lasttime'] = 0;
            } else {
                $info['lasttime'] = filemtime($addonDir . '.');
            }
            $list[$name] = $info;
        }
        usort($list, function ($info1, $info2) {
            return $info1['lasttime'] < $info2['lasttime'];
        });
        return $list;
    }

}
