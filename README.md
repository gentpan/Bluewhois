# BlueWhois

BlueWhois 是一个基于 PHP 的 WHOIS/RDAP 查询站点，支持免费查询链路：

- `RDAP (Primary)` 优先
- `WHOIS Port43 (Fallback)` 兜底
- 统一错误返回与前端展示

## Features

- 域名查询（前端输入校验）
- API 路由：`/api/{domain}`
- 旧 API 兼容：`/whois.php?mode=api&domain=example.com`
- RDAP 引导数据缓存（IANA bootstrap）
- WHOIS 服务器自动发现与回退
- ccTLD 页面与非 ccTLD 页面分离展示
- 多语言切换（中文/English）

## Project Structure

```text
.
├── api/index.php             # API 路由入口
├── index.php                 # 首页
├── whois.php                 # API/页面查询入口
├── function.php              # 核心函数（RDAP/WHOIS/校验/缓存）
├── config.php                # 配置项
├── header.php / footer.php   # 公共布局
├── pages/                    # 内页（iana / cctlds / about / contact / api-docs）
├── assets/
│   ├── css/                  # 样式
│   ├── js/                   # 前端逻辑
│   └── fonts/
├── data/iana-tlds.php        # 本地 TLD 数据
├── cache/                    # 缓存目录（运行时）
└── logs/                     # 日志目录（运行时）
```

## Requirements

- PHP 8.1+
- `mbstring` 扩展（用于 emoji 等字符处理）
- 建议启用 `intl` 扩展（用于国家名显示）
- Web Server：Nginx / Apache / Caddy

## Quick Start

### 1) 本地启动（PHP 内置服务器）

```bash
php -S 127.0.0.1:8000 -t .
```

打开：

- `http://127.0.0.1:8000/`
- `http://127.0.0.1:8000/pages/iana.php`
- `http://127.0.0.1:8000/pages/cctlds.php`

### 2) API 调用

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/example.com"
```

兼容调用：

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/whois.php?mode=api&domain=example.com"
```

## Nginx Route (Recommended)

```nginx
location ~ ^/api/([^/]+)/?$ {
    try_files $uri /api/index.php?domain=$1$is_args$args;
}
```

## Config Notes

`config.php` 中可配置：

- 缓存目录、TTL
- 日志开关
- 速率限制参数
- 其他运行参数

## Query Chain

1. 前端校验域名格式，非法输入直接阻断  
2. 后端 `dl_queryWhois()` 先走 `dl_queryRdapFree()`  
3. RDAP 失败后走 `dl_queryWhoisFallback()`  
4. 返回统一结构：`success / data / error / api_used`

## Data Separation

- `pages/iana.php`：仅展示非 `country-code` 顶级域名
- `pages/cctlds.php`：仅展示 `country-code`（ccTLD），含国家与国旗

## License

Private/Internal use by repository owner.
