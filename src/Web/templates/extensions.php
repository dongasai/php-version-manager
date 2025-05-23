<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">扩展管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> 刷新
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#installModal">
                <i class="bi bi-download"></i> 安装新扩展
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            当前PHP版本: <strong><?= $this->escape($currentVersion) ?></strong>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-puzzle me-2"></i>已安装的扩展
                </div>
                <div class="input-group input-group-sm" style="width: 300px;">
                    <input type="text" class="form-control" id="extensionSearch" placeholder="搜索扩展...">
                    <button class="btn btn-outline-secondary" type="button" id="extensionSearchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($installedExtensions)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        没有安装的扩展
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="installedExtensionsTable">
                            <thead>
                                <tr>
                                    <th>扩展名</th>
                                    <th>版本</th>
                                    <th>状态</th>
                                    <th>类型</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($installedExtensions as $extension): ?>
                                    <tr>
                                        <td><?= $this->escape($extension['name']) ?></td>
                                        <td><?= $this->escape($extension['version'] ?? '未知') ?></td>
                                        <td>
                                            <?php if ($extension['enabled'] ?? false): ?>
                                                <span class="badge bg-success">已启用</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">已禁用</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($extension['type'])): ?>
                                                <?php if ($extension['type'] === 'core'): ?>
                                                    <span class="badge bg-primary">核心</span>
                                                <?php elseif ($extension['type'] === 'pecl'): ?>
                                                    <span class="badge bg-info">PECL</span>
                                                <?php elseif ($extension['type'] === 'custom'): ?>
                                                    <span class="badge bg-warning">自定义</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= $this->escape($extension['type']) ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">未知</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($extension['enabled'] ?? false): ?>
                                                    <a href="/actions/ext-disable?name=<?= urlencode($extension['name']) ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-toggle-off"></i> 禁用
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/actions/ext-enable?name=<?= urlencode($extension['name']) ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-toggle-on"></i> 启用
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" data-bs-target="#infoModal" 
                                                        data-extension="<?= $this->escape($extension['name']) ?>">
                                                    <i class="bi bi-info-circle"></i> 详情
                                                </button>
                                                
                                                <?php if (($extension['type'] ?? '') !== 'core'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" data-bs-target="#removeModal" 
                                                            data-extension="<?= $this->escape($extension['name']) ?>">
                                                        <i class="bi bi-trash"></i> 删除
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cloud-download me-2"></i>可用的扩展
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="availableExtensionSearch" placeholder="搜索可用扩展...">
                        <button class="btn btn-outline-secondary" type="button" id="availableExtensionSearchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="availableExtensionsTable">
                        <thead>
                            <tr>
                                <th>扩展名</th>
                                <th>最新版本</th>
                                <th>描述</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableExtensions as $extension): ?>
                                <?php 
                                $isInstalled = false;
                                foreach ($installedExtensions as $installed) {
                                    if ($installed['name'] === $extension['name']) {
                                        $isInstalled = true;
                                        break;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= $this->escape($extension['name']) ?></td>
                                    <td><?= $this->escape($extension['version'] ?? '未知') ?></td>
                                    <td><?= $this->escape($extension['description'] ?? '') ?></td>
                                    <td>
                                        <?php if ($isInstalled): ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="bi bi-check-circle-fill"></i> 已安装
                                            </button>
                                        <?php else: ?>
                                            <a href="/actions/ext-install?name=<?= urlencode($extension['name']) ?>" class="btn btn-sm btn-outline-primary">
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

<!-- 安装新扩展模态框 -->
<div class="modal fade" id="installModal" tabindex="-1" aria-labelledby="installModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installModalLabel">安装新扩展</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="installForm" action="/actions/ext-install" method="get">
                    <div class="mb-3">
                        <label for="extensionSelect" class="form-label">选择扩展</label>
                        <select class="form-select" id="extensionSelect" name="name" required>
                            <option value="" selected disabled>-- 选择扩展 --</option>
                            <?php foreach ($availableExtensions as $extension): ?>
                                <?php 
                                $isInstalled = false;
                                foreach ($installedExtensions as $installed) {
                                    if ($installed['name'] === $extension['name']) {
                                        $isInstalled = true;
                                        break;
                                    }
                                }
                                if (!$isInstalled):
                                ?>
                                    <option value="<?= $this->escape($extension['name']) ?>"><?= $this->escape($extension['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="extensionVersion" class="form-label">版本 (可选)</label>
                        <input type="text" class="form-control" id="extensionVersion" name="version" placeholder="留空安装最新版本">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="submit" form="installForm" class="btn btn-primary">安装</button>
            </div>
        </div>
    </div>
</div>

<!-- 删除扩展模态框 -->
<div class="modal fade" id="removeModal" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">删除扩展</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>确定要删除扩展 <strong id="removeExtensionName"></strong> 吗？</p>
                <p class="text-danger">警告：此操作不可逆，将会删除该扩展的所有文件和配置。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="#" id="removeExtensionBtn" class="btn btn-danger">删除</a>
            </div>
        </div>
    </div>
</div>

<!-- 扩展详情模态框 -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel">扩展详情</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">加载中...</span>
                    </div>
                    <p>正在加载扩展信息...</p>
                </div>
                <div id="extensionInfo" style="display: none;">
                    <!-- 扩展信息将通过JavaScript动态加载 -->
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
    
    // 已安装扩展搜索
    document.getElementById('extensionSearchBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('extensionSearch').value.toLowerCase();
        const table = document.getElementById('installedExtensionsTable');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            if (name.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // 已安装扩展搜索（按键事件）
    document.getElementById('extensionSearch').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('extensionSearchBtn').click();
        }
    });
    
    // 可用扩展搜索
    document.getElementById('availableExtensionSearchBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('availableExtensionSearch').value.toLowerCase();
        const table = document.getElementById('availableExtensionsTable');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const description = row.cells[2].textContent.toLowerCase();
            if (name.includes(searchTerm) || description.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // 可用扩展搜索（按键事件）
    document.getElementById('availableExtensionSearch').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('availableExtensionSearchBtn').click();
        }
    });
    
    // 删除扩展模态框
    const removeModal = document.getElementById('removeModal');
    removeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const extension = button.getAttribute('data-extension');
        
        document.getElementById('removeExtensionName').textContent = extension;
        document.getElementById('removeExtensionBtn').href = '/actions/ext-remove?name=' + encodeURIComponent(extension);
    });
    
    // 扩展详情模态框
    const infoModal = document.getElementById('infoModal');
    infoModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const extension = button.getAttribute('data-extension');
        
        document.getElementById('extensionInfo').style.display = 'none';
        document.querySelector('#infoModal .spinner-border').parentElement.style.display = '';
        
        // 加载扩展信息
        fetch('/api/extension-info?name=' + encodeURIComponent(extension))
            .then(response => response.json())
            .then(data => {
                const infoDiv = document.getElementById('extensionInfo');
                
                // 构建扩展信息HTML
                let html = `
                    <h4>${data.name} ${data.version || ''}</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>状态</th>
                                        <td>
                                            <span class="badge ${data.enabled ? 'bg-success' : 'bg-danger'}">
                                                ${data.enabled ? '已启用' : '已禁用'}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>类型</th>
                                        <td>
                                            ${data.type === 'core' ? '<span class="badge bg-primary">核心</span>' : 
                                              data.type === 'pecl' ? '<span class="badge bg-info">PECL</span>' :
                                              data.type === 'custom' ? '<span class="badge bg-warning">自定义</span>' :
                                              `<span class="badge bg-secondary">${data.type || '未知'}</span>`}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>安装路径</th>
                                        <td><code>${data.path || '未知'}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>配置文件</th>
                                        <td><code>${data.ini_file || '未知'}</code></td>
                                    </tr>
                                    <tr>
                                        <th>安装日期</th>
                                        <td>${data.install_date || '未知'}</td>
                                    </tr>
                                    <tr>
                                        <th>依赖扩展</th>
                                        <td>${data.dependencies ? data.dependencies.join(', ') : '无'}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                
                // 添加配置选项
                if (data.config && Object.keys(data.config).length > 0) {
                    html += `
                        <h5 class="mt-3">配置选项</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>选项名</th>
                                        <th>当前值</th>
                                        <th>默认值</th>
                                        <th>访问权限</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    for (const [key, value] of Object.entries(data.config)) {
                        html += `
                            <tr>
                                <td>${key}</td>
                                <td><code>${value.value !== undefined ? value.value : ''}</code></td>
                                <td><code>${value.default !== undefined ? value.default : ''}</code></td>
                                <td>${value.access || ''}</td>
                            </tr>
                        `;
                    }
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                // 添加描述
                if (data.description) {
                    html += `
                        <h5 class="mt-3">描述</h5>
                        <div class="card">
                            <div class="card-body">
                                ${data.description}
                            </div>
                        </div>
                    `;
                }
                
                infoDiv.innerHTML = html;
                
                // 显示信息，隐藏加载动画
                document.querySelector('#infoModal .spinner-border').parentElement.style.display = 'none';
                infoDiv.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching extension info:', error);
                document.getElementById('extensionInfo').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        加载扩展信息失败
                    </div>
                `;
                document.querySelector('#infoModal .spinner-border').parentElement.style.display = 'none';
                document.getElementById('extensionInfo').style.display = 'block';
            });
    });
});
</script>
