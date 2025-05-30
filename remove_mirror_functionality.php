<?php
/**
 * 移除 PVM Mirror 功能脚本
 * 
 * 用于彻底移除已废弃的 pvm mirror 功能
 */

echo "=== PVM Mirror 功能移除脚本 ===\n\n";

// 需要修改的文件列表
$filesToModify = [
    'src/Web/Controller.php' => [
        'remove_property' => ['mirrorConfig'],
        'remove_constructor_line' => '$this->mirrorConfig = new MirrorConfig();',
        'remove_routes' => ['mirrors', 'actions/set-mirror', 'actions/add-mirror'],
        'remove_methods' => ['showMirrors', 'actionSetMirror', 'actionAddMirror']
    ]
];

// 需要删除的模板文件
$templatesToRemove = [
    'src/Web/templates/mirrors.php'
];

echo "1. 修改 Web Controller...\n";

// 读取 Controller 文件
$controllerFile = 'src/Web/Controller.php';
$content = file_get_contents($controllerFile);

if ($content === false) {
    echo "错误: 无法读取 $controllerFile\n";
    exit(1);
}

// 移除 mirrorConfig 属性定义
$content = preg_replace('/\s*\/\*\*\s*\*\s*镜像配置\s*\*\s*\*\s*@var\s+MirrorConfig\s*\*\/\s*private\s+\$mirrorConfig;\s*/s', '', $content);

// 移除构造函数中的 mirrorConfig 初始化
$content = str_replace('        $this->mirrorConfig = new MirrorConfig();', '', $content);

// 移除镜像相关的路由
$routesToRemove = [
    "            case 'mirrors':\n                return \$this->showMirrors();",
    "            case 'actions/set-mirror':\n                return \$this->actionSetMirror();",
    "            case 'actions/add-mirror':\n                return \$this->actionAddMirror();"
];

foreach ($routesToRemove as $route) {
    $content = str_replace($route, '', $content);
}

// 移除 showMirrors 方法
$pattern = '/\s*\/\*\*\s*\*\s*显示镜像管理页面.*?\*\/\s*private\s+function\s+showMirrors\(\).*?\{.*?\}\s*/s';
$content = preg_replace($pattern, '', $content);

// 移除 actionSetMirror 方法
$pattern = '/\s*\/\*\*\s*\*\s*处理设置镜像操作.*?\*\/\s*public\s+function\s+actionSetMirror\(\).*?\{.*?\}\s*/s';
$content = preg_replace($pattern, '', $content);

// 移除 actionAddMirror 方法
$pattern = '/\s*\/\*\*\s*\*\s*处理添加镜像操作.*?\*\/\s*public\s+function\s+actionAddMirror\(\).*?\{.*?\}\s*/s';
$content = preg_replace($pattern, '', $content);

// 写回文件
if (file_put_contents($controllerFile, $content) === false) {
    echo "错误: 无法写入 $controllerFile\n";
    exit(1);
}

echo "✓ Web Controller 修改完成\n";

echo "\n2. 删除镜像模板文件...\n";

foreach ($templatesToRemove as $template) {
    if (file_exists($template)) {
        if (unlink($template)) {
            echo "✓ 删除 $template\n";
        } else {
            echo "✗ 无法删除 $template\n";
        }
    } else {
        echo "- $template 不存在，跳过\n";
    }
}

echo "\n3. 检查配置文件...\n";

// 检查是否存在镜像配置文件
$mirrorConfigFile = 'config/mirror.php';
if (file_exists($mirrorConfigFile)) {
    echo "发现镜像配置文件: $mirrorConfigFile\n";
    echo "建议手动检查是否需要保留此文件\n";
} else {
    echo "✓ 未发现镜像配置文件\n";
}

echo "\n=== 移除完成 ===\n";
echo "已移除的功能:\n";
echo "- pvm mirror 命令\n";
echo "- MirrorCommand 类\n";
echo "- MirrorConfig 类\n";
echo "- Web 界面中的镜像管理页面\n";
echo "- 相关的路由和方法\n";

echo "\n注意事项:\n";
echo "1. pvm-mirror 系统保持不变（srcMirror/ 目录）\n";
echo "2. PvmMirrorConfig 保持不变（用于 pvm-mirror 系统）\n";
echo "3. 如果有其他代码依赖 MirrorConfig，需要手动修复\n";

echo "\n建议下一步操作:\n";
echo "1. 运行测试确保系统正常工作\n";
echo "2. 更新文档说明 pvm mirror 功能已废弃\n";
echo "3. 提交代码更改\n";

echo "\n脚本执行完成！\n";
