<?php

namespace VersionManager\Console\UI;

/**
 * 命令行界面工具类
 * 
 * 提供彩色输出、进度条和交互式菜单等功能
 */
class ConsoleUI
{
    // 颜色常量
    const COLOR_BLACK = 0;
    const COLOR_RED = 1;
    const COLOR_GREEN = 2;
    const COLOR_YELLOW = 3;
    const COLOR_BLUE = 4;
    const COLOR_MAGENTA = 5;
    const COLOR_CYAN = 6;
    const COLOR_WHITE = 7;
    
    // 样式常量
    const STYLE_NORMAL = 0;
    const STYLE_BOLD = 1;
    const STYLE_UNDERLINE = 4;
    
    /**
     * 是否启用彩色输出
     *
     * @var bool
     */
    private $colorEnabled = true;
    
    /**
     * 进度条宽度
     *
     * @var int
     */
    private $progressBarWidth = 50;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 检测是否支持彩色输出
        $this->colorEnabled = $this->supportsColor();
    }
    
    /**
     * 检测终端是否支持彩色输出
     *
     * @return bool
     */
    private function supportsColor()
    {
        // Windows 10 以上版本支持ANSI颜色
        if (DIRECTORY_SEPARATOR === '\\') {
            return getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON' || 
                   getenv('TERM') === 'xterm' || getenv('TERM') === 'xterm-256color';
        }
        
        // 检查是否是TTY终端
        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }
    
    /**
     * 启用彩色输出
     */
    public function enableColor()
    {
        $this->colorEnabled = true;
    }
    
    /**
     * 禁用彩色输出
     */
    public function disableColor()
    {
        $this->colorEnabled = false;
    }
    
    /**
     * 设置进度条宽度
     *
     * @param int $width 宽度
     */
    public function setProgressBarWidth($width)
    {
        $this->progressBarWidth = max(10, (int) $width);
    }
    
    /**
     * 输出彩色文本
     *
     * @param string $text 文本内容
     * @param int $foreground 前景色
     * @param int $background 背景色
     * @param int $style 样式
     * @return string
     */
    public function colorize($text, $foreground = null, $background = null, $style = null)
    {
        if (!$this->colorEnabled) {
            return $text;
        }
        
        $colored = '';
        
        // 添加样式
        if ($style !== null) {
            $colored .= "\033[{$style}m";
        }
        
        // 添加前景色
        if ($foreground !== null) {
            $colored .= "\033[" . (30 + $foreground) . "m";
        }
        
        // 添加背景色
        if ($background !== null) {
            $colored .= "\033[" . (40 + $background) . "m";
        }
        
        // 添加文本和重置
        $colored .= $text . "\033[0m";
        
        return $colored;
    }
    
    /**
     * 输出信息
     *
     * @param string $message 消息内容
     * @param bool $newLine 是否换行
     */
    public function info($message, $newLine = true)
    {
        echo $this->colorize($message, self::COLOR_BLUE);
        if ($newLine) {
            echo PHP_EOL;
        }
    }
    
    /**
     * 输出成功信息
     *
     * @param string $message 消息内容
     * @param bool $newLine 是否换行
     */
    public function success($message, $newLine = true)
    {
        echo $this->colorize($message, self::COLOR_GREEN);
        if ($newLine) {
            echo PHP_EOL;
        }
    }
    
    /**
     * 输出警告信息
     *
     * @param string $message 消息内容
     * @param bool $newLine 是否换行
     */
    public function warning($message, $newLine = true)
    {
        echo $this->colorize($message, self::COLOR_YELLOW);
        if ($newLine) {
            echo PHP_EOL;
        }
    }
    
    /**
     * 输出错误信息
     *
     * @param string $message 消息内容
     * @param bool $newLine 是否换行
     */
    public function error($message, $newLine = true)
    {
        echo $this->colorize($message, self::COLOR_RED);
        if ($newLine) {
            echo PHP_EOL;
        }
    }
    
    /**
     * 显示进度条
     *
     * @param int $current 当前进度
     * @param int $total 总进度
     * @param string $prefix 前缀
     * @param string $suffix 后缀
     */
    public function progressBar($current, $total, $prefix = '', $suffix = '')
    {
        $percent = $total > 0 ? round(($current / $total) * 100) : 0;
        $progressDone = round($this->progressBarWidth * ($percent / 100));
        $progressRemain = $this->progressBarWidth - $progressDone;
        
        $bar = $prefix;
        $bar .= $this->colorize('[', self::COLOR_WHITE);
        $bar .= $this->colorize(str_repeat('=', $progressDone), self::COLOR_GREEN);
        $bar .= $this->colorize(str_repeat(' ', $progressRemain), self::COLOR_WHITE);
        $bar .= $this->colorize(']', self::COLOR_WHITE);
        $bar .= " {$percent}%";
        
        if (!empty($suffix)) {
            $bar .= " {$suffix}";
        }
        
        echo "\r{$bar}";
        
        if ($current >= $total) {
            echo PHP_EOL;
        }
    }
    
    /**
     * 显示旋转指示器
     *
     * @param int $iteration 当前迭代次数
     * @param string $message 消息
     */
    public function spinner($iteration, $message = '')
    {
        $spinners = ['|', '/', '-', '\\'];
        $spinner = $spinners[$iteration % count($spinners)];
        
        echo "\r{$spinner} {$message}";
    }
    
    /**
     * 显示交互式菜单
     *
     * @param array $options 选项数组
     * @param string $prompt 提示信息
     * @param mixed $default 默认选项
     * @return mixed 选择的选项
     */
    public function menu(array $options, $prompt = '请选择一个选项:', $default = null)
    {
        echo $prompt . PHP_EOL;
        
        $i = 1;
        $indexedOptions = [];
        
        foreach ($options as $key => $option) {
            $indexedOptions[$i] = [
                'key' => $key,
                'value' => $option
            ];
            
            $defaultMark = ($default !== null && $key === $default) ? ' (默认)' : '';
            echo $this->colorize(" {$i})", self::COLOR_YELLOW) . " {$option}{$defaultMark}" . PHP_EOL;
            
            $i++;
        }
        
        $selectedIndex = $this->prompt('> ', function($input) use ($indexedOptions, $default) {
            if (empty($input) && $default !== null) {
                foreach ($indexedOptions as $idx => $option) {
                    if ($option['key'] === $default) {
                        return $idx;
                    }
                }
            }
            
            if (!is_numeric($input) || !isset($indexedOptions[(int) $input])) {
                return false;
            }
            
            return (int) $input;
        });
        
        return $indexedOptions[$selectedIndex]['key'];
    }
    
    /**
     * 提示用户输入
     *
     * @param string $prompt 提示信息
     * @param callable|null $validator 验证函数
     * @param mixed $default 默认值
     * @return string 用户输入
     */
    public function prompt($prompt, callable $validator = null, $default = null)
    {
        while (true) {
            echo $prompt;
            $input = trim(fgets(STDIN));
            
            if (empty($input) && $default !== null) {
                return $default;
            }
            
            if ($validator === null || $validator($input) !== false) {
                return $input;
            }
            
            $this->error('无效的输入，请重试。');
        }
    }
    
    /**
     * 提示用户确认
     *
     * @param string $question 问题
     * @param bool $default 默认值
     * @return bool 用户确认结果
     */
    public function confirm($question, $default = true)
    {
        $defaultText = $default ? 'Y/n' : 'y/N';
        $input = $this->prompt("{$question} [{$defaultText}] ", null, $default ? 'y' : 'n');
        
        return strtolower($input) === 'y';
    }
    
    /**
     * 清除当前行
     */
    public function clearLine()
    {
        echo "\r" . str_repeat(' ', 80) . "\r";
    }
    
    /**
     * 清除屏幕
     */
    public function clearScreen()
    {
        echo "\033[2J\033[H";
    }
}
