<?php
// 功能函数库：包含所有工具函数和 WHOIS 查询函数
include_once __DIR__ . '/config.php';

// ===== 通用工具函数 =====

/**
 * 通用错误日志函数
 */
function logError($message)
{
    if (defined('LOG_ERRORS') && LOG_ERRORS) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message" . PHP_EOL;
        @file_put_contents(LOG_FILE, $log_message, FILE_APPEND | LOCK_EX);
    }
}

/**
 * 通用 API 调用（含 SSL 与 CA 证书路径探测）
 */
function makeApiCall($url, $headers = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Network Query Tool');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $ca_bundle_paths = [
        '/etc/ssl/certs/ca-certificates.crt',  // Debian/Ubuntu
        '/etc/pki/tls/certs/ca-bundle.crt',     // RedHat/CentOS
        '/usr/local/etc/openssl/cert.pem',      // macOS (Homebrew)
        '/etc/ssl/cert.pem',                    // Alpine Linux
    ];

    foreach ($ca_bundle_paths as $path) {
        if (file_exists($path)) {
            curl_setopt($ch, CURLOPT_CAINFO, $path);
            break;
        }
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    unset($ch);

    if ($curl_errno === 60 || $curl_errno === 77) {
        logError("SSL 证书验证失败 (cURL 错误码: $curl_errno): $curl_error");
        logError("提示：可能需要配置 CURLOPT_CAINFO 或更新 CA 证书包");
    }

    return [
        'response' => $response,
        'http_code' => $http_code,
        'error' => $curl_error,
        'errno' => $curl_errno
    ];
}

/**
 * 简单文件型频率限制：返回是否允许本次请求
 */
function rateLimitAllow($bucket = 'default', $windowSeconds = 10, $limit = 8)
{
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!defined('CACHE_DIR')) return true;
        $rlFile = CACHE_DIR . 'ratelimit_' . md5($bucket . '_' . $ip) . '.json';
        $now = time();
        $timestamps = [];
        if (file_exists($rlFile)) {
            $raw = @file_get_contents($rlFile);
            $timestamps = json_decode($raw, true);
            if (!is_array($timestamps)) $timestamps = [];
        }
        $timestamps = array_values(array_filter($timestamps, function ($ts) use ($now, $windowSeconds) {
            return ($now - (int)$ts) <= $windowSeconds;
        }));
        if (count($timestamps) >= $limit) {
            return false;
        }
        $timestamps[] = $now;
        @file_put_contents($rlFile, json_encode($timestamps));
        return true;
    } catch (Throwable $e) {
        return true; // 限流异常不影响主流程
    }
}

/**
 * IP 地址验证（IPv4/IPv6）
 */
function validateIP($ip)
{
    if (empty($ip)) return false;
    $ip = trim($ip);
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

/**
 * DNS 记录类型验证
 */
function validateRecordType($type)
{
    $allowed_types = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA', 'PTR', 'SRV'];
    return in_array(strtoupper($type), $allowed_types, true);
}

/**
 * 获取基础路径（用于相对路径引用）
 * 如果当前页面在 pages/ 目录下，返回 '../'，否则返回 ''
 */
function getBasePath()
{
    return (strpos($_SERVER['REQUEST_URI'] ?? '', '/pages/') !== false) ? '../' : '';
}

/**
 * 从 URL 路径中提取域名（用于直接访问域名的情况，如 domain.com）
 * @param string|null $excluded_prefix 要排除的前缀（如 'api/'）
 * @param array $excluded_paths 要排除的路径列表
 * @return string 提取到的域名，如果未找到则返回空字符串
 */
function extractDomainFromPath($excluded_prefix = null, $excluded_paths = [])
{
    $domain = '';
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($request_uri, PHP_URL_PATH);
    
    // 移除查询字符串和锚点
    $path = preg_replace('/\?.*$/', '', $path);
    $path = preg_replace('/#.*$/', '', $path);
    
    // 移除开头的斜杠和尾部的斜杠
    $path = trim($path, '/');
    
    // 排除指定的前缀（如 'api/'）
    if ($excluded_prefix && strpos($path, $excluded_prefix) === 0) {
        return '';
    }
    
    // 默认排除的路径
    $default_excluded = ['index.php', 'whois.php', 'api', 'pages', 'assets', 'cache', 'data', 'logs', 'favicon.ico', 'config.php', 'utils.php', 'function.php', 'header.php', 'footer.php'];
    $excluded_paths = array_merge($default_excluded, $excluded_paths);
    
    if (!empty($path)) {
        $path_parts = explode('/', $path);
        
        // 只处理单层路径（不是文件或目录）
        if (count($path_parts) === 1 && !in_array(strtolower($path), $excluded_paths)) {
            // 检查是否像域名格式（必须包含点）
            if (strpos($path, '.') !== false && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]$/', $path)) {
                // 验证是否为有效域名
                if (dl_validateDomain($path)) {
                    $domain = dl_normalizeDomain($path);
                }
            }
        }
    }
    
    return $domain;
}

