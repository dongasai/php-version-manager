<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">设置</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> 刷新
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="saveBtn">
                <i class="bi bi-save"></i> 保存设置
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            当前PHP版本: <strong><?= $this->escape($currentVersion) ?></strong>
            <?php if (isset($phpIniPath)): ?>
                | 配置文件: <code><?= $this->escape($phpIniPath) ?></code>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>PHP配置
            </div>
            <div class="card-body">
                <form id="phpConfigForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>基本设置</h5>
                            
                            <div class="mb-3">
                                <label for="memory_limit" class="form-label">内存限制</label>
                                <input type="text" class="form-control" id="memory_limit" name="memory_limit" 
                                       value="<?= isset($phpIniValues['memory_limit']) ? $this->escape($phpIniValues['memory_limit']) : '128M' ?>">
                                <div class="form-text">例如: 128M, 256M, 512M</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="max_execution_time" class="form-label">最大执行时间</label>
                                <input type="number" class="form-control" id="max_execution_time" name="max_execution_time" 
                                       value="<?= isset($phpIniValues['max_execution_time']) ? $this->escape($phpIniValues['max_execution_time']) : '30' ?>">
                                <div class="form-text">秒数，0表示无限制</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="max_input_time" class="form-label">最大输入时间</label>
                                <input type="number" class="form-control" id="max_input_time" name="max_input_time" 
                                       value="<?= isset($phpIniValues['max_input_time']) ? $this->escape($phpIniValues['max_input_time']) : '60' ?>">
                                <div class="form-text">秒数，-1表示使用max_execution_time</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="post_max_size" class="form-label">POST最大大小</label>
                                <input type="text" class="form-control" id="post_max_size" name="post_max_size" 
                                       value="<?= isset($phpIniValues['post_max_size']) ? $this->escape($phpIniValues['post_max_size']) : '8M' ?>">
                                <div class="form-text">例如: 8M, 16M, 32M</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="upload_max_filesize" class="form-label">上传文件最大大小</label>
                                <input type="text" class="form-control" id="upload_max_filesize" name="upload_max_filesize" 
                                       value="<?= isset($phpIniValues['upload_max_filesize']) ? $this->escape($phpIniValues['upload_max_filesize']) : '2M' ?>">
                                <div class="form-text">例如: 2M, 8M, 16M</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>错误处理</h5>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="display_errors" name="display_errors" 
                                           <?= (isset($phpIniValues['display_errors']) && strtolower($phpIniValues['display_errors']) === 'on') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="display_errors">
                                        显示错误
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="display_startup_errors" name="display_startup_errors" 
                                           <?= (isset($phpIniValues['display_startup_errors']) && strtolower($phpIniValues['display_startup_errors']) === 'on') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="display_startup_errors">
                                        显示启动错误
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="log_errors" name="log_errors" 
                                           <?= (isset($phpIniValues['log_errors']) && strtolower($phpIniValues['log_errors']) === 'on') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="log_errors">
                                        记录错误日志
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="error_log" class="form-label">错误日志文件</label>
                                <input type="text" class="form-control" id="error_log" name="error_log" 
                                       value="<?= isset($phpIniValues['error_log']) ? $this->escape($phpIniValues['error_log']) : '' ?>">
                                <div class="form-text">留空使用系统默认</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="error_reporting" class="form-label">错误报告级别</label>
                                <select class="form-select" id="error_reporting" name="error_reporting">
                                    <option value="E_ALL" <?= (isset($phpIniValues['error_reporting']) && $phpIniValues['error_reporting'] === 'E_ALL') ? 'selected' : '' ?>>E_ALL (所有错误)</option>
                                    <option value="E_ALL & ~E_DEPRECATED & ~E_STRICT" <?= (isset($phpIniValues['error_reporting']) && $phpIniValues['error_reporting'] === 'E_ALL & ~E_DEPRECATED & ~E_STRICT') ? 'selected' : '' ?>>生产环境推荐</option>
                                    <option value="E_ERROR | E_WARNING | E_PARSE" <?= (isset($phpIniValues['error_reporting']) && $phpIniValues['error_reporting'] === 'E_ERROR | E_WARNING | E_PARSE') ? 'selected' : '' ?>>仅严重错误</option>
                                    <option value="0" <?= (isset($phpIniValues['error_reporting']) && $phpIniValues['error_reporting'] === '0') ? 'selected' : '' ?>>关闭错误报告</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>其他设置</h5>
                            
                            <div class="mb-3">
                                <label for="default_charset" class="form-label">默认字符集</label>
                                <input type="text" class="form-control" id="default_charset" name="default_charset" 
                                       value="<?= isset($phpIniValues['default_charset']) ? $this->escape($phpIniValues['default_charset']) : 'UTF-8' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_timezone" class="form-label">默认时区</label>
                                <select class="form-select" id="date_timezone" name="date.timezone">
                                    <option value="Asia/Shanghai" <?= (isset($phpIniValues['date.timezone']) && $phpIniValues['date.timezone'] === 'Asia/Shanghai') ? 'selected' : '' ?>>Asia/Shanghai</option>
                                    <option value="UTC" <?= (isset($phpIniValues['date.timezone']) && $phpIniValues['date.timezone'] === 'UTC') ? 'selected' : '' ?>>UTC</option>
                                    <option value="America/New_York" <?= (isset($phpIniValues['date.timezone']) && $phpIniValues['date.timezone'] === 'America/New_York') ? 'selected' : '' ?>>America/New_York</option>
                                    <option value="Europe/London" <?= (isset($phpIniValues['date.timezone']) && $phpIniValues['date.timezone'] === 'Europe/London') ? 'selected' : '' ?>>Europe/London</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="savePhpConfig()">
                    <i class="bi bi-save"></i> 保存配置
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="resetToDefaults()">
                    <i class="bi bi-arrow-clockwise"></i> 重置为默认值
                </button>
                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#presetModal">
                    <i class="bi bi-list"></i> 预设配置
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>配置信息
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th>PHP版本</th>
                            <td><?= $this->escape($currentVersion) ?></td>
                        </tr>
                        <tr>
                            <th>配置文件</th>
                            <td><code><?= isset($phpIniPath) ? $this->escape($phpIniPath) : '未知' ?></code></td>
                        </tr>
                        <tr>
                            <th>文件大小</th>
                            <td>
                                <?php if (isset($phpIniPath) && file_exists($phpIniPath)): ?>
                                    <?= $this->formatSize(filesize($phpIniPath)) ?>
                                <?php else: ?>
                                    未知
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>最后修改</th>
                            <td>
                                <?php if (isset($phpIniPath) && file_exists($phpIniPath)): ?>
                                    <?= date('Y-m-d H:i:s', filemtime($phpIniPath)) ?>
                                <?php else: ?>
                                    未知
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="/actions/backup-config" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download"></i> 备份配置
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#restoreModal">
                    <i class="bi bi-upload"></i> 恢复配置
                </button>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-terminal me-2"></i>快速操作
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyDevelopmentConfig()">
                        <i class="bi bi-code"></i> 应用开发环境配置
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="applyProductionConfig()">
                        <i class="bi bi-server"></i> 应用生产环境配置
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="validateConfig()">
                        <i class="bi bi-check-circle"></i> 验证配置
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 预设配置模态框 -->
<div class="modal fade" id="presetModal" tabindex="-1" aria-labelledby="presetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="presetModalLabel">预设配置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action" onclick="applyPreset('development')">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">开发环境</h5>
                            <small>推荐用于开发</small>
                        </div>
                        <p class="mb-1">启用错误显示，增加内存限制，延长执行时间</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="applyPreset('production')">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">生产环境</h5>
                            <small>推荐用于生产</small>
                        </div>
                        <p class="mb-1">关闭错误显示，优化性能设置</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" onclick="applyPreset('performance')">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">高性能</h5>
                            <small>优化性能</small>
                        </div>
                        <p class="mb-1">启用OPcache，优化内存和执行时间</p>
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 刷新按钮
    document.getElementById('refreshBtn').addEventListener('click', function() {
        location.reload();
    });
    
    // 保存按钮
    document.getElementById('saveBtn').addEventListener('click', function() {
        savePhpConfig();
    });
});

