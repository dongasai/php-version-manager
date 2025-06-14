#!/bin/bash

# GitHub Actions 工作流验证脚本
# 用于验证工作流配置的正确性

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 计数器
TOTAL_CHECKS=0
PASSED_CHECKS=0
FAILED_CHECKS=0
WARNING_CHECKS=0

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

log_debug() {
    echo -e "${BLUE}[DEBUG]${NC} $1"
}

# 检查函数
check_result() {
    local test_name="$1"
    local result="$2"
    local message="$3"
    
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    
    if [[ "$result" == "pass" ]]; then
        echo -e "${GREEN}✅ PASS${NC} $test_name"
        if [[ -n "$message" ]]; then
            echo "   $message"
        fi
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
    elif [[ "$result" == "warn" ]]; then
        echo -e "${YELLOW}⚠️  WARN${NC} $test_name"
        if [[ -n "$message" ]]; then
            echo "   $message"
        fi
        WARNING_CHECKS=$((WARNING_CHECKS + 1))
    else
        echo -e "${RED}❌ FAIL${NC} $test_name"
        if [[ -n "$message" ]]; then
            echo "   $message"
        fi
        FAILED_CHECKS=$((FAILED_CHECKS + 1))
    fi
}

# 检查工作流文件语法
check_workflow_syntax() {
    log_info "检查工作流文件语法..."
    
    local workflows_dir=".github/workflows"
    
    if [[ ! -d "$workflows_dir" ]]; then
        check_result "工作流目录存在性" "fail" "目录不存在: $workflows_dir"
        return
    fi
    
    local workflow_files=(
        "ci.yml"
        "docker-build.yml"
        "docker-release.yml"
    )
    
    for file in "${workflow_files[@]}"; do
        local filepath="$workflows_dir/$file"
        
        if [[ ! -f "$filepath" ]]; then
            check_result "工作流文件存在性: $file" "fail" "文件不存在: $filepath"
            continue
        fi
        
        # 检查YAML语法
        if python3 -c "import yaml; yaml.safe_load(open('$filepath'))" 2>/dev/null; then
            check_result "YAML语法: $file" "pass" "语法正确"
        else
            check_result "YAML语法: $file" "fail" "YAML语法错误"
            continue
        fi
        
        # 检查必需字段
        if grep -q "^name:" "$filepath" && grep -q "^on:" "$filepath" && grep -q "^jobs:" "$filepath"; then
            check_result "必需字段: $file" "pass" "包含name, on, jobs字段"
        else
            check_result "必需字段: $file" "fail" "缺少必需字段"
        fi
    done
}

# 检查Docker相关配置
check_docker_config() {
    log_info "检查Docker相关配置..."
    
    # 检查Dockerfile
    local dockerfile="docker/pvm-mirror/Dockerfile"
    if [[ -f "$dockerfile" ]]; then
        check_result "Dockerfile存在性" "pass" "文件存在: $dockerfile"
        
        # 检查基础镜像
        if grep -q "^FROM ubuntu:" "$dockerfile"; then
            check_result "基础镜像" "pass" "使用Ubuntu基础镜像"
        else
            check_result "基础镜像" "warn" "未使用推荐的Ubuntu基础镜像"
        fi
        
        # 检查用户配置
        if grep -q "USER pvm-mirror" "$dockerfile"; then
            check_result "非root用户" "pass" "使用非root用户运行"
        else
            check_result "非root用户" "warn" "建议使用非root用户运行"
        fi
        
        # 检查健康检查
        if grep -q "HEALTHCHECK" "$dockerfile"; then
            check_result "健康检查" "pass" "配置了健康检查"
        else
            check_result "健康检查" "warn" "建议配置健康检查"
        fi
    else
        check_result "Dockerfile存在性" "fail" "文件不存在: $dockerfile"
    fi
    
    # 检查Docker Compose文件
    local compose_files=(
        "docker/pvm-mirror/dev-compose.yml"
        "docker/pvm-mirror/prod-compose.yml"
    )
    
    for compose_file in "${compose_files[@]}"; do
        if [[ -f "$compose_file" ]]; then
            check_result "Compose文件: $(basename $compose_file)" "pass" "文件存在"
            
            # 检查语法
            if docker compose -f "$compose_file" config >/dev/null 2>&1; then
                check_result "Compose语法: $(basename $compose_file)" "pass" "语法正确"
            else
                check_result "Compose语法: $(basename $compose_file)" "fail" "语法错误"
            fi
        else
            check_result "Compose文件: $(basename $compose_file)" "fail" "文件不存在: $compose_file"
        fi
    done
}

