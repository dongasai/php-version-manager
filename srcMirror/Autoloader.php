<?php

/**
 * 自动加载器
 */
class Autoloader
{
    /**
     * 注册自动加载器
     */
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // 将命名空间转换为文件路径
            $file = ROOT_DIR . '/srcMirror/' . str_replace('\\', '/', str_replace('Mirror\\', '', $class)) . '.php';
            
            // 如果文件存在，则包含它
            if (file_exists($file)) {
                require $file;
                return true;
            }
            
            return false;
        });
    }
}
