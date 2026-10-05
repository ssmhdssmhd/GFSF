<?php
require __DIR__ . '/config.php';
session_start();
if (empty($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}
$user = htmlspecialchars($_SESSION['user']);
$loginAt = htmlspecialchars($_SESSION['login_at']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GFSF 算法管理后台</title>
<link rel="icon" href="https://cdn.jsdelivr.net/gh/ssmhdssmhd/MXLOGO@main/favicon/favicon.ico">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <img class="topbar-logo" src="https://cdn.jsdelivr.net/gh/ssmhdssmhd/MXLOGO@main/web/web-logo.svg" alt="GFSF">
      <div class="topbar-title">GFSF 算法管理后台 <span class="badge">v0.0.3</span></div>
      <div class="topbar-right">
        <span>管理员: <?php echo $user; ?> · 登录于 <?php echo $loginAt; ?></span>
        <a class="btn btn-ghost" href="logout.php">退出</a>
      </div>
    </div>
  </header>

  <main class="container">
    <!-- 平台概览 -->
    <section class="panel">
      <h2>平台概览</h2>
      <div id="platforms" class="cards"></div>
    </section>

    <!-- 运行控制台 -->
    <section class="panel">
      <h2>运行控制台 <small>真实调用算法脚本解析视频（先显示解析结果，点击「内嵌播放」才开始播放）</small></h2>
      <div class="row">
        <select id="run-platform" class="select"></select>
        <select id="run-script" class="select grow"></select>
        <input id="run-url" class="input grow" type="text" placeholder="粘贴视频播放地址，如 https://www.bilibili.com/video/BV1GJ411x7h7">
      </div>
      <div class="row">
        <button id="run-btn" class="btn btn-primary">运行测试</button>
        <label class="chk"><input id="run-auto" type="checkbox"> 自动刷新</label>
      </div>
      <pre id="run-output" class="output">等待运行…</pre>
      <div id="play-zone" class="play-zone hidden">
        <div class="row play-row">
          <button id="play-btn" class="btn btn-primary">▶ 内嵌播放</button>
          <button id="play-new" class="btn">新窗口打开</button>
          <button id="play-copy" class="btn">复制链接</button>
        </div>
        <video id="play-video" controls playsinline></video>
        <div id="play-url" class="muted"></div>
      </div>
    </section>

    <!-- Cookie 生成 -->
    <section class="panel">
      <h2>Cookie 生成 <small>真实调用生成脚本并保存</small></h2>
      <div class="row">
        <select id="gen-platform" class="select"></select>
        <button id="gen-btn" class="btn btn-primary">生成并保存 Cookie</button>
      </div>
      <div id="gen-info" class="muted"></div>
      <pre id="gen-output" class="output">尚未生成…</pre>
    </section>

    <!-- 扫码登录 -->
    <section class="panel">
      <h2>扫码登录 <small>生成真实 Cookie（腾讯视频 / B站 / 芒果TV，爱奇艺·优酷接口已加密请用云端获取）</small></h2>
      <div class="row">
        <select id="qr-platform" class="select"></select>
        <button id="qr-get" class="btn btn-primary">获取二维码</button>
        <button id="qr-stop" class="btn" disabled>停止轮询</button>
      </div>
      <div class="qr-row">
        <div id="qr-box" class="qr-box"><span class="muted">点击「获取二维码」后用手机扫码</span></div>
        <div id="qr-status" class="muted"></div>
      </div>
      <div id="qr-ck" class="muted"></div>
    </section>

    <!-- Cookie 管理 -->
    <section class="panel">
      <h2>Cookie 管理</h2>
      <div class="row">
        <select id="ck-platform" class="select"></select>
        <button id="ck-load" class="btn">读取 Cookie</button>
        <button id="ck-save" class="btn btn-primary">保存 Cookie</button>
        <button id="ck-clear" class="btn btn-danger">清空</button>
      </div>
      <div id="ck-info" class="muted"></div>
      <textarea id="ck-content" class="textarea" rows="8" placeholder="Cookie 内容…"></textarea>
    </section>

    <!-- 云端获取 Cookie -->
    <section class="panel">
      <h2>云端获取 <small>从 GitHub 仓库 cookies/ 拉取最新 Cookie</small></h2>
      <div class="row">
        <select id="pull-platform" class="select"></select>
        <button id="pull-btn" class="btn btn-primary">从 GitHub 获取</button>
      </div>
      <div id="pull-info" class="muted"></div>
      <pre id="pull-output" class="output">尚未获取…</pre>
    </section>

    <!-- 在线更新 -->
    <section class="panel">
      <h2>在线更新 <small>从 GitHub 拉取最新代码</small></h2>
      <div class="row">
        <span id="up-local" class="muted">本地版本：-</span>
        <span id="up-remote" class="muted">远程版本：-</span>
        <button id="up-check" class="btn">检查更新</button>
        <button id="up-run" class="btn btn-primary" disabled>立即更新</button>
      </div>
      <pre id="up-output" class="output">点击「检查更新」查看远程版本与更新内容…</pre>
    </section>
  </main>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
const $ = (id) => document.getElementById(id);
const api = async (url, opts = {}) => {
  const r = await fetch(url, opts);
  const j = await r.json();
  if (j.code === 401) { location.href = 'login.php'; return null; }
  return j;
};

/* ---------- 平台概览 ---------- */
async function loadPlatforms() {
  const j = await api('api.php?action=platforms');
  if (!j) return;
  const box = $('platforms');
  box.innerHTML = '';
  (j.data || []).forEach(p => {
    let ckHtml = '<span class="ck-none">无</span>';
    if (p.cookie) {
      if (p.ckInfo && p.ckInfo.exists) {
        ckHtml = '<span class="ck-ok">✓ ' + p.ckInfo.size + ' B · ' + p.ckInfo.mtime + '</span>';
      } else {
        ckHtml = '<span class="ck-bad">✗ 未设置</span>';
      }
    }
    const el = document.createElement('div');
    el.className = 'card';
    el.innerHTML =
      '<div class="card-head"><strong>' + p.name + '</strong><span class="badge">' + p.dir + '</span></div>' +
      '<div class="card-row">脚本: <b>' + p.scripts + '</b> 个</div>' +
      '<div class="card-row">Cookie(' + (p.cookie || '-') + '): ' + ckHtml + '</div>' +
      '<div class="card-row muted">' + (p.ckInfo && p.ckInfo.head ? '预览: ' + escapeHtml(p.ckInfo.head.slice(0, 40)) : '') + '</div>';
    box.appendChild(el);
  });
  await Promise.all([loadRunPlatforms(), loadCkPlatforms(), loadGenPlatforms(), loadQrPlatforms(), loadPullPlatforms()]);
}

/* ---------- 运行控制台 ---------- */
async function loadRunPlatforms() {
  const j = await api('api.php?action=platforms');
  if (!j) return;
  const sel = $('run-platform');
  sel.innerHTML = '';
  (j.data || []).forEach(p => {
    const o = document.createElement('option');
    o.value = p.key; o.textContent = p.name;
    sel.appendChild(o);
  });
  await loadScripts();
}
async function loadScripts() {
  const j = await api('api.php?action=scripts&platform=' + $('run-platform').value);
  if (!j) return;
  const sel = $('run-script');
  sel.innerHTML = '';
  (j.data || []).forEach(f => {
    const o = document.createElement('option');
    o.value = f.name; o.textContent = f.name + ' (' + f.size + ' B)';
    sel.appendChild(o);
  });
}
async function doRun() {
  const url = $('run-url').value.trim();
  if (!url) { $('run-output').textContent = '请先输入视频地址'; return; }
  const btn = $('run-btn');
  btn.disabled = true;
  $('run-output').textContent = '解析中…';
  hidePlay();
  const fd = new FormData();
  fd.append('platform', $('run-platform').value);
  fd.append('script', $('run-script').value);
  fd.append('url', url);
  const j = await api('api.php?action=run', { method: 'POST', body: fd });
  if (!j) return;
  const t = new Date().toLocaleTimeString();
  $('run-output').textContent = '▶ [' + t + '] ' + (j.script || '') + '\n   耗时: ' + (j.cost_s || '-') + ' s\n\n' + (j.output || '(无输出)');
  btn.disabled = false;
  showPlay(j.output || '');
}

/* ---------- 播放区 ---------- */
let lastVideoUrl = null;
function hidePlay() {
  $('play-zone').classList.add('hidden');
  $('play-video').pause();
  $('play-video').removeAttribute('src');
  lastVideoUrl = null;
}
function extractVideoUrl(text) {
  if (!text) return null;
  // 优先尝试 JSON 解析（输出多为 JSON 文本）
  try {
    const s = text.indexOf('{');
    if (s >= 0) {
      const obj = JSON.parse(text.slice(s));
      const find = (o) => {
        if (!o || typeof o !== 'object') return null;
        if (typeof o.url === 'string') return o.url;
        for (const k in o) {
          if (k === 'code' || k === 'msg' || k === 'title' || k === 'script') continue;
          const v = find(o[k]);
          if (v) return v;
        }
        return null;
      };
      const u = find(obj);
      if (u && /^https?:\/\//i.test(u)) return u;
    }
  } catch (e) {}
  // 正则兜底：直链
  const m = text.match(/https?:\/\/[^\s"'<>\\]+?\.(?:m3u8|mp4|flv)[^\s"'<>\\]*/i);
  if (m) return m[0];
  const m2 = text.match(/"url"\s*:\s*"([^"]+)"/);
  return m2 && /^https?:\/\//i.test(m2[1]) ? m2[1] : null;
}
function showPlay(output) {
  const u = extractVideoUrl(output);
  if (!u) return;
  lastVideoUrl = u;
  $('play-url').textContent = u;
  $('play-zone').classList.remove('hidden');
  // 不自动播放：先让用户查看解析结果，点击「▶ 内嵌播放」后才加载并播放
  $('play-video').removeAttribute('src');
}
$('play-btn').onclick = function () {
  if (!lastVideoUrl) return;
  if (/\.m3u8/i.test(lastVideoUrl)) {
    alert('m3u8 格式浏览器无法直接播放，请用「新窗口打开」或外部播放器');
    return;
  }
  $('play-video').src = lastVideoUrl;
  $('play-video').play().catch(() => alert('播放失败：可能存在防盗链，请用「新窗口打开」'));
};
$('play-new').onclick = function () {
  if (lastVideoUrl) window.open(lastVideoUrl, '_blank');
};
$('play-copy').onclick = function () {
  if (!lastVideoUrl) return;
  (navigator.clipboard ? navigator.clipboard.writeText(lastVideoUrl) : Promise.reject())
    .then(() => $('play-copy').textContent = '已复制')
    .catch(() => { $('play-copy').textContent = '复制失败'; })
    .finally(() => setTimeout(() => $('play-copy').textContent = '复制链接', 1500));
};

/* ---------- Cookie 生成 ---------- */
async function loadGenPlatforms() {
  const j = await api('api.php?action=platforms');
  if (!j) return;
  const sel = $('gen-platform');
  sel.innerHTML = '';
  (j.data || []).forEach(p => {
    if (p.cookie) {
      const o = document.createElement('option');
      o.value = p.key; o.textContent = p.name;
      sel.appendChild(o);
    }
  });
}
async function genCookie() {
  const btn = $('gen-btn');
  btn.disabled = true;
  $('gen-info').textContent = '正在真实调用生成脚本…';
  $('gen-output').textContent = '运行中…';
  const fd = new FormData();
  fd.append('platform', $('gen-platform').value);
  const j = await api('api.php?action=cookie_gen', { method: 'POST', body: fd });
  btn.disabled = false;
  if (!j) return;
  $('gen-output').textContent = (j.output || '(无输出)') + '\n\n▶ 脚本: ' + (j.script || '-') + ' · 耗时: ' + (j.cost_s || '-') + ' s';
  $('gen-info').textContent = (j.msg || '') + (j.data ? ' · 已保存 ' + j.data.size + ' B @ ' + j.data.mtime : '');
  await loadPlatforms();
}

/* ---------- 扫码登录 ---------- */
let qrKey = null, qrTimer = null, qrPlatform = null;
async function loadQrPlatforms() {
  const j = await api('api.php?action=platforms');
  if (!j) return;
  const sel = $('qr-platform');
  sel.innerHTML = '';
  (j.data || []).forEach(p => {
    const o = document.createElement('option');
    o.value = p.key; o.textContent = p.name;
    sel.appendChild(o);
  });
}
async function qrGet() {
  qrStop();
  qrPlatform = $('qr-platform').value;
  $('qr-status').textContent = '正在生成二维码…';
  $('qr-ck').textContent = '';
  const fd = new FormData();
  fd.append('platform', qrPlatform);
  const j = await api('api.php?action=qr_login', { method: 'POST', body: fd });
  if (!j || j.code !== 200) {
    $('qr-status').textContent = j ? (j.msg || '生成失败') : '请求失败';
    $('qr-box').innerHTML = '<span class="muted">该平台暂无可用的自动扫码接口</span>';
    return;
  }
  qrKey = j.data.key;
  $('qr-box').innerHTML = '';
  if (j.data.img) {
    // 平台直接返回二维码图片（腾讯视频 QQ 扫码）
    const img = document.createElement('img');
    img.src = j.data.img; img.width = 180; img.height = 180; img.alt = 'QR';
    $('qr-box').appendChild(img);
  } else if (typeof QRCode !== 'undefined' && j.data.url) {
    new QRCode($('qr-box'), { text: j.data.url, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
  } else {
    $('qr-box').innerHTML = '<span class="muted">二维码库未加载，请手动打开：</span><a href="' + (j.data.url || '#') + '" target="_blank">扫码链接</a>';
  }
  $('qr-status').textContent = '二维码已生成，请用手机 App 扫码（60 秒内有效）';
  $('qr-stop').disabled = false;
  qrTimer = setInterval(qrPoll, 5000);
  qrPoll();
}
async function qrPoll() {
  if (!qrKey) return;
  const fd = new FormData();
  fd.append('platform', qrPlatform);
  fd.append('key', qrKey);
  const j = await api('api.php?action=qr_poll', { method: 'POST', body: fd });
  if (!j) return;
  $('qr-status').textContent = j.msg || '';
  if (j.data && j.data.ck) {
    $('qr-ck').textContent = '✓ 已获取 Cookie（' + (j.data.size || 0) + ' B）：' + j.data.ck.slice(0, 80) + '…';
    qrStop();
    await loadPlatforms();
    $('qr-status').textContent = '登录成功，Cookie 已保存到平台文件';
  } else if (j.data && (j.data.expired || j.data.status === 86038 || j.data.status === 67)) {
    qrStop();
    $('qr-status').textContent = '二维码已过期，请点击「获取二维码」重新生成';
  }
}
function qrStop() {
  if (qrTimer) { clearInterval(qrTimer); qrTimer = null; }
  $('qr-stop').disabled = true;
}
$('qr-get').onclick = qrGet;
$('qr-stop').onclick = qrStop;

/* ---------- Cookie 管理 ---------- */
async function loadCkPlatforms() {
  const j = await api('api.php?action=platforms');
  if (!j) return;
  const sel = $('ck-platform');
  sel.innerHTML = '';
  (j.data || []).forEach(p => {
    if (p.cookie) {
      const o = document.createElement('option');
      o.value = p.key; o.textContent = p.name + ' (' + p.cookie + ')';
      sel.appendChild(o);
    }
  });
}
async function ckLoad() {
  const j = await api('api.php?action=cookie_get&platform=' + $('ck-platform').value);
  if (!j) return;
  const d = j.data || {};
  $('ck-content').value = d.content || '';
  $('ck-info').textContent = '大小: ' + (d.size || 0) + ' B · 更新时间: ' + (d.mtime || '-');
}
async function ckSave() {
  const fd = new FormData();
  fd.append('platform', $('ck-platform').value);
  fd.append('content', $('ck-content').value);
  const j = await api('api.php?action=cookie_save', { method: 'POST', body: fd });
  if (!j) return;
  $('ck-info').textContent = (j.msg || '') + ' · ' + (j.data ? j.data.size + ' B' : '');
  await loadPlatforms();
}
async function ckClear() {
  if (!confirm('确定清空该平台 cookie？')) return;
  const fd = new FormData();
  fd.append('platform', $('ck-platform').value);
  const j = await api('api.php?action=cookie_clear', { method: 'POST', body: fd });
  if (!j) return;
  $('ck-info').textContent = j.msg || '';
  $('ck-content').value = '';
  await loadPlatforms();
}

function escapeHtml(s) { return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

/* ---------- 事件绑定 ---------- */
$('run-btn').onclick = doRun;
$('run-platform').onchange = loadScripts;
$('ck-load').onclick = ckLoad;
$('ck-save').onclick = ckSave;
$('ck-clear').onclick = ckClear;
$('gen-btn').onclick = genCookie;
$('run-auto').onchange = function () {
  if (this.checked) {
    doRun();
    this._timer = setInterval(doRun, 60000);
  } else if (this._timer) {
    clearInterval(this._timer);
  }
};

loadPlatforms();

/* ---------- 云端获取 Cookie（从 GitHub 拉取） ---------- */
async function loadPullPlatforms() {
  const j = await api('api.php?action=platforms');
  if (!j) return;
  const sel = $('pull-platform');
  sel.innerHTML = '';
  (j.data || []).forEach(p => {
    if (!p.cookie) return;
    const o = document.createElement('option');
    o.value = p.key; o.textContent = p.name;
    sel.appendChild(o);
  });
}
$('pull-btn').onclick = async function () {
  const fd = new FormData();
  fd.append('platform', $('pull-platform').value);
  $('pull-info').textContent = '正在从 GitHub 获取…';
  $('pull-btn').disabled = true;
  const j = await api('api.php?action=cookie_pull', { method: 'POST', body: fd });
  $('pull-btn').disabled = false;
  if (!j) return;
  $('pull-info').textContent = j.msg || '';
  const d = j.data || {};
  let out = d.url ? '来源: ' + d.url + '\n' : '';
  out += '大小: ' + (d.size || 0) + ' B · 时间: ' + (d.mtime || '-') + '\n';
  if (d.check) {
    out += '校验: ' + (d.check.ok ? '✓ Cookie 有效' : '✗ Cookie 失效') + '\n' + (d.check.output || '');
  }
  $('pull-output').textContent = out || '(无输出)';
  await loadPlatforms();
};

/* ---------- 在线更新（从 GitHub 拉取最新代码） ---------- */
async function upCheck() {
  $('up-output').textContent = '正在检查远程版本…';
  const j = await api('api.php?action=update_check');
  if (!j) return;
  $('up-local').textContent = '本地版本：' + (j.data.local || '-');
  $('up-remote').textContent = '远程版本：' + (j.data.remote || '-');
  $('up-run').disabled = !(j.data.has_update);
  $('up-output').textContent =
    (j.data.has_update ? '✓ 检测到新版本，可点击「立即更新」\n\n' : '当前已是最新版本\n\n')
    + '—— 远程 README（前部）——\n' + (j.data.remote_readme || '');
}
$('up-check').onclick = upCheck;
$('up-run').onclick = async function () {
  if (!confirm('确认从 GitHub 拉取最新代码覆盖本地？\n（保留 cookies/、backups/ 目录与本地 config.php，旧版本会先备份）')) return;
  $('up-run').disabled = true;
  $('up-output').textContent = '正在下载并更新，请稍候…（约 10~30 秒）';
  const j = await api('api.php?action=update_run', { method: 'POST' });
  if (!j) return;
  $('up-output').textContent = j.msg + (j.data && j.data.backup ? '\n备份目录: ' + j.data.backup : '');
  if (j.code === 200) {
    $('up-output').textContent += '\n\n更新完成，请刷新页面查看新版本（如代码有变化页面可能需手动刷新）。';
  }
  $('up-run').disabled = false;
};
</script>
</body>
</html>
