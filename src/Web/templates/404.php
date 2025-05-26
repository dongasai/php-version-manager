<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <div class="error-code">
                    <h1 class="display-1 text-primary">404</h1>
                </div>
                <div class="error-message">
                    <h2 class="h3 mb-3">页面未找到</h2>
                    <p class="text-muted mb-4">
                        抱歉，您访问的页面不存在或已被移动。
                    </p>
                </div>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary me-2">
                        <i class="bi bi-house"></i> 返回首页
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                        <i class="bi bi-arrow-left"></i> 返回上页
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    padding: 60px 0;
}

.error-code h1 {
    font-size: 8rem;
    font-weight: bold;
    margin-bottom: 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.error-message h2 {
    color: #333;
}

.error-actions {
    margin-top: 30px;
}

@media (max-width: 768px) {
    .error-code h1 {
        font-size: 6rem;
    }
    
    .error-page {
        padding: 30px 0;
    }
}
</style>
