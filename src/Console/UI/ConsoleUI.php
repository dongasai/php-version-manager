<?php

namespace VersionManager\Console\UI;

/**
 * 命令行界面工具类
 *
 * 提供彩色输出、进度条、交互式菜单、表格显示等功能
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
    const STYLE_BLINK = 5;
    const STYLE_REVERSE = 7;

    // 边框样式常量
    const BORDER_STYLE_NONE = 0;
    const BORDER_STYLE_SINGLE = 1;
    const BORDER_STYLE_DOUBLE = 2;
    const BORDER_STYLE_ROUNDED = 3;

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
     * 终端宽度
     *
     * @var int
     */
    private $terminalWidth = 80;

    /**
     * 终端高度
     *
     * @var int
     */
    private $terminalHeight = 24;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 检测是否支持彩色输出
        $this->colorEnabled = $this->supportsColor();

        // 获取终端尺寸（仅在真正的TTY环境中）
        if (function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
            $this->detectTerminalSize();
        }
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
     * @param int|string $foreground 前景色（可以是颜色常量或颜色名称字符串）
     * @param int|string $background 背景色（可以是颜色常量或颜色名称字符串）
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
            $foregroundCode = $this->getColorCode($foreground);
            if ($foregroundCode !== null) {
                $colored .= "\033[" . (30 + $foregroundCode) . "m";
            }
        }

        // 添加背景色
        if ($background !== null) {
            $backgroundCode = $this->getColorCode($background);
            if ($backgroundCode !== null) {
                $colored .= "\033[" . (40 + $backgroundCode) . "m";
            }
        }

        // 添加文本和重置
        $colored .= $text . "\033[0m";

        return $colored;
    }

    /**
     * 获取颜色代码
     *
     * @param int|string $color 颜色（可以是颜色常量或颜色名称字符串）
     * @return int|null 颜色代码
     */
    private function getColorCode($color)
    {
        // 如果已经是数字，直接返回
        if (is_int($color)) {
            return $color;
        }

        // 如果是字符串，转换为对应的颜色常量
        if (is_string($color)) {
            $colorMap = [
                'black' => self::COLOR_BLACK,
                'red' => self::COLOR_RED,
                'green' => self::COLOR_GREEN,
                'yellow' => self::COLOR_YELLOW,
                'blue' => self::COLOR_BLUE,
                'magenta' => self::COLOR_MAGENTA,
                'cyan' => self::COLOR_CYAN,
                'white' => self::COLOR_WHITE,
            ];

            $color = strtolower($color);
            if (isset($colorMap[$color])) {
                return $colorMap[$color];
            }
        }

        return null;
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
     * 创建进度条管理器
     *
     * @param int $total 总进度
     * @param array $options 选项
     * @return ProgressBarManager 进度条管理器
     */
    public function createProgressBar($total, array $options = [])
    {
        return new ProgressBarManager($this, $total, $options);
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
     * 显示多选菜单
     *
     * @param array $options 选项数组
     * @param string $prompt 提示信息
     * @param array $defaults 默认选中的选项
     * @return array 选择的选项
     */
    public function multiMenu(array $options, $prompt = '请选择选项 (用逗号分隔多个选项):', array $defaults = [])
    {
        echo $prompt . PHP_EOL;

        $i = 1;
        $indexedOptions = [];

        foreach ($options as $key => $option) {
            $indexedOptions[$i] = [
                'key' => $key,
                'value' => $option
            ];

            $defaultMark = in_array($key, $defaults) ? ' [✓]' : ' [ ]';
            echo $this->colorize(" {$i})", self::COLOR_YELLOW) . "{$defaultMark} {$option}" . PHP_EOL;

            $i++;
        }

        echo $this->colorize(" a)", self::COLOR_YELLOW) . " [全选]" . PHP_EOL;
        echo $this->colorize(" n)", self::COLOR_YELLOW) . " [全不选]" . PHP_EOL;
        echo $this->colorize(" i)", self::COLOR_YELLOW) . " [反选]" . PHP_EOL;

        $selectedIndices = $this->prompt('> ', function($input) use ($indexedOptions) {
            if (strtolower($input) === 'a') {
                return array_keys($indexedOptions);
            }

            if (strtolower($input) === 'n') {
                return [];
            }

            if (strtolower($input) === 'i') {
                return 'invert';
            }

            $indices = array_map('trim', explode(',', $input));
            $validIndices = [];

            foreach ($indices as $index) {
                if (is_numeric($index) && isset($indexedOptions[(int) $index])) {
                    $validIndices[] = (int) $index;
                }
            }

            return empty($validIndices) ? false : $validIndices;
        });

        // 处理反选
        if ($selectedIndices === 'invert') {
            $defaultIndices = [];
            foreach ($indexedOptions as $idx => $option) {
                if (in_array($option['key'], $defaults)) {
                    $defaultIndices[] = $idx;
                }
            }

            $selectedIndices = array_diff(array_keys($indexedOptions), $defaultIndices);
        }

        $selectedKeys = [];
        foreach ($selectedIndices as $index) {
            $selectedKeys[] = $indexedOptions[$index]['key'];
        }

        return $selectedKeys;
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
     * 提示用户输入密码（隐藏输入）
     *
     * @param string $prompt 提示信息
     * @return string 用户输入的密码
     */
    public function password($prompt = '请输入密码: ')
    {
        echo $prompt;

        // 尝试使用stty隐藏输入
        if (function_exists('system')) {
            system('stty -echo');
            $password = trim(fgets(STDIN));
            system('stty echo');
            echo PHP_EOL;
        } else {
            // 如果stty不可用，则正常输入
            $password = trim(fgets(STDIN));
        }

        return $password;
    }

    /**
     * 显示选择列表（带搜索功能）
     *
     * @param array $items 选项列表
     * @param string $prompt 提示信息
     * @param bool $allowSearch 是否允许搜索
     * @return mixed 选择的项目
     */
    public function select(array $items, $prompt = '请选择:', $allowSearch = true)
    {
        if (empty($items)) {
            $this->error('没有可选择的项目');
            return null;
        }

        $filteredItems = $items;
        $searchTerm = '';

        while (true) {
            // 清屏（如果支持）
            if (function_exists('system')) {
                system('clear 2>/dev/null || cls 2>/dev/null');
            }

            echo $prompt . PHP_EOL;

            if ($allowSearch && !empty($searchTerm)) {
                echo "搜索: {$searchTerm}" . PHP_EOL;
                echo str_repeat('-', 30) . PHP_EOL;
            }

            // 显示选项
            $i = 1;
            $indexedItems = [];

            foreach ($filteredItems as $key => $item) {
                $indexedItems[$i] = [
                    'key' => $key,
                    'value' => $item
                ];

                echo $this->colorize(" {$i})", self::COLOR_YELLOW) . " {$item}" . PHP_EOL;
                $i++;
            }

            if ($allowSearch) {
                echo $this->colorize(" s)", self::COLOR_CYAN) . " 搜索" . PHP_EOL;
                echo $this->colorize(" c)", self::COLOR_CYAN) . " 清除搜索" . PHP_EOL;
            }
            echo $this->colorize(" q)", self::COLOR_RED) . " 退出" . PHP_EOL;

            $input = $this->prompt('> ');

            if (strtolower($input) === 'q') {
                return null;
            }

            if ($allowSearch && strtolower($input) === 's') {
                $searchTerm = $this->prompt('请输入搜索关键词: ');
                $filteredItems = $this->filterItems($items, $searchTerm);
                continue;
            }

            if ($allowSearch && strtolower($input) === 'c') {
                $searchTerm = '';
                $filteredItems = $items;
                continue;
            }

            if (is_numeric($input) && isset($indexedItems[(int) $input])) {
                return $indexedItems[(int) $input]['key'];
            }

            $this->error('无效的选择，请重试');
            sleep(1);
        }
    }

    /**
     * 过滤项目
     *
     * @param array $items 原始项目列表
     * @param string $searchTerm 搜索关键词
     * @return array 过滤后的项目列表
     */
    private function filterItems(array $items, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $items;
        }

        $filtered = [];
        $searchTerm = strtolower($searchTerm);

        foreach ($items as $key => $item) {
            if (strpos(strtolower($item), $searchTerm) !== false ||
                strpos(strtolower($key), $searchTerm) !== false) {
                $filtered[$key] = $item;
            }
        }

        return $filtered;
    }

    /**
     * 显示加载动画
     *
     * @param callable $callback 要执行的回调函数
     * @param string $message 加载消息
     * @return mixed 回调函数的返回值
     */
    public function loading(callable $callback, $message = '正在处理...')
    {
        $spinners = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
        $spinnerIndex = 0;
        $result = null;
        $finished = false;

        // 启动子进程执行回调
        $pid = pcntl_fork();

        if ($pid == -1) {
            // fork失败，直接执行
            return $callback();
        } elseif ($pid == 0) {
            // 子进程
            $result = $callback();
            exit(0);
        } else {
            // 父进程，显示加载动画
            while (!$finished) {
                $spinner = $spinners[$spinnerIndex % count($spinners)];
                echo "\r{$spinner} {$message}";

                $spinnerIndex++;
                usleep(100000); // 100ms

                // 检查子进程是否完成
                $status = pcntl_waitpid($pid, $status, WNOHANG);
                if ($status > 0) {
                    $finished = true;
                }
            }

            echo "\r" . str_repeat(' ', strlen($message) + 2) . "\r";
            return $result;
        }
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

    /**
     * 检测终端尺寸
     */
    private function detectTerminalSize()
    {
        // 尝试从环境变量获取终端尺寸
        $columns = getenv('COLUMNS');
        $lines = getenv('LINES');

        if ($columns !== false && $lines !== false) {
            $this->terminalWidth = (int) $columns;
            $this->terminalHeight = (int) $lines;
            return;
        }

        // 尝试使用stty命令获取终端尺寸（添加超时保护）
        if (PHP_OS_FAMILY !== 'Windows' && function_exists('exec')) {
            $output = [];
            $returnCode = 0;

            // 使用timeout命令防止阻塞
            exec('timeout 1 stty size 2>/dev/null || echo ""', $output, $returnCode);

            if ($returnCode === 0 && isset($output[0]) && !empty(trim($output[0]))) {
                $parts = explode(' ', trim($output[0]));
                if (count($parts) === 2) {
                    $this->terminalHeight = (int) $parts[0];
                    $this->terminalWidth = (int) $parts[1];
                    return;
                }
            }
        }

        // 尝试使用tput命令获取终端尺寸（添加超时保护）
        if (PHP_OS_FAMILY !== 'Windows' && function_exists('exec')) {
            $output = [];
            $returnCode = 0;

            exec('timeout 1 tput cols 2>/dev/null || echo ""', $output, $returnCode);
            if ($returnCode === 0 && isset($output[0]) && !empty(trim($output[0]))) {
                $this->terminalWidth = (int) $output[0];
            }

            $output = [];
            $returnCode = 0;

            exec('timeout 1 tput lines 2>/dev/null || echo ""', $output, $returnCode);
            if ($returnCode === 0 && isset($output[0]) && !empty(trim($output[0]))) {
                $this->terminalHeight = (int) $output[0];
            }
        }
    }

    /**
     * 获取终端宽度
     *
     * @return int 终端宽度
     */
    public function getTerminalWidth()
    {
        return $this->terminalWidth;
    }

    /**
     * 获取终端高度
     *
     * @return int 终端高度
     */
    public function getTerminalHeight()
    {
        return $this->terminalHeight;
    }

    /**
     * 显示表格
     *
     * @param array $headers 表头
     * @param array $rows 数据行
     * @param int $borderStyle 边框样式
     * @param array $options 选项
     */
    public function table(array $headers, array $rows, $borderStyle = self::BORDER_STYLE_SINGLE, array $options = [])
    {
        // 默认选项
        $defaultOptions = [
            'padding' => 1,
            'header_color' => self::COLOR_CYAN,
            'header_style' => self::STYLE_BOLD,
            'max_width' => $this->terminalWidth,
            'truncate_marker' => '...',
            'align' => [], // 列对齐方式，可以是 'left', 'right', 'center'
        ];

        $options = array_merge($defaultOptions, $options);

        // 计算列宽
        $columnWidths = [];

        // 处理表头
        foreach ($headers as $i => $header) {
            $columnWidths[$i] = mb_strlen($header);
        }

        // 处理数据行
        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $cellLength = mb_strlen($cell);
                if (!isset($columnWidths[$i]) || $cellLength > $columnWidths[$i]) {
                    $columnWidths[$i] = $cellLength;
                }
            }
        }

        // 应用填充
        foreach ($columnWidths as $i => $width) {
            $columnWidths[$i] += $options['padding'] * 2;
        }

        // 计算表格总宽度
        $totalWidth = array_sum($columnWidths) + count($columnWidths) + 1;

        // 如果表格太宽，调整列宽
        if ($totalWidth > $options['max_width']) {
            $excessWidth = $totalWidth - $options['max_width'];
            $columnsToAdjust = count($columnWidths);
            $widthReduction = ceil($excessWidth / $columnsToAdjust);

            foreach ($columnWidths as $i => $width) {
                $newWidth = max(3 + $options['padding'] * 2, $width - $widthReduction);
                $columnWidths[$i] = $newWidth;
            }
        }

        // 绘制表格
        $this->drawTableBorder($columnWidths, $borderStyle, 'top');

        // 绘制表头
        $this->drawTableRow($headers, $columnWidths, $borderStyle, [
            'color' => $options['header_color'],
            'style' => $options['header_style'],
            'align' => $options['align'],
            'padding' => $options['padding'],
            'truncate_marker' => $options['truncate_marker'],
        ]);

        $this->drawTableBorder($columnWidths, $borderStyle, 'middle');

        // 绘制数据行
        foreach ($rows as $row) {
            $this->drawTableRow($row, $columnWidths, $borderStyle, [
                'align' => $options['align'],
                'padding' => $options['padding'],
                'truncate_marker' => $options['truncate_marker'],
            ]);
        }

        $this->drawTableBorder($columnWidths, $borderStyle, 'bottom');
    }

    /**
     * 绘制表格边框
     *
     * @param array $columnWidths 列宽
     * @param int $borderStyle 边框样式
     * @param string $position 位置 (top, middle, bottom)
     */
    private function drawTableBorder(array $columnWidths, $borderStyle, $position)
    {
        if ($borderStyle === self::BORDER_STYLE_NONE) {
            return;
        }

        $chars = $this->getBorderChars($borderStyle, $position);

        $line = $chars['left'];

        foreach ($columnWidths as $i => $width) {
            $line .= str_repeat($chars['horizontal'], $width);

            if ($i < count($columnWidths) - 1) {
                $line .= $chars['middle'];
            }
        }

        $line .= $chars['right'];

        echo $line . PHP_EOL;
    }

    /**
     * 绘制表格行
     *
     * @param array $cells 单元格数据
     * @param array $columnWidths 列宽
     * @param int $borderStyle 边框样式
     * @param array $options 选项
     */
    private function drawTableRow(array $cells, array $columnWidths, $borderStyle, array $options = [])
    {
        // 默认选项
        $defaultOptions = [
            'color' => null,
            'style' => null,
            'align' => [],
            'padding' => 1,
            'truncate_marker' => '...',
        ];

        $options = array_merge($defaultOptions, $options);

        // 获取边框字符
        $chars = $this->getBorderChars($borderStyle, 'row');

        $line = $chars['vertical'];

        foreach ($columnWidths as $i => $width) {
            $cell = isset($cells[$i]) ? $cells[$i] : '';
            $cellContent = $this->formatCell($cell, $width, $options['padding'], $options['truncate_marker'], $options['align'][$i] ?? 'left');

            if ($options['color'] !== null || $options['style'] !== null) {
                $cellContent = $this->colorize($cellContent, $options['color'], null, $options['style']);
            }

            $line .= $cellContent;
            $line .= $chars['vertical'];
        }

        echo $line . PHP_EOL;
    }

    /**
     * 获取边框字符
     *
     * @param int $borderStyle 边框样式
     * @param string $position 位置
     * @return array 边框字符
     */
    private function getBorderChars($borderStyle, $position)
    {
        switch ($borderStyle) {
            case self::BORDER_STYLE_DOUBLE:
                $chars = [
                    'top' => ['left' => '╔', 'right' => '╗', 'horizontal' => '═', 'middle' => '╦'],
                    'middle' => ['left' => '╠', 'right' => '╣', 'horizontal' => '═', 'middle' => '╬'],
                    'bottom' => ['left' => '╚', 'right' => '╝', 'horizontal' => '═', 'middle' => '╩'],
                    'row' => ['vertical' => '║'],
                ];
                break;

            case self::BORDER_STYLE_ROUNDED:
                $chars = [
                    'top' => ['left' => '╭', 'right' => '╮', 'horizontal' => '─', 'middle' => '┬'],
                    'middle' => ['left' => '├', 'right' => '┤', 'horizontal' => '─', 'middle' => '┼'],
                    'bottom' => ['left' => '╰', 'right' => '╯', 'horizontal' => '─', 'middle' => '┴'],
                    'row' => ['vertical' => '│'],
                ];
                break;

            case self::BORDER_STYLE_NONE:
                $chars = [
                    'top' => ['left' => '', 'right' => '', 'horizontal' => '', 'middle' => ''],
                    'middle' => ['left' => '', 'right' => '', 'horizontal' => '', 'middle' => ''],
                    'bottom' => ['left' => '', 'right' => '', 'horizontal' => '', 'middle' => ''],
                    'row' => ['vertical' => ' '],
                ];
                break;

            case self::BORDER_STYLE_SINGLE:
            default:
                $chars = [
                    'top' => ['left' => '┌', 'right' => '┐', 'horizontal' => '─', 'middle' => '┬'],
                    'middle' => ['left' => '├', 'right' => '┤', 'horizontal' => '─', 'middle' => '┼'],
                    'bottom' => ['left' => '└', 'right' => '┘', 'horizontal' => '─', 'middle' => '┴'],
                    'row' => ['vertical' => '│'],
                ];
                break;
        }

        return $chars[$position];
    }

    /**
     * 格式化单元格内容
     *
     * @param string $cell 单元格内容
     * @param int $width 列宽
     * @param int $padding 填充
     * @param string $truncateMarker 截断标记
     * @param string $align 对齐方式 (left, right, center)
     * @return string 格式化后的单元格内容
     */
    private function formatCell($cell, $width, $padding, $truncateMarker, $align)
    {
        $contentWidth = $width - ($padding * 2);
        $cell = (string) $cell;

        // 如果内容太长，截断
        if (mb_strlen($cell) > $contentWidth) {
            $cell = mb_substr($cell, 0, $contentWidth - mb_strlen($truncateMarker)) . $truncateMarker;
        }

        // 根据对齐方式填充空格
        $paddingLeft = str_repeat(' ', $padding);
        $paddingRight = str_repeat(' ', $padding);

        switch ($align) {
            case 'right':
                $paddingLeft = str_repeat(' ', $width - mb_strlen($cell) - $padding);
                break;

            case 'center':
                $totalPadding = $width - mb_strlen($cell);
                $paddingLeft = str_repeat(' ', floor($totalPadding / 2));
                $paddingRight = str_repeat(' ', ceil($totalPadding / 2));
                break;

            case 'left':
            default:
                $paddingRight = str_repeat(' ', $width - mb_strlen($cell) - $padding);
                break;
        }

        return $paddingLeft . $cell . $paddingRight;
    }
}
