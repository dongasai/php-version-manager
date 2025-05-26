<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Composer管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> 刷新
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#installComposerModal">
                <i class="bi bi-download"></i> 安装Composer
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            当前PHP版本: <strong><?= $this->escape($currentVersion) ?></strong>
            <?php if ($composerVersion): ?>
                | Composer版本: <strong><?= $this->escape($composerVersion) ?></strong>
            <?php else: ?>
                | <span class="text-warning">未安装Composer</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-box me-2"></i>当前Composer状态
            </div>
            <div class="card-body">
                <?php if ($composerVersion): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Composer已安装
                    </div>
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>版本</th>
                                <td><?= $this->escape($composerVersion) ?></td>
                            </tr>
                            <tr>
                                <th>PHP版本</th>
                                <td><?= $this->escape($currentVersion) ?></td>
                            </tr>
                            <tr>
                                <th>状态</th>
                                <td><span class="badge bg-success">已安装</span></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        当前PHP版本未安装Composer
                    </div>
                    <p class="text-muted">请选择一个Composer版本进行安装。</p>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent">
                <?php if ($composerVersion): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#removeComposerModal">
                        <i class="bi bi-trash"></i> 卸载
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#composerInfoModal">
                        <i class="bi bi-info-circle"></i> 详情
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#installComposerModal">
                        <i class="bi bi-download"></i> 安装Composer
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-cloud-download me-2"></i>可用的Composer版本
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>版本</th>
                                <th>描述</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableComposerVersions as $version): ?>
                                <tr>
                                    <td>
                                        <?= $this->escape($version['name']) ?>
                                        <?php if ($version['recommended']): ?>
                                            <span class="badge bg-primary ms-1">推荐</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $this->escape($version['description']) ?></td>
                                    <td>
                                        <?php if ($composerVersion === $version['version']): ?>
                                            <span class="badge bg-success">已安装</span>
                                        <?php else: ?>
                                            <a href="/actions/composer-install?version=<?= urlencode($version['version']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> 安装
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-terminal me-2"></i>Composer命令
            </div>
            <div class="card-body">
                <?php if ($composerVersion): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>常用命令</h5>
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action" onclick="executeComposerCommand('install')">
                                    <i class="bi bi-download me-2"></i>composer install
                                    <small class="text-muted d-block">安装依赖</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action" onclick="executeComposerCommand('update')">
                                    <i class="bi bi-arrow-clockwise me-2"></i>composer update
                                    <small class="text-muted d-block">更新依赖</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action" onclick="executeComposerCommand('require')">
                                    <i class="bi bi-plus-circle me-2"></i>composer require
                                    <small class="text-muted d-block">添加依赖</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action" onclick="executeComposerCommand('remove')">
                                    <i class="bi bi-dash-circle me-2"></i>composer remove
                                    <small class="text-muted d-block">移除依赖</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>自定义命令</h5>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="customCommand" placeholder="输入Composer命令...">
                                <button class="btn btn-outline-primary" type="button" onclick="executeCustomCommand()">
                                    <i class="bi bi-play"></i> 执行
                                </button>
                            </div>
                            <div id="commandOutput" class="bg-dark text-light p-3 rounded" style="height: 200px; overflow-y: auto; display: none;">
                                <pre id="outputContent"></pre>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        请先安装Composer才能使用命令功能
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 安装Composer模态框 -->
<div class="modal fade" id="installComposerModal" tabindex="-1" aria-labelledby="installComposerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installComposerModalLabel">安装Composer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="installComposerForm" action="/actions/composer-install" method="get">
                    <div class="mb-3">
                        <label for="composerVersionSelect" class="form-label">选择Composer版本</label>
                        <select class="form-select" id="composerVersionSelect" name="version" required>
                            <option value="" selected disabled>-- 选择版本 --</option>
                            <?php foreach ($availableComposerVersions as $version): ?>
                                <option value="<?= $this->escape($version['version']) ?>" <?= $version['recommended'] ? 'selected' : '' ?>>
                                    <?= $this->escape($version['name']) ?> - <?= $this->escape($version['description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="setAsDefault" name="default" checked>
                            <label class="form-check-label" for="setAsDefault">
                                设置为默认Composer
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="submit" form="installComposerForm" class="btn btn-primary">安装</button>
            </div>
        </div>
    </div>
</div>

<!-- 卸载Composer模态框 -->
<div class="modal fade" id="removeComposerModal" tabindex="-1" aria-labelledby="removeComposerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeComposerModalLabel">卸载Composer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>确定要卸载当前PHP版本的Composer吗？</p>
                <p class="text-danger">警告：此操作不可逆，将会删除Composer及其配置。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="/actions/composer-remove?version=<?= urlencode($composerVersion ?: '') ?>" class="btn btn-danger">卸载</a>
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
});

// 执行Composer命令
function executeComposerCommand(command) {
    const customInput = document.getElementById('customCommand');
    customInput.value = command;
    executeCustomCommand();
}

// 执行自定义命令
function executeCustomCommand() {
    const command = document.getElementById('customCommand').value.trim();
    if (!command) {
        alert('请输入命令');
        return;
    }

    const outputDiv = document.getElementById('commandOutput');
    const outputContent = document.getElementById('outputContent');

    outputDiv.style.display = 'block';
    outputContent.textContent = '正在执行命令: ' + command + '\n\n';

    // 这里应该通过AJAX调用后端API执行命令
    // 暂时显示模拟输出
    setTimeout(() => {
        outputContent.textContent += '命令执行完成\n';
        outputContent.textContent += '注意：这是模拟输出，实际功能需要后端API支持\n';
    }, 1000);
}
</script>
