<?php
// +----------------------------------------------------------------------
// | ADDONDEV  [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2022 http://dungang.site All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: dungang <dungang@126.com>
// +----------------------------------------------------------------------

namespace app\admin\controller\addondev;

use addons\addondev\library\CodeFile;
use addons\addondev\library\DevEnvTrait;
use app\common\controller\Backend;


/**
 * 代码文件日志
 *
 * @icon fa fa-circle-o
 */
class Log extends Backend
{

    /**
     * AddondevLog模型对象
     * @var \app\admin\model\addondev\AddondevLog
     */
    protected $model = null;


    /**
     * 无需鉴权的方法,但需要登录
     *
     * @var array
     */
    protected $noNeedRight = [
        'index',
        'del',
        'recover',
        'code',
        'diff'
    ];

    use DevEnvTrait;

    public function _initialize()
    {
        parent::_initialize();
        $this->mustDevEnv();
        $this->model = new \app\admin\model\addondev\AddondevLog;
        $this->assign("filetypeList", $this->model->getFiletypeList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $query = $this->model
                ->with(['gen'])
                ->where($where)
                ->order($sort, $order);
            if ($gen_id = $this->request->get("gen_id")) {
                $query->where('gen_id', $gen_id);
            }
            $list = $query->paginate($limit);

            foreach ($list as $row) {

                $row->getRelation('gen')->visible(['name', 'mtable']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 对比本地代码
     */
    public function diff($ids)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $path = ROOT_PATH  . $row->filename;
        if (file_exists($path)) {
            $codeFile = new CodeFile($row->gen->addon, $path, $row->code, false);
            $this->assign('code', $codeFile->diff());
            return $this->view->fetch('addondev/gen/diff');
        } else {
            return $this->error("本地文件不纯在");
        }
    }

    /**
     * 预览代码
     */
    public function code($ids)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $path = ROOT_PATH  . $row->filename;

        $codeFile = new CodeFile($row->gen->addon, $path, $row->code, false);
        $this->assign('code', $codeFile->preview());
        $this->assign('ext', $codeFile->ext);
        return $this->view->fetch('addondev/gen/code');
    }

    /**
     * 回复文件
     */
    public function recover($ids)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $path = ROOT_PATH  . $row->filename;
        $codeFile = new CodeFile($row->gen->addon, $path, $row->code, false);
        if ($codeFile->operation === CodeFile::OP_SKIP) {
            return $this->error("文件没有差异不用恢复");
        } else {
            $log = new \app\admin\model\addondev\AddondevLog;
            $log->gen_id = $row->gen_id;
            $log->filename = $row->filename;
            $log->filetype = $row->filetype;
            $log->code = file_get_contents($path);
            if ($log->save()) {
                $codeFile->save();
                return $this->success("恢复成功");
            }
            return $this->error("恢复文件失败");
        }
    }
}