/**
 * 从 API 路径中提取域名（用于 api/domain.com 的情况）
 * @return string 提取到的域名，如果未找到则返回空字符串
 */
function extractDomainFromApiPath()
{
    $domain = '';
    
    // 优先从GET参数获取域名（Nginx 重写后）
    $domain = $_GET['domain'] ?? '';
    
    if (!empty($domain)) {
        return dl_normalizeDomain($domain);
    }
    
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($request_uri, PHP_URL_PATH);
    
    // 移除查询字符串和锚点
    $path = preg_replace('/\?.*$/', '', $path);
    $path = preg_replace('/#.*$/', '', $path);
    
    // 移除开头的斜杠和尾部的斜杠
    $path = trim($path, '/');
    
    // 提取域名部分 (api/qq.com -> qq.com)
    $path_parts = explode('/', $path);
    
    if (count($path_parts) >= 2 && $path_parts[0] === 'api') {
        $domain = $path_parts[1];
        // 清理域名（移除可能的路径片段和URL编码）
        $domain = urldecode($domain);
        $domain = preg_replace('/\/.*$/', '', $domain);
        return dl_normalizeDomain($domain);
    }
    
    // 如果还是为空，尝试从 REQUEST_URI 直接提取
    if (empty($domain) && preg_match('#^/api/([^/?]+)#', $request_uri, $matches)) {
        $domain = urldecode($matches[1]);
        return dl_normalizeDomain($domain);
    }
    
    // 如果还是为空，尝试从脚本文件名提取（某些服务器配置）
    if (empty($domain)) {
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        // 如果直接访问 api/domain.com，SCRIPT_NAME 可能是 /api/domain.com
        if (preg_match('#/api/([^/]+)$#', $script_name, $matches)) {
            $domain = urldecode($matches[1]);
            return dl_normalizeDomain($domain);
        }
    }
    
    // 最后尝试从 PATH_INFO 获取（Apache mod_rewrite 或其他配置）
    if (empty($domain) && isset($_SERVER['PATH_INFO'])) {
        $path_info = trim($_SERVER['PATH_INFO'], '/');
        if (!empty($path_info)) {
            $domain = urldecode($path_info);
            $domain = preg_replace('/\/.*$/', '', $domain);
            return dl_normalizeDomain($domain);
        }
    }
    
    // 尝试从 REDIRECT_URL 获取（某些重写规则）
    if (empty($domain) && isset($_SERVER['REDIRECT_URL'])) {
        if (preg_match('#/api/([^/]+)#', $_SERVER['REDIRECT_URL'], $matches)) {
            $domain = urldecode($matches[1]);
            return dl_normalizeDomain($domain);
    }
    }
    
    return '';
}

// ===== WHOIS 查询相关函数 =====

/**
 * 验证域名格式
 */
function dl_validateDomain($domain)
{
    $domain = dl_normalizeDomain($domain);
    if ($domain === '') return false;
    if (strlen($domain) > 253) return false;
    if (strpos($domain, '..') !== false || strpos($domain, '/') !== false || strpos($domain, "\\") !== false) {
        return false;
    }

    $labels = explode('.', $domain);
    if (count($labels) < 2) return false;

    foreach ($labels as $label) {
        $len = strlen($label);
        if ($len < 1 || $len > 63) return false;
        if ($label[0] === '-' || $label[$len - 1] === '-') return false;
        if (!preg_match('/^[a-z0-9-]+$/i', $label)) return false;
    }

    $tld = end($labels);
    if (!preg_match('/^(xn--[a-z0-9-]{2,59}|[a-z]{2,63})$/i', $tld)) {
        return false;
    }

    return true;
}

/**
 * 统一规范化域名：去空白、去尾点、统一小写
 */
function dl_normalizeDomain($domain)
{
    $domain = trim((string)$domain);
    $domain = rtrim($domain, '.');
    return strtolower($domain);
}

/**
 * 过滤占位/无效 NS（例如 not.defined.）
 */
function dl_filterNameservers($nameservers)
{
    if (!is_array($nameservers)) return [];

    $filtered = [];
    foreach ($nameservers as $ns) {
        $ns = strtolower(trim((string)$ns));
        $ns = rtrim($ns, '.');
        if ($ns === '' || $ns === 'not.defined' || $ns === 'undefined' || $ns === 'null') {
            continue;
        }
        if (strpos($ns, '.') === false) {
            continue;
        }
        $filtered[] = $ns;
    }

    return array_values(array_unique($filtered));
}

