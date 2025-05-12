<?php

namespace VersionManager\Core\Security;

/**
 * 权限管理类
 * 
 * 负责管理文件权限和用户权限
 */
class PermissionManager
{
    /**
     * 是否启用权限管理
     *
     * @var bool
     */
    private $enabled = true;
    
    /**
     * 是否严格模式
     * 
     * 严格模式下，权限检查失败会抛出异常；非严格模式下，权限检查失败只会发出警告
     *
     * @var bool
     */
    private $strict = true;
    
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmDir;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->pvmDir = getenv('HOME') . '/.pvm';
    }
    
    /**
     * 设置是否启用权限管理
     *
     * @param bool $enabled 是否启用
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }
    
    /**
     * 设置是否严格模式
     *
     * @param bool $strict 是否严格模式
     * @return $this
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;
        return $this;
    }
    
    /**
     * 检查用户权限
     *
     * @return bool 是否有权限
     * @throws \Exception 权限检查失败时抛出异常（严格模式下）
     */
    public function checkUserPermission()
    {
        if (!$this->enabled) {
            return true;
        }
        
        // 检查是否是root用户
        if ($this->isRootUser()) {
            return $this->handlePermissionFailure("不建议使用root用户运行PVM");
        }
        
        // 检查PVM目录权限
        if (!$this->checkPvmDirPermission()) {
            return $this->handlePermissionFailure("PVM目录权限不正确");
        }
        
        return true;
    }
    
    /**
     * 设置安全的文件权限
     *
     * @param string $filePath 文件路径
     * @param int $mode 权限模式
     * @return bool 是否设置成功
     */
    public function setSecureFilePermission($filePath, $mode = 0644)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        return chmod($filePath, $mode);
    }
    
    /**
     * 设置安全的目录权限
     *
     * @param string $dirPath 目录路径
     * @param int $mode 权限模式
     * @return bool 是否设置成功
     */
    public function setSecureDirPermission($dirPath, $mode = 0755)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!is_dir($dirPath)) {
            return false;
        }
        
        return chmod($dirPath, $mode);
    }
    
    /**
     * 设置安全的配置文件权限
     *
     * @param string $filePath 文件路径
     * @return bool 是否设置成功
     */
    public function setSecureConfigFilePermission($filePath)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 配置文件只有用户可读写
        return chmod($filePath, 0600);
    }
    
    /**
     * 设置安全的可执行文件权限
     *
     * @param string $filePath 文件路径
     * @return bool 是否设置成功
     */
    public function setSecureExecutableFilePermission($filePath)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 可执行文件用户可读写执行，组和其他用户可读执行
        return chmod($filePath, 0755);
    }
    
    /**
     * 递归设置目录权限
     *
     * @param string $dirPath 目录路径
     * @param int $dirMode 目录权限模式
     * @param int $fileMode 文件权限模式
     * @param int $execMode 可执行文件权限模式
     * @return bool 是否设置成功
     */
    public function setSecurePermissionsRecursive($dirPath, $dirMode = 0755, $fileMode = 0644, $execMode = 0755)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!is_dir($dirPath)) {
            return false;
        }
        
        $success = chmod($dirPath, $dirMode);
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $success = $success && chmod($item->getPathname(), $dirMode);
            } elseif ($item->isFile()) {
                if ($this->isExecutable($item->getPathname())) {
                    $success = $success && chmod($item->getPathname(), $execMode);
                } else {
                    $success = $success && chmod($item->getPathname(), $fileMode);
                }
            }
        }
        
        return $success;
    }
    
    /**
     * 检查文件权限是否安全
     *
     * @param string $filePath 文件路径
     * @param int $mode 期望的权限模式
     * @return bool 是否安全
     */
    public function isFilePermissionSecure($filePath, $mode = 0644)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $currentMode = fileperms($filePath) & 0777;
        return $currentMode <= $mode;
    }
    
    /**
     * 检查目录权限是否安全
     *
     * @param string $dirPath 目录路径
     * @param int $mode 期望的权限模式
     * @return bool 是否安全
     */
    public function isDirPermissionSecure($dirPath, $mode = 0755)
    {
        if (!$this->enabled) {
            return true;
        }
        
        if (!is_dir($dirPath)) {
            return false;
        }
        
        $currentMode = fileperms($dirPath) & 0777;
        return $currentMode <= $mode;
    }
    
    /**
     * 检查PVM目录权限
     *
     * @return bool 是否安全
     */
    private function checkPvmDirPermission()
    {
        if (!is_dir($this->pvmDir)) {
            return true;
        }
        
        // 检查PVM目录权限
        if (!$this->isDirPermissionSecure($this->pvmDir, 0755)) {
            return false;
        }
        
        // 检查配置目录权限
        $configDir = $this->pvmDir . '/config';
        if (is_dir($configDir) && !$this->isDirPermissionSecure($configDir, 0700)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 检查是否是root用户
     *
     * @return bool
     */
    private function isRootUser()
    {
        return posix_getuid() === 0;
    }
    
    /**
     * 检查文件是否可执行
     *
     * @param string $filePath 文件路径
     * @return bool
     */
    private function isExecutable($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 检查文件扩展名
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (in_array($extension, ['sh', 'bash', 'py', 'pl', 'rb'])) {
            return true;
        }
        
        // 检查文件头
        $content = file_get_contents($filePath, false, null, 0, 100);
        if (strpos($content, '#!/') === 0) {
            return true;
        }
        
        // 检查是否已经有执行权限
        return is_executable($filePath);
    }
    
    /**
     * 处理权限检查失败
     *
     * @param string $message 错误消息
     * @return bool 始终返回false
     * @throws \Exception 权限检查失败时抛出异常（严格模式下）
     */
    private function handlePermissionFailure($message)
    {
        if ($this->strict) {
            throw new \Exception($message);
        } else {
            echo "\033[33m警告: {$message}\033[0m\n";
            return false;
        }
    }
}
