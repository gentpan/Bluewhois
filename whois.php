<?php
// WHOIS 查询入口文件：提供 API 与页面渲染
// 用法：
// - API:  whois.php?mode=api&domain=example.com  返回 JSON
// - 页面: whois.php?mode=page&domain=example.com 渲染结果页

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/function.php';

// ===== API 接口处理 =====
function whois_handle_api()
{
    if (!rateLimitAllow('api', 10, 8)) {
        http_response_code(429);
        $error_data = ['success' => false, 'error' => '请求过于频繁，请稍后重试'];
        whois_output_json($error_data);
        return;
    }
    $domain = dl_normalizeQueryTarget($_GET['domain'] ?? ($_GET['target'] ?? ($_GET['ip'] ?? '')));
    if (empty($domain)) {
        http_response_code(400);
        $error_data = ['success' => false, 'error' => '请输入要查询的域名或 IP'];
        whois_output_json($error_data);
        return;
    }
    if (!dl_validateQueryTarget($domain)) {
        http_response_code(400);
        $error_data = ['success' => false, 'error' => '输入格式不正确（仅支持域名、IPv4、IPv6）'];
        whois_output_json($error_data);
        return;
    }
    $forceRefresh = isset($_GET['refresh']) && (string)$_GET['refresh'] === '1';
    $result = dl_queryWhois($domain, $forceRefresh);
    if ($result['error']) {
        if (strpos($result['error'], '未注册') !== false) {
            http_response_code(404);
        } else {
            http_response_code(502);
        }
        $output_data = ['success' => false, 'error' => $result['error'], 'domain' => $domain];
    } elseif ($result['data']) {
        $verbose = isset($_GET['verbose']) && (string)$_GET['verbose'] === '1';
        $apiData = $result['data'];

        // 兼容旧缓存：对外不暴露 IP 情报源字段
        if (isset($apiData['whoapi_data']['ip_geo']) && is_array($apiData['whoapi_data']['ip_geo'])) {
            unset($apiData['whoapi_data']['ip_geo']['source']);
        }
        // 默认返回精简结构，避免超大 raw 文本影响可读性与带宽
        if (!$verbose) {
            unset($apiData['whois']);
            if (isset($apiData['whoapi_data']) && is_array($apiData['whoapi_data'])) {
                unset($apiData['whoapi_data']['whois_raw']);
            }
        }
        $output_data = [
            'success' => true,
            'data' => $apiData,
            'api_used' => $result['api_used'],
            'domain' => $domain,
            'cached' => !empty($result['cached']),
            'cache_time' => $result['cache_time'] ?? null
        ];
    } else {
        http_response_code(502);
        $output_data = ['success' => false, 'error' => '无法获取域名 WHOIS 信息', 'domain' => $domain];
    }
    whois_output_json($output_data);
}

/**
 * 输出美化的 JSON（检测浏览器访问时返回 HTML 页面，否则返回格式化 JSON）
 */
