<?php
$page_title = 'ccTLDs - 国家代码顶级域名';
include __DIR__ . '/../header.php';
?>

<section class="hero">
    <h1><i class="fas fa-flag page-icon"></i>Country Code Top-Level Domains (ccTLDs)</h1>
    <p class="subtitle">国家代码顶级域名列表（仅 ccTLD）</p>
</section>

<section class="page-container">
    <?php
    /**
     * 通过 ccTLD 代码获取国家/地区名称
     */
    function ccTldCountryName($code)
    {
        $code = strtoupper(trim((string)$code));
        if ($code === '') return '未知';

        // IANA/历史/特殊映射优先
        $overrides = [
            'UK' => 'United Kingdom',
            'EU' => 'European Union',
            'AC' => 'Ascension Island',
            'TA' => 'Tristan da Cunha',
            'SU' => 'Soviet Union (legacy)',
            'TP' => 'Timor-Leste (legacy)',
            'AN' => 'Netherlands Antilles (historical)',
            'EH' => 'Western Sahara',
            'AQ' => 'Antarctica',
        ];
        if (isset($overrides[$code])) {
            return $overrides[$code];
        }

        // 若系统启用 intl 扩展，优先用区域代码解析国家名
        if (class_exists('Locale')) {
            $name = \Locale::getDisplayRegion('-' . $code, 'en');
            if (is_string($name) && $name !== '' && strtoupper($name) !== $code) {
                return $name;
            }
        }

        return $code;
    }

    /**
     * 通过 ccTLD 代码生成国旗 emoji
     */
    function ccTldFlagEmoji($code)
    {
        $code = strtoupper(trim((string)$code));
        if ($code === '') return '🌐';

        // 特殊映射：.uk 实际对应 GB
        $flagCode = ($code === 'UK') ? 'GB' : $code;
        if (!preg_match('/^[A-Z]{2}$/', $flagCode)) {
            return '🌐';
        }

        $base = 0x1F1E6;
        $first = mb_chr($base + (ord($flagCode[0]) - ord('A')), 'UTF-8');
        $second = mb_chr($base + (ord($flagCode[1]) - ord('A')), 'UTF-8');
        if (!$first || !$second) {
            return '🌐';
        }
        return $first . $second;
    }
    ?>

    <!-- 说明卡片 -->
    <div class="info-card">
        <p>
            Country code top-level domains (ccTLDs) are two-letter domain extensions assigned to countries and territories.
        </p>
        <p>
            国家代码顶级域名（ccTLD）是分配给国家和地区的两字母域名后缀。本页仅展示 ccTLD 条目。
        </p>
    </div>

    <!-- 表格容器 -->
    <div class="table-wrapper">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Country</th>
                        <th>Type</th>
                        <th>TLD Manager</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 加载数据并按域名去重
                    $rawTlds = require __DIR__ . '/../data/iana-tlds.php';
                    $priority = [
                        'country-code' => 4,
                        'sponsored' => 3,
                        'generic-restricted' => 2,
                        'generic' => 1,
                    ];
                    $tldMap = [];
                    foreach ($rawTlds as $row) {
                        $domainKey = strtolower(trim((string)($row['domain'] ?? '')));
                        if ($domainKey === '') continue;
                        if (!isset($tldMap[$domainKey])) {
                            $tldMap[$domainKey] = $row;
                            continue;
                        }
                        $oldType = (string)($tldMap[$domainKey]['type'] ?? '');
                        $newType = (string)($row['type'] ?? '');
                        if (($priority[$newType] ?? 0) > ($priority[$oldType] ?? 0)) {
                            $tldMap[$domainKey] = $row;
                        }
                    }
                    $tlds = array_values(array_filter($tldMap, function ($row) {
                        return (string)($row['type'] ?? '') === 'country-code';
                    }));

                    foreach ($tlds as $tld) {
                        $domain = htmlspecialchars($tld['domain']);
                        $country = htmlspecialchars(ccTldCountryName($tld['domain'] ?? ''));
                        $manager = htmlspecialchars($tld['manager']);
                        $ianaUrl = "https://www.iana.org/domains/root/db/{$domain}.html";
                    ?>
                    <tr>
                        <td><a href="<?= $ianaUrl ?>" target="_blank">.<?= $domain ?></a></td>
                        <td><?= ccTldFlagEmoji($tld['domain'] ?? '') ?> <?= $country ?></td>
                        <td><span class="badge badge-green">country-code</span></td>
                        <td><?= $manager ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 提示信息 -->
    <div class="info-box">
        <p>
            <i class="fas fa-info-circle info-icon"></i>
            当前展示 ccTLD 共 <?= count($tlds) ?> 个。更多信息请访问：
        </p>
        <a href="https://www.iana.org/domains/root/db" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            IANA Root Zone Database
        </a>
    </div>

    <!-- 统计信息卡片 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= count($tlds) ?></div>
            <div class="stat-label">ccTLD 总数</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--success);">2字母</div>
            <div class="stat-label">ISO 3166-1 为主</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #9c27b0;">IANA</div>
            <div class="stat-label">统一委派管理</div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../footer.php'; ?>
