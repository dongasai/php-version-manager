<?php

namespace VersionManager\Core\System;

use VersionManager\Core\Config\FpmConfig;

/**
 * 服务管理类
 * 
 * 负责管理PHP-FPM服务和Web服务器集成
 */
class ServiceManager
{
    /**
     * PHP版本
     *
     * @var string
     */
    private $phpVersion;
    
    /**
     * PVM根目录
     *
     * @var string
     */
    private $pvmDir;
    
    /**
     * 版本目录
     *
     * @var string
     */
    private $versionDir;
    
    /**
     * FPM配置管理器
     *
     * @var FpmConfig
     */
    private $fpmConfig;
    
    /**
     * 构造函数
     *
     * @param string $phpVersion PHP版本
     */
    public function __construct($phpVersion = null)
    {
        if ($phpVersion === null) {
            // 获取当前PHP版本
            $switcher = new \VersionManager\Core\VersionSwitcher();
            $phpVersion = $switcher->getCurrentVersion();
        }
        
        $this->phpVersion = $phpVersion;
        $this->pvmDir = getenv('HOME') . '/.pvm';
        $this->versionDir = $this->pvmDir . '/versions/' . $phpVersion;
        $this->fpmConfig = new FpmConfig($phpVersion);
    }
    
    /**
     * 启动PHP-FPM服务
     *
     * @return bool 是否启动成功
     */
    public function startFpm()
    {
        // 检查PHP-FPM是否已经运行
        if ($this->isFpmRunning()) {
            echo "PHP-FPM已经在运行\n";
            return true;
        }
        
        // 获取PHP-FPM二进制文件路径
        $fpmBin = $this->versionDir . '/sbin/php-fpm';
        
        // 检查PHP-FPM二进制文件是否存在
        if (!file_exists($fpmBin)) {
            echo "错误: PHP-FPM二进制文件不存在\n";
            return false;
        }
        
        // 获取PHP-FPM配置文件路径
        $fpmConf = $this->fpmConfig->getFpmConfPath();
        
        // 启动PHP-FPM
        $command = "{$fpmBin} -c {$this->versionDir}/etc -y {$fpmConf}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法启动PHP-FPM: " . implode("\n", $output) . "\n";
            return false;
        }
        
