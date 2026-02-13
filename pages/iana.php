<?php
$page_title = 'IANA - 根域名数据库';
include __DIR__ . '/../header.php';
?>

<section class="hero">
    <h1><i class="fas fa-database page-icon"></i>Root Zone Database</h1>
    <p class="subtitle">根域名数据库 - 通用/赞助/受限顶级域名（不含 ccTLD）</p>
</section>

<section class="page-container">
    <!-- 说明卡片 -->
    <div class="info-card">
        <p>
            This page lists non-ccTLD entries in the root zone database, including generic, generic-restricted, and sponsored TLDs.
        </p>
        <p>
            本页仅展示非国家代码顶级域名（不含 ccTLD），包括通用、受限通用和赞助型顶级域名。
        </p>
    </div>

    <!-- 表格容器 -->
    <div class="table-wrapper">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Type</th>
                        <th>TLD Manager</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 加载数据并按域名去重
                    $rawTlds = require __DIR__ . '/../data/iana-tlds.php';
                    $priority = [
                        'country-code' => 4,        // 同域名冲突时，以更权威类型为准
                        'sponsored' => 3,
                        'generic-restricted' => 2,
                        'generic' => 1,
                    ];
                    $tldMap = [];
                    foreach ($rawTlds as $row) {
                        $domainKey = strtolower(trim((string)($row['domain'] ?? '')));
                        if ($domainKey === '') {
                            continue;
                        }
                        if (!isset($tldMap[$domainKey])) {
                            $tldMap[$domainKey] = $row;
                            continue;
                        }
                        $oldType = (string)($tldMap[$domainKey]['type'] ?? '');
                        $newType = (string)($row['type'] ?? '');
                        $oldP = $priority[$oldType] ?? 0;
                        $newP = $priority[$newType] ?? 0;
                        if ($newP > $oldP) {
                            $tldMap[$domainKey] = $row;
                        }
                    }
                    // IANA 页面仅显示非 ccTLD
                    $tlds = array_values(array_filter($tldMap, function ($row) {
                        return (string)($row['type'] ?? '') !== 'country-code';
                    }));

                    foreach ($tlds as $tld) {
                        $domain = htmlspecialchars($tld['domain']);
                        $type = $tld['type'];
                        $manager = htmlspecialchars($tld['manager']);

                        // 确定标签样式类
                        $badgeClass = 'badge ';
                        if (in_array($type, ['generic', 'generic-restricted'])) {
                            $badgeClass .= 'badge-blue';
                        } elseif ($type === 'country-code') {
                            $badgeClass .= 'badge-green';
                        } elseif ($type === 'sponsored') {
                            $badgeClass .= 'badge-purple';
                        } else {
                            $badgeClass .= 'badge-gray';
                        }

                        // 生成IANA链接
                        $ianaUrl = "https://www.iana.org/domains/root/db/{$domain}.html";
                    ?>
                        <tr>
                            <td>
                                <a href="<?= $ianaUrl ?>" target="_blank">.<?= $domain ?></a>
                            </td>
                            <td>
                                <span class="<?= $badgeClass ?>"><?= htmlspecialchars($type) ?></span>
                            </td>
                            <td><?= $manager ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 统计信息 -->
    <div class="stats-grid">
        <?php
        $typeCounts = [];
        foreach ($tlds as $tld) {
            $type = $tld['type'];
            if (!isset($typeCounts[$type])) {
                $typeCounts[$type] = 0;
            }
            $typeCounts[$type]++;
        }
        ?>
        <div class="stat-card">
            <div class="stat-value"><?= count($tlds) ?></div>
            <div class="stat-label">非 ccTLD 总数</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= isset($typeCounts['generic']) ? $typeCounts['generic'] + (isset($typeCounts['generic-restricted']) ? $typeCounts['generic-restricted'] : 0) : 0 ?></div>
            <div class="stat-label">通用域名</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--success);"><?= isset($typeCounts['generic-restricted']) ? $typeCounts['generic-restricted'] : 0 ?></div>
            <div class="stat-label">受限通用</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #9c27b0;"><?= isset($typeCounts['sponsored']) ? $typeCounts['sponsored'] : 0 ?></div>
            <div class="stat-label">赞助域名</div>
        </div>
    </div>

    <!-- 提示信息 -->
    <div class="info-box">
        <p>
            <i class="fas fa-info-circle info-icon"></i>
            当前展示非 ccTLD 共 <?= count($tlds) ?> 个。国家代码域名请查看 ccTLDs 页面。更多信息请访问：
        </p>
        <a href="https://www.iana.org/domains/root/db" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            IANA Root Zone Database
        </a>
    </div>

    <!-- 域名类型说明 -->
    <div class="type-explanation-grid">
        <div class="type-card">
            <div class="type-card-badge">
                <span class="badge badge-blue">generic</span>
            </div>
            <h4>通用顶级域</h4>
            <p>如 .com, .net, .org</p>
        </div>
        <div class="type-card">
            <div class="type-card-badge">
                <span class="badge badge-purple">sponsored</span>
            </div>
            <h4>赞助顶级域</h4>
            <p>如 .aero, .edu</p>
        </div>
        <div class="type-card">
            <div class="type-card-badge">
                <span class="badge badge-orange">generic-restricted</span>
            </div>
            <h4>受限通用域</h4>
            <p>如 .biz</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../footer.php'; ?>
