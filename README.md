# BlueWhois

BlueWhois 是一个基于 PHP 的综合网络查询站点，支持 `DOMAIN / IPv4 / IPv6` 统一输入与查询。

## Core Capabilities

- 综合查询：WHOIS / RDAP / DNS / IP 信息
- 统一 API：`/api/{target}`（`target` 支持域名、IPv4、IPv6）
- 页面直查：`/{target}`（直接打开结果页）
- RDAP 优先，失败自动回退 WHOIS 兜底链路
- 统一错误返回与前端状态展示
- API 缓存与手动刷新
- 内页路由：`/about`、`/contact`、`/api-docs`

## Project Structure

```text
.
├── api/index.php             # API 路由入口
├── index.php                 # 首页 + 直查路由入口
├── whois.php                 # API/页面查询处理
├── function.php              # 核心函数（RDAP/WHOIS/IP/DNS/缓存）
├── config.php                # 配置项
├── header.php / footer.php   # 公共布局
├── pages/                    # 内页（iana / cctlds / about / contact / api-docs）
├── about/ contact/ api-docs/ # 目录路由入口（伪静态友好）
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── fonts/
├── data/iana-tlds.php
├── cache/                    # 运行时缓存
└── logs/                     # 运行时日志
```

## Requirements

- PHP 8.1+
- `mbstring` 扩展
- 建议启用 `intl` 扩展
- Web Server：Nginx / Apache / Caddy

## Quick Start

### 1) 本地启动

```bash
php -S 127.0.0.1:8000 -t .
```

访问示例：

- `http://127.0.0.1:8000/`
- `http://127.0.0.1:8000/about`
- `http://127.0.0.1:8000/contact`
- `http://127.0.0.1:8000/api-docs`
- `http://127.0.0.1:8000/bluewhois.com`
- `http://127.0.0.1:8000/1.1.1.1`

### 2) API 调用

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/bluewhois.com"
```

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/1.1.1.1"
```

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/2606:4700:4700::1111"
```

IP 地理信息（ip.sb 专用）：

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/api/ip/1.1.1.1"
```

兼容调用：

```bash
curl -H "Accept: application/json" \
  "http://127.0.0.1:8000/whois.php?mode=api&domain=bluewhois.com"
```

## Nginx Pseudo-static (Recommended)

```nginx
location / {
    try_files $uri $uri/ @bluewhois_router;
}

location @bluewhois_router {
    # IP Geo API: /api/ip/{ip}
    rewrite ^/api/ip/([^/]+)/?$ /api/index.php?mode=ipgeo&ip=$1&$args last;

    # API: /api/{target}
    rewrite ^/api/([^/]+)/?$ /api/index.php?target=$1&$args last;

    # 兼容旧 API（可选保留）: /{target}/api
    rewrite ^/([^/]+)/api/?$ /api/index.php?target=$1&$args last;

    # 页面直查: /{target}
    rewrite ^/([^/]+)/?$ /index.php?domain=$1&$args last;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass 127.0.0.1:9000;
}
```

如果你只保留一种 API 形式，请删除：

```nginx
rewrite ^/([^/]+)/api/?$ /api/index.php?target=$1&$args last;
```

## Query Strategy

1. 规范化输入目标（域名 / IPv4 / IPv6）
2. 域名优先走 RDAP，再走 WHOIS 兜底
3. IP 走 RDAP IP + 地理信息补充
4. 统一返回：`success / data / error / api_used / cached`

## Update Notes

- 新增综合查询能力：支持域名、IPv4、IPv6
- API 文档重构：路径、示例、数据源说明统一
- 新增 API 缓存标识与刷新机制
- 优化内页与卡片结构样式一致性
- 新增目录路由入口：`/about`、`/contact`、`/api-docs`

## License

Private/Internal use by repository owner.
