<?php

namespace VersionManager\Core\Security;

/**
 * 签名验证类
 *
 * 负责验证下载文件的签名
 */
class SignatureVerifier
{
    /**
     * 是否启用签名验证
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * 签名验证失败时是否严格模式
     *
     * 严格模式下，验证失败会抛出异常；非严格模式下，验证失败只会发出警告
     *
     * @var bool
     */
    private $strict = true;

    /**
     * 签名公钥目录
     *
     * @var string
     */
    private $keyDir;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->keyDir = getenv('HOME') . '/.pvm/keys';

        // 确保目录存在
        if (!is_dir($this->keyDir)) {
            mkdir($this->keyDir, 0755, true);
        }
    }

    /**
     * 设置是否启用签名验证
     *
     * @param bool $enabled 是否启用
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * 设置是否严格模式
     *
     * @param bool $strict 是否严格模式
     * @return $this
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;
        return $this;
    }

    /**
     * 验证PHP下载文件签名
     *
     * @param string $filePath 文件路径
     * @param string $version PHP版本
     * @return bool 是否验证成功
     * @throws \Exception 验证失败时抛出异常（严格模式下）
     */
    public function verifyPhpSignature($filePath, $version)
    {
        if (!$this->enabled) {
            return true;
        }

        // 获取签名文件
        $signatureFile = $filePath . '.asc';
        if (!file_exists($signatureFile)) {
            // 尝试下载签名文件
            $this->downloadPhpSignature($filePath, $version);
        }

        // 检查签名文件是否存在
        if (!file_exists($signatureFile)) {
            return $this->handleVerificationFailure("无法获取PHP {$version} 的签名文件");
        }

        // 获取PHP发布公钥
        $publicKey = $this->getPhpPublicKey();
        if (!$publicKey) {
            return $this->handleVerificationFailure("无法获取PHP发布公钥");
        }

        // 验证签名
        return $this->verifyGpgSignature($filePath, $signatureFile, $publicKey);
    }

    /**
     * 验证扩展下载文件签名
     *
     * @param string $filePath 文件路径
     * @param string $extension 扩展名称
     * @param string $version 扩展版本
     * @return bool 是否验证成功
     * @throws \Exception 验证失败时抛出异常（严格模式下）
     */
    public function verifyExtensionSignature($filePath, $extension, $version)
    {
        if (!$this->enabled) {
            return true;
        }

        // 获取签名文件
        $signatureFile = $filePath . '.asc';
        if (!file_exists($signatureFile)) {
            // 尝试下载签名文件
            $this->downloadExtensionSignature($filePath, $extension, $version);
        }

        // 检查签名文件是否存在
        if (!file_exists($signatureFile)) {
            return $this->handleVerificationFailure("无法获取扩展 {$extension} {$version} 的签名文件");
        }

        // 获取PECL发布公钥
        $publicKey = $this->getPeclPublicKey();
        if (!$publicKey) {
            return $this->handleVerificationFailure("无法获取PECL发布公钥");
        }

        // 验证签名
        return $this->verifyGpgSignature($filePath, $signatureFile, $publicKey);
    }

    /**
     * 验证GPG签名
     *
     * @param string $filePath 文件路径
     * @param string $signatureFile 签名文件路径
     * @param string $publicKey 公钥文件路径
     * @return bool 是否验证成功
     * @throws \Exception 验证失败时抛出异常（严格模式下）
     */
    private function verifyGpgSignature($filePath, $signatureFile, $publicKey)
    {
        // 检查GPG是否可用
        if (!$this->isGpgAvailable()) {
            return $this->handleVerificationFailure("GPG不可用，无法验证签名");
        }

        // 验证签名
        $command = "gpg --no-default-keyring --keyring {$publicKey} --verify {$signatureFile} {$filePath} 2>&1";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMessage = implode("\n", $output);
            return $this->handleVerificationFailure("签名验证失败: {$errorMessage}");
        }

        return true;
    }

    /**
     * 下载PHP签名文件
     *
     * @param string $filePath 文件路径
     * @param string $version PHP版本
     * @return bool 是否下载成功
     */
    private function downloadPhpSignature($filePath, $version)
    {
        $signatureUrl = "https://www.php.net/distributions/php-{$version}.tar.gz.asc";
        $signatureFile = $filePath . '.asc';

        return $this->downloadFile($signatureUrl, $signatureFile);
    }

    /**
     * 下载扩展签名文件
     *
     * @param string $filePath 文件路径
     * @param string $extension 扩展名称
     * @param string $version 扩展版本
     * @return bool 是否下载成功
     */
    private function downloadExtensionSignature($filePath, $extension, $version)
    {
        $signatureUrl = "https://pecl.php.net/get/{$extension}-{$version}.tgz.asc";
        $signatureFile = $filePath . '.asc';

        return $this->downloadFile($signatureUrl, $signatureFile);
    }

    /**
     * 下载文件
     *
     * @param string $url 文件URL
     * @param string $destination 目标路径
     * @return bool 是否下载成功
     */
    private function downloadFile($url, $destination)
    {
        // 检查curl扩展是否可用
        if (!function_exists('curl_init')) {
            // 如果没有curl扩展，尝试使用file_get_contents
            if (function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "User-Agent: PHP Version Manager\r\n",
                        'timeout' => 30,
                    ]
                ]);

                $content = @file_get_contents($url, false, $context);

                if ($content === false) {
                    return false;
                }

                if (file_put_contents($destination, $content) === false) {
                    return false;
                }

                return true;
            }

            // 如果也不支持file_get_contents或allow_url_fopen关闭，则尝试使用系统命令
            $command = "wget -q -O {$destination} {$url} 2>/dev/null || curl -s -o {$destination} {$url} 2>/dev/null";
            $returnCode = 0;
            system($command, $returnCode);

            if ($returnCode !== 0 || !file_exists($destination) || filesize($destination) === 0) {
                if (file_exists($destination)) {
                    unlink($destination);
                }
                return false;
            }

            return true;
        }

        // 使用curl下载文件
        $ch = curl_init($url);
        $fp = fopen($destination, 'w');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Version Manager');

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        fclose($fp);

        // 如果下载失败，删除文件
        if (!$success || $httpCode !== 200) {
            if (file_exists($destination)) {
                unlink($destination);
            }
            return false;
        }

        return true;
    }

    /**
     * 获取PHP发布公钥
     *
     * @return string|false 公钥文件路径，如果获取失败则返回false
     */
    private function getPhpPublicKey()
    {
        $keyFile = $this->keyDir . '/php-keyring.gpg';

        // 如果公钥文件不存在，则下载
        if (!file_exists($keyFile)) {
            // 下载PHP发布公钥
            $keyUrl = 'https://www.php.net/gpg-keys.php';
            $tempFile = $this->keyDir . '/php-keys.asc';

            if (!$this->downloadFile($keyUrl, $tempFile)) {
                return false;
            }

            // 导入公钥
            $command = "gpg --no-default-keyring --keyring {$keyFile} --import {$tempFile} 2>&1";
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // 删除临时文件
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            if ($returnCode !== 0) {
                return false;
            }
        }

        return $keyFile;
    }

    /**
     * 获取PECL发布公钥
     *
     * @return string|false 公钥文件路径，如果获取失败则返回false
     */
    private function getPeclPublicKey()
    {
        $keyFile = $this->keyDir . '/pecl-keyring.gpg';

        // 如果公钥文件不存在，则下载
        if (!file_exists($keyFile)) {
            // 下载PECL发布公钥
            $keyUrl = 'https://pecl.php.net/public.key';
            $tempFile = $this->keyDir . '/pecl-keys.asc';

            if (!$this->downloadFile($keyUrl, $tempFile)) {
                return false;
            }

            // 导入公钥
            $command = "gpg --no-default-keyring --keyring {$keyFile} --import {$tempFile} 2>&1";
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // 删除临时文件
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            if ($returnCode !== 0) {
                return false;
            }
        }

        return $keyFile;
    }

    /**
     * 检查GPG是否可用
     *
     * @return bool
     */
    private function isGpgAvailable()
    {
        $command = "which gpg";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * 处理验证失败
     *
     * @param string $message 错误消息
     * @return bool 始终返回false
     * @throws \Exception 验证失败时抛出异常（严格模式下）
     */
    private function handleVerificationFailure($message)
    {
        if ($this->strict) {
            throw new \Exception($message);
        } else {
            echo "\033[33m警告: {$message}\033[0m\n";
            return false;
        }
    }
}
