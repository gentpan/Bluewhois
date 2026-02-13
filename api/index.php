<?php
// API 路由处理：api/qq.com -> 返回 JSON
// 此文件专门用于 API 调用，始终返回 JSON 格式
include_once __DIR__ . '/../whois.php';

// 使用通用函数从 API 路径提取域名
$domain = extractDomainFromApiPath();

// 验证域名
if (empty($domain)) {
    http_response_code(400);
    // API 路由始终返回 JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'error' => '请提供域名。格式: /api/domain.com'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 设置GET参数以兼容现有代码
$_GET['domain'] = $domain;

// 强制设置为 API 模式，确保返回 JSON
$_GET['mode'] = 'api';

// 调用API处理函数（会检测请求头，但 API 路由总是返回 JSON）
whois_handle_api();
exit;
?>
