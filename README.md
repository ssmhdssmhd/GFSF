# 🦋 GFSF · 算法项目

> GFSF 算法学习与实现集合：数据结构与算法、刷题记录、常用算法模板与工具。

- 仓库地址：[github.com/ssmhdssmhd/GFSF](https://github.com/ssmhdssmhd/GFSF)
- 当前版本：`v0.0.4`（2026-10-05）
- Logo 来源：[MXLOGO · 射手沫蝴蝶 Logo 资产库](https://github.com/ssmhdssmhd/MXLOGO)

![GFSF Logo](https://cdn.jsdelivr.net/gh/ssmhdssmhd/MXLOGO@main/web/web-logo.svg)

---

## 📁 目录结构

```
GFSF/
├── dsck.php                # Cookie 自动检查 / 生成 / 同步 调度脚本（可作计划任务）
├── cookies/                # GitHub Actions 自动维护的 cookie 文件（可从 GitHub raw 读取）
├── gfsfadmin/              # 后台管理系统（登录、运行控制台、Cookie 管理、扫码登录）
├── uploads_sf/算法/         # 各平台解析 / cookie 校验 / cookie 获取脚本
├── .github/workflows/      # GitHub Actions（cookie-auto.yml：云端自动检查并生成 cookie）
├── README.md               # 项目说明
└── VERSION                 # 版本信息
```

> 具体内容随版本迭代逐步补充。

---

## 🚀 快速开始

1. 克隆仓库

```bash
git clone https://github.com/ssmhdssmhd/GFSF.git
```

2. 选择对应的算法目录进入即可查看或运行示例代码。

---

## 🍪 Cookie 全程自动化

- **dsck.php 调度脚本**：检查各平台 cookie 有效性，失效时自动重新获取真实 cookie 并写入对应文件（`uploads_sf/算法/{平台}/ck.txt`）；支持 URL 计划任务与 CLI 两种模式：

```bash
# 检查所有平台
php dsck.php --action check --platform all
# 自动处理（检查 → 失效自动生成 → 导出 cookies/）
php dsck.php --action auto
# 域名计划任务（如 cron / 宝塔计划任务）
curl "https://你的域名/dsck.php?action=auto&platform=all&token=可选令牌"
```

- **GitHub Actions 云端自动化**（`.github/workflows/cookie-auto.yml`）：每 6 小时自动检查 cookie，失效自动获取，并把最新 cookie 提交回仓库 `cookies/` 目录；也可在 Actions 页面手动触发。

- **读取 GitHub 中的 cookie**：

```bash
# 方式一：raw 链接
curl "https://raw.githubusercontent.com/ssmhdssmhd/GFSF/main/cookies/bl.txt"
# 方式二：dsck.php 拉取到本地并校验
php dsck.php --action pull --platform bl
```

> 可自动化平台：哔哩哔哩（bl）、芒果TV（mg）已有校验/获取脚本；腾讯、爱奇艺、优酷需手动或扫码更新 cookie。

---

## 📄 更新日志

| 版本 | 日期 | 更新内容 |
|---|---|---|
| v0.0.4 | 2026-10-05 | 新增 Cookie 全程自动化：dsck.php 调度脚本（检查/自动生成/拉取 GitHub cookie/导出）、GitHub Actions 定时检查并提交 cookies/ 目录、可选项访问令牌 |
| v0.0.3 | 2026-10-05 | 后台 v0.0.3：运行控制台播放区 + Cookie 生成与扫码登录（B站/芒果TV），接口真实测试通过 |
| v0.0.2 | 2026-10-05 | 新增后台管理（gfsfadmin）：登录、平台概览、脚本运行控制台、Cookie 管理；算法脚本真实测试（见 TEST-REPORT.md） |
| v0.0.1 | 2026-10-05 | 仓库初始化：README、版本信息、基础文件（.gitignore / VERSION） |

---

**© 2026 射手沫蝴蝶** · 由 [github.com/ssmhdssmhd/GFSF](https://github.com/ssmhdssmhd/GFSF) 提供
