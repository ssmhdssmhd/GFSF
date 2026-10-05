# 🦋 GFSF 算法管理后台

> 算法脚本控制台：登录后运行各平台解析 PHP、管理 Cookie（腾讯 / 哔哩哔哩 / 优酷 / 芒果TV / 爱奇艺）。

- 版本：`v0.0.3`（2026-10-05）
- 测试报告：[TEST-REPORT.md](TEST-REPORT.md)
- Logo 来源：[MXLOGO · 射手沫蝴蝶 Logo 资产库](https://github.com/ssmhdssmhd/MXLOGO)

---

## 登录

| 项目 | 值 |
|---|---|
| 地址 | `http://127.0.0.1:8092/login.php` |
| 用户名 | `admin` |
| 密码 | `gf2026admin`（见 `config.php`，首次登录后建议修改） |

## 功能

- **平台概览**：各平台脚本数量、Cookie 文件状态与预览
- **运行控制台**：选择平台/脚本，输入视频地址真实调用解析算法，展示返回结果与耗时；解析成功后显示播放区（内嵌播放 / 新窗口打开 / 复制链接），支持自动刷新
- **Cookie 生成**：真实调用生成脚本（blgetck.php / mggetck.php）自动生成并保存 Cookie，自动识别失效 Cookie
- **扫码登录**：B 站 / 芒果TV 官方接口生成二维码，手机扫码登录后自动轮询并保存真实 Cookie
- **Cookie 管理**：读取 / 编辑 / 保存 / 清空各平台 `ck.txt`（腾讯为 `qqck.txt`）

## 运行方式

```bash
# 1. 安装 PHP (需 curl 扩展)
apt-get install -y php-cli php-curl php-mbstring

# 2. 启动后台
php -S 0.0.0.0:8092 -t /workspace/gfsfadmin

# 3. 浏览器打开 http://127.0.0.1:8092/login.php
```

## 目录结构

```
gfsfadmin/
├── config.php     # 管理员账号、脚本根目录、平台配置
├── login.php      # 登录页
├── logout.php     # 退出
├── index.php      # 后台主界面（控制台 + Cookie 管理）
├── api.php        # API：platforms / scripts / run / cookie_*
├── runner.php     # 脚本执行器（CLI 子进程，cwd 指向脚本目录）
└── assets/style.css
```

> 脚本执行通过 `runner.php` 子进程完成：cwd 自动切换到脚本目录，保证 `ck.txt` 等相对路径正确加载；视频地址经 STDIN 注入 `$_GET['url']`。

## 更新日志

| 版本 | 日期 | 更新内容 |
|---|---|---|
| v0.0.3 | 2026-10-05 | 运行控制台新增播放区（内嵌播放/新窗口/复制链接）；新增 Cookie 生成与扫码登录（B站/芒果TV 二维码轮询保存 Cookie）；接口真实测试通过（B站解析返回 MP4 直链、二维码生成/轮询、过期 Cookie 自动识别） |
| v0.0.2 | 2026-10-05 | 增强 runner.php：模拟 REQUEST_URI / HTTP_HOST / REMOTE_ADDR，兼容 `$_SERVER['REQUEST_URI']` 取参脚本；完成 22 个算法脚本真实测试并输出 TEST-REPORT.md |
| v0.0.1 | 2026-10-05 | 后台初版：登录、平台概览、运行控制台、Cookie 管理 |

---

**© 2026 射手沫蝴蝶** · GFSF 算法项目
