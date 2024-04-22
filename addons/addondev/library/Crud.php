<?php

namespace addons\addondev\library;

use fast\Form;
use think\Config;
use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\Lang;
use think\Loader;
use think\Log;

class Crud
{

    public $addon;

    public $mtable;

    public $controller;

    public $model;

    public $fields;

    public $force;

    public $local_switch = 1;

    public $import_switch = 0;

    public $relation;

    public $relationmodel;

    public $relationforeignkey;

    public $relationprimarykey;

    public $relationfields;

    public $relationmode;

    public $relationcontroller;

    public $delete_switch;

    public $menu_switch;

    public $tree_switch;

    public $setcheckboxsuffix;

    public $enumradiosuffix;

    public $imagefield;

    public $filefield;

    public $intdatesuffix;

    public $switchsuffix;

    public $citysuffix;

    public $jsonsuffix;

    public $tagsuffix;

    public $tagcontroller;

    public $editorsuffix;

    public $selectpagesuffix;

    public $selectpagessuffix;

    public $selectpagefield;

    public $ignorefields;

    public $sortfield;

    public $headingfilterfield;

    public $editorclass;

    public $fixedcolumns;

    public $db = 'database';

    public $stubList = [];

    public $internalKeywords = [
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'public',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor'
    ];

    /**
     * 受保护的系统表, crud不会生效
     */
    public $systemTables = [
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
    ];

    /**
     * Selectpage搜索字段关联
     */
    public $fieldSelectpageMap = [
        'nickname' => [
            'user_id',
            'user_ids',
            'admin_id',
            'admin_ids'
        ]
    ];

    /**
     * Enum类型识别为单选框的结尾字符,默认会识别为单选下拉列表
     */
    public $enumRadioSuffix = [
        'data',
        'state',
        'status'
    ];

    /**
     * Set类型识别为复选框的结尾字符,默认会识别为多选下拉列表
     */
    public $setCheckboxSuffix = [
        'data',
        'state',
        'status'
    ];

    /**
     * Int类型识别为日期时间的结尾字符,默认会识别为日期文本框
     */
    public $intDateSuffix = [
        'time'
    ];

    /**
     * 开关后缀
     */
    public $switchSuffix = [
        'switch'
    ];

    /**
     * 富文本后缀
     */
    public $editorSuffix = [
        'content'
    ];

    /**
     * 城市后缀
     */
    public $citySuffix = [
        'city'
    ];

    /**
     * 时间区间后缀
     */
    public $rangeSuffix = [
        'range'
    ];

    /**
     * JSON后缀
     */
    public $jsonSuffix = [
        'json'
    ];

    /**
     * 标签后缀
     */
    public $tagSuffix = [
        'tag',
        'tags'
    ];

    /**
     * Selectpage对应的后缀
     */
    public $selectpageSuffix = [
        '_id',
        '_ids',
        'pid'
    ];

    /**
     * Selectpage多选对应的后缀
     */
    public $selectpagesSuffix = [
        '_ids'
    ];

    /**
     * 以指定字符结尾的字段格式化函数
     */
    public $fieldFormatterSuffix = [
        'status' => [
            'type' => [
                'varchar',
                'enum'
            ],
            'name' => 'status'
        ],
        'icon' => 'icon',
        'flag' => 'flag',
        'url' => 'url',
        'image' => 'image',
        'images' => 'images',
        'file' => 'file',
        'files' => 'files',
        'avatar' => 'image',
        'switch' => 'toggle',
        'tag' => 'flag',
        'tags' => 'flag',
        'time' => [
            'type' => [
                'int',
                'bigint',
                'timestamp'
            ],
            'name' => 'datetime'
        ]
    ];

    /**
     * 识别为图片字段
     */
    public $imageField = [
        'image',
        'images',
        'avatar',
        'avatars'
    ];

    /**
     * 识别为文件字段
     */
    public $fileField = [
        'file',
        'files'
    ];

    /**
     * 保留字段
     */
    public $reservedField = [
        'admin_id'
    ];

    /**
     * 排除字段
     */
    public $ignoreFields = [];

    /**
     * 排序字段
     */
    public $sortField = 'weigh';

    /**
     * 筛选字段
     *
     * @var string
     */
    public $headingFilterField = 'status';

    /**
     * 添加时间字段
     *
     * @var string
     */
    public $createTimeField = 'createtime';

    /**
     * 更新时间字段
     *
     * @var string
     */
    public $updateTimeField = 'updatetime';

    /**
     * 软删除时间字段
     *
     * @var string
     */
    public $deleteTimeField = 'deletetime';

    /**
     * 编辑器的Class
     */
    public $editorClass = 'editor';

    /**
     * langList的key最长字节数
     */
    public $fieldMaxLen = 0;


    public $relationmodels;

