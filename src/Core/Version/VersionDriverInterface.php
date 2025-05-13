<?php

namespace VersionManager\Core\Version;

/**
 * 版本安装驱动接口
 * 
 * 定义版本安装驱动需要实现的方法
 */
interface VersionDriverInterface
{
    /**
     * 获取驱动名称
     *
     * @return string
     */
    public function getName();

    /**
     * 获取驱动标签
     *
     * @return array
     */
    public function getTags(): array;
    
    /**
     * 获取驱动描述
     *
     * @return string
     */
    public function getDescription();
    
    /**
     * 检查是否支持指定的PHP版本
     *
     * @param string $version PHP版本
     * @return bool
     */
    public function isSupported($version);
    
    /**
     * 检查PHP版本是否已安装
     *
     * @param string $version PHP版本
     * @return bool
     */
    public function isInstalled($version);
    
    /**
     * 安装PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 安装选项
     * @return bool
     */
    public function install($version, array $options = []);
    
    /**
     * 删除PHP版本
     *
     * @param string $version PHP版本
     * @param array $options 删除选项
     * @return bool
     */
    public function remove($version, array $options = []);
    
    /**
     * 获取PHP版本信息
     *
     * @param string $version PHP版本
     * @return array
     */
    public function getInfo($version);
    
    /**
     * 获取PHP版本的二进制文件路径
     *
     * @param string $version PHP版本
     * @return string
     */
    public function getBinaryPath($version);
    
    /**
     * 获取PHP版本的配置目录
     *
     * @param string $version PHP版本
     * @return string
     */
    public function getConfigPath($version);
    
    /**
     * 获取PHP版本的扩展目录
     *
     * @param string $version PHP版本
     * @return string
     */
    public function getExtensionPath($version);
}
