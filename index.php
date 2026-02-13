<?php
include 'config.php';
include 'function.php';

// 检查是否是直接访问域名（如 domain.com 或 126.com）
// 如果不是通过 GET 参数传递，尝试从 URL 路径中提取
$domain = trim($_GET['domain'] ?? '');

if (empty($domain)) {
    // 使用通用函数从路径提取域名，排除 api/ 前缀
    $domain = extractDomainFromPath('api/');
}

// 如果有域名参数，直接显示结果页面
if (!empty($domain)) {
    // 调用页面渲染函数
    include 'whois.php';
    $_GET['domain'] = $domain;
    $_GET['mode'] = 'page';
    whois_handle_page();
    exit;
}

// 没有域名参数，显示首页
$page_title = 'BlueWhois - WHOIS 域名查询';
include 'header.php';
?>

<!-- 查询区域（始终显示） -->
<section class="query-section">
    <div class="query-card">
        <form class="query-form" data-type="whois">
            <div class="form-group">
                <input type="text" name="domain" id="domain-input" placeholder="输入域名，如：example.com" value="<?= htmlspecialchars($domain) ?>">
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
        <div class="friendship-links-scroll">
            <a href="https://www.iana.org/" target="_blank" class="friend-link-item" title="IANA">
                <img alt="IANA" class="friend-link-icon" data-domain="iana.org">
                <span class="friend-link-name">IANA</span>
            </a>
            <a href="https://www.icann.org/" target="_blank" class="friend-link-item" title="ICANN">
                <img alt="ICANN" class="friend-link-icon" data-domain="icann.org">
                <span class="friend-link-name">ICANN</span>
            </a>
            <a href="https://www.aliyun.com" target="_blank" class="friend-link-item" title="阿里云">
                <img alt="阿里云" class="friend-link-icon" data-domain="aliyun.com">
                <span class="friend-link-name">阿里云</span>
            </a>
            <a href="https://cloud.tencent.com" target="_blank" class="friend-link-item" title="腾讯云">
                <img alt="腾讯云" class="friend-link-icon" data-domain="cloud.tencent.com">
                <span class="friend-link-name">腾讯云</span>
            </a>
            <a href="https://www.name.com" target="_blank" class="friend-link-item" title="Name.com">
                <img alt="Name.com" class="friend-link-icon" data-domain="name.com">
                <span class="friend-link-name">Name</span>
            </a>
            <a href="https://www.godaddy.com" target="_blank" class="friend-link-item" title="GoDaddy">
                <img alt="GoDaddy" class="friend-link-icon" data-domain="godaddy.com">
                <span class="friend-link-name">GoDaddy</span>
            </a>
            <a href="https://www.namecheap.com" target="_blank" class="friend-link-item" title="Namecheap">
                <img alt="Namecheap" class="friend-link-icon" data-domain="namecheap.com">
                <span class="friend-link-name">Namecheap</span>
            </a>
            <a href="https://www.namesilo.com" target="_blank" class="friend-link-item" title="NameSilo">
                <img alt="NameSilo" class="friend-link-icon" data-domain="namesilo.com">
                <span class="friend-link-name">NameSilo</span>
            </a>
            <a href="https://www.dynadot.com" target="_blank" class="friend-link-item" title="Dynadot">
                <img alt="Dynadot" class="friend-link-icon" data-domain="dynadot.com">
                <span class="friend-link-name">Dynadot</span>
            </a>
            <a href="https://porkbun.com" target="_blank" class="friend-link-item" title="Porkbun">
                <img alt="Porkbun" class="friend-link-icon" data-domain="porkbun.com">
                <span class="friend-link-name">Porkbun</span>
            </a>
            <a href="https://www.cloudflare.com" target="_blank" class="friend-link-item" title="Cloudflare">
                <img alt="Cloudflare" class="friend-link-icon" data-domain="cloudflare.com">
                <span class="friend-link-name">Cloudflare</span>
            </a>
            <a href="https://www.gandi.net" target="_blank" class="friend-link-item" title="Gandi">
                <img alt="Gandi" class="friend-link-icon" data-domain="gandi.net">
                <span class="friend-link-name">Gandi</span>
            </a>
            <a href="https://www.namebeta.com" target="_blank" class="friend-link-item" title="NameBeta">
                <img alt="NameBeta" class="friend-link-icon" data-domain="namebeta.com">
                <span class="friend-link-name">NameBeta</span>
            </a>
            <a href="https://ip.sb" target="_blank" class="friend-link-item" title="IP查询">
                <img alt="IP查询" class="friend-link-icon" data-domain="ip.sb">
                <span class="friend-link-name">IP查询</span>
            </a>
            <a href="https://www.xifeng.net" target="_blank" class="friend-link-item" title="西风">
                <img alt="西风" class="friend-link-icon" data-domain="xifeng.net">
                <span class="friend-link-name">西风</span>
            </a>
            <a href="https://domain.ls" target="_blank" class="friend-link-item" title="域名列表">
                <img alt="域名列表" class="friend-link-icon" data-domain="domain.ls">
                <span class="friend-link-name">域名列表</span>
            </a>
        </div>
    </section>
<?php endif; ?>


<?php
// 确定基础路径
$basePath = getBasePath();
?>

<?php include 'footer.php'; ?>
