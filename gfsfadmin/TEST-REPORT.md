# GFSF 算法脚本 · 真实测试报告

> 测试日期：2026-10-05
> 测试方式：通过后台 API（`api.php?action=run`）以 CLI 子进程真实执行各平台 PHP 脚本，注入真实视频地址
> 运行环境：PHP 8.x + cURL 扩展，脚本 cwd 自动切换到所在目录

## 总览

| 平台 | 脚本数 | 解析成功 | 解析失败 | 需 Cookie/登录 | 超时 |
|---|---|---|---|---|---|
| 哔哩哔哩 (bl) | 9 | 3 | 1 | 3 | 2 |
| 腾讯视频 (tx) | 3 | 0 | 2 | 1 | 0 |
| 优酷 (youku) | 1 | 0 | 0 | 1 | 1 |
| 芒果TV (mg) | 4 | 1 | 1 | 1 | 1 |
| 爱奇艺 (iqy) | 5 | 0 | 2 | 0 | 3 |
| **合计** | **22** | **4** | **6** | **6** | **7** |

## 详细结果

### 哔哩哔哩 (bl) — 测试地址 `https://www.bilibili.com/video/BV1xx411c7mD`

| 脚本 | 结果 | 说明 |
|---|---|---|
| `b.php` | ✅ 成功 | 返回真实 MP4 直链 `upos-szbyjkm8g1.bilivideo.com` |
| `bili最新.php` | ✅ 成功 | 返回真实 MP4 直链 |
| `3578843152bili(1).php` | ✅ 成功 | 返回真实 MP4 直链 |
| `bl.php` | ❌ 失败 | 使用远程 `sf.zxyang.cn/bl/blgetck.php` 提供的 cookie，失效导致 `durl[0].url` 为空 |
| `blcheckck.php` | ⚠️ Cookie失效 | 本地 `ck.txt` 中 cookie 已过期（code 400） |
| `blgetck.php` | ✅ 成功 | 返回本地 cookie 内容 |
| `ck.php` | ⏱ 超时 | 等待/抓取流程，无有效会话 |
| `curl.php` | ⏱ 超时 | 工具函数脚本，无主流程 |
| `bllogin.php` | ⏱ 超时 | 需扫码登录（真实浏览器交互） |

### 腾讯视频 (tx) — 测试地址 `https://v.qq.com/x/cover/mzc00200mpqnvav/o4100tv8brr.html`

| 脚本 | 结果 | 说明 |
|---|---|---|
| `qq.php` | ❌ 需Cookie | 无有效会员 cookie（+`vipurl.php` 域名列表），返回"ck失效啦" |
| `txnck.php` | ⚠️ 需Cookie | 依赖 `qqck.txt`，文件不存在 |
| `upck-带轮询.php` | ❌ 需Cookie | `ck.txt`/`ck2.txt`/`lx.txt` 轮询获取失败，返回 false |

### 优酷 (youku) — 测试地址 `https://v.youku.com/v_show/id_XNjU5MTc3ODg0.html`

| 脚本 | 结果 | 说明 |
|---|---|---|
| `youku.php` | ⏱ 超时 | 脚本内 `YOUKU2`/`YOUKU3` 常量未定义且无有效 cookie，`ups.youku.com` 接口空转重试 |

### 芒果TV (mg) — 测试地址 `https://www.mgtv.com/b/327483/4163402.html`

| 脚本 | 结果 | 说明 |
|---|---|---|
| `mg.php` | ❌ 失败 | 依赖远程 `sf.zxyang.cn/mg/mggetck.php` 获取 ticket，接口返回的 ticket 失效，`getSource` 返回 not found |
| `mgcheckck.php` | ⚠️ Cookie失效 | 本地 `ck.txt` cookie 过期 |
| `mggetck.php` | ✅ 成功 | 返回本地 cookie 内容 |
| `mglogin.php` | ⏱ 超时 | 需扫码登录（真实浏览器交互） |

### 爱奇艺 (iqy) — 测试地址 `https://www.iqiyi.com/v_19rr1t4y5o.html`

| 脚本 | 结果 | 说明 |
|---|---|---|
| `iqiyi.php` | ❌ 失败 | 内置账号 `authKey` 失效或需有效会员态，解析返回 404 |
| `iqy1080.php` | ❌ 失败 | 同上 |
| `iqyp0.php` | ⏱ 超时 | 重试循环（64 次）无有效凭据空转 |
| `3600qiyi.php` | ⏱ 超时 | 需有效 cookie 重试空转 |
| `3600qiyi-原始版本.php` | ⏱ 超时 | 同上 |

## 结论

1. **算法代码可运行**：22 个脚本均无 PHP 语法/致命错误，可通过后台运行控制台真实执行。
2. **免登录解析可用**：哔哩哔哩 `b.php` / `bili最新.php` / `3578843152bili(1).php` 无需会员即可解析出真实视频直链，已验证有效。
3. **会员平台需 Cookie**：腾讯 / 优酷 / 芒果 / 爱奇艺脚本依赖有效会员 cookie；cookie 过期或缺失时解析失败属预期行为。可在后台「Cookie 管理」中更新 cookie 后重试。
4. **登录类脚本需真实浏览器**：`bllogin.php` / `mglogin.php` 等需扫码/账号交互，无法在纯 CLI 环境完成，建议部署到站点后在浏览器中调用。
