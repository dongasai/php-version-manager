#!/bin/bash

# PHP Version Manager Shell集成脚本

# PVM目录
PVM_DIR="${PVM_DIR:-$HOME/.pvm}"

# 当前PHP版本
PVM_CURRENT_VERSION=""

# 全局PHP版本
PVM_GLOBAL_VERSION=""

# 初始化PVM
pvm_init() {
    # 读取全局版本
    if [[ -f "$PVM_DIR/version" ]]; then
        PVM_GLOBAL_VERSION=$(cat "$PVM_DIR/version")
        PVM_CURRENT_VERSION="$PVM_GLOBAL_VERSION"
    fi
    
    # 更新PATH
    if [[ -n "$PVM_CURRENT_VERSION" ]]; then
        export PATH="$PVM_DIR/versions/$PVM_CURRENT_VERSION/bin:$PATH"
    fi
}

# 自动检测项目PHP版本
pvm_auto() {
    local dir="$PWD"
    local version_file=""
    
    # 向上递归查找.php-version文件
    while [[ "$dir" != "/" ]]; do
        if [[ -f "$dir/.php-version" ]]; then
            version_file="$dir/.php-version"
            break
        fi
        dir="$(dirname "$dir")"
    done
    
    # 如果找到版本文件，使用指定版本
    if [[ -n "$version_file" ]]; then
        local version="$(cat "$version_file" | tr -d '[:space:]')"
        if [[ -n "$version" ]] && [[ "$version" != "$PVM_CURRENT_VERSION" ]]; then
            echo "PVM: 自动切换到PHP $version (项目配置)"
            export PVM_CURRENT_VERSION="$version"
            export PATH="$PVM_DIR/versions/$version/bin:$PATH"
        fi
    else
        # 如果没有找到版本文件，使用全局版本
        if [[ -n "$PVM_CURRENT_VERSION" ]] && [[ "$PVM_CURRENT_VERSION" != "$PVM_GLOBAL_VERSION" ]]; then
            echo "PVM: 恢复到全局PHP版本 $PVM_GLOBAL_VERSION"
            export PVM_CURRENT_VERSION="$PVM_GLOBAL_VERSION"
            export PATH="$PVM_DIR/versions/$PVM_GLOBAL_VERSION/bin:$PATH"
        fi
    fi
}

# PVM命令
pvm() {
    local command="$1"
    if [[ "$command" = "use" ]]; then
        # 使用PHP版本命令
        if [[ -z "$2" ]]; then
            echo "错误: 请指定PHP版本"
            return 1
        fi
        
        local version="$2"
        local version_dir="$PVM_DIR/versions/$version"
        
        # 检查版本是否存在
        if [[ ! -d "$version_dir" ]]; then
            echo "错误: PHP版本 $version 未安装"
            echo "使用 'pvm install $version' 安装此版本"
            return 1
        fi
        
        # 更新当前版本
        export PVM_CURRENT_VERSION="$version"
        export PATH="$version_dir/bin:$PATH"
        
        echo "已切换到PHP $version"
    elif [[ "$command" = "current" ]]; then
        # 显示当前PHP版本
        if [[ -n "$PVM_CURRENT_VERSION" ]]; then
            echo "$PVM_CURRENT_VERSION"
        else
            echo "未设置PHP版本"
        fi
    elif [[ "$command" = "global" ]]; then
        # 设置全局PHP版本
        if [[ -z "$2" ]]; then
            echo "错误: 请指定PHP版本"
            return 1
        fi
        
        local version="$2"
        local version_dir="$PVM_DIR/versions/$version"
        
        # 检查版本是否存在
        if [[ ! -d "$version_dir" ]]; then
            echo "错误: PHP版本 $version 未安装"
            echo "使用 'pvm install $version' 安装此版本"
            return 1
        fi
        
        # 更新全局版本
        echo "$version" > "$PVM_DIR/version"
        export PVM_GLOBAL_VERSION="$version"
        export PVM_CURRENT_VERSION="$version"
        export PATH="$version_dir/bin:$PATH"
        
        echo "已设置全局PHP版本为 $version"
    else
        # 其他命令交给PHP脚本处理
        "$PVM_DIR/bin/pvm" "$@"
    fi
}

# 添加目录变更钩子
cd() {
    builtin cd "$@" && pvm_auto
}

# 初始化PVM
pvm_init

# 自动检测项目PHP版本
pvm_auto
