<?php

namespace VersionManager\Core;

/**
 * PHP扩展信息类
 *
 * 用于存储扩展的信息
 */
class ExtensionInfo
{
    /**
     * 扩展名称
     *
     * @var string
     */
    private $name;

    /**
     * 扩展版本
     *
     * @var string
     */
    private $version;

    /**
     * 扩展类型
     *
     * @var string
     */
    private $type;

    /**
     * 扩展状态
     *
     * @var string
     */
    private $status;

    /**
     * 扩展依赖
     *
     * @var array
     */
    private $dependencies;

    /**
     * 扩展配置
     *
     * @var array
     */
    private $config;

    /**
     * 构造函数
     *
     * @param string $name 扩展名称
     * @param string $version 扩展版本
     * @param string $type 扩展类型
     * @param string $status 扩展状态
     * @param array $dependencies 扩展依赖
     * @param array $config 扩展配置
     */
    public function __construct($name, $version = '', $type = '', $status = '', array $dependencies = [], array $config = [])
    {
        $this->name = $name;
        $this->version = $version;
        $this->type = $type;
        $this->status = $status;
        $this->dependencies = $dependencies;
        $this->config = $config;
    }

    /**
     * 获取扩展名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取扩展版本
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 获取扩展类型
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 获取扩展状态
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 获取扩展依赖
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * 获取扩展配置
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 设置扩展版本
     *
     * @param string $version 扩展版本
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * 设置扩展类型
     *
     * @param string $type 扩展类型
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 设置扩展状态
     *
     * @param string $status 扩展状态
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * 设置扩展依赖
     *
     * @param array $dependencies 扩展依赖
     * @return $this
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * 设置扩展配置
     *
     * @param array $config 扩展配置
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 添加扩展配置
     *
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @return $this
     */
    public function addConfig($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * 添加扩展依赖
     *
     * @param string $dependency 依赖名称
     * @return $this
     */
    public function addDependency($dependency)
    {
        if (!in_array($dependency, $this->dependencies)) {
            $this->dependencies[] = $dependency;
        }
        return $this;
    }

    /**
     * 检查扩展是否启用
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->status === 'enabled';
    }

    /**
     * 检查扩展是否禁用
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->status === 'disabled';
    }

    /**
     * 检查扩展是否是内置扩展
     *
     * @return bool
     */
    public function isBuiltin()
    {
        return $this->type === 'builtin';
    }

    /**
     * 检查扩展是否是动态扩展
     *
     * @return bool
     */
    public function isDynamic()
    {
        return $this->type === 'dynamic';
    }

    /**
     * 检查扩展是否是Zend扩展
     *
     * @return bool
     */
    public function isZend()
    {
        return $this->type === 'zend';
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'type' => $this->type,
            'status' => $this->status,
            'dependencies' => $this->dependencies,
            'config' => $this->config,
        ];
    }
}