        echo "PHP-FPM已启动\n";
        return true;
    }
    
    /**
     * 停止PHP-FPM服务
     *
     * @return bool 是否停止成功
     */
    public function stopFpm()
    {
        // 检查PHP-FPM是否正在运行
        if (!$this->isFpmRunning()) {
            echo "PHP-FPM未运行\n";
            return true;
        }
        
        // 获取PHP-FPM PID文件路径
        $pidFile = $this->getPidFile();
        
        // 检查PID文件是否存在
        if (!file_exists($pidFile)) {
            echo "错误: PHP-FPM PID文件不存在\n";
            return false;
        }
        
        // 读取PID
        $pid = trim(file_get_contents($pidFile));
        
        // 停止PHP-FPM
        $command = "kill {$pid}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法停止PHP-FPM: " . implode("\n", $output) . "\n";
            return false;
        }
        
        // 删除PID文件
        if (file_exists($pidFile)) {
            unlink($pidFile);
        }
        
        echo "PHP-FPM已停止\n";
        return true;
    }
    
    /**
     * 重启PHP-FPM服务
     *
     * @return bool 是否重启成功
     */
    public function restartFpm()
    {
        $this->stopFpm();
        return $this->startFpm();
    }
    
    /**
     * 检查PHP-FPM服务是否正在运行
     *
     * @return bool 是否正在运行
     */
    public function isFpmRunning()
    {
        // 获取PHP-FPM PID文件路径
        $pidFile = $this->getPidFile();
        
        // 检查PID文件是否存在
        if (!file_exists($pidFile)) {
            return false;
        }
        
        // 读取PID
        $pid = trim(file_get_contents($pidFile));
        
        // 检查进程是否存在
        $command = "ps -p {$pid} | grep php-fpm";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        return $returnCode === 0;
    }
    
    /**
     * 获取PHP-FPM状态
     *
     * @return array PHP-FPM状态信息
     */
    public function getFpmStatus()
    {
        // 检查PHP-FPM是否正在运行
        if (!$this->isFpmRunning()) {
            return [
                'running' => false,
                'pid' => null,
                'uptime' => null,
                'processes' => [],
            ];
        }
        
        // 获取PHP-FPM PID文件路径
        $pidFile = $this->getPidFile();
        
        // 读取PID
        $pid = trim(file_get_contents($pidFile));
        
        // 获取进程信息
        $command = "ps -p {$pid} -o pid,ppid,user,%cpu,%mem,vsz,rss,stat,start,time,command";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析进程信息
        $processInfo = [];
        if (count($output) > 1) {
            $headers = preg_split('/\s+/', trim($output[0]));
            $values = preg_split('/\s+/', trim($output[1]), count($headers));
            
            for ($i = 0; $i < count($headers); $i++) {
                $processInfo[$headers[$i]] = isset($values[$i]) ? $values[$i] : '';
            }
        }
        
        // 获取子进程信息
        $command = "ps -o pid,ppid,user,%cpu,%mem,vsz,rss,stat,start,time,command --ppid {$pid}";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // 解析子进程信息
        $childProcesses = [];
        if (count($output) > 1) {
            $headers = preg_split('/\s+/', trim($output[0]));
            
            for ($i = 1; $i < count($output); $i++) {
                $values = preg_split('/\s+/', trim($output[$i]), count($headers));
                $childProcess = [];
                
                for ($j = 0; $j < count($headers); $j++) {
                    $childProcess[$headers[$j]] = isset($values[$j]) ? $values[$j] : '';
                }
                
                $childProcesses[] = $childProcess;
            }
        }
        
        // 获取运行时间
        $uptime = '';
        if (isset($processInfo['START'])) {
            $startTime = strtotime($processInfo['START']);
            $uptime = time() - $startTime;
        }
        
        return [
            'running' => true,
            'pid' => $pid,
            'uptime' => $uptime,
            'process' => $processInfo,
            'children' => $childProcesses,
        ];
    }
    
    /**
     * 生成Nginx配置
     *
     * @param string $serverName 服务器名称
     * @param string $documentRoot 文档根目录
     * @param int $port 端口号
     * @return string Nginx配置
     */
    public function generateNginxConfig($serverName, $documentRoot, $port = 80)
    {
        // 获取PHP-FPM监听地址
        $listen = $this->fpmConfig->getFpmValue('www', 'listen');
        
        // 生成Nginx配置
        $config = <<<EOT
server {
    listen {$port};
    server_name {$serverName};
    root {$documentRoot};
    
    index index.php index.html index.htm;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass {$listen};
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOT;
        
        return $config;
    }
    
    /**
     * 安装Nginx配置
     *
     * @param string $serverName 服务器名称
     * @param string $documentRoot 文档根目录
     * @param int $port 端口号
     * @return bool 是否安装成功
     */
    public function installNginxConfig($serverName, $documentRoot, $port = 80)
    {
        // 生成Nginx配置
        $config = $this->generateNginxConfig($serverName, $documentRoot, $port);
        
        // 保存配置文件
        $configFile = "/etc/nginx/sites-available/{$serverName}.conf";
        
        // 检查是否有权限写入
        if (!is_writable(dirname($configFile))) {
            echo "错误: 没有权限写入Nginx配置目录\n";
            return false;
        }
        
        // 写入配置文件
        if (file_put_contents($configFile, $config) === false) {
            echo "错误: 无法写入Nginx配置文件\n";
            return false;
        }
        
        // 创建符号链接
        $enabledFile = "/etc/nginx/sites-enabled/{$serverName}.conf";
        if (!file_exists($enabledFile)) {
            if (symlink($configFile, $enabledFile) === false) {
                echo "错误: 无法创建Nginx配置符号链接\n";
                return false;
            }
        }
        
        // 重新加载Nginx配置
        $command = "sudo nginx -t && sudo systemctl reload nginx";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法重新加载Nginx配置: " . implode("\n", $output) . "\n";
            return false;
        }
        
        echo "Nginx配置已安装\n";
        return true;
    }
    
    /**
     * 卸载Nginx配置
     *
     * @param string $serverName 服务器名称
     * @return bool 是否卸载成功
     */
    public function uninstallNginxConfig($serverName)
    {
        // 删除符号链接
        $enabledFile = "/etc/nginx/sites-enabled/{$serverName}.conf";
        if (file_exists($enabledFile)) {
            if (unlink($enabledFile) === false) {
                echo "错误: 无法删除Nginx配置符号链接\n";
                return false;
            }
        }
        
        // 删除配置文件
        $configFile = "/etc/nginx/sites-available/{$serverName}.conf";
        if (file_exists($configFile)) {
            if (unlink($configFile) === false) {
                echo "错误: 无法删除Nginx配置文件\n";
                return false;
            }
        }
        
        // 重新加载Nginx配置
        $command = "sudo nginx -t && sudo systemctl reload nginx";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法重新加载Nginx配置: " . implode("\n", $output) . "\n";
            return false;
        }
        
        echo "Nginx配置已卸载\n";
        return true;
    }
    
    /**
     * 生成Apache配置
     *
     * @param string $serverName 服务器名称
     * @param string $documentRoot 文档根目录
     * @param int $port 端口号
     * @return string Apache配置
     */
    public function generateApacheConfig($serverName, $documentRoot, $port = 80)
    {
        // 获取PHP-FPM监听地址
        $listen = $this->fpmConfig->getFpmValue('www', 'listen');
        
        // 检查监听地址是否是Unix套接字
        $isSocket = strpos($listen, '/') === 0;
        
        // 生成Apache配置
        $config = <<<EOT
<VirtualHost *:{$port}>
    ServerName {$serverName}
    DocumentRoot {$documentRoot}
    
    <Directory {$documentRoot}>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://{$listen}"
    </FilesMatch>
    
    ErrorLog \${APACHE_LOG_DIR}/{$serverName}_error.log
    CustomLog \${APACHE_LOG_DIR}/{$serverName}_access.log combined
</VirtualHost>
EOT;
        
        return $config;
    }
    
    /**
     * 安装Apache配置
     *
     * @param string $serverName 服务器名称
     * @param string $documentRoot 文档根目录
     * @param int $port 端口号
     * @return bool 是否安装成功
     */
    public function installApacheConfig($serverName, $documentRoot, $port = 80)
    {
        // 生成Apache配置
        $config = $this->generateApacheConfig($serverName, $documentRoot, $port);
        
        // 保存配置文件
        $configFile = "/etc/apache2/sites-available/{$serverName}.conf";
        
        // 检查是否有权限写入
        if (!is_writable(dirname($configFile))) {
            echo "错误: 没有权限写入Apache配置目录\n";
            return false;
        }
        
        // 写入配置文件
        if (file_put_contents($configFile, $config) === false) {
            echo "错误: 无法写入Apache配置文件\n";
            return false;
        }
        
        // 启用站点
        $command = "sudo a2ensite {$serverName}.conf";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法启用Apache站点: " . implode("\n", $output) . "\n";
            return false;
        }
        
        // 启用必要的模块
        $command = "sudo a2enmod proxy_fcgi";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法启用Apache模块: " . implode("\n", $output) . "\n";
            return false;
        }
        
        // 重新加载Apache配置
        $command = "sudo apache2ctl configtest && sudo systemctl reload apache2";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法重新加载Apache配置: " . implode("\n", $output) . "\n";
            return false;
        }
        
        echo "Apache配置已安装\n";
        return true;
    }
    
    /**
     * 卸载Apache配置
     *
     * @param string $serverName 服务器名称
     * @return bool 是否卸载成功
     */
    public function uninstallApacheConfig($serverName)
    {
        // 禁用站点
        $command = "sudo a2dissite {$serverName}.conf";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法禁用Apache站点: " . implode("\n", $output) . "\n";
            return false;
        }
        
        // 删除配置文件
        $configFile = "/etc/apache2/sites-available/{$serverName}.conf";
        if (file_exists($configFile)) {
            if (unlink($configFile) === false) {
                echo "错误: 无法删除Apache配置文件\n";
                return false;
            }
        }
        
        // 重新加载Apache配置
        $command = "sudo apache2ctl configtest && sudo systemctl reload apache2";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "错误: 无法重新加载Apache配置: " . implode("\n", $output) . "\n";
            return false;
        }
        
        echo "Apache配置已卸载\n";
        return true;
    }
    
    /**
     * 获取PID文件路径
     *
     * @return string PID文件路径
     */
    private function getPidFile()
    {
        // 从配置文件中获取PID文件路径
        $pidPath = $this->fpmConfig->getFpmValue('global', 'pid', 'fpm');
        
        // 如果配置文件中没有指定PID文件路径，则使用默认路径
        if (empty($pidPath)) {
            $pidPath = "/tmp/php-fpm-{$this->phpVersion}.pid";
        }
        
        return $pidPath;
    }
}
