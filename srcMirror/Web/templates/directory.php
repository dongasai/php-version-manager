<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-folder-open"></i> 目录内容</h5>
            <div>
                <a href="#" class="btn btn-sm btn-outline-secondary" id="toggleView">
                    <i class="fas fa-th-large"></i> 切换视图
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- 表格视图 -->
        <div id="tableView">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>文件名</th>
                            <th>大小</th>
                            <th>修改时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($path)): ?>
                        <tr>
                            <td>
                                <a href="/<?= dirname($path) ?>" class="text-primary">
                                    <i class="fas fa-level-up-alt"></i> 上级目录
                                </a>
                            </td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($files as $file): ?>
                            <?php
                                $fullPath = $filePath . '/' . $file;
                                $isDir = is_dir($fullPath);
                                $size = $isDir ? '-' : $formatSize(filesize($fullPath));
                                $modTime = date('Y-m-d H:i:s', filemtime($fullPath));
                                $fileExt = pathinfo($file, PATHINFO_EXTENSION);

                                // 确定文件图标
                                $icon = 'fas fa-file';
                                if ($isDir) {
                                    $icon = 'fas fa-folder';
                                } elseif (in_array($fileExt, ['php', 'phar'])) {
                                    $icon = 'fab fa-php';
                                } elseif (in_array($fileExt, ['zip', 'gz', 'tar', 'bz2', 'xz'])) {
                                    $icon = 'fas fa-file-archive';
                                } elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                                    $icon = 'fas fa-file-image';
                                } elseif (in_array($fileExt, ['txt', 'md', 'log'])) {
                                    $icon = 'fas fa-file-alt';
                                }
                            ?>
                            <tr>
                                <td>
                                    <a href="/<?= $path ?>/<?= $file ?>" class="<?= $isDir ? 'text-primary' : 'text-dark' ?>">
                                        <i class="<?= $icon ?>"></i> <?= $file ?><?= $isDir ? '/' : '' ?>
                                    </a>
                                </td>
                                <td><?= $size ?></td>
                                <td><?= $modTime ?></td>
                                <td>
                                    <a href="/<?= $path ?>/<?= $file ?>" class="btn btn-sm btn-outline-primary" title="下载">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 网格视图 -->
        <div id="gridView" class="p-3" style="display: none;">
            <div class="row">
                <?php if (!empty($path)): ?>
                <div class="col-md-2 col-sm-3 col-4 mb-3">
                    <a href="/<?= dirname($path) ?>" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center py-3">
                                <i class="fas fa-level-up-alt fa-2x text-primary mb-2"></i>
                                <p class="mb-0 text-truncate">上级目录</p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif; ?>

                <?php foreach ($files as $file): ?>
                    <?php
                        $fullPath = $filePath . '/' . $file;
                        $isDir = is_dir($fullPath);
                        $fileExt = pathinfo($file, PATHINFO_EXTENSION);

                        // 确定文件图标
                        $icon = 'fas fa-file';
                        $iconColor = 'text-secondary';

                        if ($isDir) {
                            $icon = 'fas fa-folder';
                            $iconColor = 'text-primary';
                        } elseif (in_array($fileExt, ['php', 'phar'])) {
                            $icon = 'fab fa-php';
                            $iconColor = 'text-info';
                        } elseif (in_array($fileExt, ['zip', 'gz', 'tar', 'bz2', 'xz'])) {
                            $icon = 'fas fa-file-archive';
                            $iconColor = 'text-warning';
                        } elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                            $icon = 'fas fa-file-image';
                            $iconColor = 'text-success';
                        } elseif (in_array($fileExt, ['txt', 'md', 'log'])) {
                            $icon = 'fas fa-file-alt';
                            $iconColor = 'text-dark';
                        }
                    ?>
                    <div class="col-md-2 col-sm-3 col-4 mb-3">
                        <a href="/<?= $path ?>/<?= $file ?>" class="text-decoration-none">
                            <div class="card h-100">
                                <div class="card-body text-center py-3">
                                    <i class="<?= $icon ?> fa-2x <?= $iconColor ?> mb-2"></i>
                                    <p class="mb-0 text-truncate"><?= $file ?><?= $isDir ? '/' : '' ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
// 添加内联脚本
$inline_scripts = <<<JS
$(document).ready(function() {
    // 切换视图
    $('#toggleView').click(function(e) {
        e.preventDefault();
        $('#tableView').toggle();
        $('#gridView').toggle();

        var icon = $(this).find('i');
        if (icon.hasClass('fa-th-large')) {
            icon.removeClass('fa-th-large').addClass('fa-list');
        } else {
            icon.removeClass('fa-list').addClass('fa-th-large');
        }
    });
});
JS;
?>
