<?php

namespace VersionManager\Console\UI;

/**
 * 进度条管理器类
 * 
 * 提供动态更新的进度条功能
 */
class ProgressBarManager
{
    /**
     * 控制台UI实例
     *
     * @var ConsoleUI
     */
    private $ui;
    
    /**
     * 总进度
     *
     * @var int
     */
    private $total;
    
    /**
     * 当前进度
     *
     * @var int
     */
    private $current = 0;
    
    /**
     * 开始时间
     *
     * @var float
     */
    private $startTime;
    
    /**
     * 上次更新时间
     *
     * @var float
     */
    private $lastUpdateTime;
    
    /**
     * 前缀
     *
     * @var string
     */
    private $prefix = '';
    
    /**
     * 后缀
     *
     * @var string
     */
    private $suffix = '';
    
    /**
     * 是否显示百分比
     *
     * @var bool
     */
    private $showPercent = true;
    
    /**
     * 是否显示剩余时间
     *
     * @var bool
     */
    private $showRemaining = true;
    
    /**
     * 是否显示已用时间
     *
     * @var bool
     */
    private $showElapsed = false;
    
    /**
     * 是否显示内存使用
     *
     * @var bool
     */
    private $showMemory = false;
    
    /**
     * 进度条字符
     *
     * @var string
     */
    private $progressChar = '=';
    
    /**
     * 进度条颜色
     *
     * @var int
     */
    private $progressColor = ConsoleUI::COLOR_GREEN;
    
    /**
     * 最小更新间隔（秒）
     *
     * @var float
     */
    private $minUpdateInterval = 0.1;
    
    /**
     * 构造函数
     *
     * @param ConsoleUI $ui 控制台UI实例
     * @param int $total 总进度
     * @param array $options 选项
     */
    public function __construct(ConsoleUI $ui, $total, array $options = [])
    {
        $this->ui = $ui;
        $this->total = max(1, (int) $total);
        $this->startTime = microtime(true);
        $this->lastUpdateTime = $this->startTime;
        
        // 设置选项
        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
        
        if (isset($options['suffix'])) {
            $this->suffix = $options['suffix'];
        }
        
        if (isset($options['show_percent'])) {
            $this->showPercent = (bool) $options['show_percent'];
        }
        
        if (isset($options['show_remaining'])) {
            $this->showRemaining = (bool) $options['show_remaining'];
        }
        
        if (isset($options['show_elapsed'])) {
            $this->showElapsed = (bool) $options['show_elapsed'];
        }
        
        if (isset($options['show_memory'])) {
            $this->showMemory = (bool) $options['show_memory'];
        }
        
        if (isset($options['progress_char'])) {
            $this->progressChar = $options['progress_char'];
        }
        
        if (isset($options['progress_color'])) {
            $this->progressColor = $options['progress_color'];
        }
        
        if (isset($options['min_update_interval'])) {
            $this->minUpdateInterval = max(0.01, (float) $options['min_update_interval']);
        }
        
        // 显示初始进度条
        $this->update(0);
    }
    
    /**
     * 更新进度
     *
     * @param int $step 步进值
     * @param string|null $suffix 后缀（可选）
     * @return self
     */
    public function advance($step = 1, $suffix = null)
    {
        return $this->update($this->current + $step, $suffix);
    }
    
    /**
     * 更新进度到指定值
     *
     * @param int $current 当前进度
     * @param string|null $suffix 后缀（可选）
     * @return self
     */
    public function update($current, $suffix = null)
    {
        $this->current = min($this->total, max(0, (int) $current));
        
        if ($suffix !== null) {
            $this->suffix = $suffix;
        }
        
        $now = microtime(true);
        
        // 检查是否需要更新显示
        if ($this->current === $this->total || $now - $this->lastUpdateTime >= $this->minUpdateInterval) {
            $this->display();
            $this->lastUpdateTime = $now;
        }
        
        return $this;
    }
    
    /**
     * 完成进度条
     *
     * @param string|null $suffix 后缀（可选）
     */
    public function finish($suffix = null)
    {
        $this->update($this->total, $suffix);
    }
    
    /**
     * 显示进度条
     */
    private function display()
    {
        $percent = $this->total > 0 ? round(($this->current / $this->total) * 100) : 0;
        
        // 构建后缀
        $dynamicSuffix = '';
        
        if ($this->showPercent) {
            $dynamicSuffix .= " {$percent}%";
        }
        
        $elapsed = microtime(true) - $this->startTime;
        
        if ($this->showElapsed) {
            $dynamicSuffix .= ' [已用时: ' . $this->formatTime($elapsed) . ']';
        }
        
        if ($this->showRemaining && $this->current > 0) {
            $rate = $elapsed / $this->current;
            $remaining = $rate * ($this->total - $this->current);
            $dynamicSuffix .= ' [剩余: ' . $this->formatTime($remaining) . ']';
        }
        
        if ($this->showMemory) {
            $memory = memory_get_usage(true);
            $dynamicSuffix .= ' [内存: ' . $this->formatMemory($memory) . ']';
        }
        
        // 构建完整后缀
        $fullSuffix = $dynamicSuffix;
        if (!empty($this->suffix)) {
            $fullSuffix .= ' ' . $this->suffix;
        }
        
        // 计算进度条宽度
        $progressWidth = $this->ui->getProgressBarWidth();
        $progressDone = round($progressWidth * ($percent / 100));
        $progressRemain = $progressWidth - $progressDone;
        
        // 构建进度条
        $bar = $this->prefix;
        $bar .= $this->ui->colorize('[', ConsoleUI::COLOR_WHITE);
        $bar .= $this->ui->colorize(str_repeat($this->progressChar, $progressDone), $this->progressColor);
        $bar .= $this->ui->colorize(str_repeat(' ', $progressRemain), ConsoleUI::COLOR_WHITE);
        $bar .= $this->ui->colorize(']', ConsoleUI::COLOR_WHITE);
        $bar .= $fullSuffix;
        
        echo "\r{$bar}";
        
        // 如果完成，添加换行
        if ($this->current >= $this->total) {
            echo PHP_EOL;
        }
    }
    
    /**
     * 格式化时间
     *
     * @param float $seconds 秒数
     * @return string 格式化后的时间
     */
    private function formatTime($seconds)
    {
        if ($seconds < 60) {
            return sprintf('%.1f秒', $seconds);
        }
        
        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return sprintf('%d分%d秒', $minutes, $seconds);
        }
        
        $hours = floor($seconds / 3600);
        $seconds = $seconds % 3600;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%d时%d分%d秒', $hours, $minutes, $seconds);
    }
    
    /**
     * 格式化内存
     *
     * @param int $bytes 字节数
     * @return string 格式化后的内存
     */
    private function formatMemory($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max(0, (int) $bytes);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
