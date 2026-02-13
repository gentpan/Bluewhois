<?php
$page_title = '关于 BlueWhois';
include '../header.php';
?>

<section class="hero">
    <h1><i class="fas fa-info-circle page-icon"></i>关于 BlueWhois</h1>
    <p class="subtitle">了解 BlueWhois 的 WHOIS 查询服务</p>
</section>

<section class="page-container">
    <!-- 关于内容卡片 -->
    <div class="content-card">
        <h2>服务介绍</h2>
        <p>
            BlueWhois 提供专业的 WHOIS 域名查询服务，帮助您快速获取域名的注册信息、到期时间、注册商等详细信息。
            基于 WhoAPI 和 WhoisXML API 服务，确保查询结果的准确性和完整性。
        </p>
        <p>
            无论您是域名投资者、网站管理员还是网络安全研究人员，BlueWhois 都能为您提供便捷、高效的查询体验。
        </p>
    </div>

    <!-- 功能特点 -->
    <div class="content-card">
        <h2>功能特点</h2>
        <div class="content-grid">
            <div class="feature-item">
                <div class="feature-icon-wrapper blue">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="feature-item-content">
                    <h3>快速查询</h3>
                    <p>毫秒级响应速度，快速获取域名信息</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="feature-item-content">
                    <h3>准确可靠</h3>
                    <p>基于权威 WHOIS 数据库，确保信息准确性</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper purple">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="feature-item-content">
                    <h3>全球支持</h3>
                    <p>支持全球各类顶级域名查询</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrapper orange">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-item-content">
                    <h3>隐私保护</h3>
                    <p>严格遵守 GDPR 等隐私保护法规</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 技术说明 -->
    <div class="content-card">
        <h2>技术说明</h2>
        <ul>
            <li>基于 WhoAPI 和 WhoisXML API 提供的专业 WHOIS 查询服务</li>
            <li>支持多种顶级域名（gTLD、ccTLD、sTLD、IDN TLD、Brand TLD）</li>
            <li>自动识别域名类型并选择最优查询接口</li>
            <li>提供完整的域名注册信息，包括创建日期、到期日期、注册商等</li>
            <li>支持域名状态翻译和详细说明</li>
        </ul>
    </div>
</section>

<?php include '../footer.php'; ?>
