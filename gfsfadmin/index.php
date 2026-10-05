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
      <div class="topbar-title">GFSF 算法管理后台 <span class="badge">v0.0.1</span></div>
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
      <h2>运行控制台 <small>真实调用算法脚本解析视频</small></h2>
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
  </main>

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
  await Promise.all([loadRunPlatforms(), loadCkPlatforms()]);
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
  const fd = new FormData();
  fd.append('platform', $('run-platform').value);
  fd.append('script', $('run-script').value);
  fd.append('url', url);
  const j = await api('api.php?action=run', { method: 'POST', body: fd });
  if (!j) return;
  const t = new Date().toLocaleTimeString();
  $('run-output').textContent = '▶ [' + t + '] ' + (j.script || '') + '\n   耗时: ' + (j.cost_s || '-') + ' s\n\n' + (j.output || '(无输出)');
  btn.disabled = false;
}

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
$('run-auto').onchange = function () {
  if (this.checked) {
    doRun();
    this._timer = setInterval(doRun, 60000);
  } else if (this._timer) {
    clearInterval(this._timer);
  }
};

loadPlatforms();
</script>
</body>
</html>
