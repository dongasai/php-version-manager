<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">状态监控</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> 刷新
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="autoRefreshBtn">
                <i class="bi bi-play"></i> 自动刷新
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            当前PHP版本: <strong><?= $this->escape($currentVersion) ?></strong>
            | 最后更新: <span id="lastUpdate"><?= date('Y-m-d H:i:s') ?></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-cpu"></i>
                <div class="stats-value"><?= isset($cpuUsage['total']['cpu']) ? number_format($cpuUsage['total']['cpu'], 2) : '0.00' ?>%</div>
                <div class="stats-label">CPU使用率</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-memory"></i>
                <div class="stats-value"><?= isset($memoryUsage['total']['rss_mb']) ? number_format($memoryUsage['total']['rss_mb'], 2) : '0.00' ?> MB</div>
                <div class="stats-label">内存使用</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-code-slash"></i>
                <div class="stats-value"><?= count($phpProcesses) ?></div>
                <div class="stats-label">PHP进程</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body stats-card">
                <i class="bi bi-server"></i>
                <div class="stats-value"><?= count($fpmProcesses) ?></div>
                <div class="stats-label">PHP-FPM进程</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-code-slash me-2"></i>PHP进程列表
            </div>
            <div class="card-body">
                <?php if (empty($phpProcesses)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        没有运行的PHP进程
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>PID</th>
                                    <th>用户</th>
                                    <th>CPU%</th>
                                    <th>内存%</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($phpProcesses, 0, 10) as $process): ?>
                                    <tr>
                                        <td><?= $this->escape($process['pid']) ?></td>
                                        <td><?= $this->escape($process['user']) ?></td>
                                        <td><?= $this->escape($process['cpu']) ?>%</td>
                                        <td><?= $this->escape($process['mem']) ?>%</td>
                                        <td>
                                            <span class="badge bg-<?= strpos($process['stat'], 'S') !== false ? 'success' : 'warning' ?>">
                                                <?= $this->escape($process['stat']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($phpProcesses) > 10): ?>
                        <small class="text-muted">显示前10个进程，共<?= count($phpProcesses) ?>个</small>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-server me-2"></i>PHP-FPM进程列表
            </div>
            <div class="card-body">
                <?php if (empty($fpmProcesses)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        没有运行的PHP-FPM进程
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>PID</th>
                                    <th>用户</th>
                                    <th>CPU%</th>
                                    <th>内存%</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($fpmProcesses, 0, 10) as $process): ?>
                                    <tr>
                                        <td><?= $this->escape($process['pid']) ?></td>
                                        <td><?= $this->escape($process['user']) ?></td>
                                        <td><?= $this->escape($process['cpu']) ?>%</td>
                                        <td><?= $this->escape($process['mem']) ?>%</td>
                                        <td>
                                            <span class="badge bg-<?= strpos($process['stat'], 'S') !== false ? 'success' : 'warning' ?>">
                                                <?= $this->escape($process['stat']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($fpmProcesses) > 10): ?>
                        <small class="text-muted">显示前10个进程，共<?= count($fpmProcesses) ?>个</small>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-memory me-2"></i>内存使用详情
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <h6>PHP进程</h6>
                        <p class="mb-1">进程数: <?= isset($memoryUsage['php']['processes']) ? $memoryUsage['php']['processes'] : 0 ?></p>
                        <p class="mb-1">RSS: <?= isset($memoryUsage['php']['rss_mb']) ? number_format($memoryUsage['php']['rss_mb'], 2) : '0.00' ?> MB</p>
                        <p class="mb-0">VSZ: <?= isset($memoryUsage['php']['vsz_mb']) ? number_format($memoryUsage['php']['vsz_mb'], 2) : '0.00' ?> MB</p>
                    </div>
                    <div class="col-6">
                        <h6>PHP-FPM进程</h6>
                        <p class="mb-1">进程数: <?= isset($memoryUsage['fpm']['processes']) ? $memoryUsage['fpm']['processes'] : 0 ?></p>
                        <p class="mb-1">RSS: <?= isset($memoryUsage['fpm']['rss_mb']) ? number_format($memoryUsage['fpm']['rss_mb'], 2) : '0.00' ?> MB</p>
                        <p class="mb-0">VSZ: <?= isset($memoryUsage['fpm']['vsz_mb']) ? number_format($memoryUsage['fpm']['vsz_mb'], 2) : '0.00' ?> MB</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>总计</h6>
                        <p class="mb-1">总进程数: <?= isset($memoryUsage['total']['processes']) ? $memoryUsage['total']['processes'] : 0 ?></p>
                        <p class="mb-1">总RSS: <?= isset($memoryUsage['total']['rss_mb']) ? number_format($memoryUsage['total']['rss_mb'], 2) : '0.00' ?> MB</p>
                        <p class="mb-0">总VSZ: <?= isset($memoryUsage['total']['vsz_mb']) ? number_format($memoryUsage['total']['vsz_mb'], 2) : '0.00' ?> MB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-cpu me-2"></i>CPU使用详情
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <h6>PHP进程</h6>
                        <p class="mb-1">进程数: <?= isset($cpuUsage['php']['processes']) ? $cpuUsage['php']['processes'] : 0 ?></p>
                        <p class="mb-0">CPU: <?= isset($cpuUsage['php']['cpu']) ? number_format($cpuUsage['php']['cpu'], 2) : '0.00' ?>%</p>
                    </div>
                    <div class="col-6">
                        <h6>PHP-FPM进程</h6>
                        <p class="mb-1">进程数: <?= isset($cpuUsage['fpm']['processes']) ? $cpuUsage['fpm']['processes'] : 0 ?></p>
                        <p class="mb-0">CPU: <?= isset($cpuUsage['fpm']['cpu']) ? number_format($cpuUsage['fpm']['cpu'], 2) : '0.00' ?>%</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6>总计</h6>
                        <p class="mb-1">总进程数: <?= isset($cpuUsage['total']['processes']) ? $cpuUsage['total']['processes'] : 0 ?></p>
                        <p class="mb-0">总CPU: <?= isset($cpuUsage['total']['cpu']) ? number_format($cpuUsage['total']['cpu'], 2) : '0.00' ?>%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval = null;
