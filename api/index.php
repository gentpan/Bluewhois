<?php
// API 路由处理：api/qq.com -> 返回 JSON
// 此文件专门用于 API 调用，始终返回 JSON 格式
include_once __DIR__ . '/../whois.php';

/**
 * API 路由统一 JSON 输出
 */
function api_output_json($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

// 新增路由：/api/ip/{ip} -> 返回 ip.sb 信息
$matchedIp = null;
if (preg_match('#^/api/ip/([^/]+)/?$#i', $requestPath, $m)) {
    $matchedIp = urldecode((string)$m[1]);
} elseif (isset($_GET['mode']) && $_GET['mode'] === 'ipgeo' && !empty($_GET['ip'])) {
    $matchedIp = (string)$_GET['ip'];
}

if ($matchedIp !== null) {
    $ip = dl_normalizeQueryTarget($matchedIp);
    if (!validateIP($ip)) {
        api_output_json([
            'success' => false,
            'error' => 'IP 格式不正确（仅支持 IPv4/IPv6）',
        ], 400);
    }

    $url = 'https://api.ip.sb/geoip/' . rawurlencode($ip);
    $res = makeApiCall($url, ['Accept: application/json']);
    if (!empty($res['error']) || (int)$res['http_code'] !== 200 || empty($res['response'])) {
        api_output_json([
            'success' => false,
            'error' => 'ip.sb 查询失败',
            'ip' => $ip,
        ], 502);
    }

    $decoded = json_decode($res['response'], true);
    if (!is_array($decoded)) {
        api_output_json([
            'success' => false,
            'error' => 'ip.sb 返回格式错误',
            'ip' => $ip,
        ], 502);
    }

    $geo = dl_normalizeIpGeo($decoded, 'ip.sb');
    if (isset($geo['error'])) {
        api_output_json([
            'success' => false,
            'error' => $geo['error'],
            'ip' => $ip,
        ], 502);
    }

    api_output_json([
        'success' => true,
        'mode' => 'ip_geo',
        'provider' => 'ip.sb',
        'ip' => $ip,
        'data' => $geo,
    ]);
}

// 使用通用函数从 API 路径提取查询目标（/api/{target}）
$domain = extractDomainFromApiPath();

// 验证域名
if (empty($domain)) {
    api_output_json([
        'success' => false, 
        'error' => '请提供查询目标。格式: /api/{target} 或 /api/ip/{ip}'
    ], 400);
}

// 设置GET参数以兼容现有代码
$_GET['domain'] = $domain;

// 强制设置为 API 模式，确保返回 JSON
$_GET['mode'] = 'api';

// 调用API处理函数（会检测请求头，但 API 路由总是返回 JSON）
whois_handle_api();
exit;
?>
