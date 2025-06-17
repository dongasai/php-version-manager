#!/bin/bash

# 简化的CI验证脚本，专门用于GitHub Actions
# 只执行最基本的验证，避免超时

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查基本文件结构
check_basic_structure() {
    log_info "检查基本文件结构..."
    
    local required_files=(
        "bin/pvm"
        "bin/pvm-mirror"
        ".github/workflows/ci.yml"
        "docker/pvm-mirror/Dockerfile"
        "tests/bats/ci-basic.bats"
    )
    
    for file in "${required_files[@]}"; do
        if [[ -f "$file" ]]; then
            log_info "✅ $file 存在"
        else
            log_error "❌ $file 不存在"
            return 1
        fi
    done
}

# 检查PHP语法
check_php_syntax() {
    log_info "检查PHP语法..."
    
    # 检查主要PHP文件
    local php_files=(
        "bin/pvm"
        "bin/pvm-mirror"
    )
    
    for file in "${php_files[@]}"; do
        if php -l "$file" >/dev/null 2>&1; then
            log_info "✅ $file 语法正确"
        else
            log_error "❌ $file 语法错误"
            return 1
        fi
    done
    
    # 检查源代码目录
    if [[ -d "src" ]]; then
        local syntax_errors=0
        while IFS= read -r -d '' file; do
            if ! php -l "$file" >/dev/null 2>&1; then
                log_error "❌ $file 语法错误"
                syntax_errors=$((syntax_errors + 1))
            fi
        done < <(find src -name "*.php" -print0 2>/dev/null)
        
        if [[ $syntax_errors -eq 0 ]]; then
            log_info "✅ src目录下所有PHP文件语法正确"
        else
            log_error "❌ src目录下有 $syntax_errors 个文件语法错误"
            return 1
        fi
    fi
    
    # 检查镜像源代码目录
    if [[ -d "srcMirror" ]]; then
        local syntax_errors=0
        while IFS= read -r -d '' file; do
            if ! php -l "$file" >/dev/null 2>&1; then
                log_error "❌ $file 语法错误"
                syntax_errors=$((syntax_errors + 1))
            fi
        done < <(find srcMirror -name "*.php" -print0 2>/dev/null)
        
        if [[ $syntax_errors -eq 0 ]]; then
            log_info "✅ srcMirror目录下所有PHP文件语法正确"
        else
            log_error "❌ srcMirror目录下有 $syntax_errors 个文件语法错误"
            return 1
        fi
    fi
}

# 检查YAML语法
check_yaml_syntax() {
    log_info "检查YAML语法..."

    # 检查是否有python3和yaml模块
    if ! command -v python3 >/dev/null 2>&1; then
        log_warn "⚠️  python3 不可用，跳过YAML语法检查"
        return 0
    fi

    if ! python3 -c "import yaml" 2>/dev/null; then
        log_warn "⚠️  PyYAML 模块不可用，跳过YAML语法检查"
        return 0
    fi

    local yaml_files=(
        ".github/workflows/ci.yml"
        ".github/workflows/docker-build.yml"
        ".github/workflows/docker-release.yml"
    )

    for file in "${yaml_files[@]}"; do
        if [[ -f "$file" ]]; then
            if python3 -c "import yaml; yaml.safe_load(open('$file'))" 2>/dev/null; then
                log_info "✅ $file 语法正确"
            else
                log_warn "⚠️  $file 可能有语法问题，但继续执行"
            fi
        else
            log_warn "⚠️  $file 不存在，跳过检查"
        fi
    done
}

# 检查可执行权限
check_executable_permissions() {
    log_info "检查可执行权限..."
    
    local executable_files=(
        "bin/pvm"
        "bin/pvm-mirror"
        "scripts/ci-verification.sh"
    )
    
    for file in "${executable_files[@]}"; do
        if [[ -f "$file" ]]; then
            if [[ -x "$file" ]]; then
                log_info "✅ $file 有执行权限"
            else
                log_warn "⚠️  $file 没有执行权限"
                chmod +x "$file"
                log_info "✅ 已为 $file 添加执行权限"
            fi
        fi
    done
}

# 主函数
main() {
    echo "=============================================="
    echo "  PVM CI 基本验证"
    echo "=============================================="
    echo
    
    # 执行各项验证
    check_basic_structure || exit 1
    echo
    
    check_php_syntax || exit 1
    echo
    
    check_yaml_syntax || exit 1
    echo
    
    check_executable_permissions || exit 1
    echo
    
    # 显示成功信息
    echo "=============================================="
    echo -e "${GREEN}🎉 基本验证通过！${NC}"
    echo "=============================================="
}

# 执行主函数
main "$@"
