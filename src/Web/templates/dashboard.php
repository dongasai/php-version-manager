<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">仪表盘</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> 刷新
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="exportBtn">
                <i class="bi bi-download"></i> 导出
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-code-slash"></i>
                <div class="stats-value"><?= $this->escape($currentVersion) ?></div>
                <div class="stats-label">当前PHP版本</div>
            </div>
            <div class="card-footer bg-transparent border-top-0 text-center">
                <a href="/versions" class="btn btn-sm btn-outline-primary">管理版本</a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-puzzle"></i>
                <div class="stats-value"><?= count($installedExtensions) ?></div>
                <div class="stats-label">已安装扩展</div>
            </div>
            <div class="card-footer bg-transparent border-top-0 text-center">
                <a href="/extensions" class="btn btn-sm btn-outline-primary">管理扩展</a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-cpu"></i>
                <div class="stats-value"><?= isset($systemInfo['load']) ? $this->escape($systemInfo['load']['1min']) : '0.00' ?></div>
                <div class="stats-label">系统负载</div>
            </div>
            <div class="card-footer bg-transparent border-top-0 text-center">
                <a href="/monitor" class="btn btn-sm btn-outline-primary">查看详情</a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-hdd"></i>
                <div class="stats-value">
                    <?= isset($systemInfo['disk']['percent']) ? $this->escape($systemInfo['disk']['percent']) : '0' ?>%
                </div>
                <div class="stats-label">磁盘使用率</div>
            </div>
            <div class="card-footer bg-transparent border-top-0 text-center">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: <?= isset($systemInfo['disk']['percent']) ? $this->escape($systemInfo['disk']['percent']) : '0' ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-code-slash me-2"></i>已安装的PHP版本
            </div>
            <div class="card-body">
                <?php if (empty($installedVersions)): ?>
                    <p class="text-muted">没有安装的PHP版本</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>版本</th>
                                    <th>状态</th>
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
                                        <td>
                                            <?php if (!$versionInfo['is_current'] && $versionInfo['status'] === 'installed'): ?>
                                                <a href="/actions/use?version=<?= urlencode($versionInfo['version']) ?>" class="btn btn-sm btn-outline-primary">使用</a>
                                            <?php endif; ?>
                                            <?php if ($versionInfo['type'] === 'pvm'): ?>
                                                <a href="/actions/remove?version=<?= urlencode($versionInfo['version']) ?>" class="btn btn-sm btn-outline-danger">删除</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/versions" class="btn btn-sm btn-outline-primary">查看全部</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-puzzle me-2"></i>已安装的扩展
            </div>
            <div class="card-body">
                <?php if (empty($installedExtensions)): ?>
                    <p class="text-muted">没有安装的扩展</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>扩展名</th>
                                    <th>版本</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                foreach ($installedExtensions as $extension):
                                    if ($count++ >= 5) break; // 只显示前5个
                                ?>
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
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/extensions" class="btn btn-sm btn-outline-primary">查看全部</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>系统状态
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>系统信息</h5>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>操作系统</th>
                                    <td><?= $this->escape($systemInfo['os'] ?? '未知') ?></td>
                                </tr>
                                <tr>
                                    <th>PHP版本</th>
                                    <td><?= $this->escape($currentVersion) ?></td>
                                </tr>
                                <tr>
                                    <th>PHP-FPM状态</th>
                                    <td>
                                        <?php if ($systemInfo['php_fpm_running'] ?? false): ?>
                                            <span class="badge bg-success">运行中</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">已停止</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>系统负载</th>
                                    <td>
                                        <?php if (isset($systemInfo['load'])): ?>
                                            <?= $this->escape($systemInfo['load']['1min']) ?> (1分钟),
                                            <?= $this->escape($systemInfo['load']['5min']) ?> (5分钟),
                                            <?= $this->escape($systemInfo['load']['15min']) ?> (15分钟)
                                        <?php else: ?>
                                            未知
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>资源使用</h5>
                        <div class="mb-3">
                            <label>内存使用</label>
                            <div class="progress">
                                <?php
                                $memoryPercent = 0;
                                if (isset($systemInfo['memory']['total']) && $systemInfo['memory']['total'] > 0) {
                                    $memoryPercent = round(($systemInfo['memory']['used'] / $systemInfo['memory']['total']) * 100);
                                }
                                ?>
                                <div class="progress-bar" role="progressbar" style="width: <?= $memoryPercent ?>%"
                                     aria-valuenow="<?= $memoryPercent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $memoryPercent ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if (isset($systemInfo['memory'])): ?>
                                    已用: <?= $this->formatSize($systemInfo['memory']['used'] ?? 0) ?> /
                                    总计: <?= $this->formatSize($systemInfo['memory']['total'] ?? 0) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="mb-3">
                            <label>磁盘使用</label>
                            <div class="progress">
                                <?php
                                $diskPercent = isset($systemInfo['disk']['percent']) ? $systemInfo['disk']['percent'] : 0;
                                ?>
                                <div class="progress-bar" role="progressbar" style="width: <?= $diskPercent ?>%"
                                     aria-valuenow="<?= $diskPercent ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= $diskPercent ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if (isset($systemInfo['disk'])): ?>
                                    已用: <?= $this->formatSize($systemInfo['disk']['used'] ?? 0) ?> /
                                    总计: <?= $this->formatSize($systemInfo['disk']['total'] ?? 0) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="/monitor" class="btn btn-sm btn-outline-primary">查看详细监控</a>
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

    // 导出按钮
    document.getElementById('exportBtn').addEventListener('click', function() {
        alert('导出功能正在开发中...');
    });
});
</script>
