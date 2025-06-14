#!/bin/bash

# PVM-Mirror GitHub CI 最终验证脚本
# 验证所有功能是否正常工作

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

log_debug() {
    echo -e "${BLUE}[DEBUG]${NC} $1"
}

# 显示标题
show_title() {
    echo "=============================================="
    echo "  PVM-Mirror GitHub CI 最终验证"
    echo "=============================================="
    echo
}

# 检查Git状态
check_git_status() {
    log_info "检查Git状态..."
    
    # 检查是否在Git仓库中
    if ! git rev-parse --git-dir >/dev/null 2>&1; then
        log_error "当前目录不是Git仓库"
        return 1
    fi
    
    # 检查当前分支
    local current_branch=$(git branch --show-current)
    log_info "当前分支: $current_branch"
    
    # 检查是否有未提交的更改
    if ! git diff --quiet; then
        log_warn "有未提交的更改"
        git status --porcelain
    else
        log_info "工作目录干净"
    fi
    
    # 检查远程状态
    local git_status=$(git status)
    if echo "$git_status" | grep -q "Your branch is up to date\|您的分支与上游分支.*一致"; then
        log_info "与远程仓库同步"
    else
        log_warn "与远程仓库不同步"
        echo "$git_status" | grep -E "(ahead|behind|领先|落后)" || true
    fi
}

# 验证GitHub Actions工作流
verify_github_actions() {
    log_info "验证GitHub Actions工作流..."
    
    local workflows_dir=".github/workflows"
    local expected_workflows=(
        "ci.yml"
        "docker-build.yml"
        "docker-release.yml"
    )
    
    for workflow in "${expected_workflows[@]}"; do
        local workflow_path="$workflows_dir/$workflow"
        if [[ -f "$workflow_path" ]]; then
            log_info "✅ $workflow 存在"
            
            # 检查语法
            if python3 -c "import yaml; yaml.safe_load(open('$workflow_path'))" 2>/dev/null; then
                log_info "✅ $workflow 语法正确"
            else
                log_error "❌ $workflow 语法错误"
                return 1
            fi
        else
            log_error "❌ $workflow 不存在"
            return 1
        fi
    done
}

# 验证Docker配置
verify_docker_config() {
    log_info "验证Docker配置..."
    
    # 检查Dockerfile
    local dockerfile="docker/pvm-mirror/Dockerfile"
    if [[ -f "$dockerfile" ]]; then
        log_info "✅ Dockerfile 存在"
        
        # 检查语法（尝试构建）
        if docker build --dry-run -f "$dockerfile" . >/dev/null 2>&1; then
            log_info "✅ Dockerfile 语法正确"
        else
            log_warn "⚠️  Dockerfile 可能有问题"
        fi
    else
        log_error "❌ Dockerfile 不存在"
        return 1
    fi
    
    # 检查Compose文件
    local compose_files=(
        "docker/pvm-mirror/dev-compose.yml"
        "docker/pvm-mirror/prod-compose.yml"
    )
    
    for compose_file in "${compose_files[@]}"; do
        if [[ -f "$compose_file" ]]; then
            log_info "✅ $(basename $compose_file) 存在"
            
            # 检查语法
            if docker compose -f "$compose_file" config >/dev/null 2>&1; then
                log_info "✅ $(basename $compose_file) 语法正确"
            else
                log_error "❌ $(basename $compose_file) 语法错误"
                return 1
            fi
        else
            log_error "❌ $(basename $compose_file) 不存在"
            return 1
        fi
    done
}

# 验证脚本和工具
verify_scripts() {
    log_info "验证脚本和工具..."
    
    local scripts=(
        "docker/pvm-mirror/test.sh"
        "docker/pvm-mirror/validate-env.sh"
        "docker/pvm-mirror/manage-images.sh"
        "scripts/validate-github-actions.sh"
    )
    
    for script in "${scripts[@]}"; do
        if [[ -f "$script" && -x "$script" ]]; then
            log_info "✅ $(basename $script) 存在且可执行"
        else
            log_error "❌ $(basename $script) 不存在或不可执行"
            return 1
        fi
    done
}

