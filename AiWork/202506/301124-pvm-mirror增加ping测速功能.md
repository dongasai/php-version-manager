# PVM-Mirror 增加 /ping 测速功能

**时间**: 2025年06月30日 11:24  
**任务**: 为pvm-mirror增加 `/ping` 测速功能，支持镜像源测速选择

## 工作内容

### 1. 为pvm-mirror添加 `/ping` 端点

#### 修改Web控制器
- **文件**: `srcMirror/Web/Controller.php`
- **新增路由**: 添加 `/ping` 和 `/ping/` 路由处理
- **新增方法**: `handlePingRequest()` 方法

#### ping端点功能特性
- **响应格式**: 纯文本键值对格式，便于解析
- **响应内容**:
  - `pong` - 基本响应确认
  - `server=pvm-mirror` - 服务器标识
  - `version=1.0.0` - 版本信息
  - `status=online` - 服务状态
  - `timestamp` - 时间戳
  - `datetime` - 可读时间
  - `response_time_ms` - 响应时间（毫秒）
  - `php_versions` - PHP版本数量
  - `pecl_extensions` - PECL扩展数量
  - `load_avg` - 系统负载
  - `memory_usage_mb` - 内存使用量
  - `end` - 结束标记

#### 优化性能
- **文件**: `srcMirror/Mirror/MirrorStatus.php`
- **新增方法**: `getBasicStatus()` - 快速获取基本状态信息
- **避免耗时操作**: 不计算文件大小和详细信息，只统计数量

### 2. 创建镜像源测速类

#### 核心功能类
- **文件**: `src/Core/Download/MirrorSpeedTest.php`
- **命名空间**: `VersionManager\Core\Download\MirrorSpeedTest`

#### 主要功能
1. **智能测速**: 使用wget下载 `/ping` 端点进行测速
2. **缓存机制**: 测速结果缓存1天，避免重复测速
3. **自动排序**: 按响应时间排序镜像源
4. **错误处理**: 失败的镜像源排在最后
5. **缓存管理**: 支持缓存验证、清理和信息查询

#### 技术实现要点
- **测速方法**: `wget --quiet --timeout=10 --tries=1 --output-document=- URL/ping`
- **缓存位置**: `~/.pvm/cache/mirror_speed.cache`
- **缓存格式**: JSON格式存储测速结果
- **缓存验证**: 检查镜像源列表是否变化
- **响应验证**: 验证ping响应是否以 `pong` 开头

### 3. 功能测试验证

#### 测试结果
- **本地镜像源**: `http://localhost:34403` - 14.89ms ✅
- **默认镜像源**: `https://pvm.2sxo.com` - 超时 ❌
- **测试镜像源**: `https://httpbin.org` - 超时 ❌（预期，无ping端点）

#### 缓存机制验证
- **首次测速**: 1423.47ms（包含网络请求）
- **缓存测速**: 0.05ms（使用缓存）
- **缓存命中率**: 100%

### 4. 代码结构

#### ping端点实现
```php
public function handlePingRequest()
{
    header('Content-Type: text/plain; charset=utf-8');
    $startTime = microtime(true);
    
    // 获取基本状态信息
    $status = $this->status->getBasicStatus();
    $responseTime = round((microtime(true) - $startTime) * 1000, 2);
    
    // 输出标准化响应
    echo "pong\n";
    echo "server=pvm-mirror\n";
    echo "version=1.0.0\n";
    echo "status=online\n";
    echo "response_time_ms={$responseTime}\n";
    // ... 其他信息
    echo "end\n";
}
```

#### 测速类核心方法
```php
public function getOptimalMirrors($mirrors)
{
    // 检查缓存
    if ($this->isCacheValid()) {
        return $this->loadFromCache();
    }
    
    // 执行测速
    $results = $this->testMirrorSpeeds($mirrors);
    
    // 缓存结果
    $this->saveToCache($results);
    
    return $results;
}
```

## 技术特点

### 1. 高性能设计
- ping端点响应时间 < 20ms
- 避免耗时的文件系统操作
- 缓存机制减少重复测速

### 2. 标准化接口
- 统一的ping响应格式
- 便于解析的键值对输出
- 包含完整的服务器状态信息

### 3. 智能缓存
- 1天TTL自动过期
- 镜像源列表变化时自动失效
- 支持手动清理和状态查询

### 4. 错误处理
- 网络超时自动处理
- 无效响应自动过滤
- 失败镜像源降级处理

## 下一步计划

1. **集成到下载管理器**: 修改UrlManager使用测速结果
2. **添加命令行接口**: 提供手动测速命令
3. **更新配置系统**: 默认启用镜像源模式
4. **完善错误处理**: 增强网络异常处理

## 验证命令

```bash
# 测试ping端点
curl -s http://localhost:34403/ping

# 使用wget测试
wget -q -O - http://localhost:34403/ping

# 查看缓存文件
cat ~/.pvm/cache/mirror_speed.cache
```

## 总结

成功为pvm-mirror添加了完整的ping测速功能，包括：
- 高性能的ping端点实现
- 智能的镜像源测速类
- 完善的缓存机制
- 标准化的响应格式

这为后续的下载机制改造奠定了坚实的基础，确保PVM能够智能选择最优镜像源进行下载。
