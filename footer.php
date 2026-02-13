<?php
// 通用底部模板
?>
</main>
<footer class="footer">
    <div class="footer-main">
        <div class="footer-logo-section">
            <div class="footer-logo-text">BlueWhois</div>
            <p class="footer-description">BlueWhois 提供专业的 IP 与域名 WHOIS 信息查询服务，结果快速、准确、完整。基于 WhoisXML API、WhoAPI 与 RDAP，支持全球查询。</p>
        </div>
        <div class="footer-column">
            <h4 class="footer-column-title">服务</h4>
            <ul class="footer-links">
                <li><a href="<?= $basePath ?>pages/iana.php"><i class="fas fa-database" aria-hidden="true"></i>IANA 数据库</a></li>
                <li><a href="<?= $basePath ?>pages/cctlds.php"><i class="fas fa-flag" aria-hidden="true"></i>ccTLDs 列表</a></li>
                <li><a href="<?= $basePath ?>api-docs"><i class="fas fa-plug" aria-hidden="true"></i>API 接口</a></li>
                <li><a href="https://data.iana.org/rdap/dns.json" target="_blank" rel="noopener"><i class="fas fa-server" aria-hidden="true"></i>RDAP 列表</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4 class="footer-column-title">关于</h4>
            <ul class="footer-links">
                <li><a href="<?= $basePath ?>about"><i class="fas fa-building" aria-hidden="true"></i>关于我们</a></li>
                <li><a href="<?= $basePath ?>contact"><i class="fas fa-headset" aria-hidden="true"></i>联系我们</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4 class="footer-column-title">资源</h4>
            <ul class="footer-links">
                <li><a href="https://github.com/gentpan/Bluewhois" target="_blank" rel="noopener"><i class="fab fa-github" aria-hidden="true"></i>GitHub 仓库</a></li>
                <li><a href="mailto:support@bluewhois.com"><i class="fas fa-life-ring" aria-hidden="true"></i>技术支持</a></li>
                <li><a href="https://xifeng.net" target="_blank" rel="noopener"><i class="fas fa-wind" aria-hidden="true"></i>西风</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="copyright">© <?php echo date('Y'); ?> <span class="copyright-brand">BlueWhois</span>. All rights reserved.</div>
        <nav class="footer-nav">
            <button type="button" class="footer-legal-trigger" data-legal-modal="privacy"><i class="fas fa-user-shield" aria-hidden="true"></i>隐私政策</button>
            <button type="button" class="footer-legal-trigger" data-legal-modal="terms"><i class="fas fa-file-contract" aria-hidden="true"></i>使用条款</button>
            <a href="https://github.com/gentpan/Bluewhois" target="_blank" rel="noopener" class="footer-github-link" aria-label="GitHub">
                <i class="fab fa-github"></i>
            </a>
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
            <section class="legal-section">
                <h4>1. 适用范围</h4>
                <p>本政策适用于 BlueWhois（bluewhois.com）在提供域名查询、技术展示与相关页面访问过程中对信息的处理活动。</p>
            </section>

            <section class="legal-section">
                <h4>2. 我们处理的信息类型</h4>
                <ul>
                    <li>查询参数：如用户提交的域名、TLD 等输入内容。</li>
                    <li>访问与运行日志：如请求时间、来源 IP、浏览器标识、错误码、耗时与防护事件。</li>
                    <li>服务交互数据：如语言偏好、主题偏好等本地配置数据。</li>
                </ul>
            </section>

            <section class="legal-section">
                <h4>3. 使用目的与法律基础</h4>
                <p>我们仅在提供查询服务、保障系统安全、优化稳定性与履行合规义务的必要范围内处理信息，不用于出售个人数据或无关营销用途。</p>
            </section>

            <section class="legal-section">
                <h4>4. 数据共享与第三方</h4>
                <p>查询结果可能来自 RDAP、注册局/注册商公开接口及上游 WHOIS 服务。为完成请求，必要的查询参数将发送至对应上游服务。</p>
            </section>

            <section class="legal-section">
                <h4>5. 保留期限与安全措施</h4>
                <p>日志信息按最小必要原则保留，并采用访问控制、速率限制、异常检测与传输加密等措施降低风险。</p>
            </section>

            <section class="legal-section">
                <h4>6. 用户权利与联系我们</h4>
                <p>如需咨询数据处理事项、提出删除/更正请求或反馈隐私问题，请联系：<a href="mailto:support@bluewhois.com">support@bluewhois.com</a>。</p>
            </section>

            <p class="legal-updated">最后更新：2026-02-13</p>
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
            <section class="legal-section">
                <h4>1. 服务性质</h4>
                <p>BlueWhois 提供域名 WHOIS/RDAP 查询与信息展示服务，仅供技术参考、运维排查和合规性辅助分析使用。</p>
            </section>

            <section class="legal-section">
                <h4>2. 用户义务</h4>
                <ul>
                    <li>遵守适用法律法规及互联网服务规范。</li>
                    <li>不得实施恶意扫描、批量滥用、绕过限流或其他影响平台稳定性的行为。</li>
                    <li>不得将结果用于非法用途或侵犯第三方合法权益。</li>
                </ul>
            </section>

            <section class="legal-section">
                <h4>3. 结果声明</h4>
                <p>查询数据来源于公开注册信息与第三方服务接口，可能存在延迟、缺失、脱敏或策略差异，不构成法律、金融或商业担保。</p>
            </section>

            <section class="legal-section">
                <h4>4. 可用性与限制</h4>
                <p>平台可能基于安全、运维或上游策略对部分后缀、查询频率与请求来源进行限制或临时调整，恕不另行逐项通知。</p>
            </section>

            <section class="legal-section">
                <h4>5. 知识产权</h4>
                <p>站点页面、文案、代码结构与品牌标识受相关法律保护，未经授权不得复制、反向用于商业重分发或冒用品牌身份。</p>
            </section>

            <section class="legal-section">
                <h4>6. 责任边界</h4>
                <p>在法律允许范围内，BlueWhois 对因上游服务中断、网络异常、策略变更或不可抗力导致的结果偏差与服务中断不承担间接损失责任。</p>
            </section>

            <section class="legal-section">
                <h4>7. 条款更新</h4>
                <p>我们可根据业务与合规需求更新条款；更新后继续使用本服务视为接受最新版本。</p>
            </section>

            <p class="legal-updated">最后更新：2026-02-13</p>
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
