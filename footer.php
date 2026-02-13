<?php
// 通用底部模板
?>
</main>
<footer class="footer">
    <div class="footer-main">
        <div class="footer-logo-section">
            <div class="footer-logo-text">BlueWhois</div>
            <p class="footer-description">BlueWhois 提供专业的域名 WHOIS 信息查询服务，结果快速、准确、完整。基于 WhoisXML API、WhoAPI 与 RDAP，支持全球域名查询。</p>
        </div>
        <div class="footer-column">
            <h4 class="footer-column-title">快速链接</h4>
            <ul class="footer-links">
                <li><a href="<?= $basePath ?>index.php">首页</a></li>
                <li><a href="<?= $basePath ?>pages/api-docs.php">API 文档</a></li>
                <li><a href="<?= $basePath ?>pages/about.php">关于我们</a></li>
                <li><a href="<?= $basePath ?>pages/contact.php">联系我们</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4 class="footer-column-title">服务</h4>
            <ul class="footer-links">
                <li><a href="<?= $basePath ?>pages/iana.php">IANA 数据库</a></li>
                <li><a href="<?= $basePath ?>pages/cctlds.php">ccTLDs 列表</a></li>
                <li><a href="<?= $basePath ?>pages/api-docs.php">API 接口</a></li>
                <li><a href="#">帮助文档</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4 class="footer-column-title">关于</h4>
            <ul class="footer-links">
                <li><a href="<?= $basePath ?>pages/about.php">关于我们</a></li>
                <li><button type="button" class="footer-legal-trigger" data-legal-modal="privacy">隐私政策</button></li>
                <li><button type="button" class="footer-legal-trigger" data-legal-modal="terms">使用条款</button></li>
                <li><a href="<?= $basePath ?>pages/contact.php">联系我们</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="copyright">© <?php echo date('Y'); ?> BlueWhois 版权所有</div>
        <nav class="footer-nav">
            <button type="button" class="footer-legal-trigger" data-legal-modal="privacy">隐私政策</button>
            <button type="button" class="footer-legal-trigger" data-legal-modal="terms">使用条款</button>
            <button id="themeToggle" class="footer-theme-toggle" aria-label="切换主题">
                <svg id="themeIcon" class="theme-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path id="moon-path" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </nav>
    </div>
</footer>

<div class="legal-modal hidden" id="legal-modal-privacy" aria-hidden="true">
    <div class="legal-modal-overlay" data-legal-close></div>
    <div class="legal-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="legal-modal-privacy-title">
        <div class="legal-modal-header">
            <h3 id="legal-modal-privacy-title">隐私政策</h3>
            <button type="button" class="legal-modal-close" aria-label="关闭" data-legal-close>&times;</button>
        </div>
        <div class="legal-modal-body">
            <p>BlueWhois 重视用户隐私。本站仅在提供查询服务所需范围内处理请求数据，不会主动收集与服务无关的个人信息。</p>
            <p>为保障系统稳定与安全，服务可能记录必要的访问日志（如请求时间、来源 IP、错误信息），用于风控、排障与性能优化。</p>
            <p>如有隐私相关问题，请联系：<a href="mailto:support@bluewhois.com">support@bluewhois.com</a>。</p>
        </div>
    </div>
</div>

<div class="legal-modal hidden" id="legal-modal-terms" aria-hidden="true">
    <div class="legal-modal-overlay" data-legal-close></div>
    <div class="legal-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="legal-modal-terms-title">
        <div class="legal-modal-header">
            <h3 id="legal-modal-terms-title">使用条款</h3>
            <button type="button" class="legal-modal-close" aria-label="关闭" data-legal-close>&times;</button>
        </div>
        <div class="legal-modal-body">
            <p>本服务用于域名信息查询与技术参考。请遵守适用法律法规，不得将服务用于非法扫描、滥用请求或侵害他人权益。</p>
            <p>查询结果来自公开注册数据与上游服务，可能受注册局策略、隐私规则和缓存时效影响，不构成法律或商业承诺。</p>
            <p>BlueWhois 保留在必要时调整服务策略、限流规则与功能实现的权利。</p>
        </div>
    </div>
</div>
<?php
// 确定基础路径（如果文件在 pages/ 文件夹中，需要回到根目录）
if (!function_exists('getBasePath')) {
    include_once __DIR__ . '/function.php';
}
$basePath = getBasePath();
?>
<script src="<?= $basePath ?>assets/js/app.js"></script>
<script src="<?= $basePath ?>assets/js/main.js"></script>
</body>

</html>
