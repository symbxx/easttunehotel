<?php
// +----------------------------------------------------------------------
// | ADDON DEV  [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2022 http://dungang.site All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: dungang <dungang@126.com>
// +----------------------------------------------------------------------

namespace app\admin\model\addondev;

use think\Model;


class AddondevGen extends Model
{

    

    

    // 表名
    protected $name = 'addondev_gen';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'menu_switch_text',
        'delete_switch_text',
        'import_switch_text',
        'tree_switch_text'
    ];
    

    
    public function getMenuSwitchList()
    {
        return ['0' => __('Menu_switch 0'), '1' => __('Menu_switch 1')];
    }

    public function getDeleteSwitchList()
    {
        return ['0' => __('Delete_switch 0'), '1' => __('Delete_switch 1')];
    }

    public function getImportSwitchList()
    {
        return ['0' => __('Import_switch 0'), '1' => __('Import_switch 1')];
    }

    public function getLocalSwitchList()
    {
        return ['0' => __('Local_switch 0'), '1' => __('Local_switch 1')];
    }

    public function getTreeSwitchList()
    {
        return ['0' => __('Tree_switch 0'), '1' => __('Tree_switch 1')];
    }


    public function getMenuSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['menu_switch']) ? $data['menu_switch'] : '');
        $list = $this->getMenuSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDeleteSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['delete_switch']) ? $data['delete_switch'] : '');
        $list = $this->getDeleteSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getImportSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['import_switch']) ? $data['import_switch'] : '');
        $list = $this->getImportSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTreeSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tree_switch']) ? $data['tree_switch'] : '');
        $list = $this->getTreeSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
