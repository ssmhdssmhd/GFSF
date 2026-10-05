<?php
/**
 * dsck.php — Cookie 自动检查 / 生成 / 同步 调度脚本
 * ==================================================
 * 版本: v0.0.4 (2026-10-05)
 *
 * 功能:
 *  1. 检查各平台 cookie 有效性（调用平台官方校验脚本）
 *  2. cookie 失效时自动重新获取真实 cookie（调用平台获取脚本）并写入对应文件
 *  3. 支持从 GitHub raw 拉取 cookie（仓库 cookies/ 目录）
 *  4. 支持将本地 cookie 导出到仓库 cookies/ 目录（供 GitHub Actions 提交）
 *
 * 调用方式:
 *  域名计划任务:  https://你的域名/dsck.php?action=auto&platform=all
 *  CLI / GitHub Actions:  php dsck.php --auto --platform all --json
 *  拉取 GitHub cookie:  https://你的域名/dsck.php?action=pull&platform=bl
 *
 * 配置（环境变量，可覆盖默认值）:
 *  DSCK_TOKEN        访问令牌（设置后 URL 调用需携带 token 参数）
 *  COOKIE_GH_REPO    GitHub cookie 仓库，默认 ssmhdssmhd/GFSF
 *  COOKIE_GH_BRANCH  GitHub 分支，默认 main
 */

/* ================= 基础定义 ================= */
define('DSCK_VERSION', 'v0.0.4');
$ROOT         = __DIR__;
$SCRIPTS_ROOT = $ROOT . '/uploads_sf/算法';
$COOKIES_DIR  = $ROOT . '/cookies';

if (PHP_SAPI === 'cli') {
    $CLI = true;
    // 手动解析 CLI 参数（兼容 --action=check 与 --action check 两种写法）
    $args = array();
    $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
    $n = count($argv);
    for ($i = 1; $i < $n; $i++) {
        $a = $argv[$i];
        if (strpos($a, '--') !== 0) continue;
        $kv = explode('=', substr($a, 2), 2);
        $k = $kv[0];
        if (count($kv) === 2) {
            $args[$k] = $kv[1];
        } else {
            // 空格分隔的值（仅当后一个参数不以 -- 开头且为已知带值选项）
            if ($i + 1 < $n && strpos($argv[$i + 1], '--') !== 0
                && in_array($k, array('action', 'platform', 'token'))) {
                $args[$k] = $argv[++$i];
            } else {
                $args[$k] = true;
            }
        }
    }
    $ACTION   = isset($args['auto']) ? 'auto' : (isset($args['action']) ? $args['action'] : 'check');
    $PLATFORM = isset($args['platform']) ? $args['platform'] : 'all';
    $TOKEN    = isset($args['token']) ? $args['token'] : '';
} else {
    $CLI = false;
    header('Content-Type: application/json; charset=utf-8');
    $ACTION   = isset($_GET['action']) ? $_GET['action'] : 'check';
    $PLATFORM = isset($_GET['platform']) ? $_GET['platform'] : 'all';
    $TOKEN    = isset($_GET['token']) ? $_GET['token'] : '';
}

/* 可选鉴权：设置了 DSCK_TOKEN 后，URL 调用必须携带正确 token */
$DSCK_TOKEN = getenv('DSCK_TOKEN');
if (!$CLI && $DSCK_TOKEN !== false && $DSCK_TOKEN !== '' && $DSCK_TOKEN !== $TOKEN) {
    echo json_encode(array('code' => 403, 'msg' => 'token 无效'), JSON_UNESCAPED_UNICODE);
    exit;
}

/* GitHub cookie 仓库配置 */
$GH_REPO   = getenv('COOKIE_GH_REPO')   ? getenv('COOKIE_GH_REPO')   : 'ssmhdssmhd/GFSF';
$GH_BRANCH = getenv('COOKIE_GH_BRANCH') ? getenv('COOKIE_GH_BRANCH') : 'main';

/* ================= 平台配置 =================
 * dir    : 平台目录（相对 $SCRIPTS_ROOT）
 * cookie : cookie 文件名（相对平台目录），null 表示无独立 cookie 文件
 * check  : cookie 校验脚本（输出 JSON，code=200 为有效），null 表示无法自动校验
 * gen    : cookie 获取脚本（输出可用 cookie 文本），null 表示无法自动获取
 */
