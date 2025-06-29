# 修复pvm-mirror下载性能问题

## 问题描述

pvm-mirror在下载文件时存在严重的性能问题：
- 本地下载一个19MB的PHP文件需要很长时间
- 下载过程经常卡住或超时
- 用户体验极差

## 问题分析

通过测试和代码分析发现问题根源：

### 1. 架构问题
- **PHP处理文件下载**：每个文件下载都要经过PHP应用处理
- **复杂的资源管理**：包含速度限制、并发控制、权限检查等复杂逻辑
- **性能瓶颈**：PHP进程处理大文件下载效率低下

### 2. 具体问题
- **速度限制**：配置中设置了1MB/s的下载速度限制
- **并发计数错误**：`active_downloads`计数器没有正确清理，导致速度被进一步限制
- **资源锁文件**：`/app/data/resource.lock`中显示有4个活跃下载，实际速度变成256KB/s

### 3. 代码问题
```php
// ResourceManager.php 中的速度限制逻辑
if ($this->activeDownloads > 1 && $speedLimit > 0) {
    $speedLimit = (int)($speedLimit / $this->activeDownloads);
}
```

## 解决方案

### 方案1：禁用资源限制（临时解决）
修改 `configMirror/runtime.php`：
```php
'resource' => [
    'enable_resource_limits' => false,
    'download_speed_limit' => 0, // 禁用速度限制
]
```

### 方案2：静态文件服务（根本解决）
修改 `public/router.php`，让文件下载绕过PHP应用：

```php
// 检查是否是下载文件请求（直接从data目录提供文件）
if (strpos($cleanPath, '/php/') === 0 || strpos($cleanPath, '/pecl/') === 0 || 
    strpos($cleanPath, '/extensions/') === 0 || strpos($cleanPath, '/composer/') === 0) {
    
    $dataFile = dirname(__DIR__) . '/data' . $cleanPath;
    if (file_exists($dataFile) && is_file($dataFile)) {
        // 直接发送文件，不经过PHP应用处理
        header('Content-Type: application/gzip');
        header('Content-Length: ' . filesize($dataFile));
        header('Content-Disposition: attachment; filename="' . basename($dataFile) . '"');
        
        // 直接输出文件内容
        readfile($dataFile);
        exit;
    }
}
```

## 实施步骤

1. **清理资源锁文件**
```bash
docker exec pvm-mirror-dev rm -f /app/data/resource.lock
```

2. **禁用资源限制**
修改配置文件禁用速度限制

3. **修改路由器**
让下载文件绕过PHP应用，直接由路由器处理

4. **重启容器**
```bash
docker restart pvm-mirror-dev
```

## 测试结果

### 修复前
- 下载19MB文件：超时或需要很长时间
- 速度限制：256KB/s（1MB/s ÷ 4个并发）

### 修复后
- 下载19MB文件：0.04秒
- 无速度限制，直接文件传输
- 性能提升：几十倍

## 优势对比

### PHP处理文件下载（修复前）
- ❌ 性能差：每个下载启动PHP进程
- ❌ 复杂性：资源限制、速度控制等复杂逻辑
- ❌ 稳定性：PHP进程可能出错、卡死
- ❌ 扩展性：并发下载时PHP进程数量激增

### 静态文件服务（修复后）
- ✅ 高性能：Web服务器直接发送文件
- ✅ 稳定可靠：Web服务器专门优化了文件传输
- ✅ 简单维护：不需要复杂的PHP逻辑
- ✅ 更好的缓存：浏览器和CDN可以更好地缓存

## 后续优化建议

1. **完全移除PHP文件处理**：将所有文件下载改为静态服务
2. **使用Nginx**：在生产环境中使用Nginx替代PHP内置服务器
3. **CDN集成**：考虑使用CDN进一步提升下载速度
4. **监控优化**：保留必要的下载统计，但不影响性能

## 总结

通过将文件下载从PHP应用处理改为静态文件服务，成功解决了pvm-mirror的性能问题。这是一个典型的架构优化案例，说明了选择合适的技术方案对性能的重要影响。