# 验证文档
verify_documentation() {
    log_info "验证文档..."
    
    local docs=(
        "README.md"
        "docker/pvm-mirror/README.md"
        "docs/GitHub-CI-Docker.md"
        "docs/快速设置GitHub-CI.md"
    )
    
    for doc in "${docs[@]}"; do
        if [[ -f "$doc" ]]; then
            local line_count=$(wc -l < "$doc")
            log_info "✅ $(basename $doc) 存在 ($line_count 行)"
        else
            log_error "❌ $(basename $doc) 不存在"
            return 1
        fi
    done
}

# 运行GitHub Actions验证
run_github_actions_validation() {
    log_info "运行GitHub Actions验证..."
    
    if [[ -x "scripts/validate-github-actions.sh" ]]; then
        if ./scripts/validate-github-actions.sh >/dev/null 2>&1; then
            log_info "✅ GitHub Actions验证通过"
        else
            log_error "❌ GitHub Actions验证失败"
            return 1
        fi
    else
        log_error "❌ GitHub Actions验证脚本不可用"
        return 1
    fi
}

# 测试Docker构建
test_docker_build() {
    log_info "测试Docker构建..."
    
    if command -v docker >/dev/null 2>&1; then
        log_info "Docker可用，尝试构建测试镜像..."
        
        if docker build -t pvm-mirror:test -f docker/pvm-mirror/Dockerfile . >/dev/null 2>&1; then
            log_info "✅ Docker镜像构建成功"
            
            # 清理测试镜像
            docker rmi pvm-mirror:test >/dev/null 2>&1 || true
        else
            log_error "❌ Docker镜像构建失败"
            return 1
        fi
    else
        log_warn "⚠️  Docker不可用，跳过构建测试"
    fi
}

# 检查环境变量配置
check_env_config() {
    log_info "检查环境变量配置..."
    
    if [[ -f "docker/pvm-mirror/.env.example" ]]; then
        log_info "✅ .env.example 存在"
        
        # 检查必需变量
        local required_vars=(
            "PVM_MIRROR_ENV"
            "PVM_MIRROR_DEBUG"
            "PVM_MIRROR_LOG_LEVEL"
            "PVM_MIRROR_HOST"
            "PVM_MIRROR_PORT"
        )
        
        local missing_vars=()
        for var in "${required_vars[@]}"; do
            if grep -q "^$var=" docker/pvm-mirror/.env.example; then
                log_debug "✓ $var 已定义"
            else
                missing_vars+=("$var")
            fi
        done
        
        if [[ ${#missing_vars[@]} -eq 0 ]]; then
            log_info "✅ 所有必需环境变量已定义"
        else
            log_error "❌ 缺少环境变量: ${missing_vars[*]}"
            return 1
        fi
    else
        log_error "❌ .env.example 不存在"
        return 1
    fi
}

# 显示部署指南
show_deployment_guide() {
    log_info "部署指南..."
    
    cat << EOF

🚀 GitHub CI 已配置完成！

下一步操作：

1. 确保GitHub仓库设置正确：
   - Settings → Actions → General
   - 选择 "Read and write permissions"
   - 勾选 "Allow GitHub Actions to create and approve pull requests"

2. 触发首次构建：
   方法一：推送代码更改
   方法二：在GitHub Actions页面手动触发

3. 查看构建状态：
   - 访问 GitHub 仓库的 Actions 标签页
   - 查看工作流运行状态

4. 使用构建的镜像：
   docker pull ghcr.io/your-username/pvm/pvm-mirror:latest
   docker run -d -p 34403:34403 ghcr.io/your-username/pvm/pvm-mirror:latest

📚 详细文档：
   - docs/GitHub-CI-Docker.md
   - docs/快速设置GitHub-CI.md

EOF
}

# 主函数
main() {
    show_title
    
    # 执行各项验证
    check_git_status || exit 1
    echo
    
    verify_github_actions || exit 1
    echo
    
    verify_docker_config || exit 1
    echo
    
    verify_scripts || exit 1
    echo
    
    verify_documentation || exit 1
    echo
    
    check_env_config || exit 1
    echo
    
    run_github_actions_validation || exit 1
    echo
    
    test_docker_build || exit 1
    echo
    
    # 显示成功信息
    echo "=============================================="
    echo -e "${GREEN}🎉 所有验证都通过了！${NC}"
    echo "=============================================="
    echo
    
    show_deployment_guide
}

# 执行主函数
main "$@"
