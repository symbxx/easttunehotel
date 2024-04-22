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

namespace app\admin\model\addondev;

use think\Model;


class AddondevLog extends Model
{

    

    

    // 表名
    protected $name = 'addondev_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'filetype_text'
    ];
    

    
    public function getFiletypeList()
    {
        return ['php' => __('Filetype php'), 'js' => __('Filetype js'), 'html' => __('Filetype html'), 'other' => __('Filetype other')];
    }


    public function getFiletypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['filetype']) ? $data['filetype'] : '');
        $list = $this->getFiletypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function gen()
    {
        return $this->belongsTo('AddondevGen', 'gen_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}