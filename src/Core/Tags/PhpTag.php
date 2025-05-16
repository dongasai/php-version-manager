<?php

namespace VersionManager\Core\Tags;

/**
 * PHP版本标签类
 *
 * 定义所有PHP版本相关的标签常量
 */
class PhpTag
{
    // PHP主要版本
    const PHP5 = 'php5';
    const PHP7 = 'php7';
    const PHP8 = 'php8';

    // PHP次要版本
    const PHP54 = 'php54';
    const PHP55 = 'php55';
    const PHP56 = 'php56';
    const PHP70 = 'php70';
    const PHP71 = 'php71';
    const PHP72 = 'php72';
    const PHP73 = 'php73';
    const PHP74 = 'php74';
    const PHP80 = 'php80';
    const PHP81 = 'php81';
    const PHP82 = 'php82';
    const PHP83 = 'php83';

    /**
     * 获取所有标签
     *
     * @return array
     */
    public static function getAllTags()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        return array_values($reflection->getConstants());
    }

    /**
     * 根据PHP版本获取标签
     *
     * @param string $version PHP版本（如7.4.0）
     * @return array 标签数组
     */
    public static function getTagsFromVersion($version)
    {
        $tags = [];

        // 提取主要和次要版本号
        if (preg_match('/^(\d+)\.(\d+)/', $version, $matches)) {
            $major = (int)$matches[1];
            $minor = (int)$matches[2];

            // 添加主要版本标签
            $tags[] = "php{$major}";

            // 添加次要版本标签
            $tags[] = "php{$major}{$minor}";

            // 添加特定版本常量
            $versionKey = "PHP{$major}{$minor}";
            if (defined("self::{$versionKey}")) {
                $tags[] = constant("self::{$versionKey}");
            }
        }

        return $tags;
    }
}