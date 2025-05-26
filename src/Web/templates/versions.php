<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">版本管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> 刷新
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#installModal">
                <i class="bi bi-download"></i> 安装新版本
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            当前使用的PHP版本: <strong><?= $this->escape($currentVersion) ?></strong>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-code-slash me-2"></i>已安装的PHP版本
            </div>
            <div class="card-body">
                <?php if (empty($installedVersions)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        没有安装的PHP版本
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>状态</th>
                                    <th>安装路径</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($installedVersions as $versionInfo): ?>
                                    <tr>
                                        <td>
                                            <?= $this->escape($versionInfo['version']) ?>
                                            <small class="text-muted ms-2">(<?= $this->escape($versionInfo['type']) ?>)</small>
                                        </td>
                                        <td>
                                            <?php if ($versionInfo['is_current']): ?>
                                                <span class="badge bg-primary">当前</span>
                                            <?php endif; ?>
                                            <?php if ($versionInfo['status'] === 'active'): ?>
                                                <span class="badge bg-success">活跃</span>
                                            <?php elseif ($versionInfo['status'] === 'installed'): ?>
                                                <span class="badge bg-info">已安装</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">不完整</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= $this->escape($versionInfo['path']) ?></code></td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if (!$versionInfo['is_current'] && $versionInfo['status'] === 'installed'): ?>
                                                    <a href="/actions/use?version=<?= urlencode($versionInfo['version']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-check-circle"></i> 使用
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal" data-bs-target="#infoModal"
                                                        data-version="<?= $this->escape($versionInfo['version']) ?>">
                                                    <i class="bi bi-info-circle"></i> 详情
                                                </button>
                                                <?php if ($versionInfo['type'] === 'pvm'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal" data-bs-target="#removeModal"
                                                            data-version="<?= $this->escape($versionInfo['version']) ?>">
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
                <i class="bi bi-cloud-download me-2"></i>可用的PHP版本
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="versionSearch" placeholder="搜索版本...">
                        <button class="btn btn-outline-secondary" type="button" id="versionSearchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="availableVersionsTable">
                        <thead>
                            <tr>
                                <th>版本</th>
                                <th>发布日期</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableVersions as $version): ?>
                                <?php
                                $installedVersionsList = array_column($installedVersions, 'version');
                                $isInstalled = in_array($version['version'], $installedVersionsList);
                                $isCurrent = $version['version'] === $currentVersion;
                                ?>
                                <tr>
                                    <td><?= $this->escape($version['version']) ?></td>
                                    <td><?= isset($version['release_date']) ? $this->formatTime(strtotime($version['release_date']), 'Y-m-d') : '未知' ?></td>
                                    <td>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge bg-success">当前</span>
                                        <?php elseif ($isInstalled): ?>
                                            <span class="badge bg-secondary">已安装</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">可安装</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$isInstalled): ?>
                                            <a href="/actions/install?version=<?= urlencode($version['version']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> 安装
                                            </a>
                                        <?php elseif (!$isCurrent): ?>
                                            <a href="/actions/use?version=<?= urlencode($version['version']) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-check-circle"></i> 使用
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                                <i class="bi bi-check-circle-fill"></i> 当前使用
                                            </button>
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

<!-- 安装新版本模态框 -->
<div class="modal fade" id="installModal" tabindex="-1" aria-labelledby="installModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installModalLabel">安装新PHP版本</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="installForm" action="/actions/install" method="get">
                    <div class="mb-3">
                        <label for="versionSelect" class="form-label">选择PHP版本</label>
                        <select class="form-select" id="versionSelect" name="version" required>
                            <option value="" selected disabled>-- 选择版本 --</option>
                            <?php foreach ($availableVersions as $version): ?>
                                <?php
                                $installedVersionsList = array_column($installedVersions, 'version');
                                if (!in_array($version['version'], $installedVersionsList)):
                                ?>
                                    <option value="<?= $this->escape($version['version']) ?>"><?= $this->escape($version['version']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="withDefaultExtensions" name="with_default_extensions" checked>
                            <label class="form-check-label" for="withDefaultExtensions">
                                安装默认扩展
                            </label>
                        </div>
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

<!-- 删除版本模态框 -->
<div class="modal fade" id="removeModal" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeModalLabel">删除PHP版本</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>确定要删除PHP版本 <strong id="removeVersionName"></strong> 吗？</p>
                <p class="text-danger">警告：此操作不可逆，将会删除该版本的所有文件和配置。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="#" id="removeVersionBtn" class="btn btn-danger">删除</a>
            </div>
        </div>
    </div>
</div>

<!-- 版本详情模态框 -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel">PHP版本详情</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">加载中...</span>
                    </div>
                    <p>正在加载版本信息...</p>
                </div>
                <div id="versionInfo" style="display: none;">
                    <!-- 版本信息将通过JavaScript动态加载 -->
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

    // 版本搜索
    document.getElementById('versionSearchBtn').addEventListener('click', function() {
        const searchTerm = document.getElementById('versionSearch').value.toLowerCase();
        const table = document.getElementById('availableVersionsTable');
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const version = row.cells[0].textContent.toLowerCase();
            if (version.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // 版本搜索（按键事件）
    document.getElementById('versionSearch').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('versionSearchBtn').click();
        }
    });

    // 删除版本模态框
    const removeModal = document.getElementById('removeModal');
    removeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const version = button.getAttribute('data-version');

        document.getElementById('removeVersionName').textContent = version;
        document.getElementById('removeVersionBtn').href = '/actions/remove?version=' + encodeURIComponent(version);
    });

    // 版本详情模态框
    const infoModal = document.getElementById('infoModal');
    infoModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const version = button.getAttribute('data-version');

        document.getElementById('versionInfo').style.display = 'none';
        document.querySelector('#infoModal .spinner-border').parentElement.style.display = '';

        // 加载版本信息
        fetch('/api/version-info?version=' + encodeURIComponent(version))
            .then(response => response.json())
            .then(data => {
                const infoDiv = document.getElementById('versionInfo');

                // 构建版本信息HTML
                let html = `
                    <h4>PHP ${data.version}</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>安装路径</th>
                                        <td><code>${data.path}</code></td>
                                    </tr>
                                    <tr>
                                        <th>配置文件</th>
                                        <td><code>${data.php_ini}</code></td>
                                    </tr>
                                    <tr>
                                        <th>扩展目录</th>
                                        <td><code>${data.extension_dir}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>已安装扩展</th>
                                        <td>${data.extensions.length}</td>
                                    </tr>
                                    <tr>
                                        <th>编译选项</th>
                                        <td>${data.configure_options ? data.configure_options.length : 0}</td>
                                    </tr>
                                    <tr>
                                        <th>安装日期</th>
                                        <td>${data.install_date || '未知'}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <h5 class="mt-3">已安装扩展</h5>
                    <div class="row">
                `;

                // 添加扩展列表
                if (data.extensions && data.extensions.length > 0) {
                    for (const ext of data.extensions) {
                        html += `
                            <div class="col-md-4 mb-2">
                                <span class="badge ${ext.enabled ? 'bg-success' : 'bg-danger'}">${ext.name}</span>
                                ${ext.version ? ' ' + ext.version : ''}
                            </div>
                        `;
                    }
                } else {
                    html += '<div class="col-12"><p class="text-muted">没有安装扩展</p></div>';
                }

                html += '</div>';

                // 添加编译选项
                if (data.configure_options && data.configure_options.length > 0) {
                    html += `
                        <h5 class="mt-3">编译选项</h5>
                        <div class="bg-light p-2 rounded">
                            <code>${data.configure_options.join(' \\\n')}</code>
                        </div>
                    `;
                }

                infoDiv.innerHTML = html;

                // 显示信息，隐藏加载动画
                document.querySelector('#infoModal .spinner-border').parentElement.style.display = 'none';
                infoDiv.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching version info:', error);
                document.getElementById('versionInfo').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        加载版本信息失败
                    </div>
                `;
                document.querySelector('#infoModal .spinner-border').parentElement.style.display = 'none';
                document.getElementById('versionInfo').style.display = 'block';
            });
    });
});
</script>
