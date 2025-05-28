<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->escape($title) : 'PVM 管理面板' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            width: 16.66667%; /* col-md-3 col-lg-2 的宽度 */
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        @media (min-width: 768px) {
            .sidebar {
                width: 25%; /* col-md-3 */
            }
        }

        @media (min-width: 992px) {
            .sidebar {
                width: 16.66667%; /* col-lg-2 */
            }
        }

        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }

        .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }

        .navbar .form-control {
            padding: .75rem 1rem;
            border-width: 0;
            border-radius: 0;
        }

        .form-control-dark {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .1);
        }

        .form-control-dark:focus {
            border-color: transparent;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .main-content {
            margin-top: 48px;
            margin-left: 16.66667%; /* 与侧边栏宽度匹配 */
            flex: 1;
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .main-content {
                margin-left: 25%; /* col-md-3 */
            }
        }

        @media (min-width: 992px) {
            .main-content {
                margin-left: 16.66667%; /* col-lg-2 */
            }
        }

        @media (max-width: 767px) {
            .main-content {
                margin-left: 0; /* 在小屏幕上不需要左边距 */
            }

            .sidebar {
                position: fixed;
                top: 48px;
                left: -100%;
                width: 280px;
                transition: left 0.3s ease-in-out;
                z-index: 1000;
            }

            .sidebar.show {
                left: 0;
            }
        }

        .footer {
            padding: 1rem 0;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            margin-left: 16.66667%; /* 与侧边栏宽度匹配 */
        }

        @media (min-width: 768px) {
            .footer {
                margin-left: 25%; /* col-md-3 */
            }
        }

        @media (min-width: 992px) {
            .footer {
                margin-left: 16.66667%; /* col-lg-2 */
            }
        }

        @media (max-width: 767px) {
            .footer {
                margin-left: 0; /* 在小屏幕上不需要左边距 */
            }
        }

        .nav-link {
            color: #333;
        }

        .nav-link.active {
            color: #007bff;
            font-weight: bold;
        }

        .card {
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: bold;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.075);
        }

        .version-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            margin-right: 5px;
        }

        .progress {
            height: 5px;
            margin-bottom: 10px;
        }

        .stats-card {
            text-align: center;
            padding: 15px;
        }

        .stats-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #007bff;
        }

        .stats-card .stats-value {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .stats-card .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="/">PVM 管理面板</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" onclick="toggleSidebar()" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>

        <!-- 权限状态提示 -->
        <?php if (isset($privilegeStatus)): ?>
            <div class="navbar-nav me-3">
                <div class="nav-item">
                    <?php if ($privilegeStatus === 'limited'): ?>
                        <span class="navbar-text text-warning">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <small>权限受限</small>
                        </span>
                    <?php elseif ($privilegeStatus === 'sudo'): ?>
                        <span class="navbar-text text-info">
                            <i class="bi bi-shield-check me-1"></i>
                            <small>Sudo权限</small>
                        </span>
                    <?php else: ?>
                        <span class="navbar-text text-success">
                            <i class="bi bi-shield-fill me-1"></i>
                            <small>管理员权限</small>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="/logout">退出</a>
            </div>
        </div>
    </header>

    <nav id="sidebarMenu" class="d-md-block bg-light sidebar collapse">
        <div class="position-sticky pt-3 sidebar-sticky">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'dashboard' ? 'active' : '' ?>" href="/">
                        <i class="bi bi-speedometer2 me-2"></i>
                        仪表盘
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'versions' ? 'active' : '' ?>" href="/versions">
                        <i class="bi bi-code-slash me-2"></i>
                        版本管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'extensions' ? 'active' : '' ?>" href="/extensions">
                        <i class="bi bi-puzzle me-2"></i>
                        扩展管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'composer' ? 'active' : '' ?>" href="/composer">
                        <i class="bi bi-box me-2"></i>
                        Composer管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'pvm-mirror' ? 'active' : '' ?>" href="/pvm-mirror">
                        <i class="bi bi-cloud-download me-2"></i>
                        PVM镜像源
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'monitor' ? 'active' : '' ?>" href="/monitor">
                        <i class="bi bi-graph-up me-2"></i>
                        状态监控
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($active) && $active === 'settings' ? 'active' : '' ?>" href="/settings">
                        <i class="bi bi-gear me-2"></i>
                        设置
                    </a>
                </li>
            </ul>

            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>快速操作</span>
            </h6>
            <ul class="nav flex-column mb-2">
                <li class="nav-item">
                    <a class="nav-link" href="/actions/restart-fpm">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        重启PHP-FPM
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/actions/clear-cache">
                        <i class="bi bi-trash me-2"></i>
                        清除缓存
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <?= $content ?>
    </main>

    <footer class="footer mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> PHP Version Manager</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="https://github.com/dongasai/php-version-manager" target="_blank">
                            <i class="bi bi-github me-1"></i>GitHub
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.0/dist/chart.umd.min.js"></script>
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $this->escape($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // 切换侧边栏显示/隐藏
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        }

        // 点击主内容区域时隐藏侧边栏（仅在移动设备上）
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const sidebar = document.getElementById('sidebarMenu');

            if (mainContent && sidebar) {
                mainContent.addEventListener('click', function() {
                    if (window.innerWidth <= 767) {
                        sidebar.classList.remove('show');
                    }
                });
            }
        });
    </script>

    <?php if (isset($inlineScripts)): ?>
        <script>
            <?= $inlineScripts ?>
        </script>
    <?php endif; ?>
</body>
</html>
