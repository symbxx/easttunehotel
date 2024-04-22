<?php

namespace addons\addondev\library;

use think\exception\HttpResponseException;
use think\Response;

trait DevEnvTrait
{
    protected function mustDevEnv()
    {
        if (config('app_debug') && config("fastadmin.addon_pure_mode") === false) {
            return true;
        }
        $content = $this->fetch("addondev/addon/check");
        throw new HttpResponseException(Response::create($content, 'html'));
    }
}
