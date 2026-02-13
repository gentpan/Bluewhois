<?php
include 'config.php';
include 'function.php';

// 支持 /{target}/api 风格 API：例如 /xifeng.com/api 或 /1.1.1.1/api
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
if (preg_match('#^/([^/]+)/api/?$#', $requestPath, $m)) {
    $target = dl_normalizeQueryTarget(urldecode((string)$m[1]));
    if (dl_validateQueryTarget($target)) {
        include 'whois.php';
        $_GET['domain'] = $target;
        $_GET['mode'] = 'api';
        whois_handle_api();
        exit;
    }
}

// 检查是否是直接访问域名（如 domain.com 或 126.com）
// 如果不是通过 GET 参数传递，尝试从 URL 路径中提取
$domain = trim($_GET['domain'] ?? '');

if (empty($domain)) {
    // 使用通用函数从路径提取域名，排除 api/ 前缀
    $domain = extractDomainFromPath('api/');
}

// 统一使用首页查询容器渲染（含路径自动查询）
$page_title = 'BlueWhois - WHOIS 域名查询';
include 'header.php';
?>

<!-- 查询区域（始终显示） -->
<section class="query-section">
    <div class="query-card">
        <form class="query-form" data-type="whois">
            <div class="form-group">
                <input type="text" name="domain" id="domain-input" placeholder="输入域名或 IP，如：example.com / 1.1.1.1 / 2606:4700:4700::1111" value="<?= htmlspecialchars($domain) ?>">
                <!-- 域名补全下拉列表 -->
                <div id="domain-suggestions" class="hidden">
                    <!-- 建议项将通过 JavaScript 动态插入 -->
                </div>
            </div>
            <button type="submit" class="btn-primary">
                <span class="query-button-content">
                    <i class="fas fa-search"></i>查询 WHOIS
                </span>
                <span class="query-button-loading hidden">
                    <i class="fas fa-spinner fa-spin"></i>查询中...
                </span>
            </button>
        </form>
    </div>
    <!-- 查询结果显示区域 -->
    <div id="query-result" class="result-container"></div>
</section>

