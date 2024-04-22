<?php

namespace addons\addondev\command;

use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\console\Command;
use addons\addondev\library\Addon;
use addons\addondev\library\OutputFacade;

class Addondev extends Command
{

    protected function configure()
    {
        $this->setName('addon-dev')
            ->setDescription('Addon dev manager')
            ->addOption('name', 'a', Option::VALUE_REQUIRED, 'addon name', null)
            ->addOption('action', 'c', Option::VALUE_REQUIRED, 'action(create/enable/disable/install/uninstall/upgrade/refresh/package/move/watch)', 'create')
            ->addOption('force', 'f', Option::VALUE_OPTIONAL, 'force override', null)
            ->addOption('release', 'r', Option::VALUE_OPTIONAL, 'addon release version', null)
            ->addOption('uid', 'u', Option::VALUE_OPTIONAL, 'fastadmin uid', null)
            ->addOption('token', 't', Option::VALUE_OPTIONAL, 'fastadmin token', null)
            ->addOption('domain', 'd', Option::VALUE_OPTIONAL, 'domain', null)
            ->addOption('local', 'l', Option::VALUE_OPTIONAL, 'local package', null)
            ->addOption('extended', 'e', Option::VALUE_OPTIONAL, 'package branch (regular,extended). 0 is default regular package, 1 is extends page', 0)
            ->setDescription('Addon manager');
    }

    protected function execute(Input $input, Output $output)
    {
        $addon = new Addon();
        $addon->name = $input->getOption('name') ?: '';
        $addon->action = $input->getOption('action') ?: '';
        // 强制覆盖
        $addon->force = $input->getOption('force');
        // 版本
        $addon->release = $input->getOption('release') ?: '';
        // uid
        $addon->uid = $input->getOption('uid') ?: '';
        // token
        $addon->token = $input->getOption('token') ?: '';
        // 发行高级版分支
        $addon->extended = $input->getOption('extended') ?: 0;

        return $addon->execute(new OutputFacade($output));
    }
}