    /**
     * 代码文件
     *
     * @var CodeFile[]
     */
    public $codeFiles = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $field => $val) {
            if (property_exists($this, $field)) {
                $this->$field = $val;
            }
        }
        if (!IS_CLI) {
            //基础参数
            $this->relation = $this->formateArray($this->relation);
            $this->relationmodels = $this->formateArray($this->relationmodel);
            $this->relationmode = $this->formateArray($this->relationmode);
            $this->relationforeignkey = $this->formateArray($this->relationforeignkey);
            $this->relationprimarykey = $this->formateArray($this->relationprimarykey);
            $this->relationfields = $this->formateArray($this->relationfields);
            $this->relationcontroller = $this->formateArray($this->relationcontroller);
            $this->selectpagefield  = $this->formateArray($this->selectpagefield);
            //扩展基础参数
            if ($this->relationforeignkey && $this->selectpagefield && count($this->relationforeignkey) == count($this->selectpagefield)) {
                $this->selectpagefield = array_combine($this->relationforeignkey, $this->selectpagefield);
            } else {
                $this->selectpagefield = [];
            }

            //其他可选参数
            $this->ignorefields = $this->formateArray($this->ignorefields, ",");
            $this->setcheckboxsuffix = $this->formateArray($this->setcheckboxsuffix, ",");
            $this->enumradiosuffix = $this->formateArray($this->enumradiosuffix, ",");
            $this->imagefield = $this->formateArray($this->imagefield, ",");
            $this->filefield = $this->formateArray($this->filefield, ",");
            $this->tagsuffix = $this->formateArray($this->tagsuffix, ",");
            $this->intdatesuffix = $this->formateArray($this->intdatesuffix, ",");
            $this->switchsuffix = $this->formateArray($this->switchsuffix, ",");
            $this->editorsuffix = $this->formateArray($this->editorsuffix, ",");
            $this->citysuffix = $this->formateArray($this->citysuffix, ",");
            $this->jsonsuffix = $this->formateArray($this->jsonsuffix, ",");
            $this->selectpagesuffix = $this->formateArray($this->selectpagesuffix, ",");
            $this->selectpagessuffix = $this->formateArray($this->selectpagessuffix, ",");
        }
    }

    public function formateArray($data, $sg = "\n")
    {
        if ($data) {
            return array_map(function ($m) {
                return trim($m);
            }, array_filter(explode($sg, $data), function ($v) {
                return $v;
            }));
        }
        return null;
    }

    public function execute(OutputFacade $output)
    {

        if (empty($this->addon)) {
            throw new Exception('addon name can\'t empty -A');
        }

        // 插件路径
        $addonPath = ADDON_PATH . $this->addon . DS;

        // 插件管理端路径
        $adminPath = $addonPath . 'application' . DS . 'admin' . DS;

        // 表名
        if (!$this->mtable) {
            throw new Exception('table name can\'t empty -t');
        }

        // 自定义控制器
        // 默认加上插件的目录，大多数场景都会以插件为包集合文件
        if (!empty($this->controller)) {
            $this->controller = $this->addon . DS . $this->controller;
        }
        // 自定义模型
        if (empty($this->model)) {
            $this->model = $this->addon . DS . Loader::parseName($this->mtable, 1);
        }

        // 验证器类
        $validate = $this->model;

        if ($this->setcheckboxsuffix) {
            $this->setCheckboxSuffix = $this->setcheckboxsuffix;
        }
        if ($this->enumradiosuffix) {
            $this->enumRadioSuffix = $this->enumradiosuffix;
        }
        if ($this->imagefield) {
            $this->imageField = $this->imagefield;
        }
        if ($this->filefield) {
            $this->fileField = $this->filefield;
        }
        if ($this->tagsuffix) {
            $this->tagSuffix = $this->tagsuffix;
        }
        if ($this->intdatesuffix) {
            $this->intDateSuffix = $this->intdatesuffix;
        }
        if ($this->switchsuffix) {
            $this->switchSuffix = $this->switchsuffix;
        }
        if ($this->editorsuffix) {
            $this->editorSuffix = $this->editorsuffix;
        }
        if ($this->citysuffix) {
            $this->citySuffix = $this->citysuffix;
        }
        if ($this->jsonsuffix) {
            $this->jsonSuffix = $this->jsonsuffix;
        }
        if ($this->selectpagesuffix) {
            $this->selectpageSuffix = $this->selectpagesuffix;
        }
        if ($this->selectpagessuffix) {
            $this->selectpagesSuffix = $this->selectpagessuffix;
        }
        if ($this->ignoreFields) {
            $this->ignoreFields = $this->ignoreFields;
        }
        if ($this->editorclass) {
            $this->editorClass = $this->editorclass;
        }
        if ($this->sortfield) {
            $this->sortField = $this->sortfield;
        }
        if ($this->headingfilterfield) {
            $this->headingFilterField = $this->headingfilterfield;
        }

        $this->reservedField = array_merge($this->reservedField, [
            $this->createTimeField,
            $this->updateTimeField,
            $this->deleteTimeField
        ]);
        if (IS_CLI) {
            $dbconnect = Db::connect($this->db);
            $dbname = Config::get($this->db . '.database');
            $prefix = Config::get($this->db . '.prefix');
        } else {
            $dbconnect = Db::connect();
            $dbname = Config::get('database.database');
            $prefix = Config::get('database.prefix');
        }

        // 系统表无法生成，防止后台错乱
        if (in_array(str_replace($prefix, "", $this->mtable), $this->systemTables)) {
            throw new Exception(__('system table can\'t be crud'));
        }

        // 模块
        $moduleName = 'admin';
        $modelModuleName = $this->local_switch ? $moduleName : 'common';
        $validateModuleName = $this->local_switch ? $moduleName : 'common';

        // 检查主表
        $modelName = $this->mtable = stripos($this->mtable, $prefix) === 0 ? substr($this->mtable, strlen($prefix)) : $this->mtable;
        $modelTableType = 'table';
        $modelTableTypeName = $modelTableName = $modelName;
        $modelTableInfo = $dbconnect->query("SHOW TABLE STATUS LIKE '{$modelTableName}'", [], true);
        if (!$modelTableInfo) {
            $modelTableType = 'name';
            $modelTableName = $prefix . $modelName;
            $modelTableInfo = $dbconnect->query("SHOW TABLE STATUS LIKE '{$modelTableName}'", [], true);
            if (!$modelTableInfo) {
                throw new Exception(__("table not found"));
            }
        }
        $modelTableInfo = $modelTableInfo[0];

        $relations = [];
        // 检查关联表
        if ($this->relation) {
            $relationArr = $this->relation;
            $relations = [];
            foreach ($relationArr as $index => $relationTable) {
                $relationName = stripos($relationTable, $prefix) === 0 ? substr($relationTable, strlen($prefix)) : $relationTable;
                $relationTableType = 'table';
                $relationTableTypeName = $relationTableName = $relationName;
                $relationTableInfo = $dbconnect->query("SHOW TABLE STATUS LIKE '{$relationTableName}'", [], true);
                if (!$relationTableInfo) {
                    $relationTableType = 'name';
                    $relationTableName = $prefix . $relationName;
                    $relationTableInfo = $dbconnect->query("SHOW TABLE STATUS LIKE '{$relationTableName}'", [], true);
                    if (!$relationTableInfo) {
                        throw new Exception(__("relation table [%s] not found", [$relationTableName]));
                    }
                }
                $relationTableInfo = $relationTableInfo[0];
                $relationModel = isset($this->relationmodels[$index]) ? $this->relationmodels[$index] : '';

                if (in_array($relationTable, $this->systemTables)) {
                    list($relationNamespace, $relationName, $relationFile) = $this->getModelData($modelModuleName, $relationModel, $relationName);
                } else {
                    list($relationNamespace, $relationName, $relationFile) = $this->getAddonModelData($addonPath, $modelModuleName, $relationModel, $relationName);
                }

                $relations[] = [
                    // 关联表基础名
                    'relationName' => $relationName,
                    // 关联表类命名空间
                    'relationNamespace' => $relationNamespace,
                    // 关联模型名
                    'relationModel' => $relationModel,
                    // 关联文件
                    'relationFile' => $relationFile,
                    // 关联表名称
                    'relationTableName' => $relationTableName,
                    // 关联表信息
                    'relationTableInfo' => $relationTableInfo,
                    // 关联模型表类型(name或table)
                    'relationTableType' => $relationTableType,
                    // 关联模型表类型名称
                    'relationTableTypeName' => $relationTableTypeName,
                    // 关联字段
                    'relationFields' => isset($this->relationfields[$index]) ? explode(',', $this->relationfields[$index]) : [],
                    // 关联模式
                    'relationMode' => isset($this->relationmode[$index]) ? $this->relationmode[$index] : 'belongsto',
                    // 关联模型控制器
                    'relationController' => isset($this->relationcontroller[$index]) ? $this->relationcontroller[$index] : '',
                    // 关联表外键
                    'relationForeignKey' => isset($this->relationforeignkey[$index]) ? $this->relationforeignkey[$index] : '',
                    // 关联表主键
                    'relationPrimaryKey' => isset($this->relationprimarykey[$index]) ? $this->relationprimarykey[$index] : ''
                ];
            }
        }

        // 根据表名匹配对应的Fontawesome图标
        $iconPath = ROOT_PATH . str_replace('/', DS, '/public/assets/libs/font-awesome/less/variables.less');
        $iconName = is_file($iconPath) && stripos(file_get_contents($iconPath), '@fa-var-' . $this->mtable . ':') ? 'fa fa-' . $this->mtable : 'fa fa-circle-o';

        // 控制器
        list($controllerNamespace, $controllerName, $controllerFile, $controllerArr) = $this->getAddonControllerData($addonPath, $moduleName, $this->controller, $this->mtable);

        // 模型
        list($modelNamespace, $modelName, $modelFile, $modelArr) = $this->getAddonModelData($addonPath, $modelModuleName, $this->model, $this->mtable);

        // 验证器
        list($validateNamespace, $validateName, $validateFile, $validateArr) = $this->getAddonValidateData($addonPath, $validateModuleName, $validate, $this->mtable);

        // 处理基础文件名，取消所有下划线并转换为小写
        $baseNameArr = $controllerArr;
        $baseFileName = Loader::parseName(array_pop($baseNameArr), 0);
        array_push($baseNameArr, $baseFileName);
        $controllerBaseName = strtolower(implode(DS, $baseNameArr));
        // $controllerUrl = strtolower(implode('/', $baseNameArr));
        $controllerUrl = $this->getControllerUrl($moduleName, $baseNameArr);

        // 视图文件
        $viewArr = $controllerArr;
        $lastValue = array_pop($viewArr);
        $viewArr[] = Loader::parseName($lastValue, 0);
        array_unshift($viewArr, 'view');
        $viewDir = $adminPath . strtolower(implode(DS, $viewArr)) . DS;

        // 最终将生成的文件路径
        // $javascriptFile = ROOT_PATH . 'public' . DS . 'assets' . DS . 'js' . DS . 'backend' . DS . $controllerBaseName . '.js';
        $javascriptFile = $addonPath . 'public' . DS . 'assets' . DS . 'js' . DS . 'backend' . DS . $controllerBaseName . '.js';
        $addFile = $viewDir . 'add.html';
        $editFile = $viewDir . 'edit.html';
        $indexFile = $viewDir . 'index.html';
        $recyclebinFile = $viewDir . 'recyclebin.html';
        $langFile = $adminPath . 'lang' . DS . Lang::detect() . DS . $controllerBaseName . '.php';

        // 是否为删除模式
        if ($this->delete_switch) {
            if (IS_CLI) {
                return $this->cliDelMode(
                    $controllerFile,
                    $modelFile,
                    $validateFile,
                    $addFile,
                    $editFile,
                    $indexFile,
                    $recyclebinFile,
                    $langFile,
                    $javascriptFile,
                    $modelArr,
                    $validateArr,
                    $viewArr,
                    $controllerArr,
                    $addonPath,
                    $controllerUrl,
                    $output
                );
            } else {
                return $this->onlineDelMode(
                    $controllerFile,
                    $modelFile,
                    $validateFile,
                    $addFile,
                    $editFile,
                    $indexFile,
                    $recyclebinFile,
                    $langFile,
                    $javascriptFile,
                    $controllerUrl
                );
            }
        }

        // where cli mode
        $this->checkFileExisted($controllerFile, $modelFile, $validateFile);

        // 加载公共函数库
        // require $adminPath . DS . 'common.php';
        require ROOT_PATH . 'application' . DS . 'admin' . DS . 'common.php';

        // 从数据库中获取表字段信息
        $sql = "SELECT * FROM `information_schema`.`columns` " . "WHERE TABLE_SCHEMA = ? AND table_name = ? " . "ORDER BY ORDINAL_POSITION";

        // 加载主表的列
        $columnList = $dbconnect->query($sql, [
            $dbname,
            $modelTableName
        ]);
        $fieldArr = [];
        foreach ($columnList as $k => $v) {
            $fieldArr[] = $v['COLUMN_NAME'];
        }

        // 加载关联表的列
        foreach ($relations as $index => &$relation) {
            $relationColumnList = $dbconnect->query($sql, [
                $dbname,
                $relation['relationTableName']
            ]);

            $relationFieldList = [];
            foreach ($relationColumnList as $k => $v) {
                $relationFieldList[] = $v['COLUMN_NAME'];
            }
            if (!$relation['relationPrimaryKey']) {
                foreach ($relationColumnList as $k => $v) {
                    if ($v['COLUMN_KEY'] == 'PRI') {
                        $relation['relationPrimaryKey'] = $v['COLUMN_NAME'];
                        break;
                    }
                }
            }
            // 如果主键为空
            if (!$relation['relationPrimaryKey']) {
                throw new Exception(__('Relation Primary key not found!'));
            }
            // 如果主键不在表字段中
            if (!in_array($relation['relationPrimaryKey'], $relationFieldList)) {
                throw new Exception(__('Relation Primary key not found in table!'));
            }
            $relation['relationColumnList'] = $relationColumnList;
            $relation['relationFieldList'] = $relationFieldList;
        }

        unset($relation);

        $addList = [];
        $editList = [];
        $javascriptList = [];
        $langList = [];
        $operateButtonList = [];
        $field = 'id';
        $order = 'id';
        $priDefined = false;
        $priKeyArr = [];
        $relationPrimaryKey = '';

        foreach ($columnList as $k => $v) {
            if ($v['COLUMN_KEY'] == 'PRI') {
                $priKeyArr[] = $v['COLUMN_NAME'];
            }
        }
        if (!$priKeyArr) {
            throw new Exception(__('Primary key not found!'));
        }
        if (count($priKeyArr) > 1) {
            throw new Exception(__('Multiple primary key not support!'));
        }
        $priKey = reset($priKeyArr);

        $order = $priKey;

        $foreignKeyControllerMap = [];
        // 如果是关联模型
        foreach ($relations as $index => &$relation) {
            if ($relation['relationMode'] == 'hasone') {
                $relationForeignKey = $relation['relationForeignKey'] ? $relation['relationForeignKey'] : $this->mtable . "_id";
                $relationPrimaryKey = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $priKey;

                if (!in_array($relationForeignKey, $relation['relationFieldList'])) {
                    throw new Exception(__('relation table [%s] must be contain field [%s]', [$relation['relationTableName'], $relationForeignKey]));
                }
                if (!in_array($relationPrimaryKey, $fieldArr)) {
                    throw new Exception(__('table [%s] must be contain field [%s]', [$modelTableName, $relationPrimaryKey]));
                }
            } elseif ($relation['relationMode'] == 'belongsto') {
                $relationForeignKey = $relation['relationForeignKey'] ? $relation['relationForeignKey'] : Loader::parseName($relation['relationName']) . "_id";
                $relationPrimaryKey = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $relation['relationPriKey'];
                if (!in_array($relationForeignKey, $fieldArr)) {
                    throw new Exception(__('table [%s] must be contain field [%s]', [$modelTableName, $relationForeignKey]));
                }
                if (!in_array($relationPrimaryKey, $relation['relationFieldList'])) {
                    throw new Exception(__('relation table [%s] must be contain field [%s]', [$relation['relationTableName'], $relationPrimaryKey]));
                }
            } elseif ($relation['relationMode'] == 'hasmany') {
                $relationForeignKey = $relation['relationForeignKey'] ? $relation['relationForeignKey'] : $this->mtable . "_id";
                $relationPrimaryKey = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $priKey;
                if (!in_array($relationForeignKey, $relation['relationFieldList'])) {
                    throw new Exception(__('relation table [%s] must be contain field [%s]', [$relation['relationTableName'], $relationForeignKey]));
                }
                if (!in_array($relationPrimaryKey, $fieldArr)) {
                    throw new Exception(__('table [%s] must be contain field [%s]', [$modelTableName, $relationPrimaryKey]));
                }
                $relation['relationColumnList'] = [];
                $relation['relationFieldList'] = [];
            }
            $relation['relationForeignKey'] = $relationForeignKey;
            $relation['relationPrimaryKey'] = $relationPrimaryKey;
            $relation['relationClassName'] = $modelNamespace != $relation['relationNamespace'] ? $relation['relationNamespace'] . '\\' . $relation['relationName'] : $relation['relationName'];
            $addonName = basename($modelNamespace);
            if ($addonName == 'model') {
                $foreignKeyControllerMap[$relationForeignKey] = [
                    'c' => strtolower($relation['relationName']),
                    't' => $relation['relationTableName']
                ];
            } else {
                $foreignKeyControllerMap[$relationForeignKey] = [
                    'c' => $addonName . '/' . strtolower($relation['relationName']),
                    't' => $relation['relationTableName']
                ];
            }

            if (!empty($relation['relationController'])) {
                $foreignKeyControllerMap[$relationForeignKey]['c'] = $relation['relationController'];
            }
        }
        unset($relation);

        try {
            Form::setEscapeHtml(false);
            $setAttrArr = [];
            $getAttrArr = [];
            $getEnumArr = [];
            $appendAttrList = [];
            $controllerAssignList = [];
            $headingHtml = '{:build_heading()}';
            $controllerImport = '';
            $importHtml = '';
            $multipleHtml = '';
            $recyclebinHtml = '';

            if ($this->import_switch) {
                $controllerImport = $this->getReplacedStub('mixins/import', []);
                $importHtml = '<a href="javascript:;" class="btn btn-danger btn-import {:$auth->check(\'' . $controllerUrl . '/import\')?\'\':\'hide\'}" title="{:__(\'Import\')}" id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"><i class="fa fa-upload"></i> {:__(\'Import\')}</a>';
            }

            // 循环所有字段,开始构造视图的HTML和JS信息
            foreach ($columnList as $k => $v) {
                $field = $v['COLUMN_NAME'];
                //添加子的按钮
                if ($field == 'pid') {
                    $operateButtonList[] = $this->getAddSubButtonJs($controllerUrl);
                }
                $itemArr = [];
                // 这里构建Enum和Set类型的列表数据
                if (in_array($v['DATA_TYPE'], [
                    'enum',
                    'set',
                    'tinyint'
                ]) || $this->headingFilterField == $field) {
                    if ($v['DATA_TYPE'] !== 'tinyint') {
                        $itemArr = substr($v['COLUMN_TYPE'], strlen($v['DATA_TYPE']) + 1, -1);
                        $itemArr = explode(',', str_replace("'", '', $itemArr));
                    }
                    $itemArr = $this->getItemArray($itemArr, $field, $v['COLUMN_COMMENT']);
                    // 如果类型为tinyint且有使用备注数据
                    if ($itemArr && !in_array($v['DATA_TYPE'], [
                        'enum',
                        'set'
                    ])) {
                        $v['DATA_TYPE'] = 'enum';
                    }
                }
                // 语言列表
                if ($v['COLUMN_COMMENT'] != '') {
                    $langList[] = $this->getLangItem($field, $v['COLUMN_COMMENT']);
                }
                $inputType = '';
                // 保留字段不能修改和添加
                if ($v['COLUMN_KEY'] != 'PRI' && !in_array($field, $this->reservedField) && !in_array($field, $this->ignoreFields)) {
                    $inputType = $this->getFieldType($v);

                    // 如果是number类型时增加一个步长
                    $step = $inputType == 'number' && $v['NUMERIC_SCALE'] > 0 ? "0." . str_repeat(0, $v['NUMERIC_SCALE'] - 1) . "1" : 0;

                    $attrArr = [
                        'id' => "c-{$field}"
                    ];
                    $cssClassArr = [
                        'form-control'
                    ];
                    $fieldName = "row[{$field}]";
                    $defaultValue = $v['COLUMN_DEFAULT'];
                    $editValue = "{\$row.{$field}|htmlentities}";
                    // 如果默认值非null,则是一个必选项
                    if ($v['IS_NULLABLE'] == 'NO') {
                        $attrArr['data-rule'] = 'required';
                    }

                    // 如果字段类型为无符号型，则设置<input min=0>
                    if (stripos($v['COLUMN_TYPE'], 'unsigned') !== false) {
                        $attrArr['min'] = 0;
                    }

                    if ($inputType == 'select') {
                        $cssClassArr[] = 'selectpicker';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        if ($v['DATA_TYPE'] == 'set') {
                            $attrArr['multiple'] = '';
                            $fieldName .= "[]";
                        }
                        $attrArr['name'] = $fieldName;

                        $this->getEnum($getEnumArr, $controllerAssignList, $field, $itemArr, $v['DATA_TYPE'] == 'set' ? 'multiple' : 'select');

                        $itemArr = $this->getLangArray($itemArr, false);
                        // 添加一个获取器
                        $this->getAttr($getAttrArr, $field, $v['DATA_TYPE'] == 'set' ? 'multiple' : 'select');
                        if ($v['DATA_TYPE'] == 'set') {
                            $this->setAttr($setAttrArr, $field, $inputType);
                        }
                        $this->appendAttr($appendAttrList, $field);
                        $formAddElement = $this->getReplacedStub('html/select', [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'fieldList' => $this->getFieldListName($field),
                            'attrStr' => Form::attributes($attrArr),
                            'selectedValue' => $defaultValue
                        ]);
                        $formEditElement = $this->getReplacedStub('html/select', [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'fieldList' => $this->getFieldListName($field),
                            'attrStr' => Form::attributes($attrArr),
                            'selectedValue' => "\$row.{$field}"
                        ]);
                    } elseif ($inputType == 'datetime') {
                        $cssClassArr[] = 'datetimepicker';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $format = "YYYY-MM-DD HH:mm:ss";
                        $phpFormat = "Y-m-d H:i:s";
                        $fieldFunc = '';
                        switch ($v['DATA_TYPE']) {
                            case 'year':
                                $format = "YYYY";
                                $phpFormat = 'Y';
                                break;
                            case 'date':
                                $format = "YYYY-MM-DD";
                                $phpFormat = 'Y-m-d';
                                break;
                            case 'time':
                                $format = "HH:mm:ss";
                                $phpFormat = 'H:i:s';
                                break;
                            case 'timestamp':
                                $fieldFunc = 'datetime';
                                // no break
                            case 'datetime':
                                $format = "YYYY-MM-DD HH:mm:ss";
                                $phpFormat = 'Y-m-d H:i:s';
                                break;
                            default:
                                $fieldFunc = 'datetime';
                                $this->getAttr($getAttrArr, $field, $inputType);
                                $this->setAttr($setAttrArr, $field, $inputType);
                                $this->appendAttr($appendAttrList, $field);
                                break;
                        }
                        $defaultDateTime = "{:date('{$phpFormat}')}";
                        $attrArr['data-date-format'] = $format;
                        $attrArr['data-use-current'] = "true";
                        $formAddElement = Form::text($fieldName, $defaultDateTime, $attrArr);
                        $formEditElement = Form::text($fieldName, ($fieldFunc ? "{:\$row.{$field}?{$fieldFunc}(\$row.{$field}):''}" : "{\$row.{$field}{$fieldFunc}}"), $attrArr);
                    } elseif ($inputType == 'datetimerange') {
                        $cssClassArr[] = 'datetimerange';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $attrArr['data-locale'] = '{"format":"YYYY-MM-DD HH:mm:ss"}';
                        $fieldFunc = '';
                        $defaultDateTime = "";
                        $formAddElement = Form::text($fieldName, $defaultDateTime, $attrArr);
                        $formEditElement = Form::text($fieldName, $editValue, $attrArr);
                    } elseif ($inputType == 'checkbox' || $inputType == 'radio') {
                        unset($attrArr['data-rule']);
                        $fieldName = $inputType == 'checkbox' ? $fieldName .= "[]" : $fieldName;
                        $attrArr['name'] = "row[{$fieldName}]";

                        $this->getEnum($getEnumArr, $controllerAssignList, $field, $itemArr, $inputType);
                        $itemArr = $this->getLangArray($itemArr, false);
                        // 添加一个获取器
                        $this->getAttr($getAttrArr, $field, $inputType);
                        if ($inputType == 'checkbox') {
                            $this->setAttr($setAttrArr, $field, $inputType);
                        }
                        $this->appendAttr($appendAttrList, $field);
                        $defaultValue = $inputType == 'radio' && !$defaultValue ? key($itemArr) : $defaultValue;

                        $formAddElement = $this->getReplacedStub('html/' . $inputType, [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'fieldList' => $this->getFieldListName($field),
                            'attrStr' => Form::attributes($attrArr),
                            'selectedValue' => $defaultValue
                        ]);
                        $formEditElement = $this->getReplacedStub('html/' . $inputType, [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'fieldList' => $this->getFieldListName($field),
                            'attrStr' => Form::attributes($attrArr),
                            'selectedValue' => "\$row.{$field}"
                        ]);
                    } elseif ($inputType == 'textarea' && !$this->isMatchSuffix($field, $this->selectpagesSuffix) && !$this->isMatchSuffix($field, $this->imageField)) {
                        $cssClassArr[] = $this->isMatchSuffix($field, $this->editorSuffix) ? $this->editorClass : '';
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $attrArr['rows'] = 5;
                        $formAddElement = Form::textarea($fieldName, $defaultValue, $attrArr);
                        $formEditElement = Form::textarea($fieldName, $editValue, $attrArr);
                    } elseif ($inputType == 'switch') {
                        unset($attrArr['data-rule']);
                        if ($defaultValue === '1' || $defaultValue === 'Y') {
                            $yes = $defaultValue;
                            $no = $defaultValue === '1' ? '0' : 'N';
                        } else {
                            $no = $defaultValue;
                            $yes = $defaultValue === '0' ? '1' : 'Y';
                        }
                        if (!$itemArr) {
                            $itemArr = [
                                $yes => 'Yes',
                                $no => 'No'
                            ];
                        }
                        $stateNoClass = 'fa-flip-horizontal text-gray';
                        $formAddElement = $this->getReplacedStub('html/' . $inputType, [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'fieldYes' => $yes,
                            'fieldNo' => $no,
                            'attrStr' => Form::attributes($attrArr),
                            'fieldValue' => $defaultValue,
                            'fieldSwitchClass' => $defaultValue == $no ? $stateNoClass : ''
                        ]);
                        $formEditElement = $this->getReplacedStub('html/' . $inputType, [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'fieldYes' => $yes,
                            'fieldNo' => $no,
                            'attrStr' => Form::attributes($attrArr),
                            'fieldValue' => "{\$row.{$field}}",
                            'fieldSwitchClass' => "{eq name=\"\$row.{$field}\" value=\"{$no}\"}fa-flip-horizontal text-gray{/eq}"
                        ]);
                    } elseif ($inputType == 'citypicker') {
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $attrArr['data-toggle'] = "city-picker";
                        $formAddElement = sprintf("<div class='control-relative'>%s</div>", Form::input('text', $fieldName, $defaultValue, $attrArr));
                        $formEditElement = sprintf("<div class='control-relative'>%s</div>", Form::input('text', $fieldName, $editValue, $attrArr));
                    } elseif ($inputType == 'tagsinput') {
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $attrArr['data-role'] = "tagsinput";
                        if ($this->tagcontroller) {
                            $attrArr['data-tagsinput-options'] = '{"autocomplete":{"url":"' . $this->tagcontroller . '"}}';
                        }
                        $formAddElement = Form::input('text', $fieldName, $defaultValue, $attrArr);
                        $formEditElement = Form::input('text', $fieldName, $editValue, $attrArr);
                    } elseif ($inputType == 'fieldlist') {
                        $itemArr = $this->getItemArray($itemArr, $field, $v['COLUMN_COMMENT']);
                        $templateName = !isset($itemArr['key']) && !isset($itemArr['value']) && count($itemArr) > 0 ? 'fieldlist-template' : 'fieldlist';
                        $itemKey = isset($itemArr['key']) ? ucfirst($itemArr['key']) : 'Key';
                        $itemValue = isset($itemArr['value']) ? ucfirst($itemArr['value']) : 'Value';
                        $theadListArr = $tbodyListArr = [];
                        foreach ($itemArr as $index => $item) {
                            $theadListArr[] = "<td>{:__('" . $item . "')}</td>";
                            $tbodyListArr[] = '<td><input type="text" name="<%=name%>[<%=index%>][' . $index . ']" class="form-control" value="<%=row.' . $index . '%>"/></td>';
                        }
                        $colspan = count($theadListArr) + 1;
                        $commonFields = [
                            'field' => $field,
                            'fieldName' => $fieldName,
                            'itemKey' => $itemKey,
                            'itemValue' => $itemValue,
                            'theadList' => implode("\n", $theadListArr),
                            'tbodyList' => implode("\n", $tbodyListArr),
                            'colspan' => $colspan
                        ];
                        $formAddElement = $this->getReplacedStub('html/' . $templateName, array_merge($commonFields, [
                            'fieldValue' => $defaultValue
                        ]));
                        $formEditElement = $this->getReplacedStub('html/' . $templateName, array_merge($commonFields, [
                            'fieldValue' => $editValue
                        ]));
                    } else {
                        $search = $replace = '';
                        // 特殊字段为关联搜索 
                        // 并且配置相关的配置才可以使用
                        if ($this->isMatchSuffix($field, $this->selectpageSuffix)) {
                            $inputType = 'text';
                            $defaultValue = '';
                            //$attrArr['data-rule'] = 'required';
                            $cssClassArr[] = 'selectpage';
                            if (isset($foreignKeyControllerMap[$field])) {
                                $foreignKeyData = $foreignKeyControllerMap[$field];
                                $selectpageTable = $foreignKeyData['t'];
                                $selectpageField = '';
                                $selectpageController = $foreignKeyData['c'];
                            } else {

                                if ($field == 'pid') {
                                    $selectpageTable = $this->mtable;
                                    $defaultValue = '{$Request.param.pid??0}';
                                } else {
                                    $selectpageTable = substr($field, 0, strripos($field, '_'));
                                    try {
                                        //先检查插件之外的表，如果存在就使用
                                        $tableInfo = \think\Db::name($selectpageTable)->getTableInfo();
                                    } catch (\Exception $e) {
                                        //否则使用插件表
                                        $selectpageTable = $this->addon . '_' . $selectpageTable;
                                    }
                                }
                                $selectpageField = '';
                                $selectpageController = str_replace('_', '/', $selectpageTable);
                            }

                            $attrArr['data-source'] = $selectpageController . "/index";
                            // 如果是类型表需要特殊处理下
                            if ($selectpageController == 'category') {
                                $attrArr['data-source'] = 'category/selectpage';
                                $attrArr['data-params'] = '##replacetext##';
                                $search = '"##replacetext##"';
                                $replace = '\'{"custom[type]":"' . $this->mtable . '"}\'';
                            } elseif ($selectpageController == 'admin') {
                                $attrArr['data-source'] = 'auth/admin/selectpage';
                            } elseif ($selectpageController == 'user') {
                                $attrArr['data-source'] = 'user/user/index';
                                $attrArr['data-field'] = 'nickname';
                            }
                            if ($this->isMatchSuffix($field, $this->selectpagesSuffix)) {
                                $attrArr['data-multiple'] = 'true';
                            }

                            $tableInfo = null;
                            try {
                                $tableInfo = \think\Db::name($selectpageTable)->getTableInfo();
                                if (isset($tableInfo['fields'])) {
                                    //对比特殊字段
                                    foreach ($tableInfo['fields'] as $m => $n) {
                                        if (in_array($n, [
                                            'nickname',
                                            'title',
                                            'name'
                                        ])) {
                                            $selectpageField = $n;
                                            $attrArr['data-field'] = $n;
                                            break;
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error($e->getMessage());
                            }
                            if (!$selectpageField) {
                                foreach ($this->fieldSelectpageMap as $m => $n) {
                                    if (in_array($field, $n)) {
                                        $attrArr['data-field'] = $m;
                                        break;
                                    }
                                }
                            }

                            if ($this->selectpagefield) {
                                foreach ($this->selectpagefield as $m => $n) {
                                    if ($field == $m) {
                                        $showFields = explode(",", $n);
                                        if (count($showFields) == 1) {
                                            $attrArr['data-field'] = $n;
                                        } else {
                                            $attrArr['data-format-item'] = implode("-", array_map(function ($f) {
                                                return "{" . $f . "}";
                                            }, $showFields));
                                        }
                                    }
                                }
                            } else {
                                $tableInfo = null;

                                try {
                                    $dbprefix = config('database.prefix');
                                    $tmpprefix = substr($selectpageTable, 0, strlen($dbprefix));
                                    if ($tmpprefix == $dbprefix) {
                                        $tmptable = substr($selectpageTable, strlen($dbprefix));
                                        $tableInfo = \think\Db::name($tmptable)->getTableInfo();
                                    } else {
                                        $tableInfo = \think\Db::name($selectpageTable)->getTableInfo();
                                    }
                                    //var_dump($tableInfo['fields']);
                                    if (isset($tableInfo['fields'])) {

                                        foreach ($tableInfo['fields'] as $m => $n) {
                                            if (in_array($n, [
                                                'nickname',
                                                'title',
                                                'name'
                                            ])) {
                                                $selectpageField = $n;
                                                $attrArr['data-field'] = $n;
                                                break;
                                            }
                                        }
                                    }
                                } catch (\Exception $e) {
                                    Log::error($e->getMessage());

                                    //return;
                                }
                                if (!$selectpageField) {
                                    foreach ($this->fieldSelectpageMap as $m => $n) {
                                        if (in_array($field, $n)) {
                                            $attrArr['data-field'] = $m;
                                            break;
                                        }
                                    }
                                }
                            }

                            if ($this->relationprimarykey) {
                                foreach ($this->relationprimarykey as $m => $n) {
                                    if ($field == $m) {
                                        $attrArr['data-primary-key'] = $n;
                                    }
                                }
                            }
                        }
                        // 因为有自动完成可输入其它内容
                        $step = array_intersect($cssClassArr, [
                            'selectpage'
                        ]) ? 0 : $step;
                        $attrArr['class'] = implode(' ', $cssClassArr);
                        $isUpload = false;
                        if ($this->isMatchSuffix($field, array_merge($this->imageField, $this->fileField))) {
                            $isUpload = true;
                        }
                        // 如果是步长则加上步长
                        if ($step) {
                            $attrArr['step'] = $step;
                        }
                        // 如果是图片加上个size
                        if ($isUpload) {
                            $attrArr['size'] = 50;
                        }

                        $formAddElement = Form::input($inputType, $fieldName, $defaultValue, $attrArr);
                        $formEditElement = Form::input($inputType, $fieldName, $editValue, $attrArr);
                        if ($search && $replace) {
                            $formAddElement = str_replace($search, $replace, $formAddElement);
                            $formEditElement = str_replace($search, $replace, $formEditElement);
                        }
                        // 如果是图片或文件
                        if ($isUpload) {
                            $formAddElement = $this->getImageUpload($field, $formAddElement);
                            $formEditElement = $this->getImageUpload($field, $formEditElement);
                        }
                    }
                    // 构造添加和编辑HTML信息
                    $addList[] = $this->getFormGroup($field, $formAddElement);
                    $editList[] = $this->getFormGroup($field, $formEditElement);
                }

                // 过滤text类型字段
                if ($v['DATA_TYPE'] != 'text' && $inputType != 'fieldlist') {
                    // 主键
                    if ($v['COLUMN_KEY'] == 'PRI' && !$priDefined) {
                        $priDefined = true;
                        $javascriptList[] = "{checkbox: true}";
                    }
                    if ($this->deleteTimeField == $field) {
                        $recyclebinHtml = $this->getReplacedStub('html/recyclebin-html', [
                            'controllerUrl' => $controllerUrl
                        ]);
                        continue;
                    }
                    if (!$this->fields || in_array($field, explode(',', $this->fields))) {
                        //忽略关系表的外键
                        $ignorepid = $this->relationforeignkey && in_array($field, $this->relationforeignkey);
                        if (!$ignorepid) {
                            // 构造JS列信息
                            $javascriptList[] = $this->getJsColumn($field, $v['DATA_TYPE'], $inputType && in_array($inputType, [
                                'select',
                                'checkbox',
                                'radio'
                            ]) ? '_text' : '', $itemArr);
                        }
                    }
                    if ($this->headingFilterField && $this->headingFilterField == $field && $itemArr) {
                        $headingHtml = $this->getReplacedStub('html/heading-html', [
                            'field' => $field,
                            'fieldName' => Loader::parseName($field, 1, false)
                        ]);
                        $multipleHtml = $this->getReplacedStub('html/multiple-html', ['field' => $field, 'fieldName' => Loader::parseName($field, 1, false), 'controllerUrl' => $controllerUrl]);
                    }
                    // 排序方式,如果有指定排序字段,否则按主键排序
                    $order = $field == $this->sortField ? $this->sortField : $order;
                }
            }

            // 循环关联表,追加语言包和JS列
            foreach ($relations as $index => $relation) {
                if ($relation['relationMode'] == 'hasmany') {
                    $relationFieldText = ucfirst(strtolower($relation['relationName'])) . ' List';
                    // 语言列表
                    if ($relation['relationTableInfo']['Comment']) {
                        $langList[] = $this->getLangItem($relationFieldText, rtrim($relation['relationTableInfo']['Comment'], "表") . "列表");
                    }

                    $relationTableName = $relation['relationTableName'];
                    $relationTableName = stripos($relationTableName, $prefix) === 0 ? substr($relationTableName, strlen($prefix)) : $relationTableName;

                    if (in_array($relationTableName, $this->systemTables)) {
                        list($relationControllerNamespace, $relationControllerName, $relationControllerFile, $relationControllerArr) = $this->getControllerData($moduleName, $relation['relationController'], $relationTableName);
                    } else {
                        list($relationControllerNamespace, $relationControllerName, $relationControllerFile, $relationControllerArr) = $this->getAddonControllerData($addonPath, $moduleName, $relation['relationController'], $relationTableName);
                    }
                    $relationControllerArr = array_map("strtolower", $relationControllerArr);
                    if (count($relationControllerArr) > 1) {
                        $relationControllerArr = [
                            implode('.', $relationControllerArr)
                        ];
                    }
                    $relationControllerArr[] = 'index';
                    //修改默认的传参的方式，主要是匹配 index.js 中的 Controller._queryString
                    //$relationControllerArr[] = $relation['relationForeignKey'] . '/{ids}';
                    $relationControllerArr[] = '?' . $relation['relationForeignKey'] . '={ids}';
                    $relationControllerUrl = implode('/', $relationControllerArr);

                    // 构造JS列信息
                    $operateButtonList[] = "{name: 'addtabs',title: __('{$relationFieldText}'),text: __('{$relationFieldText}'),classname: 'btn btn-xs btn-info btn-dialog',icon: 'fa fa-list',url: '" . $relationControllerUrl . "'}";
                    // echo "php think crud -t {$relation['relationTableName']} -c {$relation['relationController']} -m {$relation['relationModel']} -i " . implode(',', $relation['relationFields']);
                    // 不存在关联表控制器的情况下才进行生成

                    if (!is_file($relationControllerFile)) {
                        //echo $relationControllerFile;die;
                        $templCrud = new Crud([
                            'addon' => $this->addon,
                            'mtable' => $relationTableName,
                            'relationcontroller' => $relation['relationController'],
                            'model' => $relation['relationModel'],
                            'relationfields' => implode(',', $relation['relationFields'])
                        ]);
                        if ($relation['relationMode'] === 'hasmany') {
                            $ctrparts = explode("/", $relation['relationController']);
                            $templCrud->controller = $this->getCamelizeName(array_pop($ctrparts));
                        }
                        $templCrud->execute($output);
                        // 合并代码文件到主文件中
                        $this->codeFiles = array_merge($this->codeFiles, $templCrud->codeFiles);
                        //$this->norepeatFiles();
                        // exec( "php think crud -t {$relation['relationTableName']} -c {$relation['relationController']} -m {$relation['relationModel']} -i " . implode( ',', $relation['relationFields'] ) );
                    }
                }

                $modenameprefix = $this->foreignkey2prop($relation['relationForeignKey']);
                foreach ($relation['relationColumnList'] as $k => $v) {
                    // 不显示的字段直接过滤掉
                    if ($relation['relationFields'] && !in_array($v['COLUMN_NAME'], $relation['relationFields'])) {
                        continue;
                    }

                    // $relationField = strtolower($relation['relationName']) . "." . $v['COLUMN_NAME'];
                    $relationField = $modenameprefix . "." . $v['COLUMN_NAME'];
                    // 语言列表
                    if ($v['COLUMN_COMMENT'] != '') {
                        $langList[] = $this->getLangItem($relationField, $v['COLUMN_COMMENT']);
                    }

                    // 过滤text类型字段
                    if ($v['DATA_TYPE'] != 'text') {
                        // 构造JS列信息
                        if (in_array(strtolower($v['DATA_TYPE']), ['set', 'enum', 'tinyint'])) {
                            $itemArr = $this->getItemArray([], $relationField, $v['COLUMN_COMMENT']);
                            $javascriptList[] = $this->getJsColumn($relationField, $v['DATA_TYPE'], '', $itemArr);
                        } else {

                            $javascriptList[] = $this->getJsColumn($relationField, $v['DATA_TYPE']);
                        }
                    }
                }
            }

            // JS最后一列加上操作列
            $javascriptList[] = str_repeat(" ", 24) . "{field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, " . ($operateButtonList ? "buttons: [" . implode(',', $operateButtonList) . "], " : "") . "formatter: Table.api.formatter.operate}";
            $addList = implode("\n", array_filter($addList));
            $editList = implode("\n", array_filter($editList));
            $javascriptList = implode(",\n", array_filter($javascriptList));
            $langList = implode(",\n", array_filter($langList));
            // 数组等号对齐
            $langList = array_filter(explode(",\n", $langList . ",\n"));
            foreach ($langList as &$line) {
                if (preg_match("/^\s+'([^']+)'\s*=>\s*'([^']+)'\s*/is", $line, $matches)) {
                    $line = "    '{$matches[1]}'" . str_pad('=>', ($this->fieldMaxLen - strlen($matches[1]) + 3), ' ', STR_PAD_LEFT) . " '{$matches[2]}'";
                }
            }
            unset($line);
            $langList = implode(",\n", array_filter($langList));
            $this->fixedcolumns = count($columnList) >= 10 ? 1 : $this->fixedcolumns;

            $fixedColumnsJs = '';
            if (is_numeric($this->fixedcolumns) && $this->fixedcolumns) {
                $fixedColumnsJs = "\n" . str_repeat(" ", 16) . "fixedColumns: true,\n" . str_repeat(" ", 16) . ($this->fixedcolumns < 0 ? "fixedNumber" : "fixedRightNumber") . ": " . $this->fixedcolumns . ",";
            }

            // 表注释
            $tableComment = $modelTableInfo['Comment'];
            $tableComment = mb_substr($tableComment, -1) == '表' ? mb_substr($tableComment, 0, -1) . '管理' : $tableComment;

            $modelInit = '';
            if ($priKey != $order) {
                $modelInit = $this->getReplacedStub('mixins' . DS . 'modelinit', [
                    'order' => $order
                ]);
            }

            $data = [
                'modelConnection' => $this->db == 'database' ? '' : "public \$connection = '{$this->db}';",
                'controllerNamespace' => $controllerNamespace,
                'modelNamespace' => $modelNamespace,
                'validateNamespace' => $validateNamespace,
                'controllerUrl' => $controllerUrl,
                'controllerName' => $controllerName,
                'controllerAssignList' => implode("\n", $controllerAssignList),
                'modelName' => $modelName,
                'modelTableName' => $modelTableName,
                'modelTableType' => $modelTableType,
                'modelTableTypeName' => $modelTableTypeName,
                'validateName' => $validateName,
                'tableComment' => $tableComment,
                'iconName' => $iconName,
                'pk' => $priKey,
                'order' => $order,
                'fixedColumnsJs' => $fixedColumnsJs,
                'table' => $this->mtable,
                'tableName' => $modelTableName,
                'addList' => $addList,
                'editList' => $editList,
                'javascriptList' => $javascriptList,
                'langList' => $langList,
                'sofeDeleteClassPath' => in_array($this->deleteTimeField, $fieldArr) ? "use traits\model\SoftDelete;" : '', //兼容1.2版本
                'softDeleteClassPath' => in_array($this->deleteTimeField, $fieldArr) ? "use traits\model\SoftDelete;" : '',
                'softDelete' => in_array($this->deleteTimeField, $fieldArr) ? "use SoftDelete;" : '',
                'modelAutoWriteTimestamp' => in_array($this->createTimeField, $fieldArr) || in_array($this->updateTimeField, $fieldArr) ? "'integer'" : 'false',
                'createTime' => in_array($this->createTimeField, $fieldArr) ? "'{$this->createTimeField}'" : 'false',
                'updateTime' => in_array($this->updateTimeField, $fieldArr) ? "'{$this->updateTimeField}'" : 'false',
                'deleteTime' => in_array($this->deleteTimeField, $fieldArr) ? "'{$this->deleteTimeField}'" : 'false',
                'relationSearch' => $relations ? 'true' : 'false',
                'relationWithList' => '',
                'relationMethodList' => '',
                'controllerImport' => $controllerImport,
                'controllerIndex' => '',
                'recyclebinJs' => '',
                'headingHtml' => $headingHtml,
                'multipleHtml' => $multipleHtml,
                'importHtml' => $importHtml,
                'recyclebinHtml' => $recyclebinHtml,
                'visibleFieldList' => $this->fields ? "\$row->visible(['" . implode("','", array_filter(in_array($priKey, explode(',', $this->fields)) ? explode(',', $this->fields) : explode(',', $priKey . ',' . $this->fields))) . "']);" : '',
                'appendAttrList' => implode(",\n", $appendAttrList),
                'getEnumList' => implode("\n\n", $getEnumArr),
                'getAttrList' => implode("\n\n", $getAttrArr),
                'setAttrList' => implode("\n\n", $setAttrArr),
                'modelInit' => $modelInit
            ];

            // 如果使用关联模型
            if ($relations) {
                $relationWithList = $relationMethodList = $relationVisibleFieldList = [];
                $relationKeyArr = [
                    'hasone' => 'hasOne',
                    'belongsto' => 'belongsTo',
                    'hasmany' => 'hasMany'
                ];

                foreach ($relations as $index => $relation) {

                    // 关联的模式
                    $relation['relationMode'] = strtolower($relation['relationMode']);
                    $relation['relationMode'] = array_key_exists($relation['relationMode'], $relationKeyArr) ? $relationKeyArr[$relation['relationMode']] : '';
                    // 需要构造关联的方法
                    if ($relation['relationMode'] == 'hasMany') {
                        $relation['relationMethod'] = strtolower($relation['relationName']);
                    } else {
                        $relation['relationMethod'] = $this->foreignkey2prop($relation['relationForeignKey']);
                    }
                    // 关联字段
                    $relation['relationPrimaryKey'] = $relation['relationPrimaryKey'] ? $relation['relationPrimaryKey'] : $priKey;

                    // 构造关联模型的方法
                    $relationMethodList[] = $this->getReplacedStub('mixins' . DS . 'modelrelationmethod' . ($relation['relationMode'] == 'hasMany' ? '-hasmany' : ''), $relation);

                    if ($relation['relationMode'] == 'hasMany') {
                        continue;
                    }

                    // 预载入的方法
                    $relationWithList[] = $relation['relationMethod'];

                    unset($relation['relationColumnList'], $relation['relationFieldList'], $relation['relationTableInfo']);

                    // 如果设置了显示主表字段，则必须显式将关联表字段显示
                    if ($this->fields) {
                        $relationVisibleFieldList[] = "\$row->visible(['{$relation['relationMethod']}']);";
                    }

                    // 显示的字段
                    if ($relation['relationFields']) {
                        $relationVisibleFieldList[] = "\$row->getRelation('" . $relation['relationMethod'] . "')->visible(['" . implode("','", $relation['relationFields']) . "']);";
                    }
                }

                $data['relationWithList'] = "->with(['" . implode("','", $relationWithList) . "'])";
                $data['relationMethodList'] = implode("\n\n", $relationMethodList);
                $data['relationVisibleFieldList'] = implode("\n\t\t\t\t", $relationVisibleFieldList);
                if ($this->tree_switch) {
                    $data['treeName'] = $relation['relationFields'];
                    // 需要重写index方法
                    $data['controllerIndex'] = $this->getReplacedStub('addondev_controllertreeindex', $data);
                } else {
                    if ($relationWithList) {
                        // 需要重写index方法
                        $data['controllerIndex'] = $this->getReplacedStub('controllerindex', $data);
                    }
                }
            } elseif ($this->fields) {
                $data = array_merge($data, [
                    'relationWithList' => '',
                    'relationMethodList' => '',
                    'relationVisibleFieldList' => ''
                ]);
                // 需要重写index方法
                $data['controllerIndex'] = $this->getReplacedStub('controllerindex', $data);
            }

            // 生成控制器文件
            $this->writeToFile('controller', $data, $controllerFile);
            // 生成模型文件
            $this->writeToFile('model', $data, $modelFile);

            if ($relations) {
                foreach ($relations as $i => $relation) {
                    $relation['modelNamespace'] = $relation['relationNamespace'];
                    //命名空间为app\admin\model的模型是系统内部模型不能覆盖
                    //必须忽略
                    //1.关系模型文件已经存在
                    //2.关系模型文件与主模型文件相同
                    //3.关系模型的namespace是app\admin\model
                    if (
                        is_file($relation['relationFile']) ||
                        $relation['relationFile'] == $modelFile ||
                        $relation['modelNamespace'] == 'app\\admin\\model'
                    ) {
                        continue;
                    } else {
                        // 生成关联模型文件
                        $this->writeToFile('relationmodel', $relation, $relation['relationFile']);
                    }
                }
            }
            // 生成验证文件
            $this->writeToFile('validate', $data, $validateFile);
            // 生成视图文件
            $this->writeToFile('add', $data, $addFile);
            $this->writeToFile('edit', $data, $editFile);
            $this->writeToFile('index', $data, $indexFile);
            if ($recyclebinHtml) {
                $this->writeToFile('recyclebin', $data, $recyclebinFile);
                $recyclebinTitle = in_array('title', $fieldArr) ? 'title' : (in_array('name', $fieldArr) ? 'name' : '');
                $recyclebinTitleJs = $recyclebinTitle ? "\n                        {field: '{$recyclebinTitle}', title: __('" . (ucfirst($recyclebinTitle)) . "'), align: 'left'}," : '';
                $data['recyclebinJs'] = $this->getReplacedStub('mixins/recyclebinjs', [
                    'deleteTimeField' => $this->deleteTimeField,
                    'recyclebinTitleJs' => $recyclebinTitleJs,
                    'controllerUrl' => $controllerUrl
                ]);
            }
            // 生成JS文件
            if ($this->tree_switch) {
                $this->writeToFile('addondev_javascripttree', $data, $javascriptFile);
            } else {
                $this->writeToFile('addondev_javascript', $data, $javascriptFile);
            }
            // 生成语言文件
            $this->writeToFile('lang', $data, $langFile);
        } catch (ErrorException $e) {
            throw new Exception("Code: " . $e->getCode() . "\nLine: " . $e->getLine() . "\nMessage: " . $e->getMessage() . "\nFile: " . $e->getFile());
        }

        $this->norepeatFiles();
        $output->info("Build Successed");
        return $controllerUrl;
    }

    public function checkFileExisted($controllerFile, $modelFile, $validateFile)
    {
        if (IS_CLI) {
            // 非覆盖模式时如果存在控制器文件则报错
            if (is_file($controllerFile) && !$this->force) {
                throw new Exception("controller already exists!\nIf you need to rebuild again, use the parameter --force=true ");
            }

            // 非覆盖模式时如果存在模型文件则报错
            if (is_file($modelFile) && !$this->force) {
                throw new Exception("model already exists!\nIf you need to rebuild again, use the parameter --force=true ");
            }

            // 非覆盖模式时如果存在验证文件则报错
            if (is_file($validateFile) && !$this->force) {
                throw new Exception("validate already exists!\nIf you need to rebuild again, use the parameter --force=true ");
            }
        }
    }

    public function genMenu($output, $controllerUrl)
    {
        // 继续生成菜单
        if (!$this->delete_switch) {
            // 同步文件到application
            // exec( "php think addon-dev -a {$this->addon} -c sync" );
            $tempAddon = new Addon();
            $tempAddon->do($output, $this->addon, 'sync');
            // 同步文件成功才能反射，执行后面的菜单生成的命令
            // exec( "php think menu -c {$controllerUrl}" );
            $tempMenu = new Menu();
            if ($this->menu_switch) {
                $tempMenu->do($output, $controllerUrl);
            } else {
                $tempMenu->do($output, $controllerUrl, '', null, null, false);
            }
        }
    }

    /**
     * 获取控制器相关信息
     *
     * @param
     *            $module
     * @param
     *            $controller
     * @param
     *            $table
     * @return array
     */
    public function getAddonControllerData($addonPath, $module, $controller, $table)
    {
        return $this->getAddonParseNameData($addonPath, $module, $controller, $table, 'controller');
    }

    /**
     * 获取模型相关信息
     *
     * @param
     *            $module
     * @param
     *            $model
     * @param
     *            $table
     * @return array
     */
    public function getAddonModelData($addonPath, $module, $model, $table)
    {
        return $this->getAddonParseNameData($addonPath, $module, $model, $table, 'model');
    }

    /**
     * 获取验证器相关信息
     *
     * @param
     *            $module
     * @param
     *            $validate
     * @param
     *            $table
     * @return array
     */
    public function getAddonValidateData($addonPath, $module, $validate, $table)
    {
        return $this->getAddonParseNameData($addonPath, $module, $validate, $table, 'validate');
    }

    /**
     * 获取已解析相关信息
     *
     * @param string $module
     *            模块名称
     * @param string $name
     *            自定义名称
     * @param string $table
     *            数据表名
     * @param string $type
     *            解析类型，本例中为controller、model、validate
     * @return array
     */
    public function getAddonParseNameData($addonPath, $module, $name, $table, $type)
    {
        $arr = [];
        if (!$name) {
            $parseName = Loader::parseName($table, 1);
            // $name = str_replace('_', '/', $table);
            $name = $this->addon . DS . $parseName;
        }

        $appNamespace = Config::get('app_namespace');
        $baseNamespace = "{$appNamespace}\\{$module}\\{$type}";

        //如果只是设置了模型的基础名称，则检查是否在管理后台已经有非子目录的模型了，
        //如果设置的名称有子目录，则忽略，根据子目录来定位模型名称
        if (basename($name) == $name) {
            if (!class_exists($baseNamespace . "\\" . $name)) {
                $name = $this->addon . DS . $name;
            }
        }
        $name = str_replace([
            '.',
            '/',
            '\\'
        ], '/', $name);
        $arr = explode('/', $name);
        $parseName = ucfirst($this->getCamelizeName(array_pop($arr)));
        $parseArr = $arr;
        array_push($parseArr, $parseName);
        // 类名不能为内部关键字
        if (in_array(strtolower($parseName), $this->internalKeywords)) {
            throw new Exception(__('Unable to use internal variable:%', [$parseName]));
        }
        $parseNamespace = $baseNamespace . ($arr ? "\\" . implode("\\", $arr) : "");
        $moduleDir = $addonPath . 'application' . DS . $module . DS;
        $parseFile = $moduleDir . $type . DS . ($arr ? implode(DS, $arr) . DS : '') . $parseName . '.php';

        return [
            $parseNamespace,
            $parseName,
            $parseFile,
            $parseArr
        ];
    }

    public function getEnum(&$getEnum, &$controllerAssignList, $field, $itemArr = [], $inputType = '')
    {
        if (!in_array($inputType, [
            'datetime',
            'select',
            'multiple',
            'checkbox',
            'radio'
        ])) {
            return;
        }
        $fieldList = $this->getFieldListName($field);
        $methodName = 'get' . ucfirst($fieldList);
        foreach ($itemArr as $k => &$v) {
            $v = "__('" . mb_ucfirst($v) . "')";
        }
        unset($v);
        $itemString = $this->getArrayString($itemArr);
        $getEnum[] = <<<EOD
    public function {$methodName}()
    {
        return [{$itemString}];
    }
EOD;
        $controllerAssignList[] = <<<EOD
        \$this->assign("{$fieldList}", \$this->model->{$methodName}());
EOD;
    }

    public function getAttr(&$getAttr, $field, $inputType = '')
    {
        if (!in_array($inputType, [
            'datetime',
            'select',
            'multiple',
            'checkbox',
            'radio'
        ])) {
            return;
        }
        $attrField = ucfirst($this->getCamelizeName($field));
        $getAttr[] = $this->getReplacedStub("mixins" . DS . $inputType, [
            'field' => $field,
            'methodName' => "get{$attrField}TextAttr",
            'listMethodName' => "get{$attrField}List"
        ]);
    }

    public function setAttr(&$setAttr, $field, $inputType = '')
    {
        if (!in_array($inputType, [
            'datetime',
            'checkbox',
            'select'
        ])) {
            return;
        }
        $attrField = ucfirst($this->getCamelizeName($field));
        if ($inputType == 'datetime') {
            $return = <<<EOD
return \$value === '' ? null : (\$value && !is_numeric(\$value) ? strtotime(\$value) : \$value);
EOD;
        } elseif (in_array($inputType, [
            'checkbox',
            'select'
        ])) {
            $return = <<<EOD
return is_array(\$value) ? implode(',', \$value) : \$value;
EOD;
        }
        $setAttr[] = <<<EOD
    public function set{$attrField}Attr(\$value)
    {
        $return
    }
EOD;
    }

    public function appendAttr(&$appendAttrList, $field)
    {
        $appendAttrList[] = <<<EOD
        '{$field}_text'
EOD;
    }

    /**
     * 移除相对的空目录
     *
     * @param
     *            $parseFile
     * @param
     *            $parseArr
     * @return bool
     */
    public function removeEmptyBaseDir($parseFile, $parseArr)
    {
        if (count($parseArr) > 1) {
            $parentDir = dirname($parseFile);
            for ($i = 0; $i < count($parseArr); $i++) {
                try {
                    $iterator = new \FilesystemIterator($parentDir);
                    $isDirEmpty = !$iterator->valid();
                    if ($isDirEmpty) {
                        rmdir($parentDir);
                        $parentDir = dirname($parentDir);
                    } else {
                        return true;
                    }
                } catch (\UnexpectedValueException $e) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 获取控制器URL
     *
     * @param string $moduleName
     * @param array $baseNameArr
     * @return string
     */
    public function getControllerUrl($moduleName, $baseNameArr)
    {
        for ($i = 0; $i < count($baseNameArr) - 1; $i++) {
            $temp = array_slice($baseNameArr, 0, $i + 1);
            $temp[$i] = ucfirst($temp[$i]);
            $controllerFile = APP_PATH . $moduleName . DS . 'controller' . DS . implode(DS, $temp) . '.php';
            // 检测父级目录同名控制器是否存在，存在则变更URL格式
            if (is_file($controllerFile)) {
                $baseNameArr = [
                    implode('.', $baseNameArr)
                ];
                break;
            }
        }
        $controllerUrl = strtolower(implode('/', $baseNameArr));
        return $controllerUrl;
    }

    /**
     * 获取控制器相关信息
     *
     * @param
     *            $module
     * @param
     *            $controller
     * @param
     *            $table
     * @return array
     */
    public function getControllerData($module, $controller, $table)
    {
        return $this->getParseNameData($module, $controller, $table, 'controller');
    }

    /**
     * 获取模型相关信息
     *
     * @param
     *            $module
     * @param
     *            $model
     * @param
     *            $table
     * @return array
     */
    public function getModelData($module, $model, $table)
    {
        return $this->getParseNameData($module, $model, $table, 'model');
    }

    /**
     * 获取验证器相关信息
     *
     * @param
     *            $module
     * @param
     *            $validate
     * @param
     *            $table
     * @return array
     */
    public function getValidateData($module, $validate, $table)
    {
        return $this->getParseNameData($module, $validate, $table, 'validate');
    }

    /**
     * 获取已解析相关信息
     *
     * @param string $module
     *            模块名称
     * @param string $name
     *            自定义名称
     * @param string $table
     *            数据表名
     * @param string $type
     *            解析类型，本例中为controller、model、validate
     * @return array
     */
    public function getParseNameData($module, $name, $table, $type)
    {
        $arr = [];
        if (!$name) {
            $parseName = Loader::parseName($table, 1);
            $name = str_replace('_', '/', $table);
        }

        $name = str_replace([
            '.',
            '/',
            '\\'
        ], '/', $name);
        $arr = explode('/', $name);
        $parseName = ucfirst(array_pop($arr));
        $parseArr = $arr;
        array_push($parseArr, $parseName);

        // 类名不能为内部关键字
        if (in_array(strtolower($parseName), $this->internalKeywords)) {
            throw new Exception(__('Unable to use internal variable:%', [$parseName]));
        }
        $appNamespace = Config::get('app_namespace');
        $parseNamespace = "{$appNamespace}\\{$module}\\{$type}" . ($arr ? "\\" . implode("\\", $arr) : "");
        $moduleDir = APP_PATH . $module . DS;
        $parseFile = $moduleDir . $type . DS . ($arr ? implode(DS, $arr) . DS : '') . $parseName . '.php';
        return [
            $parseNamespace,
            $parseName,
            $parseFile,
            $parseArr
        ];
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
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $content = $this->getReplacedStub($name, $data);
        if (IS_CLI) {
            if (!is_dir(dirname($pathname))) {
                mkdir(dirname($pathname), 0755, true);
            }
            file_put_contents($pathname, $content);
        } else {
            $codeFile = new CodeFile($this->addon, $pathname, $content);
            $this->codeFiles[] = $codeFile;
        }
    }

    /**
     * 获取替换后的数据
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public function getReplacedStub($name, $data)
    {
        foreach ($data as $index => &$datum) {
            $datum = is_array($datum) ? '' : $datum;
        }
        unset($datum);
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[] = "{%{$k}%}";
            $replace[] = $v;
        }
        $stubname = $this->getStub($name);
        if (isset($this->stubList[$stubname])) {
            $stub = $this->stubList[$stubname];
        } else {
            $this->stubList[$stubname] = $stub = file_get_contents($stubname);
        }
        $content = str_replace($search, $replace, $stub);
        return $content;
    }

    /**
     * 获取基础模板
     *
     * @param string $name
     * @return string
     */
    protected function getStub($name)
    {
        if (substr($name, 0, 9) == 'addondev_') {
            $name = substr($name, 9);
            return ROOT_PATH . 'addons' . DS . 'addondev' . DS . 'command' . DS . 'stubs' . DS . $name . '.stub';
        }
        return ROOT_PATH . 'application' . DS . 'admin' . DS . 'command' . DS . 'Crud' . DS . 'stubs' . DS . $name . '.stub';
    }

    public function getLangItem($field, $content)
    {
        if ($content || !Lang::has($field)) {
            $isHeadingField = $this->headingFilterField ==  $field;
            $this->fieldMaxLen = strlen($field) > $this->fieldMaxLen ? strlen($field) : $this->fieldMaxLen;

            $content = str_replace('，', ',', $content);
            if (stripos($content, ':') !== false && stripos($content, ',') && stripos($content, '=') !== false) {
                list($fieldLang, $item) = explode(':', $content);
                $itemArr = [
                    $field => $fieldLang
                ];
                foreach (explode(',', $item) as $k => $v) {
                    $valArr = explode('=', $v);
                    if (count($valArr) == 2) {
                        list($key, $value) = $valArr;
                        $lang_key = $field . ' ' . $key;
                        $itemArr[$lang_key] = $value;
                        //处理过滤字段，多操作的方言
                        if ($isHeadingField) {
                            $lang_key = 'Set ' . $field . ' to ' . $key;
                            $itemArr[$lang_key] = '设为' . $value;
                        }
                        $this->fieldMaxLen = strlen($lang_key) > $this->fieldMaxLen ? strlen($lang_key) : $this->fieldMaxLen;
                    }
                }
            } else {
                $itemArr = [
                    $field => $content
                ];
                if ($field == 'pid') {
                    $itemArr['Add Sub'] = '添加子';
                    $this->fieldMaxLen = strlen('Add Sub') > $this->fieldMaxLen ? strlen('Add Sub') : $this->fieldMaxLen;
                }
            }
            $resultArr = [];
            foreach ($itemArr as $k => $v) {
                $resultArr[] = "    '" . mb_ucfirst($k) . "' => '{$v}'";
            }
            return implode(",\n", $resultArr);
        } else {
            return '';
        }
    }



    /**
     * 读取数据和语言数组列表
     *
     * @param array $arr
     * @param boolean $withTpl
     * @return array
     */
    public function getLangArray($arr, $withTpl = true)
    {
        $langArr = [];
        foreach ($arr as $k => $v) {
            $langArr[$k] = is_numeric($k) ? ($withTpl ? "{:" : "") . "__('" . mb_ucfirst($v) . "')" . ($withTpl ? "}" : "") : $v;
        }
        return $langArr;
    }

    /**
     * 将数据转换成带字符串
     *
     * @param array $arr
     * @return string
     */
    public function getArrayString($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }
        $stringArr = [];
        foreach ($arr as $k => $v) {
            $is_var = in_array(substr($v, 0, 1), [
                '$',
                '_'
            ]);
            if (!$is_var) {
                $v = str_replace("'", "\'", $v);
                $k = str_replace("'", "\'", $k);
            }
            $stringArr[] = "'" . $k . "' => " . ($is_var ? $v : "'{$v}'");
        }
        return implode(", ", $stringArr);
    }

    public function getItemArray($item, $field, $comment)
    {
        $itemArr = [];
        $comment = str_replace('，', ',', $comment);
        if (stripos($comment, ':') !== false && stripos($comment, ',') && stripos($comment, '=') !== false) {
            list($fieldLang, $item) = explode(':', $comment);
            $itemArr = [];
            foreach (explode(',', $item) as $k => $v) {
                $valArr = explode('=', $v);
                if (count($valArr) == 2) {
                    list($key, $value) = $valArr;
                    $itemArr[$key] = $field . ' ' . $key;
                }
            }
        } else {
            foreach ($item as $k => $v) {
                $itemArr[$v] = is_numeric($v) ? $field . ' ' . $v : $v;
            }
        }
        return $itemArr;
    }

    public function getFieldType(&$v)
    {
        $inputType = 'text';
        switch ($v['DATA_TYPE']) {
            case 'bigint':
            case 'int':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':
                $inputType = 'number';
                break;
            case 'enum':
            case 'set':
                $inputType = 'select';
                break;
            case 'decimal':
            case 'double':
            case 'float':
                $inputType = 'number';
                break;
            case 'longtext':
            case 'text':
            case 'mediumtext':
            case 'smalltext':
            case 'tinytext':
                $inputType = 'textarea';
                break;
            case 'year':
            case 'date':
            case 'time':
            case 'datetime':
            case 'timestamp':
                $inputType = 'datetime';
                break;
            default:
                break;
        }
        $fieldsName = $v['COLUMN_NAME'];
        // 指定后缀说明也是个时间字段
        if ($this->isMatchSuffix($fieldsName, $this->intDateSuffix)) {
            $inputType = 'datetime';
        }
        // 指定后缀结尾且类型为enum,说明是个单选框
        if ($this->isMatchSuffix($fieldsName, $this->enumRadioSuffix) && $v['DATA_TYPE'] == 'enum') {
            $inputType = "radio";
        }
        // 指定后缀结尾且类型为set,说明是个复选框
        if ($this->isMatchSuffix($fieldsName, $this->setCheckboxSuffix) && $v['DATA_TYPE'] == 'set') {
            $inputType = "checkbox";
        }
        // 指定后缀结尾且类型为char或tinyint且长度为1,说明是个Switch复选框
        if ($this->isMatchSuffix($fieldsName, $this->switchSuffix) && ($v['COLUMN_TYPE'] == 'tinyint(1)' || $v['COLUMN_TYPE'] == 'char(1)') && $v['COLUMN_DEFAULT'] !== '' && $v['COLUMN_DEFAULT'] !== null) {
            $inputType = "switch";
        }
        // 指定后缀结尾城市选择框
        if ($this->isMatchSuffix($fieldsName, $this->citySuffix) && ($v['DATA_TYPE'] == 'varchar' || $v['DATA_TYPE'] == 'char')) {
            $inputType = "citypicker";
        }
        // 指定后缀结尾城市选择框
        if ($this->isMatchSuffix($fieldsName, $this->rangeSuffix) && ($v['DATA_TYPE'] == 'varchar' || $v['DATA_TYPE'] == 'char')) {
            $inputType = "datetimerange";
        }
        // 指定后缀结尾JSON配置
        if ($this->isMatchSuffix($fieldsName, $this->jsonSuffix) && ($v['DATA_TYPE'] == 'varchar' || $v['DATA_TYPE'] == 'text')) {
            $inputType = "fieldlist";
        }
        // 指定后缀结尾标签配置
        if ($this->isMatchSuffix($fieldsName, $this->tagSuffix) && ($v['DATA_TYPE'] == 'varchar' || $v['DATA_TYPE'] == 'text')) {
            $inputType = "tagsinput";
        }
        return $inputType;
    }

    /**
     * 判断是否符合指定后缀
     *
     * @param string $field
     *            字段名称
     * @param mixed $suffixArr
     *            后缀
     * @return boolean
     */
    public function isMatchSuffix($field, $suffixArr)
    {
        $suffixArr = is_array($suffixArr) ? $suffixArr : explode(',', $suffixArr);
        foreach ($suffixArr as $k => $v) {
            if (preg_match("/{$v}$/i", $field)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取表单分组数据
     *
     * @param string $field
     * @param string $content
     * @return string
     */
    public function getFormGroup($field, $content)
    {
        $langField = mb_ucfirst($field);
        return <<<EOD
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">{:__('{$langField}')}:</label>
        <div class="col-xs-12 col-sm-8">
            {$content}
        </div>
    </div>
EOD;
    }

    /**
     * 获取图片模板数据
     *
     * @param string $field
     * @param string $content
     * @return string
     */
    public function getImageUpload($field, $content)
    {
        $uploadfilter = $selectfilter = '';
        if ($this->isMatchSuffix($field, $this->imageField)) {
            $uploadfilter = ' data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp,image/webp"';
            $selectfilter = ' data-mimetype="image/*"';
        }
        $multiple = substr($field, -1) == 's' ? ' data-multiple="true"' : ' data-multiple="false"';
        $preview = ' data-preview-id="p-' . $field . '"';
        $previewcontainer = $preview ? '<ul class="row list-inline faupload-preview" id="p-' . $field . '"></ul>' : '';
        return <<<EOD
<div class="input-group">
                {$content}
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="faupload-{$field}" class="btn btn-danger faupload" data-input-id="c-{$field}"{$uploadfilter}{$multiple}{$preview}><i class="fa fa-upload"></i> {:__('Upload')}</button></span>
                    <span><button type="button" id="fachoose-{$field}" class="btn btn-primary fachoose" data-input-id="c-{$field}"{$selectfilter}{$multiple}><i class="fa fa-list"></i> {:__('Choose')}</button></span>
                </div>
                <span class="msg-box n-right" for="c-{$field}"></span>
            </div>
            {$previewcontainer}
EOD;
    }

    /**
     * 获取JS列数据
     *
     * @param string $field
     * @param string $datatype
     * @param string $extend
     * @param array $itemArr
     * @return string
     */
    public function getJsColumn($field, $datatype = '', $extend = '', $itemArr = [])
    {
        $lang = mb_ucfirst($field);
        $formatter = '';
        foreach ($this->fieldFormatterSuffix as $k => $v) {
            if (preg_match("/{$k}$/i", $field)) {
                if (is_array($v)) {
                    if (in_array($datatype, $v['type'])) {
                        $formatter = $v['name'];
                        break;
                    }
                } else {
                    $formatter = $v;
                    break;
                }
            }
        }
        $html = str_repeat(" ", 24) . "{field: '{$field}', title: __('{$lang}')";

        if ($datatype == 'set') {
            $formatter = 'label';
        }
        foreach ($itemArr as $k => &$v) {
            if (substr($v, 0, 3) !== '__(') {
                $v = "__('" . mb_ucfirst($v) . "')";
            }
        }
        unset($v);
        $searchList = json_encode($itemArr, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        $searchList = str_replace([
            '":"',
            '"}',
            ')","'
        ], [
            '":',
            '}',
            '),"'
        ], $searchList);
        if ($itemArr) {
            $html .= ", searchList: " . $searchList;
        }

        // 文件、图片、权重等字段默认不加入搜索栏，字符串类型默认LIKE
        $noSearchFiles = [
            'file$',
            'files$',
            'image$',
            'images$',
            '^weigh$'
        ];
        if (preg_match("/" . implode('|', $noSearchFiles) . "/i", $field)) {
            $html .= ", operate: false";
        } else if (in_array($datatype, [
            'varchar'
        ])) {
            $html .= ", operate: 'LIKE'";
        }

        if (in_array($datatype, [
            'date',
            'datetime'
        ]) || $formatter === 'datetime') {
            $html .= ", operate:'RANGE', addclass:'datetimerange', autocomplete:false";
        } elseif (in_array($datatype, [
            'float',
            'double',
            'decimal'
        ])) {
            $html .= ", operate:'BETWEEN'";
        }
        if (in_array($datatype, [
            'set'
        ])) {
            $html .= ", operate:'FIND_IN_SET'";
        }
        if (isset($fieldConfig['CHARACTER_MAXIMUM_LENGTH']) && $fieldConfig['CHARACTER_MAXIMUM_LENGTH'] >= 255 && in_array($datatype, ['varchar']) && !$formatter) {
            $formatter = 'content';
            $html .= ", table: table, class: 'autocontent'";
        }
        if (in_array($formatter, [
            'image',
            'images'
        ])) {
            $html .= ", events: Table.api.events.image";
        }
        if (in_array($formatter, [
            'toggle'
        ])) {
            $html .= ", table: table";
        }

        if ($this->tree_switch && $this->relationfields && in_array($field, $this->relationfields)) {
            $html .= ', formatter: Controller.api.formatter.treelabel}';
        } else {
            if ($itemArr && !$formatter) {
                $formatter = 'normal';
            }
            if ($formatter) {
                $html .= ", formatter: Table.api.formatter." . $formatter . "}";
            } else {
                $html .= "}";
            }
        }

        return $html;
    }

    public function getCamelizeName($uncamelized_words, $separator = '_')
    {
        if (\str_contains($uncamelized_words, $separator)) {
            $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
            return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
        } else {
            return ltrim($uncamelized_words, $separator);
        }
    }

    public function getFieldListName($field)
    {
        return $this->getCamelizeName($field) . 'List';
    }

    /**
     * 外健转化成属性名称
     * pid特殊处理
     * 必须都是小写字母，因为在生成关联模型关系的时候，关系对象指向的模型键值不兼容驼峰命名
     * 比如：如果设置了parentNode ，在关系模型中实际为  parent_node
     * $row->getRelation('parentNode') ，则为空，因为 parentNode =>  parent_node
     * 代码是自动生成的，所以规避都用小写 parentnode
     */
    public function foreignkey2prop($key)
    {
        if ($key == 'pid') {
            return "parentnode";
        }
        $key = str_replace("_", "", $key);
        $suffix = strtolower(substr($key, -2));
        if ($suffix == 'id') {
            return substr($key, 0, -2);
        } else {
            //规避field 与 方法同名
            return $key . 'info';
        }
    }

    protected function norepeatFiles()
    {
        $files = [];
        foreach ($this->codeFiles as $file) {
            $files[$file->id] = $file;
        }
        $this->codeFiles = array_values($files);
    }

    protected function onlineDelMode(
        $controllerFile,
        $modelFile,
        $validateFile,
        $addFile,
        $editFile,
        $indexFile,
        $recyclebinFile,
        $langFile,
        $javascriptFile,
        $controllerUrl
    ) {
        $readyFiles = [
            $controllerFile,
            $modelFile,
            $validateFile,
            $addFile,
            $editFile,
            $indexFile,
            $recyclebinFile,
            $langFile,
            $javascriptFile
        ];
        foreach ($readyFiles as $file) {
            $this->codeFiles[] = new CodeFile($this->addon, $file);
        }
        return  $controllerUrl;
    }

    protected function cliDelMode(
        $controllerFile,
        $modelFile,
        $validateFile,
        $addFile,
        $editFile,
        $indexFile,
        $recyclebinFile,
        $langFile,
        $javascriptFile,
        $modelArr,
        $validateArr,
        $viewArr,
        $controllerArr,
        $addonPath,
        $controllerUrl,
        $output
    ) {
        $readyFiles = [
            $controllerFile,
            $modelFile,
            $validateFile,
            $addFile,
            $editFile,
            $indexFile,
            $recyclebinFile,
            $langFile,
            $javascriptFile
        ];
        foreach ($readyFiles as $k => $v) {
            $output->warning($v);
        }
        if (!$this->force) {
            $output->info("Are you sure you want to delete all those files?  Type 'yes' to continue: ");
            $line = fgets(defined('STDIN') ? STDIN : fopen('php://stdin', 'r'));
            if (trim($line) != 'yes') {
                throw new Exception(__("Operation is aborted!"));
            }
        }
        // 删除 插件目录下的文件
        foreach ($readyFiles as $k => $v) {
            if (file_exists($v)) {
                unlink($v);
            }
            // 删除空文件夹
            switch ($v) {
                case $modelFile:
                    $this->removeEmptyBaseDir($v, $modelArr);
                    break;
                case $validateFile:
                    $this->removeEmptyBaseDir($v, $validateArr);
                    break;
                case $addFile:
                case $editFile:
                case $indexFile:
                case $recyclebinFile:
                    $this->removeEmptyBaseDir($v, $viewArr);
                    break;
                default:
                    $this->removeEmptyBaseDir($v, $controllerArr);
            }
        }
        // 删除 项目 目录下的文件
        foreach ($readyFiles as $k => $v) {
            // 替换为对应的 项目下的文件
            $v = str_replace($addonPath, ROOT_PATH, $v);
            if (file_exists($v)) {
                unlink($v);
            }
            // 删除空文件夹
            switch ($v) {
                case $modelFile:
                    $this->removeEmptyBaseDir($v, $modelArr);
                    break;
                case $validateFile:
                    $this->removeEmptyBaseDir($v, $validateArr);
                    break;
                case $addFile:
                case $editFile:
                case $indexFile:
                case $recyclebinFile:
                    $this->removeEmptyBaseDir($v, $viewArr);
                    break;
                default:
                    $this->removeEmptyBaseDir($v, $controllerArr);
            }
        }

        // 继续删除菜单
        $this->removeMenu($output, $controllerUrl);
        $output->info("Delete Successed");
        return;
    }

    public function removeMenu($output, $controllerUrl)
    {
        // 继续删除菜单
        if ($this->menu_switch) {
            // exec( "php think menu -c {$controllerUrl} -d 1 -f 1" );
            $tempMenu = new Menu();
            $tempMenu->do($output, $controllerUrl, 1, 1);
        }
    }

    public function getAddSubButtonJs($controllerUrl)
    {
        return "{ name: 'add', text: __('Add Sub'), classname: 'btn btn-info btn-xs btn-dialog', icon: 'fa fa-plus',  url: '" . $controllerUrl . "/add/pid/{ids}'}";
    }
}
