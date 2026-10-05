# 🦋 GFSF · 算法项目

> GFSF 算法学习与实现集合：各平台视频解析算法脚本 + 后台管理 + Cookie 全程自动化。

- 仓库地址：[github.com/ssmhdssmhd/GFSF](https://github.com/ssmhdssmhd/GFSF)
- 当前版本：`v0.0.7`（2026-10-05）
- Logo 来源：[MXLOGO · 射手沫蝴蝶 Logo 资产库](https://github.com/ssmhdssmhd/MXLOGO)

![GFSF Logo](https://cdn.jsdelivr.net/gh/ssmhdssmhd/MXLOGO@main/web/web-logo.svg)

---

## 📁 目录结构（任意 PHP 环境直接部署）

```
GFSF/                        ← 部署根目录（站点根 / 宝塔站点 / 虚拟主机均可）
├── index.php                # 后台主页（登录后使用：运行控制台 + Cookie 管理 + 扫码登录）
├── login.php / logout.php   # 登录 / 退出
├── api.php                  # 后台 API（platforms / scripts / run / cookie_* / qr_login / qr_poll / cookie_pull / update_*）
├── config.php               # 全局配置（管理员账号、脚本根目录自动探测、平台配置）
├── runner.php               # 脚本执行器（CLI 子进程，自动切换脚本目录）
├── dsck.php                 # Cookie 自动检查 / 生成 / 同步 调度脚本（可作计划任务）
├── assets/                  # 样式资源
├── uploads_sf/算法/          # 各平台解析 / cookie 校验 / cookie 获取脚本
├── cookies/                 # GitHub Actions 自动维护的 cookie 文件（可从 GitHub raw 读取）
├── backups/                 # 在线更新前的本地备份（不入库）
├── .github/workflows/       # GitHub Actions（cookie-auto.yml：云端自动检查并生成 cookie）
├── TEST-REPORT.md           # 算法脚本真实测试报告
├── README.md                # 项目说明
└── VERSION                  # 版本信息
```

> **任意 PHP 环境部署**：只需 PHP（需 curl / mbstring 扩展），把仓库根目录作为站点根即可，**无需修改任何代码**（脚本根目录自动探测：环境变量 `GFSF_SCRIPTS_ROOT` > 同级 `uploads_sf/算法` > 上级 `uploads_sf/算法`）。

---

## 🚀 快速开始

```bash
# 1. 安装 PHP（需 curl / mbstring 扩展）
apt-get install -y php-cli php-curl php-mbstring

# 2. 启动（开发环境）
php -S 0.0.0.0:8092 -t /workspace

# 3. 浏览器打开 http://127.0.0.1:8092/login.php
#    用户名 admin，密码见 config.php（首次登录后建议修改）
```

生产环境：将仓库根目录指向 Nginx/Apache/宝塔站点根即可。

---

## 🖥 后台管理（index.php）

- **平台概览**：各平台脚本数量、Cookie 文件状态与预览
- **运行控制台**：选择平台/脚本，输入视频地址真实调用解析算法；解析后先显示结果，点击「内嵌播放」才开始播放（支持新窗口打开 / 复制链接）
- **Cookie 生成**：真实调用生成脚本（blgetck.php / mggetck.php）自动生成并保存 Cookie，自动识别失效 Cookie
- **扫码登录**：腾讯视频（QQ 官方 ptlogin2 扫码）、B 站、芒果TV 生成二维码，手机扫码后自动轮询并保存真实 Cookie；爱奇艺、优酷因官方接口已加密，引导使用「云端获取」或手动管理 Cookie
- **Cookie 管理**：读取 / 编辑 / 保存 / 清空各平台 `ck.txt`（腾讯为 `qqck.txt`）
- **云端获取**：一键从 GitHub 仓库 `cookies/` 拉取最新 Cookie 到本地并自动校验
- **在线更新**：检查 GitHub 最新版本，一键拉取最新代码覆盖本地（自动备份，保留 cookies/、backups/ 与本地 config.php）

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
| v0.0.7 | 2026-10-05 | 扫码登录扩展至所有平台：新增腾讯视频（QQ 官方 ptlogin2 扫码，hash33 计算 ptqrtoken，登录后抓取 Cookie 保存到 qqck.txt），爱奇艺/优酷因官方接口加密引导使用云端获取或手动管理 Cookie；运行控制台优化：解析后先显示结果，点击「内嵌播放」才开始播放 |
| v0.0.6 | 2026-10-05 | 后台新增云端获取（从 GitHub cookies/ 拉取 Cookie 并校验）与在线更新（检查远程版本、一键拉取最新代码覆盖本地，自动备份并保留 cookies/ 与本地 config.php） |
| v0.0.5 | 2026-10-05 | 整理目录结构：后台文件移至仓库根目录，任意 PHP 环境直接部署（脚本根目录自动探测，无需改代码）；移除 gfsfadmin 子目录 |
| v0.0.4 | 2026-10-05 | 新增 Cookie 全程自动化：dsck.php 调度脚本（检查/自动生成/拉取 GitHub cookie/导出）、GitHub Actions 定时检查并提交 cookies/ 目录、可选项访问令牌 |
| v0.0.3 | 2026-10-05 | 后台 v0.0.3：运行控制台播放区 + Cookie 生成与扫码登录（B站/芒果TV），接口真实测试通过 |
| v0.0.2 | 2026-10-05 | 新增后台管理：登录、平台概览、脚本运行控制台、Cookie 管理；算法脚本真实测试（见 TEST-REPORT.md） |
| v0.0.1 | 2026-10-05 | 仓库初始化：README、版本信息、基础文件（.gitignore / VERSION） |

---

**© 2026 射手沫蝴蝶** · 由 [github.com/ssmhdssmhd/GFSF](https://github.com/ssmhdssmhd/GFSF) 提供
