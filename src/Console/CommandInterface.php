<?php

namespace VersionManager\Console;

interface CommandInterface
{
    /**
     * 执行命令
     *
     * @param array $args 命令参数
     * @return int 返回状态码
     */
    public function execute(array $args);
    
    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription();
    
    /**
     * 获取命令用法
     *
     * @return string
     */
    public function getUsage();
}
