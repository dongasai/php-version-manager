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

    /**
     * 安装系统依赖包
     *
     * @param array $packages 要安装的包列表
     * @param array $options 安装选项
     * @return bool 是否安装成功
     * @throws \Exception 安装失败时抛出异常
     */
    public function installPackages(array $packages, array $options = []);

    /**
     * 更新包管理器缓存
     *
     * @param array $options 更新选项
     * @return bool 是否更新成功
     * @throws \Exception 更新失败时抛出异常
     */
    public function updatePackageCache(array $options = []);

    /**
     * 检查包是否已安装
     *
     * @param string $package 包名
     * @return bool 是否已安装
     */
    public function isPackageInstalled($package);

    /**
     * 检查是否有sudo权限
     *
     * @return bool 是否有sudo权限
     */
    public function hasSudoAccess();

    /**
     * 执行需要权限提升的命令
     *
     * @param string $command 要执行的命令
     * @param array $options 执行选项
     * @return array [output, returnCode]
     * @throws \Exception 执行失败时抛出异常
     */
    public function executeWithPrivileges($command, array $options = []);

    /**
     * 获取包管理器名称
     *
     * @return string 包管理器名称（如apt、yum、dnf等）
     */
    public function getPackageManager();
}