$PLATFORMS = array(
    'bl'    => array('name' => '哔哩哔哩', 'dir' => 'bl',    'cookie' => 'ck.txt',   'check' => 'blcheckck.php', 'gen' => 'blgetck.php'),
    'mg'    => array('name' => '芒果TV',   'dir' => 'mg',    'cookie' => 'ck.txt',   'check' => 'mgcheckck.php', 'gen' => 'mggetck.php'),
    'tx'    => array('name' => '腾讯视频', 'dir' => 'tx',    'cookie' => 'qqck.txt', 'check' => null, 'gen' => null),
    'iqy'   => array('name' => '爱奇艺',   'dir' => 'iqy',   'cookie' => 'ck.txt',   'check' => null, 'gen' => null),
    'youku' => array('name' => '优酷',     'dir' => 'youku', 'cookie' => null,       'check' => null, 'gen' => null),
);

/* ================= 工具函数 ================= */
/** 在脚本目录内以 CLI 方式执行脚本（cwd 切到脚本目录，保证相对路径生效）；过滤 PHP Warning 等污染行 */
function runScript($dir, $file, $timeout = 30) {
    if (!is_file($dir . '/' . $file)) return '';
    $cmd = 'cd ' . escapeshellarg($dir)
         . ' && echo "" | timeout ' . (int)$timeout . ' php ' . escapeshellarg($dir . '/' . $file) . ' 2>&1';
    $out = trim((string)shell_exec($cmd));
    // 去掉 PHP 运行时告警/提示行，只保留脚本业务输出
    $lines = preg_split('/\r?\n/', $out);
    $keep = array();
    foreach ($lines as $ln) {
        if (preg_match('/^PHP (Warning|Notice|Deprecated|Fatal error)/i', trim($ln))) continue;
        $keep[] = $ln;
    }
    return trim(implode("\n", $keep));
}

/** cURL 请求（用于拉取 GitHub cookie） */
function httpGet($url, $timeout = 20) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'dsck/1.0 (cookie automation)');
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array('code' => $code, 'body' => $res);
}

/** 判断 cookie 文本是否带失效标记 */
function isCookieOut($text) {
    foreach (array('Cookieout', 'Cookie失效', '获取转跳链接失败', '解析失败') as $w) {
        if (mb_strpos($text, $w) !== false) return true;
    }
    return false;
}

/** 检查单个平台 cookie，返回结果数组 */
function checkOne($SCRIPTS_ROOT, $p, $key) {
    $dir = $SCRIPTS_ROOT . '/' . $p['dir'];
    $ckPath = $p['cookie'] ? $dir . '/' . $p['cookie'] : null;
    $has = $ckPath && is_file($ckPath) && filesize($ckPath) > 0;

    if (!$p['check']) {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'skip', 'msg' => '无自动校验脚本', 'has_cookie' => $has, 'size' => $has ? filesize($ckPath) : 0);
    }
    $out = runScript($dir, $p['check']);
    $j = json_decode($out, true);
    $code = (is_array($j) && isset($j['code'])) ? (int)$j['code'] : null;
    if ($code === 200) {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'ok', 'msg' => 'Cookie 正常', 'has_cookie' => $has, 'size' => $has ? filesize($ckPath) : 0);
    }
    return array('platform' => $key, 'name' => $p['name'], 'status' => 'invalid', 'msg' => ($j && isset($j['msg'])) ? $j['msg'] : 'Cookie 失效', 'check_output' => $out, 'has_cookie' => $has, 'size' => $has ? filesize($ckPath) : 0);
}

/** 生成单个平台 cookie（调用获取脚本）并写入文件 */
function genOne($SCRIPTS_ROOT, $p, $key) {
    $dir = $SCRIPTS_ROOT . '/' . $p['dir'];
    $ckPath = $p['cookie'] ? $dir . '/' . $p['cookie'] : null;
    if (!$p['gen'] || !$ckPath) {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'skip', 'msg' => '无自动获取脚本');
    }
    $out = runScript($dir, $p['gen'], 45);
    if ($out === '' || isCookieOut($out)) {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'fail', 'msg' => '获取失败或远程 cookie 亦失效，请扫码登录后手动更新', 'gen_output' => $out);
    }
    if (file_put_contents($ckPath, $out) === false) {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'fail', 'msg' => '写入失败');
    }
    // 重新校验一次，确认新 cookie 真实可用
    $after = checkOne($SCRIPTS_ROOT, $p, $key);
    $after['msg'] = ($after['status'] === 'ok')
        ? '已自动生成并保存可用 cookie'
        : '已保存新 cookie，但校验未通过（' . $after['msg'] . '）';
    $after['gen_output'] = $out;
    return $after;
}