# 检查环境变量配置
check_env_config() {
    log_info "检查环境变量配置..."
    
    # 检查.env.example文件
    local env_example="docker/pvm-mirror/.env.example"
    if [[ -f "$env_example" ]]; then
        check_result ".env.example存在性" "pass" "文件存在"
        
        # 检查必需的环境变量
        local required_vars=(
            "PVM_MIRROR_ENV"
            "PVM_MIRROR_DEBUG"
            "PVM_MIRROR_LOG_LEVEL"
            "PVM_MIRROR_HOST"
            "PVM_MIRROR_PORT"
        )
        
        local missing_vars=()
        for var in "${required_vars[@]}"; do
            if grep -q "^$var=" "$env_example"; then
                check_result "环境变量: $var" "pass" "已定义"
            else
                missing_vars+=("$var")
            fi
        done
        
        if [[ ${#missing_vars[@]} -gt 0 ]]; then
            check_result "必需环境变量" "warn" "缺少: ${missing_vars[*]}"
        else
            check_result "必需环境变量" "pass" "所有必需变量已定义"
        fi
    else
        check_result ".env.example存在性" "fail" "文件不存在: $env_example"
    fi
    
    # 检查环境变量验证脚本
    local validate_script="docker/pvm-mirror/validate-env.sh"
    if [[ -f "$validate_script" && -x "$validate_script" ]]; then
        check_result "环境变量验证脚本" "pass" "脚本存在且可执行"
    else
        check_result "环境变量验证脚本" "warn" "脚本不存在或不可执行"
    fi
}

# 检查测试配置
check_test_config() {
    log_info "检查测试配置..."
    
    # 检查测试脚本
    local test_script="docker/pvm-mirror/test.sh"
    if [[ -f "$test_script" && -x "$test_script" ]]; then
        check_result "Docker测试脚本" "pass" "脚本存在且可执行"
    else
        check_result "Docker测试脚本" "warn" "脚本不存在或不可执行"
    fi
    
    # 检查BATS测试
    if [[ -d "tests/bats" ]]; then
        local bats_files=$(find tests/bats -name "*.bats" | wc -l)
        if [[ $bats_files -gt 0 ]]; then
            check_result "BATS测试文件" "pass" "找到 $bats_files 个测试文件"
        else
            check_result "BATS测试文件" "warn" "tests/bats目录存在但无测试文件"
        fi
    else
        check_result "BATS测试目录" "warn" "tests/bats目录不存在"
    fi
    
    # 检查管理脚本
    local manage_script="docker/pvm-mirror/manage-images.sh"
    if [[ -f "$manage_script" && -x "$manage_script" ]]; then
        check_result "镜像管理脚本" "pass" "脚本存在且可执行"
    else
        check_result "镜像管理脚本" "warn" "脚本不存在或不可执行"
    fi
}

# 检查文档
check_documentation() {
    log_info "检查文档..."
    
    # 检查主要文档文件
    local doc_files=(
        "README.md"
        "docker/pvm-mirror/README.md"
        "docs/GitHub-CI-Docker.md"
    )
    
    for doc_file in "${doc_files[@]}"; do
        if [[ -f "$doc_file" ]]; then
            check_result "文档: $(basename $doc_file)" "pass" "文件存在"
            
            # 检查文档长度
            local line_count=$(wc -l < "$doc_file")
            if [[ $line_count -gt 10 ]]; then
                check_result "文档内容: $(basename $doc_file)" "pass" "$line_count 行内容"
            else
                check_result "文档内容: $(basename $doc_file)" "warn" "内容较少 ($line_count 行)"
            fi
        else
            check_result "文档: $(basename $doc_file)" "warn" "文件不存在: $doc_file"
        fi
    done
}

# 检查权限和安全
check_security() {
    log_info "检查安全配置..."
    
    # 检查工作流权限配置
    if grep -r "permissions:" .github/workflows/ >/dev/null 2>&1; then
        check_result "工作流权限配置" "pass" "已配置权限"
    else
        check_result "工作流权限配置" "warn" "建议明确配置权限"
    fi
    
    # 检查安全扫描配置
    if grep -r "trivy" .github/workflows/ >/dev/null 2>&1; then
        check_result "安全扫描配置" "pass" "已配置Trivy扫描"
    else
        check_result "安全扫描配置" "warn" "建议配置安全扫描"
    fi
    
    # 检查密钥使用
    if grep -r "secrets\." .github/workflows/ >/dev/null 2>&1; then
        local secret_usage=$(grep -r "secrets\." .github/workflows/ | wc -l)
        if [[ $secret_usage -le 5 ]]; then
            check_result "密钥使用" "pass" "合理使用密钥 ($secret_usage 处)"
        else
            check_result "密钥使用" "warn" "密钥使用较多 ($secret_usage 处)"
        fi
    else
        check_result "密钥使用" "pass" "未使用自定义密钥"
    fi
}

# 显示结果摘要
show_summary() {
    echo
    echo "=============================================="
    echo "  GitHub Actions 验证结果"
    echo "=============================================="
    echo "总检查项: $TOTAL_CHECKS"
    echo -e "通过: ${GREEN}$PASSED_CHECKS${NC}"
    echo -e "警告: ${YELLOW}$WARNING_CHECKS${NC}"
    echo -e "失败: ${RED}$FAILED_CHECKS${NC}"
    echo
    
    if [[ $FAILED_CHECKS -eq 0 ]]; then
        echo -e "${GREEN}🎉 所有关键检查项都通过了！${NC}"
        if [[ $WARNING_CHECKS -gt 0 ]]; then
            echo -e "${YELLOW}⚠️  有 $WARNING_CHECKS 个警告项，建议优化${NC}"
        fi
        echo
        echo "✅ GitHub Actions 配置已就绪，可以推送到仓库"
        return 0
    else
        echo -e "${RED}❌ 有 $FAILED_CHECKS 个关键问题需要修复${NC}"
        echo
        echo "🔧 请修复上述问题后重新运行验证"
        return 1
    fi
}

# 主函数
main() {
    echo "=============================================="
    echo "  GitHub Actions 工作流验证"
    echo "=============================================="
    echo
    
    # 检查当前目录
    if [[ ! -d ".github/workflows" ]]; then
        log_error "请在项目根目录运行此脚本"
        exit 1
    fi
    
    # 执行各项检查
    check_workflow_syntax
    echo
    check_docker_config
    echo
    check_env_config
    echo
    check_test_config
    echo
    check_documentation
    echo
    check_security
    echo
    
    # 显示结果摘要
    show_summary
}

# 执行主函数
main "$@"