function savePhpConfig() {
    const form = document.getElementById('phpConfigForm');
    const formData = new FormData(form);
    
    // 处理复选框
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        formData.set(checkbox.name, checkbox.checked ? 'On' : 'Off');
    });
    
    // 这里应该通过AJAX提交到后端
    alert('配置保存功能需要后端API支持');
}

function resetToDefaults() {
    if (confirm('确定要重置为默认配置吗？这将覆盖当前所有设置。')) {
        // 这里应该调用后端API重置配置
        alert('重置功能需要后端API支持');
    }
}

function applyDevelopmentConfig() {
    if (confirm('确定要应用开发环境配置吗？')) {
        // 这里应该调用后端API
        alert('应用开发环境配置功能需要后端API支持');
    }
}

function applyProductionConfig() {
    if (confirm('确定要应用生产环境配置吗？')) {
        // 这里应该调用后端API
        alert('应用生产环境配置功能需要后端API支持');
    }
}

function validateConfig() {
    // 这里应该调用后端API验证配置
    alert('配置验证功能需要后端API支持');
}

function applyPreset(preset) {
    if (confirm('确定要应用 ' + preset + ' 预设配置吗？')) {
        // 这里应该调用后端API
        alert('应用预设配置功能需要后端API支持');
    }
}
</script>