function whois_output_json($data)
{
    // 检查是否是通过 api/index.php 调用的（API 路由始终返回 JSON）
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $is_api_route = strpos($script_name, '/api/') !== false || 
                    strpos($_SERVER['PHP_SELF'] ?? '', '/api/') !== false ||
                    (isset($_GET['mode']) && $_GET['mode'] === 'api');
    
    $format = $_GET['format'] ?? '';
    
    // 检查 Accept 头，如果是 API 请求（期望 JSON），强制返回 JSON
    $accept_header = $_SERVER['HTTP_ACCEPT'] ?? '';
    $wants_json = strpos($accept_header, 'application/json') !== false;
    
    // 检查是否是 fetch/AJAX 请求（通常带有 X-Requested-With 或其他标识）
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // API 路由始终返回 JSON
    if ($is_api_route || $format === 'raw' || $wants_json || $is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return;
    }
    
    // 如果明确要求 HTML
    if ($format === 'html') {
        whois_output_json_html($data);
        return;
    }
    
    // 浏览器直接访问（地址栏输入），返回美化页面
    $is_browser = isset($_SERVER['HTTP_USER_AGENT']) && 
                  preg_match('/(Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)/i', $_SERVER['HTTP_USER_AGENT']);
    if ($is_browser && !$wants_json && !$is_ajax) {
        whois_output_json_html($data);
    } else {
        // 默认返回 JSON（安全起见）
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

/**
 * 输出 HTML 美化的 JSON 查看页面
 */
function whois_output_json_html($data)
{
    $json_str = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $json_escaped = htmlspecialchars($json_str, ENT_QUOTES, 'UTF-8');
    $basePath = getBasePath();
    
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueWhois API 响应 - JSON 查看器</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-actions {
            display: flex;
            gap: 12px;
        }
        .btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        .content {
            padding: 32px;
            background: #f8f9fa;
        }
        .json-container {
            background: #1e1e1e;
            border-radius: 8px;
            padding: 24px;
            overflow-x: auto;
            position: relative;
        }
        .json-code {
            color: #d4d4d4;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            white-space: pre;
            overflow-x: auto;
        }
        /* JSON 语法高亮 */
        .json-key {
            color: #9cdcfe;
        }
        .json-string {
            color: #ce9178;
        }
        .json-number {
            color: #b5cea8;
        }
        .json-boolean {
            color: #569cd6;
        }
        .json-null {
            color: #569cd6;
        }
        .json-punctuation {
            color: #d4d4d4;
        }
        .info-bar {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 16px 24px;
            margin-bottom: 24px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .info-bar .info-text {
            color: #1976d2;
            font-size: 14px;
        }
        .info-bar .info-link {
            color: #1976d2;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .info-bar .info-link:hover {
            text-decoration: underline;
        }
        .success-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #4caf50;
            color: white;
            margin-left: 12px;
        }
        .error-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #f44336;
            color: white;
            margin-left: 12px;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            .header-actions {
                width: 100%;
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-code"></i>
                BlueWhois API JSON 响应
                <?php if (isset($data['success']) && $data['success']): ?>
                    <span class="success-badge">成功</span>
                <?php else: ?>
                    <span class="error-badge">错误</span>
                <?php endif; ?>
            </h1>
            <div class="header-actions">
                <button class="btn" onclick="copyJson()">
                    <i class="fas fa-copy"></i> 复制 JSON
                </button>
                <button class="btn" onclick="downloadJson()">
                    <i class="fas fa-download"></i> 下载 JSON
                </button>
                <a href="?<?= http_build_query(array_merge($_GET, ['format' => 'raw'])) ?>" class="btn">
                    <i class="fas fa-file-code"></i> 原始 JSON
                </a>
            </div>
        </div>
        <div class="content">
            <div class="info-bar">
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    <strong>提示：</strong>这是 API 的美化视图。如需原始 JSON，请添加 <code>?format=raw</code> 参数或使用编程方式访问。
                </div>
                <?php if (isset($data['domain'])): ?>
                    <a href="../index.php?domain=<?= urlencode($data['domain']) ?>" class="info-link">
                        <i class="fas fa-external-link-alt"></i> 查看页面结果
                    </a>
                <?php endif; ?>
            </div>
            <div class="json-container">
                <pre class="json-code" id="json-code"><?= $json_escaped ?></pre>
            </div>
        </div>
    </div>
    <script>
        // 高亮 JSON
        function highlightJson() {
            const codeEl = document.getElementById('json-code');
            if (!codeEl) return;
            
            let text = codeEl.textContent;
            
            // 转义 HTML
            text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            // JSON 高亮处理（按顺序，从最具体到最通用）
            // 1. 高亮布尔值和 null（必须在数字之前，避免匹配）
            text = text.replace(/:(\s*)\b(true|false|null)\b/g, ':$1<span class="json-' + 
                (text.match(/:(\s*)\b(null)\b/) ? 'null' : 
                 text.match(/:(\s*)\b(true|false)\b/) ? 'boolean' : 'null') + 
                '">$2</span>');
            
            // 2. 高亮数字（必须在字符串之前）
            text = text.replace(/:(\s*)(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)\b/g, ':$1<span class="json-number">$2</span>');
            
            // 3. 高亮字符串值（冒号后的字符串）
            text = text.replace(/:(\s*)("(?:[^"\\]|\\.)*")/g, ':$1<span class="json-string">$2</span>');
            
            // 4. 高亮键名（冒号前的字符串）
            text = text.replace(/("(?:[^"\\]|\\.)*")(\s*):/g, '<span class="json-key">$1</span>$2:');
            
            // 5. 高亮标点符号（但要避免覆盖已有的 span）
            text = text.replace(/(?<!span[^>]*>)([{}[\],:])/g, '<span class="json-punctuation">$1</span>');
            
            // 如果上面的前瞻不支持，使用简单替换（但需要避免重复包装）
            if (text.indexOf('json-punctuation') === -1) {
                // 只在没有被包装的地方添加标点高亮
                text = text.replace(/([{}[\],:])/g, '<span class="json-punctuation">$1</span>');
            }
            
            codeEl.innerHTML = text;
        }
        
        // 更简单可靠的高亮方法
        function highlightJsonSimple() {
            const codeEl = document.getElementById('json-code');
            if (!codeEl) return;
            
            let html = codeEl.textContent;
            
            // 转义 HTML
            html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            // 使用标记来避免重复处理
            const MARKER = '\uE000'; // 私有使用区字符
            
            // 1. 先标记已处理的字符串（避免干扰后续处理）
            const strings = [];
            html = html.replace(/("(?:[^"\\]|\\.)*")/g, function(match) {
                strings.push(match);
                return MARKER + (strings.length - 1) + MARKER;
            });
            
            // 2. 高亮键名（标记后的，在冒号前）
            html = html.replace(/(MARKER\d+MARKER)(\s*):/g, function(match, marker) {
                const idx = parseInt(marker.replace(/MARKER/g, ''));
                return '<span class="json-key">' + strings[idx] + '</span>' + match.substring(marker.length + 1);
            });
            
            // 3. 恢复字符串并高亮字符串值（在冒号后）
            html = html.replace(/:(\s*)(MARKER(\d+)MARKER)/g, function(match, spaces, marker, idx) {
                return ':' + spaces + '<span class="json-string">' + strings[parseInt(idx)] + '</span>';
            });
            
            // 4. 恢复剩余的字符串（未被处理的）
            html = html.replace(/MARKER(\d+)MARKER/g, function(match, idx) {
                return strings[parseInt(idx)];
            });
            
            // 5. 高亮布尔值和 null
            html = html.replace(/\b(null|true|false)\b/g, function(match) {
                return '<span class="json-' + (match === 'null' ? 'null' : 'boolean') + '">' + match + '</span>';
            });
            
            // 6. 高亮数字（避免匹配已高亮的布尔值）
            html = html.replace(/(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)\b/g, function(match, num) {
                // 检查是否已经在 span 中
                const prev = html.substring(0, html.indexOf(match));
                const lastSpan = prev.lastIndexOf('<span');
                const lastClose = prev.lastIndexOf('</span>');
                if (lastSpan > lastClose) return match; // 已在 span 中
                return '<span class="json-number">' + match + '</span>';
            });
            
            // 7. 最后高亮标点符号（但避免在已有 span 内部）
            html = html.replace(/([{}\[\],:])/g, function(match, punct) {
                // 简单检查：如果前后没有 > 或 <，说明不在标签内，可以高亮
                const index = html.indexOf(match, arguments[arguments.length - 2] || 0);
                const before = html.substring(Math.max(0, index - 10), index);
                const after = html.substring(index + 1, Math.min(html.length, index + 11));
                // 如果前后都没有标签符号，可以安全高亮
                if (before.indexOf('<') === -1 && after.indexOf('>') === -1) {
                    return '<span class="json-punctuation">' + match + '</span>';
                }
                return match;
            });
            
            codeEl.innerHTML = html;
        }
        
        // 最简单可靠的高亮方法
        function highlightJsonFinal() {
            const codeEl = document.getElementById('json-code');
            if (!codeEl) return;
            
            let text = codeEl.textContent;
            
            // 转义 HTML
            text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            // 按顺序高亮，使用临时标记避免冲突
            // 1. 先处理键名（在冒号前的字符串）
            text = text.replace(/("(?:[^"\\]|\\.)*")(\s*):/g, '<span class="json-key">$1</span>$2:');
            
            // 2. 处理字符串值（冒号后的字符串，且未被包装）
            text = text.replace(/:(\s*)("(?:[^"\\]|\\.)*")/g, function(match, spaces, str) {
                // 检查是否已经在 span 中（作为键名的一部分）
                if (!match.includes('json-key')) {
                    return ':' + spaces + '<span class="json-string">' + str + '</span>';
                }
                return match;
            });
            
            // 3. 处理布尔值和 null
            text = text.replace(/\b(null|true|false)\b/g, function(match) {
                const cls = match === 'null' ? 'json-null' : 'json-boolean';
                return '<span class="' + cls + '">' + match + '</span>';
            });
            
            // 4. 处理数字（纯数字，不在字符串内）
            text = text.replace(/:(\s*)(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g, function(match, spaces, num) {
                // 如果不在已有 span 中
                if (!match.includes('json-string') && !match.includes('json-key')) {
                    return ':' + spaces + '<span class="json-number">' + num + '</span>';
                }
                return match;
            });
            
            // 5. 最后处理标点符号（不在已有标签内）
            text = text.replace(/([{}[\],])/g, function(match) {
                // 简单的检查：如果不在 span 标签内，就高亮
                return '<span class="json-punctuation">' + match + '</span>';
            });
            
            codeEl.innerHTML = text;
        }
        
        // 复制 JSON
        function copyJson() {
            const jsonStr = <?= json_encode($json_str, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(jsonStr).then(() => {
                    alert('JSON 已复制到剪贴板！');
                }).catch(err => {
                    console.error('复制失败:', err);
                    fallbackCopy(jsonStr);
                });
            } else {
                fallbackCopy(jsonStr);
            }
        }
        
        function fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('JSON 已复制到剪贴板！');
            } catch (err) {
                alert('复制失败，请手动选择复制');
            }
            document.body.removeChild(textArea);
        }
        
        // 下载 JSON
        function downloadJson() {
            const jsonStr = <?= json_encode($json_str, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            const blob = new Blob([jsonStr], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'api-response-' + new Date().getTime() + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        
        // 页面加载时高亮（使用最终优化方法）
        highlightJsonFinal();
    </script>
</body>
</html>
<?php
}

// ===== 页面渲染处理 =====
function whois_handle_page()
{
    if (!rateLimitAllow('search', 10, 8)) {
        http_response_code(429);
        die('错误：请求过于频繁，请稍后重试');
    }

    $domain = dl_normalizeQueryTarget($_GET['domain'] ?? '');
    if (empty($domain)) {
        http_response_code(400);
        die('错误：请输入要查询的域名或 IP');
    }
    if (!dl_validateQueryTarget($domain)) {
        http_response_code(400);
        logError("无效的域名输入: $domain");
        die('错误：输入格式不正确（仅支持域名、IPv4、IPv6）');
    }

    $result = dl_queryWhois($domain);
    $data = $result['data'];
    $error_msg = $result['error'];
    $api_used = $result['api_used'];

    $query_info = ['title' => 'WHOIS 查询结果', 'icon' => 'fas fa-globe', 'color' => 'blue'];
    $page_title = $query_info['title'];
    include __DIR__ . '/header.php';
?>
    <div class="search-container">
        <div class="search-mt-8">
            <?php $basePath = getBasePath(); ?>
            <a href="<?= $basePath ?>index.php" class="search-back-link" style="color: var(--accent-black);">
                <i class="fas fa-arrow-left"></i> 返回首页
            </a>
        </div>
        <div class="search-result-card">
            <div class="search-header">
                <div class="search-icon-wrapper <?= $query_info['color'] ?>">
                    <i class="<?= $query_info['icon'] ?>" style="font-size: 24px;"></i>
                </div>
                <div>
                    <h1 class="search-title"><?= htmlspecialchars($query_info['title']) ?></h1>
                    <div class="search-subtitle">
                        <span>查询目标: <strong><?= htmlspecialchars($domain) ?></strong></span>
                        <?php if ($api_used): ?><span>数据源: <strong><?= htmlspecialchars($api_used) ?></strong></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if ($error_msg): ?>
                <div class="search-error-box">
                    <i class="fas fa-exclamation-triangle search-error-icon"></i>
                    <div class="search-error-content">
                        <h3>查询失败</h3>
                        <p><?= htmlspecialchars($error_msg) ?></p>
                    </div>
                </div>
            <?php elseif ($data): ?>
                <div class="search-result-content">
                    <?php if (isset($data['whois_data'])): ?>
                        <?php $whois_data = $data['whois_data']; ?>
                        <div class='search-space-y-4'>
                            <?php if (isset($whois_data['domainName'])): ?><div><strong>域名:</strong> <?= htmlspecialchars($whois_data['domainName']) ?></div><?php endif; ?>
                            <?php if (isset($whois_data['registrant']['name'])): ?><div><strong>注册人:</strong> <?= htmlspecialchars($whois_data['registrant']['name']) ?></div><?php endif; ?>
                            <?php if (isset($whois_data['createdDate'])): ?><div><strong>创建日期:</strong> <?= htmlspecialchars($whois_data['createdDate']) ?></div><?php endif; ?>
                            <?php if (isset($whois_data['expiresDate'])): ?><div><strong>过期日期:</strong> <?= htmlspecialchars($whois_data['expiresDate']) ?></div><?php endif; ?>
                            <?php if (isset($whois_data['rawText'])): ?>
                                <div class='search-mt-6'>
                                    <h4 class='search-section-title'>完整 WHOIS 信息:</h4>
                                    <pre class='search-code-block'><?= htmlspecialchars($whois_data['rawText']) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif (isset($data['whois'])): ?>
                        <pre class='search-code-block'><?= htmlspecialchars($data['whois']) ?></pre>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="search-actions">
                <a href="<?= $basePath ?>index.php" class="search-btn <?= $query_info['color'] ?>">
                    <i class="fas fa-home"></i>返回首页
                </a>
                <button onclick="window.history.back()" class="search-btn gray">
                    <i class="fas fa-arrow-left"></i>返回上页
                </button>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/footer.php'; ?>
<?php
}

// ===== 入口分发 =====
$mode = $_GET['mode'] ?? null;
if ($mode === 'api') {
    whois_handle_api();
} elseif ($mode === 'page') {
    whois_handle_page();
}
// 若作为模块被调用，不自动输出；仅在显式传入 mode 参数时执行

?>
