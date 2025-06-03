<?php
/**
 * 安装进度页面模板
 */
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">安装进度</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/versions" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 返回版本管理
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-download me-2"></i>
                    正在安装 PHP <?= $this->escape($version) ?>
                </h5>
            </div>
            <div class="card-body">
                <!-- 进度条 -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">安装进度</span>
                        <span id="progressPercent" class="text-muted">0%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <!-- 状态信息 -->
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div id="statusIcon" class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span id="statusMessage" class="text-muted">正在启动安装任务...</span>
                    </div>
                    <small id="elapsedTime" class="text-muted">已用时间: 0秒</small>
                </div>

                <!-- 日志输出 -->
                <div class="mb-3">
                    <h6>安装日志</h6>
                    <div id="logContainer" class="bg-dark text-light p-3 rounded" style="height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                        <div id="logContent">等待日志输出...</div>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="d-flex justify-content-between">
                    <button id="toggleLog" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye"></i> 显示详细日志
                    </button>
                    <div>
                        <button id="cancelBtn" class="btn btn-sm btn-outline-danger me-2" style="display: none;">
                            <i class="bi bi-x-circle"></i> 取消安装
                        </button>
                        <button id="retryBtn" class="btn btn-sm btn-outline-primary" style="display: none;">
                            <i class="bi bi-arrow-clockwise"></i> 重试安装
                        </button>
                        <a id="completeBtn" href="/versions" class="btn btn-sm btn-success" style="display: none;">
                            <i class="bi bi-check-circle"></i> 完成
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 错误信息 -->
        <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
            <h6 class="alert-heading">安装失败</h6>
            <p id="errorMessage" class="mb-0"></p>
        </div>
    </div>
</div>

<script>
// 安装进度监控
class InstallProgressMonitor {
    constructor(taskId, version) {
        this.taskId = taskId;
        this.version = version;
        this.startTime = Date.now();
        this.pollInterval = null;
        this.isCompleted = false;

        this.initElements();
        this.startPolling();
    }

    initElements() {
        this.progressBar = document.getElementById('progressBar');
        this.progressPercent = document.getElementById('progressPercent');
        this.statusIcon = document.getElementById('statusIcon');
        this.statusMessage = document.getElementById('statusMessage');
        this.elapsedTime = document.getElementById('elapsedTime');
        this.logContent = document.getElementById('logContent');
        this.logContainer = document.getElementById('logContainer');
        this.errorAlert = document.getElementById('errorAlert');
        this.errorMessage = document.getElementById('errorMessage');
        this.completeBtn = document.getElementById('completeBtn');
        this.retryBtn = document.getElementById('retryBtn');
        this.cancelBtn = document.getElementById('cancelBtn');
        this.toggleLogBtn = document.getElementById('toggleLog');

        // 日志显示状态
        this.showDetailedLog = false;

        // 绑定日志切换按钮事件
        this.toggleLogBtn.addEventListener('click', () => this.toggleLogDisplay());

        // 更新已用时间
        setInterval(() => this.updateElapsedTime(), 1000);
    }

    startPolling() {
        this.pollInterval = setInterval(() => {
            if (!this.isCompleted) {
                this.checkStatus();
            }
        }, 2000); // 每2秒检查一次

        // 立即检查一次
        this.checkStatus();
    }

    async checkStatus() {
        try {
            const response = await fetch(`/api/install-status?task_id=${this.taskId}`);
            const data = await response.json();

            if (data.error) {
                this.showError(data.error);
                return;
            }

            this.updateProgress(data);
            this.updateLog(data);

            if (data.status === 'completed' || data.status === 'failed') {
                this.isCompleted = true;
                clearInterval(this.pollInterval);
                this.handleCompletion(data);
            }
        } catch (error) {
            console.error('检查状态失败:', error);
        }
    }

    updateProgress(data) {
        const progress = data.progress || 0;
        const message = data.message || '处理中...';

        // 更新进度条
        this.progressBar.style.width = progress + '%';
        this.progressBar.setAttribute('aria-valuenow', progress);
        this.progressPercent.textContent = progress + '%';

        // 更新状态消息
        this.statusMessage.textContent = message;

        // 更新状态图标
        if (data.status === 'running') {
            this.statusIcon.className = 'spinner-border spinner-border-sm text-primary me-2';
        } else if (data.status === 'completed') {
            this.statusIcon.className = 'bi bi-check-circle-fill text-success me-2';
        } else if (data.status === 'failed') {
            this.statusIcon.className = 'bi bi-x-circle-fill text-danger me-2';
        }
    }

    handleCompletion(data) {
        if (data.status === 'completed') {
            this.progressBar.className = 'progress-bar bg-success';
            this.statusMessage.textContent = data.message;
            this.completeBtn.style.display = 'inline-block';

            // 显示成功动画
            this.showSuccessAnimation();

            // 显示成功消息和后续操作选项
            setTimeout(() => {
                this.showCompletionOptions();
            }, 2000);
        } else if (data.status === 'failed') {
            this.progressBar.className = 'progress-bar bg-danger';
            this.statusMessage.textContent = data.message;
            this.retryBtn.style.display = 'inline-block';

            // 显示失败动画
            this.showFailureAnimation();

            if (data.error) {
                this.showError(data.error);
            }
        }
    }

