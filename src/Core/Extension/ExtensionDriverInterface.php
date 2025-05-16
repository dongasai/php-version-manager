<?php

namespace VersionManager\Core\Extension;

/**
 * 扩展驱动接口
 *
 * 定义扩展驱动需要实现的方法
 */
interface ExtensionDriverInterface
{
    /**
     * 获取扩展名称
     *
     * @return string
     */
    public function getName();

    /**
     * 获取扩展描述
     *
     * @return string
     */
    public function getDescription();

    /**
     * 获取扩展标签
     *
     * @return array
     */
    public function getTags(): array;

    /**
     * 获取扩展版本
     *
     * @return string
     */
    public function getVersion();

    /**
     * 获取扩展类型
     *
     * @return string
     */
    public function getType();

    /**
     * 获取扩展依赖
     *
     * @return array
     */
    public function getDependencies();

    /**
     * 获取扩展默认配置
     *
     * @return array
     */
    public function getDefaultConfig();

    /**
     * 检查扩展是否已安装
     *
     * @param string $phpVersion PHP版本
     * @return bool
     */
    public function isInstalled($phpVersion);

    /**
     * 检查扩展是否可用
     *
     * @param string $phpVersion PHP版本
     * @return bool
     */
    public function isAvailable($phpVersion);

    /**
     * 安装扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $options 安装选项
     * @return bool
     */
    public function install($phpVersion, array $options = []);

    /**
     * 删除扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $options 删除选项
     * @return bool
     */
    public function remove($phpVersion, array $options = []);

    /**
     * 启用扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $config 扩展配置
     * @return bool
     */
    public function enable($phpVersion, array $config = []);

    /**
     * 禁用扩展
     *
     * @param string $phpVersion PHP版本
     * @return bool
     */
    public function disable($phpVersion);

    /**
     * 配置扩展
     *
     * @param string $phpVersion PHP版本
     * @param array $config 扩展配置
     * @return bool
     */
    public function configure($phpVersion, array $config);

    /**
     * 获取扩展信息
     *
     * @param string $phpVersion PHP版本
     * @return array
     */
    public function getInfo($phpVersion);

    /**
     * 检查是否是Zend扩展
     *
     * @return bool
     */
    public function isZend();
}