/**
 * 获取域名 TLD
 */
function dl_getDomainTld($domain)
{
    $parts = explode('.', dl_normalizeDomain($domain));
    $tld = strtolower((string)end($parts));
    return $tld;
}

/**
 * WHOIS 文本中提取推荐的 WHOIS 服务器地址
 */
function dl_extractWhoisServerFromText($text)
{
    if (!is_string($text) || trim($text) === '') return '';

    $patterns = [
        '/^\s*whois\s*:\s*([a-z0-9.-]+)\s*$/mi',
        '/^\s*refer\s*:\s*([a-z0-9.-]+)\s*$/mi',
        '/^\s*Registrar WHOIS Server\s*:\s*([a-z0-9.-]+)\s*$/mi',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $m)) {
            $host = strtolower(trim((string)$m[1]));
            $host = preg_replace('#^https?://#i', '', $host);
            $host = preg_replace('#/.*$#', '', $host);
            $host = rtrim($host, '.');
            if ($host !== '' && preg_match('/^[a-z0-9.-]+$/', $host)) {
                return $host;
            }
        }
    }

    return '';
}

/**
 * 通过 whois.iana.org 动态发现 TLD 对应的 WHOIS 服务器
 */
function dl_discoverWhoisServerFromIana($domain)
{
    $tld = dl_getDomainTld($domain);
    if ($tld === '') return '';

    $cacheFile = defined('CACHE_DIR') ? (CACHE_DIR . 'whois_iana_servers.json') : null;
    $ttl = 604800; // 7 天
    $cache = [];

    if ($cacheFile && file_exists($cacheFile)) {
        $cache = json_decode(@file_get_contents($cacheFile), true);
        if (!is_array($cache)) $cache = [];
    }

    if (isset($cache[$tld]) && is_array($cache[$tld])) {
        $item = $cache[$tld];
        $cachedAt = (int)($item['cached_at'] ?? 0);
        $server = (string)($item['server'] ?? '');
        if ($cachedAt > 0 && (time() - $cachedAt) < $ttl) {
            return $server;
        }
    }

    $raw = dl_queryWhoisRaw($tld, 'whois.iana.org', 12);
    if (isset($raw['error'])) {
        return '';
    }
    $server = dl_extractWhoisServerFromText($raw['response'] ?? '');

    $cache[$tld] = [
        'server' => $server,
        'cached_at' => time()
    ];
    if ($cacheFile) {
        @file_put_contents($cacheFile, json_encode($cache, JSON_UNESCAPED_UNICODE));
    }

    return $server;
}

/**
 * 提供 WHOIS 服务器候选列表（手工映射 + IANA 发现 + 通用兜底）
 */
function dl_getWhoisFallbackServers($domain)
{
    $tld = dl_getDomainTld($domain);
    if ($tld === '') return [];

    $manualMap = [
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'info' => 'whois.afilias.net',
        'biz' => 'whois.nic.biz',
        'sb' => 'whois.nic.net.sb',
        'bi' => 'whois1.nic.bi',
    ];

    $servers = [];
    if (!empty($manualMap[$tld])) {
        $servers[] = $manualMap[$tld];
    }

    $ianaServer = dl_discoverWhoisServerFromIana($domain);
    if ($ianaServer !== '') {
        $servers[] = $ianaServer;
    }

    // 通用主机名兜底，提升 ccTLD 成功率
    $servers[] = 'whois.nic.' . $tld;
    $servers[] = 'whois.nic.net.' . $tld;
    $servers[] = 'whois.' . $tld;

    $cleaned = [];
    foreach ($servers as $server) {
        $server = strtolower(trim((string)$server));
        if ($server === '' || !preg_match('/^[a-z0-9.-]+$/', $server)) continue;
        $cleaned[] = rtrim($server, '.');
    }

    return array_values(array_unique($cleaned));
}

/**
 * WHOIS Port43 原始查询
 */
function dl_queryWhoisRaw($query, $server, $timeout = 15)
{
    $fp = @fsockopen($server, 43, $errno, $errstr, $timeout);
    if (!$fp) {
        return ['error' => "WHOIS 连接失败: {$errstr} ({$errno})"];
    }

    stream_set_timeout($fp, $timeout);
    fwrite($fp, (string)$query . "\r\n");

    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 4096);
    }
    fclose($fp);

    if (trim($response) === '') {
        return ['error' => 'WHOIS 返回空响应'];
    }

    return ['response' => $response];
}

/**
 * 通过 WHOIS Port43 查询域名
 */