    showSuccessAnimation() {
        // 添加成功动画效果
        const successIcon = document.createElement('div');
        successIcon.innerHTML = '✅';
        successIcon.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 4rem;
            z-index: 1000;
            animation: successPulse 1s ease-in-out;
        `;

        // 添加CSS动画
        if (!document.getElementById('success-animation-style')) {
            const style = document.createElement('style');
            style.id = 'success-animation-style';
            style.textContent = `
                @keyframes successPulse {
                    0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
                    50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
                    100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(successIcon);

        setTimeout(() => {
            successIcon.remove();
        }, 2000);
    }

    showFailureAnimation() {
        // 添加失败动画效果
        const failureIcon = document.createElement('div');
        failureIcon.innerHTML = '❌';
        failureIcon.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 4rem;
            z-index: 1000;
            animation: failureShake 0.5s ease-in-out;
        `;

        // 添加CSS动画
        if (!document.getElementById('failure-animation-style')) {
            const style = document.createElement('style');
            style.id = 'failure-animation-style';
            style.textContent = `
                @keyframes failureShake {
                    0%, 100% { transform: translate(-50%, -50%) translateX(0); }
                    25% { transform: translate(-50%, -50%) translateX(-10px); }
                    75% { transform: translate(-50%, -50%) translateX(10px); }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(failureIcon);

        setTimeout(() => {
            failureIcon.remove();
        }, 1000);
    }

    showCompletionOptions() {
        // 创建完成选项对话框
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">🎉 安装成功！</h5>
                    </div>
                    <div class="modal-body">
                        <p>PHP ${this.version} 已成功安装！</p>
                        <p>您现在可以：</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="window.location.href='/versions'">
                                📋 查看所有版本
                            </button>
                            <button type="button" class="btn btn-info" onclick="window.location.href='/extensions'">
                                🧩 管理扩展
                            </button>
                            <button type="button" class="btn btn-warning" onclick="window.location.href='/composer'">
                                📦 安装 Composer
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='/'">
                                🏠 返回首页
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // 显示模态框
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        // 自动清理
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    showError(error) {
        this.errorMessage.textContent = error;
        this.errorAlert.style.display = 'block';
    }

    updateElapsedTime() {
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;

        if (minutes > 0) {
            this.elapsedTime.textContent = `已用时间: ${minutes}分${seconds}秒`;
        } else {
            this.elapsedTime.textContent = `已用时间: ${seconds}秒`;
        }
    }

    updateLog(data) {
        if (data.log_lines && data.log_lines.length > 0) {
            if (this.showDetailedLog) {
                // 显示详细日志
                this.logContent.innerHTML = data.log_lines.map(line =>
                    `<div>${this.escapeHtml(line)}</div>`
                ).join('');
            } else {
                // 显示简化日志（只显示最后几行重要信息）
                const importantLines = data.log_lines.filter(line => {
                    const l = line.toLowerCase();
                    return l.includes('下载') || l.includes('download') ||
                           l.includes('解压') || l.includes('extract') ||
                           l.includes('配置') || l.includes('configure') ||
                           l.includes('编译') || l.includes('make') ||
                           l.includes('安装') || l.includes('install') ||
                           l.includes('%') || l.includes('error') || l.includes('错误');
                }).slice(-5); // 只显示最后5行重要信息

                if (importantLines.length > 0) {
                    this.logContent.innerHTML = importantLines.map(line =>
                        `<div>${this.escapeHtml(line)}</div>`
                    ).join('');
                } else {
                    this.logContent.innerHTML = '<div class="text-muted">正在处理中...</div>';
                }
            }

            // 自动滚动到底部
            this.logContainer.scrollTop = this.logContainer.scrollHeight;
        } else {
            this.logContent.innerHTML = '<div class="text-muted">等待日志输出...</div>';
        }
    }

    toggleLogDisplay() {
        this.showDetailedLog = !this.showDetailedLog;

        if (this.showDetailedLog) {
            this.toggleLogBtn.innerHTML = '<i class="bi bi-eye-slash"></i> 隐藏详细日志';
            this.logContainer.style.height = '400px';
        } else {
            this.toggleLogBtn.innerHTML = '<i class="bi bi-eye"></i> 显示详细日志';
            this.logContainer.style.height = '300px';
        }

        // 立即更新日志显示
        this.checkStatus();
    }

    escapeHtml(text) {
        // 清理ANSI颜色代码
        const cleanText = text.replace(/\x1b\[[0-9;]*m/g, '');

        const div = document.createElement('div');
        div.textContent = cleanText;
        return div.innerHTML;
    }
}

// 初始化进度监控
document.addEventListener('DOMContentLoaded', function() {
    const taskId = '<?= $this->escape($taskId) ?>';
    const version = '<?= $this->escape($version) ?>';

    new InstallProgressMonitor(taskId, version);

    // 重试按钮事件
    document.getElementById('retryBtn').addEventListener('click', function() {
        if (confirm('确定要重新安装吗？')) {
            window.location.href = `/actions/install?version=${encodeURIComponent(version)}`;
        }
    });
});
</script>
