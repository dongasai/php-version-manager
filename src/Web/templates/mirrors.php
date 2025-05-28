<?php
/**
 * 镜像管理页面模板
 */

// 设置活动菜单项
$active = 'mirrors';

// 开始输出缓冲
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">镜像管理</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshMirrors()">
                            <i class="bi bi-arrow-clockwise"></i> 刷新
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
        <!-- PHP镜像配置 -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-code-slash me-2"></i>PHP 镜像
                    </h5>
                    <span class="badge bg-primary">当前: <?= array_search($currentPhpMirror, $phpMirrors) ?: 'unknown' ?></span>
                </div>
                <div class="card-body">
                    <form method="post" action="/actions/set-mirror">
                        <input type="hidden" name="type" value="php">
                        <div class="mb-3">
                            <label for="phpMirror" class="form-label">选择镜像源</label>
                            <select class="form-select" id="phpMirror" name="mirror" required>
                                <?php foreach ($phpMirrors as $name => $url): ?>
                                    <option value="<?= htmlspecialchars($name) ?>" 
                                            <?= $url === $currentPhpMirror ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?> 
                                        <?php if ($name === 'official'): ?>
                                            (官方)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                当前地址: <code><?= htmlspecialchars($currentPhpMirror) ?></code>
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-circle"></i> 设置为默认
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PECL镜像配置 -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-puzzle me-2"></i>PECL 镜像
                    </h5>
                    <span class="badge bg-success">当前: <?= array_search($currentPeclMirror, $peclMirrors) ?: 'unknown' ?></span>
                </div>
                <div class="card-body">
                    <form method="post" action="/actions/set-mirror">
                        <input type="hidden" name="type" value="pecl">
                        <div class="mb-3">
                            <label for="peclMirror" class="form-label">选择镜像源</label>
                            <select class="form-select" id="peclMirror" name="mirror" required>
                                <?php foreach ($peclMirrors as $name => $url): ?>
                                    <option value="<?= htmlspecialchars($name) ?>" 
                                            <?= $url === $currentPeclMirror ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?> 
                                        <?php if ($name === 'official'): ?>
                                            (官方)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                当前地址: <code><?= htmlspecialchars($currentPeclMirror) ?></code>
                            </small>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check-circle"></i> 设置为默认
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Composer镜像配置 -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box me-2"></i>Composer 镜像
                    </h5>
                    <span class="badge bg-warning">当前: <?= array_search($currentComposerMirror, $composerMirrors) ?: 'unknown' ?></span>
                </div>
                <div class="card-body">
                    <form method="post" action="/actions/set-mirror">
                        <input type="hidden" name="type" value="composer">
                        <div class="mb-3">
                            <label for="composerMirror" class="form-label">选择镜像源</label>
                            <select class="form-select" id="composerMirror" name="mirror" required>
                                <?php foreach ($composerMirrors as $name => $url): ?>
                                    <option value="<?= htmlspecialchars($name) ?>" 
                                            <?= $url === $currentComposerMirror ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?> 
                                        <?php if ($name === 'official'): ?>
                                            (官方)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                当前地址: <code><?= htmlspecialchars($currentComposerMirror) ?></code>
                            </small>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="bi bi-check-circle"></i> 设置为默认
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 镜像测试 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>镜像速度测试
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">测试各个镜像源的连接速度，帮助选择最快的镜像。</p>
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary" onclick="testMirrorSpeed('php')">
                                <i class="bi bi-play-circle"></i> 测试 PHP 镜像
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-success" onclick="testMirrorSpeed('pecl')">
                                <i class="bi bi-play-circle"></i> 测试 PECL 镜像
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-warning" onclick="testMirrorSpeed('composer')">
                                <i class="bi bi-play-circle"></i> 测试 Composer 镜像
                            </button>
                        </div>
                    </div>
                    <div id="speedTestResults" class="mt-3" style="display: none;">
                        <h6>测试结果:</h6>
                        <div id="speedTestContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 自定义镜像 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>添加自定义镜像
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/actions/add-mirror">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="mirrorType" class="form-label">镜像类型</label>
                                <select class="form-select" id="mirrorType" name="type" required>
                                    <option value="php">PHP</option>
                                    <option value="pecl">PECL</option>
                                    <option value="composer">Composer</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="mirrorName" class="form-label">镜像名称</label>
                                <input type="text" class="form-control" id="mirrorName" name="name" 
                                       placeholder="例如: custom" required>
                            </div>
                            <div class="col-md-4">
                                <label for="mirrorUrl" class="form-label">镜像地址</label>
                                <input type="url" class="form-control" id="mirrorUrl" name="url" 
                                       placeholder="https://example.com/mirror" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="bi bi-plus"></i> 添加
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshMirrors() {
    location.reload();
}

function testMirrorSpeed(type) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> 测试中...';
    button.disabled = true;
    
    fetch(`/api/test-mirror-speed?type=${type}`)
        .then(response => response.json())
        .then(data => {
            displaySpeedTestResults(type, data);
        })
        .catch(error => {
            console.error('测试失败:', error);
            alert('镜像速度测试失败');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

function displaySpeedTestResults(type, results) {
    const resultsDiv = document.getElementById('speedTestResults');
    const contentDiv = document.getElementById('speedTestContent');
    
    let html = `<h6>${type.toUpperCase()} 镜像速度测试结果:</h6>`;
    html += '<div class="table-responsive">';
    html += '<table class="table table-sm">';
    html += '<thead><tr><th>镜像</th><th>响应时间</th><th>状态</th></tr></thead>';
    html += '<tbody>';
    
    for (const [name, result] of Object.entries(results)) {
        const statusClass = result.success ? 'text-success' : 'text-danger';
        const statusIcon = result.success ? 'bi-check-circle' : 'bi-x-circle';
        html += `<tr>
            <td>${name}</td>
            <td>${result.time || 'N/A'}</td>
            <td class="${statusClass}"><i class="bi ${statusIcon}"></i> ${result.status}</td>
        </tr>`;
    }
    
    html += '</tbody></table></div>';
    
    contentDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
}
</script>

<?php
// 获取输出内容
$content = ob_get_clean();

// 包含布局模板
include __DIR__ . '/layout.php';
?>
