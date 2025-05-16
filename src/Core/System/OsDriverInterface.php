<?php

namespace VersionManager\Core\System;

use VersionManager\Core\Tags\TaggableInterface;

/**
 * 操作系统驱动接口
 *
 * 定义操作系统驱动需要实现的方法
 */
interface OsDriverInterface extends TaggableInterface
{
    /**
     * 获取操作系统名称
     *
     * @return string
     */
    public function getName();

    /**
     * 获取操作系统描述
     *
     * @return string
     */
    public function getDescription();

    /**
     * 获取操作系统版本
     *
     * @return string
     */
    public function getVersion();

    /**
     * 获取操作系统架构
     *
     * @return string
     */
    public function getArch();

    /**
     * 获取操作系统信息
     *
     * @return array 操作系统信息
     */
    public function getInfo();
}
