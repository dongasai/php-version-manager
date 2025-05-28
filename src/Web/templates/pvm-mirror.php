<?php
/**
 * PVM镜像源管理页面模板
 */

// 设置活动菜单项
$active = 'pvm-mirror';

// 开始输出缓冲
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2">PVM镜像源管理</h1>
                    <p class="text-muted mb-0">
                        <small>统一管理PVM自建镜像源，用于下载PHP源码、PECL扩展、Composer等所有内容。</small>
                    </p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshPage()">
                            <i class="bi bi-arrow-clockwise"></i> 刷新
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="testAllMirrors()">
                            <i class="bi bi-speedometer2"></i> 测试连接
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 显示消息 -->
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-<?= $_GET['type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- 镜像源状态 -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>镜像源状态
                    </h5>
                    <?php if ($config['enabled']): ?>
                        <span class="badge bg-success">已启用</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">已禁用</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>启用状态:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php if ($config['enabled']): ?>
                                <span class="text-success">
                                    <i class="bi bi-check-circle"></i> 已启用
                                </span>
                            <?php else: ?>
                                <span class="text-secondary">
                                    <i class="bi bi-x-circle"></i> 已禁用
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>主镜像源:</strong>
                        </div>
                        <div class="col-sm-8">
                            <code><?= htmlspecialchars($config['mirror_url']) ?></code>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>备用镜像:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?= $config['fallback_count'] ?> 个
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>连接超时:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?= $config['timeout'] ?> 秒
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>自动回退:</strong>
                        </div>
                        <div class="col-sm-8">
                            <?php if ($config['auto_fallback']): ?>
                                <span class="text-success">
                                    <i class="bi bi-check-circle"></i> 启用
                                </span>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="bi bi-x-circle"></i> 禁用
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 镜像源控制 -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>镜像源控制
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($config['enabled']): ?>
                            <form method="post" action="/actions/pvm-mirror-disable" class="d-inline">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-pause-circle"></i> 禁用PVM镜像源
                                </button>
                            </form>
                            <small class="text-muted">禁用后将使用官方源下载</small>
                        <?php else: ?>
                            <form method="post" action="/actions/pvm-mirror-enable" class="d-inline">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play-circle"></i> 启用PVM镜像源
                                </button>
                            </form>
                            <small class="text-muted">启用后将优先使用PVM镜像源</small>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <h6>设置主镜像源</h6>
                    <form method="post" action="/actions/pvm-mirror-set">
                        <div class="input-group mb-2">
                            <input type="url" class="form-control" name="url" 
                                   value="<?= htmlspecialchars($config['mirror_url']) ?>"
                                   placeholder="http://pvm.2sxo.com" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> 设置
                            </button>
                        </div>
                        <small class="text-muted">设置PVM镜像源的主地址</small>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 所有镜像源列表 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list me-2"></i>镜像源列表
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>类型</th>
                                    <th>地址</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allMirrors as $index => $mirror): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index === 0): ?>
                                                <span class="badge bg-primary">主镜像</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">备用镜像</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($mirror) ?></code>
                                        </td>
                                        <td>
                                            <span class="mirror-status" data-url="<?= htmlspecialchars($mirror) ?>">
                                                <span class="text-muted">
                                                    <i class="bi bi-hourglass-split"></i> 检测中...
                                                </span>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="testMirror('<?= htmlspecialchars($mirror) ?>')">
                                                <i class="bi bi-speedometer2"></i> 测试
                                            </button>
                                            <?php if ($index > 0): ?>
                                                <form method="post" action="/actions/pvm-mirror-remove" class="d-inline">
                                                    <input type="hidden" name="url" value="<?= htmlspecialchars($mirror) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('确定要移除这个备用镜像源吗？')">
                                                        <i class="bi bi-trash"></i> 移除
                                                    </button>
                                                </form>
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

    <!-- 添加备用镜像源 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>添加备用镜像源
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/actions/pvm-mirror-add">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="fallbackUrl" class="form-label">备用镜像源地址</label>
                                <input type="url" class="form-control" id="fallbackUrl" name="url" 
                                       placeholder="http://localhost:34403" required>
                                <div class="form-text">添加备用镜像源，当主镜像源不可用时自动使用</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-success d-block w-100">
                                    <i class="bi bi-plus"></i> 添加备用镜像源
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 使用说明 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-question-circle me-2"></i>使用说明
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>什么是PVM镜像源？</h6>
                            <p class="text-muted">
                                PVM镜像源是统一的下载源，用于下载PHP源码、PECL扩展、Composer等所有内容。
                                使用镜像源可以提高下载速度，特别是在网络环境较差的情况下。
                            </p>
                            
                            <h6>镜像源优先级</h6>
                            <ul class="text-muted">
                                <li>首先尝试主镜像源</li>
                                <li>如果主镜像源不可用，尝试备用镜像源</li>
                                <li>如果所有镜像源都不可用，回退到官方源</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>推荐镜像源</h6>
                            <ul class="text-muted">
                                <li><strong>官方镜像:</strong> http://pvm.2sxo.com</li>
                                <li><strong>本地镜像:</strong> http://localhost:34403</li>
                            </ul>
                            
                            <h6>命令行管理</h6>
                            <p class="text-muted">
                                您也可以使用命令行管理PVM镜像源：
                            </p>
                            <pre class="bg-light p-2 rounded"><code>pvm pvm-mirror status
pvm pvm-mirror enable
pvm pvm-mirror set http://pvm.2sxo.com
pvm pvm-mirror test</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshPage() {
    location.reload();
}

function testMirror(url) {
    const statusElement = document.querySelector(`[data-url="${url}"] .mirror-status`);
    if (!statusElement) return;
    
    statusElement.innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split"></i> 测试中...</span>';
    
    fetch(`/api/test-pvm-mirror?url=${encodeURIComponent(url)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusElement.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> 正常 (${data.response_time}ms)</span>`;
            } else {
                statusElement.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle"></i> 失败</span>`;
            }
        })
        .catch(error => {
            statusElement.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> 测试失败</span>';
        });
}

function testAllMirrors() {
    const mirrors = <?= json_encode($allMirrors) ?>;
    mirrors.forEach(mirror => {
        testMirror(mirror);
    });
}

// 页面加载完成后自动测试所有镜像源
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(testAllMirrors, 1000);
});
</script>

<?php
// 获取输出内容
$content = ob_get_clean();

// 包含布局模板
include __DIR__ . '/layout.php';
?>
