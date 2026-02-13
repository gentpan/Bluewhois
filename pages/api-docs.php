<?php $page_title = 'API 使用说明';
include __DIR__ . '/../header.php'; ?>

<section class="hero api-docs-hero">
    <h1><i class="fas fa-book page-icon"></i>API 使用说明</h1>
    <p class="subtitle">综合查询接口：WHOIS / IP / DNS（含 Domain Lookback 规划）</p>
</section>

<section class="page-container api-docs-page">

    <!-- 综合查询 -->
    <div class="content-card api-section api-overview-card">
        <div class="api-section-header api-overview-header">
            <div class="api-section-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div>
                <div class="api-section-title">综合查询</div>
                <div class="api-section-subtitle">域名/IPv4/IPv6 统一输入，返回注册信息与网络数据</div>
            </div>
        </div>

        <div class="api-metric-row">
            <div class="api-metric">
                <span class="api-metric-label">输入目标</span>
                <strong>DOMAIN / IPv4 / IPv6</strong>
            </div>
            <div class="api-metric">
                <span class="api-metric-label">核心链路</span>
                <strong>RDAP 优先 + WHOIS 补充</strong>
            </div>
            <div class="api-metric">
                <span class="api-metric-label">数据输出</span>
                <strong>WHOIS + IP + DNS</strong>
            </div>
        </div>

        <div class="api-block-grid">
            <div class="api-block">
                <h3><i class="fas fa-layer-group"></i>查询能力</h3>
                <ul class="api-pill-list">
                    <li><strong>WHOIS / RDAP：</strong>域名注册信息、注册商、到期时间、状态码</li>
                    <li><strong>IP 查询：</strong>IPv4 / IPv6 归属网络、ASN、地理位置</li>
                    <li><strong>DNS 摘要：</strong>A / AAAA / CNAME / MX / NS / TXT</li>
                    <li><strong>Lookback：</strong>历史快照能力（规划中）</li>
                </ul>
            </div>
            <div class="api-block">
                <h3><i class="fas fa-list-check"></i>返回结构</h3>
                <ul class="api-pill-list">
                    <li><strong>基础信息：</strong>目标类型、查询耗时、缓存状态</li>
                    <li><strong>注册信息：</strong>Registrar、注册时间、到期时间、联系人</li>
                    <li><strong>网络信息：</strong>Nameserver、IP、ASN、网络段</li>
                    <li><strong>扩展信息：</strong>地理坐标、地图跳转、刷新缓存</li>
                </ul>
            </div>
            <div class="api-block">
                <h3><i class="fas fa-link"></i>示例目标</h3>
                <div class="api-code-block api-target-links">
                    <span>域名</span><strong>bluewhois.com</strong>
                    <span>IPv4</span><strong>1.1.1.1</strong>
                    <span>IPv6</span><strong>2606:4700:4700::1111</strong>
                </div>
            </div>
            <div class="api-block">
                <h3><i class="fas fa-signal"></i>能力状态</h3>
                <div class="api-status-grid">
                    <div><span>WHOIS / RDAP</span><b>已上线</b></div>
                    <div><span>IP 查询</span><b>已上线</b></div>
                    <div><span>DNS 摘要</span><b>已上线</b></div>
                    <div><span>Lookback</span><b>规划中</b></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 使用说明 -->
    <div class="content-card api-guide-card api-guide-modern">
        <div class="api-section-header api-guide-headlike">
            <div class="api-section-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <div class="api-section-title">使用说明</div>
                <div class="api-section-subtitle">接口规范、数据来源与执行策略说明</div>
            </div>
        </div>

        <div class="api-guide-grid">
            <section class="api-guide-panel">
                <h3><i class="fas fa-database"></i>数据来源</h3>
                <ul class="api-guide-list">
                    <li><strong>whoAPI</strong><span><a href="https://whoapi.com/whois-api/" target="_blank" rel="noopener">Whois API</a></span></li>
                    <li><strong>WhoisXML API</strong><span><a href="https://whois.whoisxmlapi.com/lookup" target="_blank" rel="noopener">WHOIS Lookup</a></span></li>
                    <li><strong>RDAP 服务器列表</strong><span>从 <a href="https://data.iana.org/rdap/dns.json" target="_blank" rel="noopener">https://data.iana.org/rdap/dns.json</a> 下载，30 天缓存</span></li>
                    <li><strong>Public Suffix List</strong><span>从 <a href="https://publicsuffix.org/list/public_suffix_list.dat" target="_blank" rel="noopener">https://publicsuffix.org/list/public_suffix_list.dat</a> 下载，30 天缓存</span></li>
                    <li><strong>IP 信息来源</strong><span><a href="https://www.maxmind.com/en/geoip-databases" target="_blank" rel="noopener">MaxMind GeoIP Databases</a></span></li>
                </ul>
            </section>

            <section class="api-guide-panel">
                <h3><i class="fas fa-route"></i>接口路径</h3>
                <p class="api-guide-note">统一 API 路径：其中 <code>{target}</code> 可以是域名、IPv4、IPv6。</p>
                <div class="api-path-list">
                    <code>https://bluewhois.com/api/{target}</code>
                </div>
                <div class="api-example-list">
                    <p>示例（域名）：<a href="https://bluewhois.com/api/bluewhois.com" target="_blank" rel="noopener"><code>https://bluewhois.com/api/bluewhois.com</code></a></p>
                    <p>示例（IPv4）：<a href="https://bluewhois.com/api/1.1.1.1" target="_blank" rel="noopener"><code>https://bluewhois.com/api/1.1.1.1</code></a></p>
                    <p>示例（IPv6）：<a href="https://bluewhois.com/api/2606:4700:4700::1111" target="_blank" rel="noopener"><code>https://bluewhois.com/api/2606:4700:4700::1111</code></a></p>
                    <p class="api-guide-tip">注意：实际请求时建议对 IPv6 做 URL 编码（例如 <code>%3A</code>）。</p>
                </div>
            </section>

            <section class="api-guide-panel">
                <h3><i class="fas fa-gears"></i>执行策略</h3>
                <ul class="api-guide-list compact">
                    <li><strong>域名查询</strong><span>优先 RDAP，并结合付费 WHOIS 数据源（WhoAPI / WhoisXML）补充完整字段。</span></li>
                    <li><strong>IP 查询</strong><span>使用 RDAP IP，结合 MaxMind GeoIP 数据补充网络与地理信息。</span></li>
                </ul>
            </section>

            <section class="api-guide-panel">
                <h3><i class="fas fa-shield-halved"></i>错误处理</h3>
                <p class="api-guide-note">当主要服务不可用时，系统会自动切换备用数据源并返回标准错误结构，确保接口可用性与可诊断性。</p>
            </section>
        </div>
    </div>

    <!-- 技术支持 -->
    <div class="api-support-card">
        <h2><i class="fas fa-headset" style="margin-right: 8px;"></i>技术支持</h2>
        <p>如果您在使用过程中遇到任何问题，请通过以下方式联系我们：</p>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 16px;">
            <div>
                <p><i class="fas fa-envelope" style="margin-right: 8px;"></i>邮箱：support@bluewhois.com</p>
                <p><i class="fas fa-bug" style="margin-right: 8px;"></i>问题反馈：GitHub Issues</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../footer.php'; ?>
