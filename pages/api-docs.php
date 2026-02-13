<?php $page_title = 'API 使用说明';
include '../header.php'; ?>

<section class="hero">
    <h1><i class="fas fa-book page-icon"></i>API 使用说明</h1>
    <p class="subtitle">了解如何使用 BlueWhois 的查询接口</p>
</section>

<section class="page-container">

    <!-- WHOIS 查询 -->
    <div class="content-card api-section">
        <div class="api-section-header">
            <div class="api-section-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div>
                <div class="api-section-title">WHOIS 查询</div>
                <div class="api-section-subtitle">获取域名注册信息和详细资料</div>
            </div>
        </div>

        <div class="api-info-grid">
            <div class="api-info-item">
                <h3>支持的查询</h3>
                <ul>
                    <li>域名注册信息</li>
                    <li>注册商信息</li>
                    <li>创建和过期日期</li>
                    <li>联系人信息</li>
                    <li>DNS 服务器信息</li>
                </ul>
            </div>
            <div class="api-info-item">
                <h3>示例域名</h3>
                <div class="api-code-block">
                    example.com<br>
                    google.com<br>
                    github.com
                </div>
            </div>
        </div>
    </div>

    <!-- 使用说明 -->
    <div class="content-card">
        <h2><i class="fas fa-info-circle" style="color: var(--accent-black); margin-right: 8px;"></i>使用说明</h2>
        <div>
            <h3>数据来源</h3>
            <p>我们使用多个可靠的数据源来确保数据的准确性和可用性：</p>
            <ul>
                <li>WhoAPI - WHOIS 查询数据源（付费 API）</li>
                <li>WhoisXML API - 备用 WHOIS 查询服务</li>
            </ul>
        </div>
        <div style="margin-top: 24px;">
            <h3>查询模式</h3>
            <p>当前接口默认使用免费 RDAP 查询，覆盖大量主流后缀，不再需要额外传参。</p>
            <p>对于 RDAP 暂无引导的后缀（如 .sb、.bi），系统会自动尝试 WHOIS Port43 后备服务器。</p>
            <p>示例：<code>/api/example.com</code></p>
        </div>
        <div style="margin-top: 24px;">
            <h3>错误处理</h3>
            <p>如果主要 API 服务不可用，系统会自动尝试备用方案，确保服务的可用性。</p>
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

<?php include '../footer.php'; ?>
