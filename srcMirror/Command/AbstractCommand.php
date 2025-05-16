<?php

namespace Mirror\Command;

/**
 * 抽象命令类
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $name;

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description;

    /**
     * 构造函数
     *
     * @param string $name 命令名称
     * @param string $description 命令描述
     */
    public function __construct($name, $description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * 获取命令名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取命令描述
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 加载配置
     *
     * @return array
     */
    protected function loadConfig()
    {
        $configFile = ROOT_DIR . '/config/mirror.php';
        
        if (!file_exists($configFile)) {
            echo "错误: 配置文件不存在: $configFile\n";
            exit(1);
        }
        
        return require $configFile;
    }
}
