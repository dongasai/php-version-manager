<?php

namespace VersionManager\Core\Tags;

/**
 * 可标签化接口
 * 
 * 所有支持标签匹配的驱动类都应该实现此接口
 */
interface TaggableInterface
{
    /**
     * 获取驱动的标签
     * 
     * @return array 标签数组
     */
    public function getTags(): array;
}