<!-- 功能特点板块 -->
<?php if (empty($domain)): ?>
    <section class="features-section">
        <div class="section-header">
            <h2 class="section-title">功能特点</h2>
            <p class="section-subtitle">专业的 WHOIS 查询服务，满足您的各种需求</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <div class="feature-title">快速查询</div>
                <div class="feature-desc">基于专业 API，毫秒级响应速度</div>
            </div>
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <div class="feature-title">数据完整</div>
                <div class="feature-desc">提取所有可用信息，包括注册商、日期等</div>
            </div>
            <div class="feature-card">
                <svg class="feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="feature-title">全球支持</div>
                <div class="feature-desc">支持所有主流顶级域名类型</div>
            </div>
        </div>
    </section>

    <!-- 友情链接板块 -->
    <section class="friendship-links-section">
        <div class="section-header">
            <h2 class="section-title">友情链接</h2>
            <p class="section-subtitle">推荐的相关资源和服务</p>
        </div>
        <div class="brand-logo-grid">
            <a href="https://www.aliyun.com" target="_blank" class="brand-logo-item" title="阿里云">
                <img src="<?= $basePath ?>assets/images/brands/aliyun.svg" alt="阿里云" class="brand-logo-image">
            </a>
            <a href="https://cloud.tencent.com" target="_blank" class="brand-logo-item" title="腾讯云">
                <img src="<?= $basePath ?>assets/images/brands/TencentCloud.svg" alt="腾讯云" class="brand-logo-image">
            </a>
            <a href="https://www.dnspod.cn" target="_blank" class="brand-logo-item" title="DNSPod">
                <img src="<?= $basePath ?>assets/images/brands/DNSPOD.svg" alt="DNSPod" class="brand-logo-image">
            </a>
            <a href="https://www.cloudflare.com" target="_blank" class="brand-logo-item" title="Cloudflare">
                <img src="<?= $basePath ?>assets/images/brands/Cloudflare.svg" alt="Cloudflare" class="brand-logo-image">
            </a>
            <a href="https://www.name.com" target="_blank" class="brand-logo-item" title="Name.com">
                <img src="<?= $basePath ?>assets/images/brands/Namecom.svg" alt="Name.com" class="brand-logo-image">
            </a>
            <a href="https://www.godaddy.com" target="_blank" class="brand-logo-item" title="GoDaddy">
                <img src="<?= $basePath ?>assets/images/brands/GoDaddy.svg" alt="GoDaddy" class="brand-logo-image">
            </a>
            <a href="https://www.gandi.net" target="_blank" class="brand-logo-item" title="Gandi">
                <img src="<?= $basePath ?>assets/images/brands/Gandi.svg" alt="Gandi" class="brand-logo-image">
            </a>
        </div>
        <div class="friendship-links-scroll">
            <a href="https://www.quyu.net" target="_blank" class="friend-link-item" title="趣域">
                <img alt="趣域" class="friend-link-icon" data-domain="quyu.net">
                <span class="friend-link-name">趣域</span>
            </a>
            <a href="https://www.west.cn" target="_blank" class="friend-link-item" title="西部数码">
                <img alt="西部数码" class="friend-link-icon" data-domain="west.cn">
                <span class="friend-link-name">西部数码</span>
            </a>
            <a href="https://www.dynadot.com" target="_blank" class="friend-link-item" title="Dynadot">
                <img alt="Dynadot" class="friend-link-icon" data-domain="dynadot.com">
                <span class="friend-link-name">Dynadot</span>
            </a>
            <a href="https://namebeta.com" target="_blank" class="friend-link-item" title="NameBeta">
                <img alt="NameBeta" class="friend-link-icon" data-domain="namebeta.com">
                <span class="friend-link-name">NameBeta</span>
            </a>
            <a href="https://ip.sb" target="_blank" class="friend-link-item" title="IP.SB">
                <img alt="IP.SB" class="friend-link-icon" data-domain="ip.sb">
                <span class="friend-link-name">IP.SB</span>
            </a>
            <a href="https://www.icann.org" target="_blank" class="friend-link-item" title="ICANN">
                <img alt="ICANN" class="friend-link-icon" data-domain="icann.org">
                <span class="friend-link-name">ICANN</span>
            </a>
            <a href="https://www.iana.org" target="_blank" class="friend-link-item" title="IANA">
                <img alt="IANA" class="friend-link-icon" data-domain="iana.org">
                <span class="friend-link-name">IANA</span>
            </a>
            <a href="https://rdap.ss/" target="_blank" class="friend-link-item" title="RDAP.SS">
                <img alt="RDAP.SS" class="friend-link-icon" data-domain="rdap.ss">
                <span class="friend-link-name">RDAP.SS</span>
            </a>
            <a href="https://dalao.net" target="_blank" class="friend-link-item" title="大佬论坛">
                <img alt="大佬论坛" class="friend-link-icon" data-domain="dalao.net">
                <span class="friend-link-name">大佬论坛</span>
            </a>
            <a href="https://www.namecheap.com" target="_blank" class="friend-link-item" title="Namecheap">
                <img alt="Namecheap" class="friend-link-icon" data-domain="namecheap.com">
                <span class="friend-link-name">Namecheap</span>
            </a>
            <a href="https://www.ename.net" target="_blank" class="friend-link-item" title="易名">
                <img alt="易名" class="friend-link-icon" data-domain="ename.net">
                <span class="friend-link-name">易名</span>
            </a>
            <a href="https://www.bluehost.com" target="_blank" class="friend-link-item" title="Bluehost">
                <img alt="Bluehost" class="friend-link-icon" data-domain="bluehost.com">
                <span class="friend-link-name">Bluehost</span>
            </a>
        </div>
    </section>
<?php endif; ?>


<?php
// 确定基础路径
$basePath = getBasePath();
?>

<?php include 'footer.php'; ?>