function dl_queryWhoisPort43($domain, $server, $timeout = 15)
{
    $rawResult = dl_queryWhoisRaw($domain, $server, $timeout);
    if (isset($rawResult['error'])) {
        return $rawResult;
    }

    $response = (string)$rawResult['response'];
    $lines = preg_split('/\r\n|\r|\n/', $response);
    $nameservers = [];
    $created = '';
    $updated = '';
    $expires = '';
    $registrar = '';

    foreach ($lines as $line) {
        if (preg_match('/^\s*(?:name server|nserver)\s*:\s*(.+)$/i', $line, $m)) {
            $raw = trim($m[1]);
            if ($raw !== '') {
                $parts = preg_split('/\s+/', $raw);
                if (!empty($parts[0])) $nameservers[] = $parts[0];
            }
            continue;
        }

        if ($registrar === '' && preg_match('/^\s*registrar\s*:\s*(.+)$/i', $line, $m)) {
            $registrar = trim($m[1]);
            continue;
        }

        if ($created === '' && preg_match('/^\s*(?:creation date|created on|created date|created)\s*:\s*(.+)$/i', $line, $m)) {
            $created = trim($m[1]);
            continue;
        }

        if ($updated === '' && preg_match('/^\s*(?:updated date|last updated on|changed)\s*:\s*(.+)$/i', $line, $m)) {
            $updated = trim($m[1]);
            continue;
        }

        if ($expires === '' && preg_match('/^\s*(?:registry expiry date|expiry date|expires on|expiration date|paid-till)\s*:\s*(.+)$/i', $line, $m)) {
            $expires = trim($m[1]);
            continue;
        }
    }

    $nameservers = dl_filterNameservers($nameservers);

    $data = [
        'domain_name' => $domain,
        'registered' => true,
        'whois_server' => $server,
        'whois_raw' => $response,
        'status' => 0,
        'status_desc' => 'Success'
    ];

    if (!empty($registrar)) $data['registrar'] = $registrar;
    if (!empty($created)) {
        $ts = strtotime($created);
        if ($ts) $data['date_created'] = date('Y-m-d H:i:s', $ts);
    }
    if (!empty($updated)) {
        $ts = strtotime($updated);
        if ($ts) $data['date_updated'] = date('Y-m-d H:i:s', $ts);
    }
    if (!empty($expires)) {
        $ts = strtotime($expires);
        if ($ts) $data['date_expires'] = date('Y-m-d H:i:s', $ts);
    }
    if (!empty($nameservers)) $data['nameservers'] = $nameservers;

    return $data;
}

/**
 * WHOIS 后备链路：自动尝试多个服务器，并处理 referral
 */
function dl_queryWhoisFallback($domain)
{
    $servers = dl_getWhoisFallbackServers($domain);
    if (empty($servers)) {
        return ['error' => '该后缀暂无可用 WHOIS 服务器'];
    }

    $attemptErrors = [];
    foreach ($servers as $server) {
        $primary = dl_queryWhoisPort43($domain, $server, 15);
        if (isset($primary['error'])) {
            $attemptErrors[] = $server . ': ' . $primary['error'];
            continue;
        }

        $primary['query_chain'] = [$server];
        $referral = dl_extractWhoisServerFromText($primary['whois_raw'] ?? '');
        if ($referral !== '' && strcasecmp($referral, $server) !== 0) {
            $refData = dl_queryWhoisPort43($domain, $referral, 15);
            if (!isset($refData['error'])) {
                $refData['query_chain'] = [$server, $referral];
                $refData['referral_from'] = $server;
                return $refData;
            }
            $attemptErrors[] = $referral . ': ' . $refData['error'];
        }

        return $primary;
    }

    $tail = !empty($attemptErrors) ? ('；详情: ' . implode(' | ', array_slice($attemptErrors, 0, 4))) : '';
    return ['error' => 'WHOIS 后备查询失败' . $tail];
}

/**
 * 查询 WhoisXML API
 */
