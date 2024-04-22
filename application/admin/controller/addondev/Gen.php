<?php

namespace app\admin\controller\addondev;

use app\common\controller\Backend;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use addons\addondev\library\Crud;
use addons\addondev\library\GenHelper;
use addons\addondev\library\OutputFacade;
use app\admin\model\addondev\AddondevLog;
use think\exception\DbException;
use think\Log;

/**
 * 生成代码模板
 *
 * @icon fa fa-circle-o
 */
class Gen extends Backend
{

    /**
     * AddondevGen模型对象
     *
     * @var \app\admin\model\addondev\AddondevGen
     */
    protected $model = null;

    /**
     * 无需鉴权的方法,但需要登录
     *
     * @var array
     */
    protected $noNeedRight = [
        'index',
        'add',
        'edit',
        'del',
        'code',
        'diff',
        'tables'
    ];

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\admin\model\addondev\AddondevGen();
        $this->assign("menuSwitchList", $this->model->getMenuSwitchList());
        $this->assign("deleteSwitchList", $this->model->getDeleteSwitchList());
        $this->assign("importSwitchList", $this->model->getImportSwitchList());
        $this->assign("localSwitchList", $this->model->getLocalSwitchList());
        $this->assign("treeSwitchList", $this->model->getTreeSwitchList());
        $this->assign("addonList", GenHelper::getAddonList());
    }

    /**
     * 添加代码模板
     */
    public function add()
    {
        if ($this->request->isGet()) {
            $id = $this->request->param('id');
            if ($id) {
                $model = $this->model->get($id);
                if ($model) {
                    $this->model = $model;
                }
            }
            $this->assign('row', $this->model);
        }
        return parent::add();
    }


    /**
     * 编辑代码模板
     *
     * @param
     *            $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            $this->assign('fileList', $this->genCodeFiles($row->toArray()));
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            // 是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            $crud = new Crud($row->toArray());
            $output = new OutputFacade();
            $controllerUrl = $crud->execute($output);
            if (isset($params['file']) && $params['file']) {
                //name[] filed posted from form not emputy
                $files = array_filter($params['file'], function ($file) {
                    return $file != '';
                });
                if (count($files)) {
                    foreach ($crud->codeFiles as $file) {
                        if (in_array($file->id, $params['file'])) {
                            //本地文件记录历史代码
                            if (file_exists($file->path)) {
                                $log = new AddondevLog();
                                $log->gen_id = $ids;
                                $log->filename = $file->shortPath;
                                //检查文件类型是否在指定的清单，否则就是Other
                                $types = $log->getFiletypeList();
                                if (empty($types[$file->ext])) {
                                    $log->filetype = 'other';
                                } else {
                                    $log->filetype = $file->ext;
                                }
                                $log->code = file_get_contents($file->path);
                                if ($log->save()) {
                                    if ($crud->delete_switch) {
                                        if ($file->isController()) {
                                            $crud->removeMenu($output, $controllerUrl);
                                        }
                                        $file->delete();
                                    } else {
                                        $file->save();
                                    }
                                }
                            } else {
                                if (!$crud->delete_switch)
                                    $file->save();
                            }
                        }
                    }
                }
            }

            // 是否生成菜单
            $output->showError = false;
            $crud->genMenu($output, $controllerUrl);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        } else {
            $this->success();
        }
    }

    /**
     * 预览代码
     */
    public function code()
    {
        $ids = $this->request->get('ids');
        $file_id = $this->request->get('file_id');
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        $files = $this->genCodeFiles($row->toArray());
        $codeFile = null;
        foreach ($files as $file) {
            if ($file->id == $file_id) {
                $codeFile = $file;
                break;
            }
        }
        if (!$codeFile) {
            $this->error(__('No File were found'));
        }
        if ($codeFile) {
            $this->assign('code', $codeFile->preview());
            $this->assign('ext', $codeFile->ext);
        }
        return $this->view->fetch();
    }

    /**
     * 对比代码
     */
    public function diff()
    {
        $ids = $this->request->get('ids');
        $file_id = $this->request->get('file_id');
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        $files = $this->genCodeFiles($row->toArray());
        $codeFile = null;
        foreach ($files as $file) {
            if ($file->id == $file_id) {
                $codeFile = $file;
                break;
            }
        }
        if (!$codeFile) {
            $this->error(__('No File were found'));
        } else {
            $this->assign('code', $codeFile->diff());
            return $this->view->fetch();
        }
    }


    /**
     * 数据表列表
     *
     * @return string
     */
    public function tables()
    {
        $query = $this->request->get("query", '');
        $suggestions = [];
        if ($query) {
            $dbname = config('database.database');
            $prefixLen = strlen(config('database.prefix'));
            list($systemTables, $inPlaceHolders) = GenHelper::systemTables();
            array_unshift($systemTables,  $dbname, '%' . $query . '%');
            $data = Db::query("select table_name from information_schema.tables where table_schema=? and table_name like ? and table_name not in (" . $inPlaceHolders . ") ", $systemTables);
            if ($data) {
                foreach ($data as $val) {
                    $suggestions[] = substr($val['table_name'], $prefixLen);
                }
            }
        }
        return json([
            'query' => $query,
            'suggestions' => $suggestions
        ]);
    }



    protected function genCodeFiles($config, $silence = true)
    {
        $crud = new Crud($config);
        $files = [];
        if ($silence) {
            try {
                $crud->execute(new OutputFacade());
                $files = $crud->codeFiles;
            } catch (Exception $e) {
                Log::info("代码生成错误：" . $e->getMessage());
                $this->assign("error", "代码生成错误：" . $e->getMessage());
                return [];
            }
        } else {
            $crud->execute(new OutputFacade());
            $files = $crud->codeFiles;
        }
        return array_filter($files, function ($file) {
            return !empty($file->content);
        });
    }
}
