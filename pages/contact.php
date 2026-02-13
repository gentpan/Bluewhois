<?php
$page_title = '联系 BlueWhois';
include __DIR__ . '/../header.php';
?>

<section class="hero">
    <h1><i class="fas fa-envelope page-icon"></i>联系 BlueWhois</h1>
    <p class="subtitle">如有任何问题或建议，欢迎通过邮箱联系我们</p>
</section>

<section class="page-container">
    <!-- 联系卡片 -->
    <div class="content-card">
        <h2>联系方式</h2>
        <div class="contact-list">
            <div class="contact-item">
                <div class="contact-icon-wrapper blue">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-item-content">
                    <h3>邮箱联系</h3>
                    <a href="mailto:support@bluewhois.com">support@bluewhois.com</a>
                </div>
            </div>
            <div class="contact-item">
                <div class="contact-icon-wrapper green">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="contact-item-content">
                    <h3>反馈建议</h3>
                    <p>如果您在使用过程中遇到问题或有改进建议，欢迎通过邮件与我们联系。</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 常见问题 -->
    <div class="content-card">
        <h2>常见问题</h2>
        <div class="faq-item">
            <h3>查询结果显示不完整？</h3>
            <p>由于 GDPR 等隐私保护法规，部分域名的完整信息可能被隐藏。我们已尽力提取所有可用的结构化数据。</p>
        </div>
        <div class="faq-item">
            <h3>支持哪些域名类型？</h3>
            <p>我们支持所有常见的顶级域名，包括 .com、.net、.org 等 gTLD，以及 .cn、.uk 等 ccTLD，还包括 IDN 域名。</p>
        </div>
        <div class="faq-item">
            <h3>查询速度如何？</h3>
            <p>我们的服务基于专业的 WHOIS API，查询速度通常在 1-2 秒内完成，确保快速响应用户请求。</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../footer.php'; ?>