function dl_queryWhoisXMLAPI($domain)
{
    if (empty(WHOISXML_API_KEY)) {
        return ['error' => '付费接口已禁用'];
    }

    $endpoint = defined('WHOISXML_API_ENDPOINT') ? WHOISXML_API_ENDPOINT : 'https://www.whoisxmlapi.com/whoisserver/WhoisService';
    $url = $endpoint . '?apiKey=' . WHOISXML_API_KEY . '&domainName=' . urlencode($domain) . '&outputFormat=JSON';
    $result = makeApiCall($url);

    if ($result['http_code'] !== 200) {
        logError("WhoisXML API HTTP 错误: HTTP {$result['http_code']} - 域名: $domain");
        if (!empty($result['response'])) {
            logError("WhoisXML API 响应: " . substr($result['response'], 0, 500));
        }
        return ['error' => "HTTP {$result['http_code']} 错误"];
    }
    if ($result['error']) {
        logError("WhoisXML API cURL 错误: {$result['error']} (errno: {$result['errno']}) - 域名: $domain");
        return ['error' => $result['error']];
    }
    if (empty($result['response'])) {
        logError("WhoisXML API 空响应 - 域名: $domain");
        return ['error' => 'API 返回空响应'];
    }

    $whoisxml_data = json_decode($result['response'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logError("WhoisXML API JSON 解析错误: " . json_last_error_msg() . " - 域名: $domain");
        logError("WhoisXML API 原始响应: " . substr($result['response'], 0, 500));
        return ['error' => 'JSON 解析失败: ' . json_last_error_msg()];
    }

    if (isset($whoisxml_data['ErrorMessage'])) {
        $error_msg = $whoisxml_data['ErrorMessage']['msg'] ?? 'WhoisXML API 查询失败';
        logError("WhoisXML API 错误: $error_msg - 域名: $domain");
        return ['error' => $error_msg];
    }

    if ($whoisxml_data && isset($whoisxml_data['WhoisRecord'])) {
        $record = $whoisxml_data['WhoisRecord'];
        $converted = [
            'domain_name' => $record['domainName'] ?? $domain,
            'registered' => !empty($record['domainName']),
            'whois_server' => $record['registryData']['whoisServer'] ?? ($record['whoisServer'] ?? ''),
        ];
        if (isset($record['registryData']['createdDate'])) {
            $converted['date_created'] = date('Y-m-d H:i:s', strtotime($record['registryData']['createdDate']));
        } elseif (isset($record['createdDate'])) {
            $converted['date_created'] = date('Y-m-d H:i:s', strtotime($record['createdDate']));
        }
        if (isset($record['registryData']['expiresDate'])) {
            $converted['date_expires'] = date('Y-m-d H:i:s', strtotime($record['registryData']['expiresDate']));
        } elseif (isset($record['expiresDate'])) {
            $converted['date_expires'] = date('Y-m-d H:i:s', strtotime($record['expiresDate']));
        }
        if (isset($record['registryData']['updatedDate'])) {
            $converted['date_updated'] = date('Y-m-d H:i:s', strtotime($record['registryData']['updatedDate']));
        } elseif (isset($record['updatedDate'])) {
            $converted['date_updated'] = date('Y-m-d H:i:s', strtotime($record['updatedDate']));
        }
        if (isset($record['registryData']['registrarName'])) {
            $converted['registrar'] = $record['registryData']['registrarName'];
        } elseif (isset($record['registrarName'])) {
            $converted['registrar'] = $record['registrarName'];
        }
        if (isset($record['registryData']['registrarIANAID'])) {
            $converted['registrar_iana_id'] = $record['registryData']['registrarIANAID'];
        } elseif (isset($record['registrarIANAID'])) {
            $converted['registrar_iana_id'] = $record['registrarIANAID'];
        }
        $contacts = [];
        foreach (['registrant', 'administrative', 'technical'] as $type) {
            $key = $type . 'Contact';
            if (isset($record['registryData'][$key])) {
                $c = $record['registryData'][$key];
                $contacts[] = [
                    'type' => $type === 'administrative' ? 'admin' : ($type === 'technical' ? 'tech' : $type),
                    'name' => $c['name'] ?? '',
                    'organization' => $c['organization'] ?? '',
                    'email' => $c['email'] ?? '',
                    'phone' => $c['telephone'] ?? '',
                    'country' => $c['country'] ?? '',
                    'city' => $c['city'] ?? '',
                    'address' => isset($c['street']) ? (is_array($c['street']) ? implode(', ', $c['street']) : $c['street']) : ''
                ];
            } elseif (isset($record[$key])) {
                $c = $record[$key];
                $contacts[] = [
                    'type' => $type === 'administrative' ? 'admin' : ($type === 'technical' ? 'tech' : $type),
                    'name' => $c['name'] ?? '',
                    'organization' => $c['organization'] ?? '',
                    'email' => $c['email'] ?? '',
                    'phone' => $c['telephone'] ?? '',
                    'country' => $c['country'] ?? '',
                    'city' => $c['city'] ?? '',
                    'address' => isset($c['street']) ? (is_array($c['street']) ? implode(', ', $c['street']) : $c['street']) : ''
                ];
            }
        }
        if (isset($record['registryData']['registrar'])) {
            $ri = $record['registryData']['registrar'];
            $contacts[] = [
                'type' => 'registrar',
                'name' => $ri['name'] ?? ($converted['registrar'] ?? ''),
                'organization' => $ri['name'] ?? ($converted['registrar'] ?? ''),
                'email' => $ri['email'] ?? '',
                'phone' => $ri['phone'] ?? '',
                'url' => $ri['url'] ?? ''
            ];
        }
        if (!empty($contacts)) $converted['contacts'] = $contacts;
        if (isset($record['registryData']['status'])) {
            $status = $record['registryData']['status'];
            $converted['domain_status'] = is_array($status) ? $status : [$status];
        } elseif (isset($record['status'])) {
            $status = $record['status'];
            $converted['domain_status'] = is_array($status) ? $status : [$status];
        }
        if (isset($record['registryData']['nameServers']['hostNames'])) {
            $converted['nameservers'] = is_array($record['registryData']['nameServers']['hostNames'])
                ? $record['registryData']['nameServers']['hostNames']
                : [$record['registryData']['nameServers']['hostNames']];
        } elseif (isset($record['nameServers']['hostNames'])) {
            $converted['nameservers'] = is_array($record['nameServers']['hostNames'])
                ? $record['nameServers']['hostNames']
                : [$record['nameServers']['hostNames']];
        }
        if (isset($record['tld'])) $converted['tld'] = $record['tld'];
        if (isset($record['registryData']['rawText'])) {
            $converted['whois_raw'] = $record['registryData']['rawText'];
        } elseif (isset($record['rawText'])) {
            $converted['whois_raw'] = $record['rawText'];
        }
        $converted['status'] = 0;
        $converted['status_desc'] = 'Success';
        return $converted;
    }
    logError("WhoisXML API 未预期的响应格式 - 域名: $domain");
    return ['error' => 'API 返回格式不正确'];
}

/**
 * 加载 RDAP Bootstrap（IANA 官方），用于免费查询多后缀
 */
function dl_getRdapBootstrap()
{
    $cacheFile = defined('CACHE_DIR') ? (CACHE_DIR . 'rdap_bootstrap_dns.json') : null;
    $ttl = 86400; // 24 小时

    if ($cacheFile && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $cached = json_decode(@file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['services'])) {
            return $cached;
        }
    }

    $url = 'https://data.iana.org/rdap/dns.json';
    $result = makeApiCall($url);
    if ($result['http_code'] === 200 && empty($result['error']) && !empty($result['response'])) {
        $data = json_decode($result['response'], true);
        if (is_array($data) && isset($data['services'])) {
            if ($cacheFile) {
                @file_put_contents($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE));
            }
            return $data;
        }
    }

    if ($cacheFile && file_exists($cacheFile)) {
        $cached = json_decode(@file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['services'])) {
            return $cached;
        }
    }

    return null;
}

