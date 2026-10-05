# Cookies 存储目录

本目录由 **GitHub Actions 自动维护**（见 `.github/workflows/cookie-auto.yml`）：

- 云端定时检查各平台 cookie，失效自动重新获取
- 每次运行后将最新 cookie 导出到本目录并提交回仓库

## 文件说明

| 文件 | 对应平台 | 本地保存位置 |
|---|---|---|
| `bl.txt` | 哔哩哔哩 | `uploads_sf/算法/bl/ck.txt` |
| `mg.txt` | 芒果TV | `uploads_sf/算法/mg/ck.txt` |
| `tx.txt` | 腾讯视频 | `uploads_sf/算法/tx/qqck.txt` |
| `iqy.txt` | 爱奇艺 | `uploads_sf/算法/iqy/ck.txt` |

## 如何读取 GitHub 中的 Cookie

**方式一：raw 链接（任何环境）**

```
https://raw.githubusercontent.com/ssmhdssmhd/GFSF/main/cookies/bl.txt
```

**方式二：dsck.php 拉取（服务器 / 本地）**

```bash
# 从 GitHub 拉取 B 站 cookie 到本地 bl/ck.txt 并校验
php dsck.php --action pull --platform bl
# 或域名访问
curl "https://你的域名/dsck.php?action=pull&platform=bl"
```

> 注意：cookie 为敏感凭据，仓库为公开时会同步公开。如不希望公开请将本目录加入 `.gitignore` 并改用私有仓库存储。
