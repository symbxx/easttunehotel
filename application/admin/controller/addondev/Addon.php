<?php

namespace app\admin\controller\addondev;

use addons\addondev\library\Addon as LibraryAddon;
use addons\addondev\library\DevEnvTrait;
use addons\addondev\library\GenHelper;
use addons\addondev\library\OutputFacade;
use app\admin\model\AuthRule;
use app\common\controller\Backend;
use app\common\library\Menu;
use fast\Pinyin;
use fast\Tree;
use think\Exception;

class Addon extends Backend
{

    use DevEnvTrait;

    public function _initialize()
    {
        parent::_initialize();
        $this->mustDevEnv();
        $this->assign("addonList", GenHelper::getAddonList());

    }
    /**
     * 无需鉴权的方法,但需要登录
     *
     * @var array
     */
    protected $noNeedRight = [
        'index',
        'add',
        'edit',
        'check',
        'exportmenu',
        'backup',
        'recover',
    ];

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            $addons = GenHelper::addonList();
            $filter = $this->request->get('filter','');
            $filter = (array)json_decode($filter, true);
            if($filter){
                $addons = array_filter($addons,function($addon) use ($filter){
                    foreach($addon as $key=>$val){
                        if(isset($filter[$key]) && $word = $filter[$key]){
                            return stristr($val,$word) !== false;
                        }
                    }
                    return false;
                });
            }
            foreach ($addons as $k => &$v) {
                $config = get_addon_config($v['name']);
                $v['config'] = $config ? 1 : 0;
                $v['id'] = $v['name'];
                $v['url'] = str_replace($this->request->server('SCRIPT_NAME'), '', $v['url']);
            }
            $rows = array_values($addons);
            return json([
                'rows' => $rows,
                'total' => count($rows)
            ]);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isGet()) {
            return $this->fetch();
        } else {
            $row = $this->request->post("row/a");
            $output = new OutputFacade();
            $addon = new LibraryAddon();
            $addon->name = $row['addon'];
            $addon->action = 'create';
            $addon->setInfo($row);
            try {
                $addon->execute($output);
                return $this->success("创建成功", '');
            } catch (Exception $e) {
                return $this->error($e->getMessage(), '');
            }
        }
    }

    public function edit($ids = null)
    {
        $info = get_addon_info($ids);
        if ($this->request->isGet()) {
            $info['addon'] = $ids;
            $this->assign('row', $info);
            return $this->fetch();
        } else {
            $row = $this->request->post("row/a");
            $addon = new LibraryAddon();
            $addon->name = $ids;
            $row = array_merge($info,$row);
            $addon->setInfo($row);
            try {
                $addon->writeInfo($addon->name);
                return $this->success("更新成功", '');
            } catch (Exception $e) {
                return $this->error($e->getMessage(), '');
            }
        }
    }

    /**
     * 备份
     *
     * @param string $addon
     * @return mixed
     */
    public function backup($addon = null)
    {
        $addon = $this->request->param("addon");
        if ($this->request->isPost()) {
            if ($addon) {
                try {
                    GenHelper::backupGen($addon);
                } catch (Exception $e) {
                    return $this->error($e->getMessage());
                }
                return $this->success("备份成功");
            }
            return $this->error("请求不正确");
        } else {
            $this->assign("addon", $addon);
            return $this->fetch("backup-recover");
        }
    }

    /**
     * 恢复
     *
     * @param string $addon
     * @return mixed
     */
    public function recover($addon = null)
    {
        $addon = $this->request->param("addon");
        if ($this->request->isPost()) {
            if ($addon) {
                try {
                    GenHelper::recoverGen($addon);
                } catch (Exception $e) {
                    return $this->error($e->getMessage());
                }
                return $this->success("恢复成功");
            }
            return $this->error("请求不正确");
        } else {
            $this->assign("addon", $addon);
            return $this->fetch("backup-recover");
        }
    }

    /**
     * 导出菜单
     */
    public function exportmenu()
    {
        $addon = $this->request->get("addon");
        $ids = Menu::getAuthRuleIdsByName($addon);
        if (!$ids) {
            return $this->error("该插件没有菜单可导出", '');
        }
        $menuList = [];
        $menu = AuthRule::getByName($addon);
        if ($menu) {
            $ruleList = collection(AuthRule::where('id', 'in', $ids)->field('id,pid,name,title,icon,ismenu,py,pinyin,remark,weigh')->select())->toArray();
            foreach ($ruleList as &$rule) {
                $rule['py'] = Pinyin::get($rule['title'], true);
                $rule['pinyin'] = Pinyin::get($rule['title']);
            }
            $menuList = Tree::instance()->init($ruleList)->getTreeArray($menu['id']);
            $menu = [
                'name' => $menu->name,
                'title' => $menu->title,
                'icon' => $menu->icon,
                'remark' => $menu->remark,
                'ismenu' => 1,
                'py' => Pinyin::get($menu->title, true),
                'pinyin' => Pinyin::get($menu->title),
            ];
            $menu['childlist'] = $menuList;
            $menu = [$menu];
            $menu = GenHelper::arrayRemoveEmpty($menu);
            return $this->fetch("code", [
                'code' => highlight_string(GenHelper::rebuildArrayForMenu($addon, $menu), true),
                'ext' => 'php'
            ]);
        } else {
            return $this->error("没有生产菜单", '');
        }
    }
}