/**
 * 从 RDAP Bootstrap 中查找域名可用的 RDAP 服务器
 */
function dl_findRdapServersForDomain($domain, $bootstrap)
{
    if (!is_array($bootstrap) || !isset($bootstrap['services']) || !is_array($bootstrap['services'])) {
        return [];
    }

    $domain = dl_normalizeDomain($domain);
    $parts = explode('.', $domain);
    $candidates = [];
    for ($i = 0; $i < count($parts); $i++) {
        $candidates[] = implode('.', array_slice($parts, $i));
    }

    $servicesMap = [];
    foreach ($bootstrap['services'] as $service) {
        if (!is_array($service) || count($service) < 2 || !is_array($service[0]) || !is_array($service[1])) {
            continue;
        }
        foreach ($service[0] as $suffix) {
            $suffixKey = strtolower(trim((string)$suffix));
            if ($suffixKey === '') continue;
            $servicesMap[$suffixKey] = $service[1];
        }
    }

    foreach ($candidates as $suffix) {
        $suffix = strtolower($suffix);
        if (!empty($servicesMap[$suffix])) {
            return $servicesMap[$suffix];
        }
    }

    return [];
}

/**
 * 手工补充的 RDAP 查询点（用于补强 IANA bootstrap 覆盖）
 */
function dl_getManualRdapServers($domain)
{
    $parts = explode('.', dl_normalizeDomain($domain));
    $tld = strtolower(end($parts) ?: '');

    $map = [
        'cx' => ['https://rdap.nic.cx'],
    ];

    return $map[$tld] ?? [];
}

/**
 * 从 RDAP vCard 数组中提取字段值
 */
function dl_rdapVcardValue($vcardArray, $key)
{
    if (!is_array($vcardArray) || count($vcardArray) < 2 || !is_array($vcardArray[1])) {
        return '';
    }
    foreach ($vcardArray[1] as $item) {
        if (!is_array($item) || count($item) < 4) continue;
        if (strtolower((string)$item[0]) === strtolower($key)) {
            return is_scalar($item[3]) ? (string)$item[3] : '';
        }
    }
    return '';
}

/**
 * 免费 RDAP 查询（覆盖大量后缀）
 */