let isAutoRefreshing = false;

document.addEventListener('DOMContentLoaded', function() {
    // 刷新按钮
    document.getElementById('refreshBtn').addEventListener('click', function() {
        location.reload();
    });
    
    // 自动刷新按钮
    document.getElementById('autoRefreshBtn').addEventListener('click', function() {
        toggleAutoRefresh();
    });
});

function toggleAutoRefresh() {
    const btn = document.getElementById('autoRefreshBtn');
    
    if (isAutoRefreshing) {
        // 停止自动刷新
        clearInterval(autoRefreshInterval);
        isAutoRefreshing = false;
        btn.innerHTML = '<i class="bi bi-play"></i> 自动刷新';
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-outline-secondary');
    } else {
        // 开始自动刷新
        autoRefreshInterval = setInterval(() => {
            updateLastRefreshTime();
            // 这里可以通过AJAX更新数据而不是整页刷新
            location.reload();
        }, 5000); // 每5秒刷新一次
        
        isAutoRefreshing = true;
        btn.innerHTML = '<i class="bi bi-stop"></i> 停止刷新';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-outline-danger');
    }
}

function updateLastRefreshTime() {
    const now = new Date();
    const timeString = now.getFullYear() + '-' + 
                      String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                      String(now.getDate()).padStart(2, '0') + ' ' + 
                      String(now.getHours()).padStart(2, '0') + ':' + 
                      String(now.getMinutes()).padStart(2, '0') + ':' + 
                      String(now.getSeconds()).padStart(2, '0');
    document.getElementById('lastUpdate').textContent = timeString;
}
</script>
