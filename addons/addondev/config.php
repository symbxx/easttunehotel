<?php

return [
    [
        'name' => 'announcement',
        'title' => '文件声明',
        'type' => 'text',
        'group' => '',
        'visible' => '',
        'content' => [],
        'value' => '// +----------------------------------------------------------------------'."\r\n"
            .'// | {addonName}  [ WE CAN DO IT JUST THINK ]'."\r\n"
            .'// +----------------------------------------------------------------------'."\r\n"
            .'// | Copyright (c) {year} {website} All rights reserved.'."\r\n"
            .'// +----------------------------------------------------------------------'."\r\n"
            .'// | Author: {author}'."\r\n"
            .'// +----------------------------------------------------------------------',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => 'rows="10"',
    ],
    [
        'name'    => '__tips__',
        'title'   => '温馨提示',
        'type'    => 'string',
        'content' => [],
        'value'   => '生成文件头部声明，可用变量{addonName},{year},{date},{datetime}和info.ini的参数',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => '',
    ],
];