function dl_queryRdapFree($domain)
{
    $bootstrap = dl_getRdapBootstrap();
    if (!$bootstrap) {
        return ['error' => '无法加载 RDAP 引导数据'];
    }

    $servers = dl_findRdapServersForDomain($domain, $bootstrap);
    $manualServers = dl_getManualRdapServers($domain);
    // 合并多个查询点：优先 bootstrap，再尝试手工补充节点
    $servers = array_values(array_unique(array_merge($servers, $manualServers)));
    if (empty($servers)) {
        return ['error' => '该后缀暂无可用 RDAP 服务器'];
    }

    $lastError = '免费 RDAP 查询失败';
    $had404 = false;
    $attempts = [];
    foreach ($servers as $base) {
        $base = trim((string)$base);
        if ($base === '') continue;
        $url = rtrim($base, '/') . '/domain/' . rawurlencode($domain);
        $result = makeApiCall($url, ['Accept: application/rdap+json, application/json']);

        if (!empty($result['error'])) {
            $lastError = $result['error'];
            $attempts[] = $base . ': ' . $lastError;
            continue;
        }

        if ($result['http_code'] === 404) {
            $had404 = true;
            $lastError = '域名未注册或无公开 RDAP 记录';
            $attempts[] = $base . ': HTTP 404';
            continue;
        }

        if ($result['http_code'] !== 200 || empty($result['response'])) {
            $lastError = "HTTP {$result['http_code']} 错误";
            $attempts[] = $base . ': ' . $lastError;
            continue;
        }

        $rdap = json_decode($result['response'], true);
        if (!is_array($rdap)) {
            $lastError = 'RDAP 响应解析失败';
            $attempts[] = $base . ': ' . $lastError;
            continue;
        }

        $converted = [
            'domain_name' => $rdap['ldhName'] ?? ($rdap['unicodeName'] ?? $domain),
            'registered' => true,
            'whois_server' => parse_url($base, PHP_URL_HOST) ?: '',
            'domain_status' => isset($rdap['status']) && is_array($rdap['status']) ? $rdap['status'] : [],
            'rdap_source' => $base,
            'whois_raw' => json_encode($rdap, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'status' => 0,
            'status_desc' => 'Success'
        ];

        if (isset($rdap['events']) && is_array($rdap['events'])) {
            foreach ($rdap['events'] as $evt) {
                if (!is_array($evt)) continue;
                $action = strtolower((string)($evt['eventAction'] ?? ''));
                $date = $evt['eventDate'] ?? '';
                if (empty($date)) continue;
                $formatted = date('Y-m-d H:i:s', strtotime($date));
                if (strpos($action, 'registration') !== false || strpos($action, 'created') !== false) {
                    $converted['date_created'] = $formatted;
                } elseif (strpos($action, 'expiration') !== false || strpos($action, 'expiry') !== false) {
                    $converted['date_expires'] = $formatted;
                } elseif (strpos($action, 'last changed') !== false || strpos($action, 'last update') !== false || strpos($action, 'updated') !== false) {
                    $converted['date_updated'] = $formatted;
                }
            }
        }

        if (isset($rdap['nameservers']) && is_array($rdap['nameservers'])) {
            $nameservers = [];
            foreach ($rdap['nameservers'] as $ns) {
                if (!is_array($ns)) continue;
                $name = $ns['ldhName'] ?? ($ns['unicodeName'] ?? '');
                if (!empty($name)) $nameservers[] = $name;
            }
            $nameservers = dl_filterNameservers($nameservers);
            if (!empty($nameservers)) $converted['nameservers'] = $nameservers;
        }

        $contacts = [];
        if (isset($rdap['entities']) && is_array($rdap['entities'])) {
            foreach ($rdap['entities'] as $entity) {
                if (!is_array($entity)) continue;
                $roles = isset($entity['roles']) && is_array($entity['roles']) ? array_map('strtolower', $entity['roles']) : [];
                $vcard = $entity['vcardArray'] ?? [];
                $name = dl_rdapVcardValue($vcard, 'fn');
                $org = dl_rdapVcardValue($vcard, 'org');
                $email = dl_rdapVcardValue($vcard, 'email');
                $tel = dl_rdapVcardValue($vcard, 'tel');

                if (in_array('registrar', $roles, true) && empty($converted['registrar'])) {
                    $converted['registrar'] = $name ?: $org;
                }

                $roleMap = [
                    'registrant' => 'registrant',
                    'administrative' => 'admin',
                    'technical' => 'tech',
                    'registrar' => 'registrar',
                    'abuse' => 'abuse'
                ];
                foreach ($roles as $role) {
                    if (!isset($roleMap[$role])) continue;
                    $contacts[] = [
                        'type' => $roleMap[$role],
                        'name' => $name,
                        'organization' => $org,
                        'email' => $email,
                        'phone' => $tel
                    ];
                }
            }
        }
        if (!empty($contacts)) {
            $converted['contacts'] = $contacts;
        }

        if (!empty($attempts)) {
            $converted['rdap_attempts'] = $attempts;
        }

        return $converted;
    }

    if ($had404 && $lastError === '域名未注册或无公开 RDAP 记录') {
        return ['error' => $lastError, 'rdap_attempts' => $attempts];
    }

    return ['error' => $lastError, 'rdap_attempts' => $attempts];
}

/**
 * 主 WHOIS 查询函数（包含缓存和多 API 源支持）
 */
function dl_queryWhois($domain)
{
    $domain = dl_normalizeDomain($domain);
    $data = null;
    $error_msg = '';
    $api_used = '';
    $cache_key = 'whois_' . md5('free|' . $domain);
    $cacheFile = defined('CACHE_DIR') ? (CACHE_DIR . $cache_key . '.json') : null;
    if ($cacheFile && file_exists($cacheFile) && (time() - filemtime($cacheFile) < (defined('CACHE_TTL') ? CACHE_TTL : 3600))) {
        $cached = json_decode(@file_get_contents($cacheFile), true);
        if (is_array($cached) && array_key_exists('data', $cached) && array_key_exists('error', $cached)) {
            return $cached;
        }
    }

    $rdap_data = dl_queryRdapFree($domain);
    if ($rdap_data && !isset($rdap_data['error'])) {
        $whois_content = $rdap_data['whois_raw'] ?? '';
        if (!empty($whois_content)) {
            $data = [
                'status' => 0,
                'domain' => $domain,
                'whois' => $whois_content,
                'api_source' => 'RDAP (Primary)',
                'whoapi_data' => $rdap_data
            ];
            $api_used = 'RDAP (Primary)';
        } else {
            $structured_info = [];
            if (isset($rdap_data['domain_name'])) $structured_info[] = "域名: " . $rdap_data['domain_name'];
            if (isset($rdap_data['registered'])) $structured_info[] = "注册状态: " . ($rdap_data['registered'] ? '已注册' : '未注册');
            if (isset($rdap_data['date_created'])) $structured_info[] = "创建日期: " . $rdap_data['date_created'];
            if (isset($rdap_data['date_expires'])) $structured_info[] = "过期日期: " . $rdap_data['date_expires'];
            if (isset($rdap_data['date_updated'])) $structured_info[] = "更新日期: " . $rdap_data['date_updated'];
            if (isset($rdap_data['registrar'])) $structured_info[] = "注册商: " . $rdap_data['registrar'];
            if (isset($rdap_data['domain_status']) && is_array($rdap_data['domain_status'])) {
                $structured_info[] = "\n域名状态: " . implode(', ', $rdap_data['domain_status']);
            }
            if (isset($rdap_data['nameservers']) && is_array($rdap_data['nameservers'])) {
                $structured_info[] = "\nDNS 服务器 (" . count($rdap_data['nameservers']) . " 个):";
                foreach ($rdap_data['nameservers'] as $ns) $structured_info[] = '  • ' . $ns;
            }
            $data = [
                'status' => 0,
                'domain' => $domain,
                'whois' => "以下是从 RDAP 免费服务返回的结构化数据:\n\n" . implode("\n", $structured_info),
                'api_source' => 'RDAP (Primary)',
                'whoapi_data' => $rdap_data
            ];
            $api_used = 'RDAP (Primary)';
        }
    } else {
        $rdapError = isset($rdap_data['error']) ? $rdap_data['error'] : '免费 RDAP 查询失败';
        logError("RDAP 免费查询失败: $domain - $rdapError");

        $whoisFallback = dl_queryWhoisFallback($domain);
        if ($whoisFallback && !isset($whoisFallback['error'])) {
            $whois_content = $whoisFallback['whois_raw'] ?? '';
            $data = [
                'status' => 0,
                'domain' => $domain,
                'whois' => $whois_content,
                'api_source' => 'WHOIS (Fallback)',
                'whoapi_data' => $whoisFallback
            ];
            $api_used = 'WHOIS (Fallback)';
            $error_msg = '';
        } else {
            $fallbackErr = isset($whoisFallback['error']) ? $whoisFallback['error'] : 'WHOIS 后备查询失败';
            logError("WHOIS 后备查询失败: $domain - $fallbackErr");
            $error_msg = "RDAP 失败: {$rdapError}；WHOIS 兜底失败: {$fallbackErr}";
        }
    }

    $res = ['data' => $data, 'error' => $error_msg ?? '', 'api_used' => $api_used];
    if (isset($cacheFile) && $cacheFile && $data && empty($error_msg)) {
        @file_put_contents($cacheFile, json_encode($res, JSON_UNESCAPED_UNICODE));
    }
    return $res;
}
