<?php

namespace addons\addondev\library;

use think\addons\AddonException;
use think\addons\Service;
use think\Config;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class Addon
{
    protected static $ignorePackageDirs = [
        '.git', '.svn', '.settings', '.vscode',
        'node_modules', 'dist_electron'
    ];

    protected static $ignorePackageFiles = [
        '.DS_Store', 'Thumbs.db',
        'README.md', 'readme.md', '.addonrc',
        '.regularignore', '.extendedigore',
        'regular_menu.php', 'extended_menu.php',
        'regular_install.sql', 'extended_install.sql',
    ];

    public $name;

    public $action = 'create';

    public $force;

    public $release;

    public $uid;

    public $token;

    public $domain;

    public $local;

    /**
     * 发行高级版分支
     */
    public $extended;

    public $info;



    /**
     * 不观察到符号
     */
    protected $ignoreWatchTokens = [];

    /**
     * 只能使用在单插件模式的命令
     */
    protected $supportOneAddonCommands = [
        'create',
        'disable',
        'enable',
        'install',
        'reinstall',
        'uninstall',
        'upgrade',
        'package',
        'move',
    ];

    /**
     * 可以使用到多个插件的命令
     */
    protected $supportMoreAddonCommands = [
        'refresh',
        'watch',
        'sync'
    ];

    public function do(OutputFacade $output, $name, $action, $force = null, $release = null, $uid = null, $token = null, $domain = null, $local = null)
    {
        $this->name = $name;
        $this->action = $action;
        $this->force = $force;
        $this->release = $release;
        $this->uid = $uid;
        $this->token = $token;
        $this->domain = $domain;
        $this->local = $local;
        $this->execute($output);
    }

    /**
     * 网页版设置个性化信息
     */
    public function setInfo($info)
    {
        $default = [
            'name' => '',
            'title' => '',
            'intro' => '',
            'author' => 'youname',
            'website' => 'http://www.addondev.cn',
            'version' => '1.0.0',
            'state' => '1',
            'url' => '',
            'license' => '',
            'licenseto' => '',
        ];
        $this->info = array_merge($default, $info);
    }

    protected function parseAddonName($command)
    {
        if ($this->name) {
            $this->name = explode(",", $this->name);
            $this->name  = array_map(function ($name) {
                if (stripos($name, 'addons' . DS) !== false) {
                    $name = explode(DS, $name)[1];
                }
                return $name;
            }, $this->name);
            if (!in_array($command, $this->supportMoreAddonCommands)) {
                $this->name = $this->name[0];
            }
        }
    }

    public function execute(OutputFacade $output)
    {
        $allowCommands = array_merge($this->supportOneAddonCommands, $this->supportMoreAddonCommands);
        $action = $this->action ?: '';
        $this->parseAddonName($action);
        $name = $this->name ?: '';

        // 版本
        $release = $this->release ?: '';
        // uid
        $uid = $this->uid ?: '';
        // token
        $token = $this->token ?: '';

        $this->ignoreWatchTokens = $this->parseIgnoreFile(dirname(__DIR__) . DS);

        //加载帮助函数
        include ROOT_PATH . 'application' . DS . 'admin' . DS . 'common.php';

        if (!$name && !in_array($action, [
            'refresh'
        ])) {
            throw new Exception('Addon name could not be empty');
        }

        if (!$action || !in_array($action, $allowCommands)) {
            throw new Exception('Please input correct action name');
        }

        // 查询一次SQL,判断连接是否正常
        Db::execute("SELECT 1");

        $movePath = [
            'adminOnlySelfDir' => [
                'admin/behavior',
                'admin/controller',
                'admin/library',
                'admin/model',
                'admin/validate',
                'admin/view',
                'common/model',
                'common/validate',
                'index/controller',
                'index/validate',
                'index/view',
                'api/controller',
                'api/validate',
            ],
            'adminAllSubDir' => [
                'admin/lang',
                'index/lang',
                'api/lang',
            ],
            'publicDir' => [
                'public/assets/js/backend',
                'public/assets/js/frontend'
            ],
            //键值优先，否则使用值的目录
            //如果使用了键，则值是目标目录的base目录
            //如果使用了值，则正常流程
            'shortMapDir' => [
                'assets' => 'public/assets/addons',
            ]
        ];
        switch ($action) {
            case 'create':
                $this->commandCreate($name, $output);
                break;
            case 'disable':
            case 'enable':
                $this->commandAble($name, $action, $output);
                break;
            case 'install':
                $this->commandInstall($name, $output);
                break;
            case 'reinstall':
                $this->commandReinstall($name, $output);
                break;
            case 'uninstall':
                $this->commandUninstall($name, $output);
                break;
            case 'upgrade':
                $this->commandUpgrade($name, $output);
                break;
            case 'refresh':
                $this->commandRefresh($output);
                break;
            case 'package':
                $this->commandPackage($name, $output);
                break;
            case 'move':
                $this->move($name, $movePath, $this->force);
                break;
            case 'watch':
                $this->watchChange($name, $movePath, $output);
                break;
            case 'sync':
                $this->watchChange($name, $movePath, $output, false);
                break;
            default:
                $output->warning("Command [ " . $name . " ] NOT FOUND!");
                break;
        }
    }

    protected function commandCreate($name, $output)
    {
        $addonDir  = ADDON_PATH . $name . DS;
        // 非覆盖模式时如果存在则报错
        if (is_dir($addonDir) && !$this->force) {
            throw new Exception("addon already exists!\nIf you need to create again, use the parameter --force=true ");
        }
        // 如果存在先移除
        if (is_dir($addonDir)) {
            rmdirs($addonDir);
        }
        mkdir($addonDir, 0755, true);
        mkdir($addonDir . DS . 'controller', 0755, true);
        $menuList = \app\common\library\Menu::export($name);
        $createMenu = $this->getCreateMenu($menuList);
        $prefix = Config::get('database.prefix');
        $createTableSql = '';
        try {
            $result = Db::query("SHOW CREATE TABLE `" . $prefix . $name . "`;");
            if (isset($result[0]) && isset($result[0]['Create Table'])) {
                $createTableSql = $result[0]['Create Table'];
            }
        } catch (PDOException $e) {
        }

        $data = [
            'name' => $name,
            'addon' => $name,
            'addonClassName' => ucfirst($name),
            'addonInstallMenu' => $createMenu ? "\$menu = " . var_export_short($createMenu) . ";\n\tMenu::create(\$menu);" : '',
            'addonUninstallMenu' => $menuList ? 'Menu::delete("' . $name . '");' : '',
            'addonEnableMenu' => $menuList ? 'Menu::enable("' . $name . '");' : '',
            'addonDisableMenu' => $menuList ? 'Menu::disable("' . $name . '");' : ''
        ];
        $this->writeToFile("addon", $data, $addonDir . ucfirst($name) . '.php');
        $this->writeToFile("config", $data, $addonDir . 'config.php');
        //$this->writeToFile("info", $data, $addonDir . 'info.ini');
        $this->writeAddonInfo("info", $data, $addonDir . 'info.ini');
        $this->writeToFile("controller", $data, $addonDir . 'controller' . DS . 'Index.php');
        if ($createTableSql) {
            $createTableSql = str_replace("`" . $prefix, '`__PREFIX__', $createTableSql);
            file_put_contents($addonDir . 'install.sql', $createTableSql);
        }

        $output->info("Create [ " . $name . " ] Successed!");
    }

    protected function commandAble($name, $action, $output)
    {
        try {
            // 调用启用、禁用的方法
            Service::$action($name, 0);
        } catch (AddonException $e) {
            if ($e->getCode() != -3) {
                throw new Exception($e->getMessage());
            }
            if (!$this->force) {
                // 如果有冲突文件则提醒
                $data = $e->getData();
                foreach ($data['conflictlist'] as $k => $v) {
                    $output->warning($v);
                }
                $output->info("Are you sure you want to " . ($action == 'enable' ? 'override' : 'delete') . " all those files?  Type 'yes' to continue: ");
                $line = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($line) != 'yes') {
                    throw new Exception("Operation is aborted!");
                }
            }
            // 调用启用、禁用的方法
            Service::$action($name, 1);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        $output->info(ucfirst($action) . " [ " . $name . " ] Successed!");
    }


    protected function commandInstall($name, $output)
    {
        // 默认启用该插件
        $info = get_addon_info($name);
        Db::startTrans();
        try {
            if (!$info['state']) {
                $info['state'] = 1;
                set_addon_info($name, $info);
            }
            // 执行安装脚本
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install();
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        // 导入
        Service::importsql($name);
        // 启用插件
        Service::enable($name, true);

        $output->info("Install  [ " . $name . " ]  Successed!");
    }

    protected function commandReinstall($name, $output)
    {
        // 非覆盖模式时如果存在则报错
        if (!$this->force) {
            throw new Exception("If you need to reinstall addon, use the parameter --force=true ");
        }
        // 执行卸载脚本

        $class = get_addon_class($name);
        Db::startTrans();
        try {
            if (class_exists($class)) {
                $addon = new $class();
                //执行 插件卸载方法
                $addon->uninstall();
                if ($this->force) {
                    //清理插件的数据表
                    $tables = get_addon_tables($name);
                    if ($tables) {
                        $prefix = Config::get('database.prefix');
                        //删除插件关联表
                        foreach ($tables as $table) {
                            //忽略非插件标识的表名
                            if (!preg_match("/^{$prefix}{$name}/", $table)) {
                                continue;
                            }
                            Db::execute("DROP TABLE IF EXISTS `{$table}`");
                        }
                    }
                }
                //重新安装插件
                $addon->install();
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        // 导入
        Service::importsql($name);
        // 启用插件
        Service::enable($name, true);

        $output->info("Reinstall [ " . $name . " ] Successed!");
    }

    protected function commandUninstall($name, $output)
    {
        // 非覆盖模式时如果存在则报错
        if (!$this->force) {
            throw new Exception("If you need to uninstall addon, use the parameter --force=true ");
        }
        try {
            Service::uninstall($name, 0);
        } catch (AddonException $e) {
            if ($e->getCode() != -3) {
                throw new Exception($e->getMessage());
            }
            if (!$this->force) {
                // 如果有冲突文件则提醒
                $data = $e->getData();
                foreach ($data['conflictlist'] as $k => $v) {
                    $output->warning($v);
                }
                $output->info("Are you sure you want to delete all those files?  Type 'yes' to continue: ");
                $line = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
                if (trim($line) != 'yes') {
                    throw new Exception("Operation is aborted!");
                }
            }
            Service::uninstall($name, 1);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $output->info("Uninstall [ " . $name . " ] Successed!");
    }

    protected function commandUpgrade($name, $output)
    {
        $addonDir = Service::getAddonDir($name);
        // 导入
        Service::importsql($name);

        // 执行升级脚本
        try {
            $addonName = ucfirst($name);
            //创建临时类用于调用升级的方法
            $sourceFile = $addonDir . $addonName . ".php";
            $destFile = $addonDir . $addonName . "Upgrade.php";

            $classContent = str_replace("class {$addonName} extends", "class {$addonName}Upgrade extends", file_get_contents($sourceFile));

            //创建临时的类文件
            file_put_contents($destFile, $classContent);

            $className = "\\addons\\" . $name . "\\" . $addonName . "Upgrade";
            $addon = new $className($name);

            //调用升级的方法
            if (method_exists($addon, "upgrade")) {
                $addon->upgrade();
            }

            //移除临时文件
            @unlink($destFile);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        // 刷新
        Service::refresh();

        $output->info("Upgrade  [ " . $name . " ]  Successed!");
    }

    protected function commandRefresh($output)
    {
        Service::refresh();
        $output->info("Refresh Successed!");
    }

    protected function commandPackage($name, $output)
    {
        $addonDir  = ADDON_PATH . $name . DS;
        $infoFile = $addonDir . 'info.ini';
        if (!is_file($infoFile)) {
            throw new Exception(__('Addon info file was not found'));
        }

        $info = get_addon_info($name);
        if (!$info) {
            throw new Exception(__('Addon info file data incorrect'));
        }
        $infoname = isset($info['name']) ? $info['name'] : '';
        if (!$infoname || !preg_match("/^[a-z]+$/i", $infoname) || $infoname != $name) {
            throw new Exception(__('Addon info name incorrect'));
        }

        $infoversion = isset($info['version']) ? $info['version'] : '';
        if (!$infoversion || !preg_match("/^\d+\.\d+\.\d+$/i", $infoversion)) {
            throw new Exception(__('Addon info version incorrect'));
        }

        $addonTmpDir = RUNTIME_PATH . 'addons' . DS;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }
        if ($this->extended) {
            $addonFile = $addonTmpDir . $infoname . '-' . $infoversion . '-extended.zip';
        } else {
            $addonFile = $addonTmpDir . $infoname . '-' . $infoversion . '-regular.zip';
        }
        if (!class_exists('ZipArchive')) {
            throw new Exception(__('ZinArchive not install'));
        }
        $zip = new \ZipArchive();
        $zip->open($addonFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($addonDir), \RecursiveIteratorIterator::LEAVES_ONLY);
        $extend_tokens = $this->getPackageExtendIgnoreTokens($addonDir);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativeFilePath = str_replace(ADDON_PATH . $name . DS, '', $filePath);
                if (!$this->ignorePackageToken($relativeFilePath, $extend_tokens)) {
                    $zip->addFile($filePath, $relativeFilePath);
                }
            }
        }
        $zip->close();
        if ($this->extended) {
            $output->info("Package [ " . $name . " extended ] Successed!");
        } else {
            $output->info("Package [ " . $name . " regular ] Successed!");
        }
    }

    protected function getPackageExtendIgnoreTokens($addonDir)
    {
        //打包支持自定义忽略符号
        $ignore_package_file =  '.regularignore';
        $menu_file = $addonDir . 'inc' . DS . 'backendmenu.php';
        $sql_file = $addonDir . DS . 'install.sql';
        $test_file = $addonDir . DS . 'testdata.sql';
        if ($this->extended) {
            $ignore_package_file =  '.extendedignore';
            $extended_menu_file = $addonDir . 'inc' . DS . 'extended_menu.php';
            if (file_exists($extended_menu_file)) {
                file_put_contents($menu_file, file_get_contents($extended_menu_file));
            }
            $extended_install_sql = $addonDir . 'inc' . DS . 'extended_install.sql';
            if (file_exists($extended_install_sql)) {
                file_put_contents($sql_file, file_get_contents($extended_install_sql));
            }
            $extended_test_sql = $addonDir . 'inc' . DS . 'extended_test.sql';
            if (file_exists($extended_test_sql)) {
                file_put_contents($test_file, file_get_contents($extended_test_sql));
            }
        } else {
            $regular_menu_file = $addonDir . 'inc' . DS . 'regular_menu.php';
            if (file_exists($regular_menu_file)) {
                file_put_contents($menu_file, file_get_contents($regular_menu_file));
            }
            $regular_install_sql = $addonDir . 'inc' . DS . 'regular_install.sql';
            if (file_exists($regular_install_sql)) {
                file_put_contents($sql_file, file_get_contents($regular_install_sql));
            }
            $regular_test_sql = $addonDir . 'inc' . DS . 'regular_test.sql';
            if (file_exists($regular_test_sql)) {
                file_put_contents($test_file, file_get_contents($regular_test_sql));
            }
        }
        $tokens = $this->parseIgnoreFile($addonDir, $ignore_package_file);
        return $tokens;
    }

    protected function ignorePackageToken($relativePath, $extend_tokends)
    {
        foreach (self::$ignorePackageDirs as $token) {
            if (static::ignoreMatch($token, $relativePath)) {
                return true;
            }
        }

        $filename = basename($relativePath);
        if (in_array($filename, self::$ignorePackageFiles)) {
            return true;
        }
        if (is_array($extend_tokends)) {
            foreach ($extend_tokends as $token) {
                if (static::ignoreMatch($token, $relativePath)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected $fileTimes = [];

    protected function findChangedFiles($addon)
    {
        $fileTimes = [];
        if (isset($this->fileTimes[$addon])) {
            $fileTimes = $this->fileTimes[$addon];
        }
        $files = [];
        $addon_path = ADDON_PATH . $addon;
        $this->scandirs($addon, $addon_path, $files);
        $changedFiles = [];
        if (empty($fileTimes)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $fileTimes[$file] = filemtime($file);
                    $changedFiles[] = $file;
                }
            }
        } else {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $time = filemtime($file);
                    if (isset($fileTimes[$file])) {
                        if ($fileTimes[$file] < $time) {
                            $changedFiles[] = $file;
                        }
                    } else {
                        $changedFiles[] = $file;
                    }
                    $fileTimes[$file] = $time;
                }
            }
        }
        $this->fileTimes[$addon] = $fileTimes;
        return $changedFiles;
    }

    protected function watchChange($addons, $copyDirs, $output, $loop = true)
    {
        $output->info("start watch addons: [" . implode(",", $addons) . "]");
        $output->info(" ");
        $appPath = ROOT_PATH . 'application' . DS;

        $copyDirMaps = $this->genCopyDirMaps($addons, $copyDirs, $appPath);

        while (true) {
            $this->processChangedFiles($addons, $copyDirMaps, $output);
            if (!$loop) {
                break;
            }
            sleep(1);
        }
    }

    protected function genCopyDirMaps($addons, $copyDirs, $appPath)
    {
        $copyDirMaps = [];
        foreach ($addons as $addon) {
            $copyDirMaps[$addon] = [];
            $addon_path = ADDON_PATH . $addon . DS;
            foreach ($copyDirs as $key => $dirs) {
                if ($key == 'adminOnlySelfDir' || $key == 'adminAllSubDir') {
                    foreach ($dirs as $dir) {
                        $dir = str_replace("/", DS, $dir);
                        $source = $addon_path . 'application' . DS . $dir;
                        $dist = $appPath . $dir;
                        $copyDirMaps[$addon][$source] = $dist;
                    }
                } else if ($key == 'publicDir') {
                    foreach ($dirs as $dir) {
                        $dir = str_replace("/", DS, $dir);
                        $source = $addon_path . $dir;
                        $dist = ROOT_PATH . $dir;
                        $copyDirMaps[$addon][$source] = $dist;
                    }
                } else if ($key == 'shortMapDir') {
                    foreach ($dirs as $short => $dir) {
                        $short = str_replace("/", DS, $short);
                        $dir = str_replace("/", DS, $dir);
                        $source = $addon_path . $short;
                        $dist = ROOT_PATH . $dir . DS . $addon;
                        $copyDirMaps[$addon][$source] = $dist;
                        $source = $addon_path . $dir;
                        $dist = ROOT_PATH . $dir;
                        $copyDirMaps[$addon][$source] = $dist;
                    }
                }
            }
        }
        return $copyDirMaps;
    }

    protected function processChangedFiles($addons, $copyDirMaps, $output)
    {
        $copyFilesMap = [];
        $addonsChangedFiles = [];
        foreach ($addons as $addon) {
            $changedFiles = $this->findChangedFiles($addon);
            $addonsChangedFiles = array_merge($addonsChangedFiles, $changedFiles);
            $addonCopyDirMaps = $copyDirMaps[$addon];
            foreach ($changedFiles as $srcFile) {
                foreach ($addonCopyDirMaps as $source => $dist) {
                    $len = strlen($source);
                    $sub = substr($srcFile, 0, $len);
                    if ($sub == $source) {
                        $distFile = $dist . substr($srcFile, $len);
                        $distFileDir = dirname($distFile);
                        if (!is_dir($distFileDir)) {
                            // 递归生成目录
                            mkdir($distFileDir, 0755, true);
                        }
                        $copyFilesMap[$srcFile] = [
                            'addon' => $addon,
                            'file' => $distFile,
                        ];
                        break;
                    }
                }
            }
        }

        if ($addonsChangedFiles) {
            if ($copyFilesMap) {
                foreach ($copyFilesMap as $srcFile => $distFile) {
                    if (file_exists($srcFile)) {
                        copy($srcFile, $distFile['file']);
                        $output->highlight("[" . date('H:i:s') . "][" . $distFile['addon'] . "] " . str_replace(ROOT_PATH, '', $srcFile));
                    }
                }
                $output->info("[" . date('H:i:s') . "] ====================>>> 文件同步完成 <<<================= ");
            }
            Service::refresh();
            $this->cleanTempFiles();
            $output->info("[" . date('H:i:s') . "] ====================>>> 刷新插件缓存 <<<================= ");
            //强制垃圾回收
            gc_collect_cycles();
        }
    }

    public function scandirs($addon, $directory, &$findFiles = [])
    {
        $files = scandir($directory);
        foreach ($files as $file) {
            if (substr($file, 0, 1) === '.') {
                continue;
            }
            $filePath = $directory . DS . $file;
            $file = str_replace(ADDON_PATH . $addon . DS, '', $filePath);
            if ($this->isIgnoreFile($file)) {
                continue;
            }
            if (is_dir($filePath)) {
                $this->scandirs($addon, $filePath, $findFiles);
            } else {
                $findFiles[] = $filePath;
            }
        }
    }

    protected function parseIgnoreFile($dir = '', $name = '.devignore')
    {
        $ignorefile = $dir . $name;
        if (file_exists($ignorefile)) {
            $tokens =  array_map(function ($token) {
                return trim($token, "\r\n");
            }, array_filter(file($ignorefile), function ($token) {
                $token = trim($token, "\r\n");
                return empty($token) ? false : true;
            }));
            return $tokens;
        }
        return [];
    }

    protected function isIgnoreFile($file)
    {
        foreach ($this->ignoreWatchTokens as $token) {
            if (static::ignoreMatch($token, $file)) {
                return true;
            }
        }
        return false;
    }

    public static function ignoreMatch($pattern, $string)
    {
        $pattern = str_replace('\\', '/', $pattern);
        $pattern = str_replace('.', '\.', $pattern);
        $firstChar = substr($pattern, 0, 1);
        if ($firstChar == '!') {
            $pattern = '#' . substr($pattern, 1)  . '#us';
            return preg_match($pattern, $string) !== 1;
        } else if ($firstChar === '/') {
            $pattern = '#^' . substr($pattern, 1)  . '#us';
        } else {
            $pattern = '#' . $pattern  . '#us';
        }
        return preg_match($pattern, $string) === 1;
    }

    protected function cleanTempFiles()
    {
        $directory = RUNTIME_PATH . 'temp';
        $files = scandir($directory);
        foreach ($files as $file) {
            if (substr($file, -4) == '.php') {
                @unlink($directory . DS . $file);
            }
        }
    }

    public function loadFiles($name, $movePath = [])
    {
        $paths = [];
        $appPath = str_replace('/', DS, APP_PATH);
        $rootPath = str_replace('/', DS, ROOT_PATH);
        foreach ($movePath as $k => $items) {
            switch ($k) {
                case 'adminOnlySelfDir':
                    foreach ($items as $v) {
                        $v = str_replace('/', DS, $v);
                        $oldPath = $appPath . $v . DS . $name;
                        $newPath = $rootPath . "addons" . DS . $name . DS . "application" . DS . $v . DS . $name;
                        $paths[$oldPath] = $newPath;
                    }
                    break;
                case 'adminAllSubDir':
                    foreach ($items as $v) {
                        $v = str_replace('/', DS, $v);
                        $vPath = $appPath . $v;
                        $list = scandir($vPath);
                        foreach ($list as $_v) {
                            if (!in_array($_v, [
                                '.',
                                '..'
                            ]) && is_dir($vPath . DS . $_v)) {
                                $oldPath = $appPath . $v . DS . $_v . DS . $name;
                                $newPath = $rootPath . "addons" . DS . $name . DS . "application" . DS . $v . DS . $_v . DS . $name;
                                $paths[$oldPath] = $newPath;
                            }
                        }
                    }
                    break;
                case 'publicDir':
                    foreach ($items as $v) {
                        $v = str_replace('/', DS, $v);
                        $oldPath = $rootPath . $v . DS . $name;
                        $newPath = $rootPath . 'addons' . DS . $name . DS . $v . DS . $name;
                        $paths[$oldPath] = $newPath;
                    }
                    break;
            }
        }
        return $paths;
    }

    public function move($name, $movePath = [], $force = false)
    {
        foreach ($this->loadFiles($name, $movePath) as $oldPath => $newPath) {
            if (is_dir($oldPath)) {
                if ($force) {
                    if (is_dir($newPath)) {
                        $list = scandir($newPath);
                        foreach ($list as $_v) {
                            if (!in_array($_v, [
                                '.',
                                '..'
                            ])) {
                                $file = $newPath . DS . $_v;
                                @chmod($file, 0777);
                                @unlink($file);
                            }
                        }
                        @rmdir($newPath);
                    }
                }
                copydirs($oldPath, $newPath);
            }
        }
    }

    /**
     * 获取基础模板
     *
     * @param string $name
     * @return string
     */
    public function getStub($name)
    {
        return ROOT_PATH . 'application' . DS . 'admin' . DS . 'command' . DS . 'Addon/stubs/' . $name . '.stub';
    }

    /**
     * 获取创建菜单的数组
     *
     * @param array $menu
     * @return array
     */
    public function getCreateMenu($menu)
    {
        $result = [];
        foreach ($menu as $k => &$v) {
            $arr = [
                'name' => $v['name'],
                'title' => $v['title']
            ];
            if ($v['icon'] != 'fa fa-circle-o') {
                $arr['icon'] = $v['icon'];
            }
            if ($v['ismenu']) {
                $arr['ismenu'] = $v['ismenu'];
            }
            if (isset($v['childlist']) && $v['childlist']) {
                $arr['sublist'] = $this->getCreateMenu($v['childlist']);
            }
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * 写入到文件
     *
     * @param string $name
     * @param array $data
     * @param string $pathname
     * @return mixed
     */
    public function writeToFile($name, $data, $pathname)
    {
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[] = "{%{$k}%}";
            $replace[] = $v;
        }
        $stub = file_get_contents($this->getStub($name));
        $content = str_replace($search, $replace, $stub);

        if (!is_dir(dirname($pathname))) {
            mkdir(strtolower(dirname($pathname)), 0755, true);
        }
        return file_put_contents($pathname, $content);
    }

    public function writeAddonInfo($name, $data, $pathname)
    {
        if (IS_CLI) {
            return $this->writeToFile($name, $data, $pathname);
        } else {
            return $this->writeInfo();
        }
    }

    public function writeInfo()
    {
        $addonDir = Service::getAddonDir($this->name);
        $pathname = $addonDir . 'info.ini';
        $info = $this->info;
        $info['addon'] = $this->name;
        $content = <<<INFO
name = $info[addon]
title = $info[title]
intro = $info[intro]
author = $info[author]
website = $info[website]
version = $info[version]
state = $info[state]
INFO;

        if ($info['license']) {
            $extend = <<<EXTEND
            
url = $info[url]
license = $info[license]
licenseto = $info[licenseto]
EXTEND;
            $content .= $extend;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(strtolower(dirname($pathname)), 0755, true);
        }
        return file_put_contents($pathname, $content);
    }
}
