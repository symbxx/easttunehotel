<?php

namespace addons\addondev\library;

use ReflectionClass;
use think\Loader;

/**
 * ClassLoader class
 *  
 * @method static void addPsr0($prefix, $paths, $prepend = false) 添加 PSR-0 命名空间
 * @method static void addPsr4($prefix, $paths, $prepend = false) 添加 PSR-4 命名空间
 */
class ClassLoader
{

    public static function __callStatic($name, $arguments)
    {
        $reflection = new ReflectionClass(Loader::class);
        $staticMethod = $reflection->getMethod($name);
        if ($staticMethod) {
            if (!$staticMethod->isPublic() && $staticMethod->isStatic()) {
                $staticMethod->setAccessible(true);
                array_unshift($arguments,null);
                call_user_func_array([$staticMethod,'invoke'],$arguments);
            }
        }
    }
}
