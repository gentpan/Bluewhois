<?php
// 加载环境变量（如果存在 .env 文件）
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // 跳过注释
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key) && !empty($value)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// API Key 配置（仅从环境变量读取，不在代码中保留默认明文密钥）
$whoapi_key = $_ENV['WHOAPI_KEY'] ?? getenv('WHOAPI_KEY') ?? '';
$whoapi_key = is_string($whoapi_key) ? trim($whoapi_key) : '';
define('WHOAPI_KEY', $whoapi_key);

$whoisxml_key = $_ENV['WHOISXML_API_KEY'] ?? getenv('WHOISXML_API_KEY') ?? '';
$whoisxml_key = is_string($whoisxml_key) ? trim($whoisxml_key) : '';
define('WHOISXML_API_KEY', $whoisxml_key);
define('WHOISXML_API_ENDPOINT', 'https://www.whoisxmlapi.com/whoisserver/WhoisService');

// 缓存时长（秒）
define('CACHE_TTL', 3600);

// 缓存目录
define('CACHE_DIR', __DIR__ . '/cache/');
if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

// 错误日志
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/logs/error.log');
if (LOG_ERRORS && !is_dir(dirname(LOG_FILE))) {
    mkdir(dirname(LOG_FILE), 0755, true);
}