/** 从 GitHub raw 拉取 cookie 并写入本地文件 */
function pullCookie($SCRIPTS_ROOT, $GH_REPO, $GH_BRANCH, $p, $key) {
    $dir = $SCRIPTS_ROOT . '/' . $p['dir'];
    $ckPath = $p['cookie'] ? $dir . '/' . $p['cookie'] : null;
    if (!$ckPath) {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'skip', 'msg' => '该平台无 cookie 文件');
    }
    $url = 'https://raw.githubusercontent.com/' . $GH_REPO . '/' . $GH_BRANCH . '/cookies/' . $key . '.txt';
    $r = httpGet($url);
    if ($r['code'] !== 200 || trim((string)$r['body']) === '') {
        return array('platform' => $key, 'name' => $p['name'], 'status' => 'fail', 'msg' => 'GitHub 未找到 cookie（' . $r['code'] . '）');
    }
    file_put_contents($ckPath, $r['body']);
    $after = checkOne($SCRIPTS_ROOT, $p, $key);
    $after['msg'] = ($after['status'] === 'ok')
        ? '已从 GitHub 拉取并校验通过'
        : '已从 GitHub 拉取，但校验未通过（' . $after['msg'] . '）';
    return $after;
}

/** 导出本地 cookie 到仓库 cookies/ 目录（供 GitHub Actions 提交），返回变更列表 */
function exportCookies($ROOT, $COOKIES_DIR, $SCRIPTS_ROOT, $PLATFORMS) {
    if (!is_dir($COOKIES_DIR)) @mkdir($COOKIES_DIR, 0777, true);
    $changed = array();
    foreach ($PLATFORMS as $key => $p) {
        if (!$p['cookie']) continue;
        $src = $SCRIPTS_ROOT . '/' . $p['dir'] . '/' . $p['cookie'];
        $dst = $COOKIES_DIR . '/' . $key . '.txt';
        $content = (is_file($src) && filesize($src) > 0) ? file_get_contents($src) : '';
        $old = is_file($dst) ? file_get_contents($dst) : '';
        if ($content !== $old) {
            file_put_contents($dst, $content);
            $changed[] = array('file' => 'cookies/' . $key . '.txt', 'size' => strlen($content), 'changed' => true);
        } else {
            $changed[] = array('file' => 'cookies/' . $key . '.txt', 'size' => strlen($content), 'changed' => false);
        }
    }
    return $changed;
}

/* ================= 主流程 ================= */
$keys = ($PLATFORM === 'all') ? array_keys($PLATFORMS) : array($PLATFORM);
$platforms = array();
foreach ($keys as $k) {
    if (isset($PLATFORMS[$k])) $platforms[$k] = $PLATFORMS[$k];
}

$result = array('version' => DSCK_VERSION, 'time' => date('Y-m-d H:i:s'), 'action' => $ACTION, 'results' => array());

switch ($ACTION) {
    case 'check':
        foreach ($platforms as $k => $p) {
            $result['results'][$k] = checkOne($SCRIPTS_ROOT, $p, $k);
        }
        break;

    case 'gen':
        foreach ($platforms as $k => $p) {
            $result['results'][$k] = genOne($SCRIPTS_ROOT, $p, $k);
        }
        break;

    case 'pull':
        foreach ($platforms as $k => $p) {
            $result['results'][$k] = pullCookie($SCRIPTS_ROOT, $GH_REPO, $GH_BRANCH, $p, $k);
        }
        break;

    case 'export':
        $result['results']['export'] = exportCookies($ROOT, $COOKIES_DIR, $SCRIPTS_ROOT, $PLATFORMS);
        break;

    case 'auto':
        // auto = 检查 → 失效则自动生成 → 导出到 cookies/（供 GitHub Actions 提交）
        foreach ($platforms as $k => $p) {
            $r = checkOne($SCRIPTS_ROOT, $p, $k);
            if ($r['status'] === 'invalid') {
                $g = genOne($SCRIPTS_ROOT, $p, $k);
                if ($g['status'] === 'ok') {
                    $r = $g;
                    $r['msg'] = 'cookie 已失效，已自动重新生成真实 cookie';
                } else {
                    $r['msg'] = 'cookie 已失效，自动获取未成功：' . (isset($g['msg']) ? $g['msg'] : '');
                    $r['gen_output'] = isset($g['gen_output']) ? $g['gen_output'] : '';
                }
            }
            $result['results'][$k] = $r;
        }
        if (in_array('bl', $platforms) || in_array('mg', $platforms)) {
            $result['results']['export'] = exportCookies($ROOT, $COOKIES_DIR, $SCRIPTS_ROOT, $PLATFORMS);
        }
        break;

    default:
        $result['code'] = 400;
        $result['msg']  = '未知操作：' . $ACTION . '（支持 check / gen / pull / export / auto）';
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
}

$result['code'] = 200;
$result['msg']  = 'ok';

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
