<?php
/**
 * GFSF 算法后台 · API 接口
 * 所有接口均需登录态
 */
require __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['login'])) {
    echo json_encode(array('code' => 401, 'msg' => '未登录'), JSON_UNESCAPED_UNICODE);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$out    = array('code' => 404, 'msg' => '未知操作');

switch ($action) {

    /* ---------- 平台概览 ---------- */
    case 'platforms':
        $list = array();
        foreach ($PLATFORMS as $key => $p) {
            $dir    = SCRIPTS_ROOT . '/' . $p['dir'];
            $scripts = glob($dir . '/*.php');
            $ckInfo  = null;
            if ($p['cookie']) {
                $ckPath = $dir . '/' . $p['cookie'];
                if (is_file($ckPath)) {
                    $ckInfo = array(
                        'exists' => true,
                        'size'   => filesize($ckPath),
                        'mtime'  => date('Y-m-d H:i:s', filemtime($ckPath)),
                        'head'   => mb_substr(file_get_contents($ckPath), 0, 120),
                    );
                } else {
                    $ckInfo = array('exists' => false, 'size' => 0, 'mtime' => '-', 'head' => '');
                }
            }
            $list[] = array(
                'key'      => $key,
                'name'     => $p['name'],
                'dir'      => $p['dir'],
                'scripts'  => $scripts ? count($scripts) : 0,
                'cookie'   => $p['cookie'],
                'ckInfo'   => $ckInfo,
            );
        }
        $out = array('code' => 200, 'msg' => 'ok', 'data' => $list);
        break;

    /* ---------- 平台脚本列表 ---------- */
    case 'scripts':
        $platform = isset($_GET['platform']) ? $_GET['platform'] : '';
        if (!isset($PLATFORMS[$platform])) {
            $out = array('code' => 400, 'msg' => '平台不存在');
            break;
        }
        $dir = SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir'];
        $files = array();
        foreach (glob($dir . '/*.php') ?: array() as $f) {
            $files[] = array('name' => basename($f), 'size' => filesize($f));
        }
        $out = array('code' => 200, 'msg' => 'ok', 'data' => $files);
        break;

    /* ---------- 运行脚本 ---------- */
    case 'run':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        $script   = isset($_POST['script'])   ? $_POST['script']   : '';
        $url      = isset($_POST['url'])      ? trim($_POST['url']) : '';
        if (!isset($PLATFORMS[$platform])) {
            $out = array('code' => 400, 'msg' => '平台不存在');
            break;
        }
        if ($url === '' || strlen($url) > 2000) {
            $out = array('code' => 400, 'msg' => '视频链接为空或过长');
            break;
        }
        // 校验脚本路径：必须在平台目录内且为 .php
        $dirReal = realpath(SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir']);
        $scriptReal = realpath($dirReal . '/' . $script);
        if ($dirReal === false || $scriptReal === false
            || strpos($scriptReal, $dirReal) !== 0
            || !is_file($scriptReal)
            || pathinfo($scriptReal, PATHINFO_EXTENSION) !== 'php'
            || basename($scriptReal) !== basename($script)) {
            $out = array('code' => 400, 'msg' => '脚本路径不合法');
            break;
        }

        $runner = __DIR__ . '/runner.php';
        $cmd = 'cd ' . escapeshellarg(dirname($scriptReal))
             . ' && echo ' . escapeshellarg($url)
             . ' | timeout 45 php ' . escapeshellarg($runner) . ' '
             . escapeshellarg(dirname($scriptReal)) . ' '
             . escapeshellarg(basename($scriptReal)) . ' 2>&1';

        $t0 = microtime(true);
        $ret = shell_exec($cmd);
        $cost = round(microtime(true) - $t0, 3);
        $out = array(
            'code'   => 200,
            'msg'    => 'ok',
            'script' => $PLATFORMS[$platform]['dir'] . '/' . basename($scriptReal),
            'url'    => $url,
            'cost_s' => $cost,
            'output' => $ret === null ? '(执行超时或失败)' : $ret,
        );
        break;

    /* ---------- 读取 Cookie ---------- */
    case 'cookie_get':
        $platform = isset($_GET['platform']) ? $_GET['platform'] : '';
        if (!isset($PLATFORMS[$platform]) || !$PLATFORMS[$platform]['cookie']) {
            $out = array('code' => 400, 'msg' => '该平台无 cookie 文件');
            break;
        }
        $ckPath = SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir'] . '/' . $PLATFORMS[$platform]['cookie'];
        if (!is_file($ckPath)) {
            $out = array('code' => 200, 'msg' => 'ok', 'data' => array('content' => '', 'size' => 0, 'mtime' => '-'));
            break;
        }
        $out = array('code' => 200, 'msg' => 'ok', 'data' => array(
            'content' => file_get_contents($ckPath),
            'size'    => filesize($ckPath),
            'mtime'   => date('Y-m-d H:i:s', filemtime($ckPath)),
        ));
        break;

    /* ---------- 保存 Cookie ---------- */
    case 'cookie_save':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        $content  = isset($_POST['content'])  ? $_POST['content']  : '';
        if (!isset($PLATFORMS[$platform]) || !$PLATFORMS[$platform]['cookie']) {
            $out = array('code' => 400, 'msg' => '该平台无 cookie 文件');
            break;
        }
        if (strlen($content) > 65536) {
            $out = array('code' => 400, 'msg' => '内容过长');
            break;
        }
        $ckPath = SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir'] . '/' . $PLATFORMS[$platform]['cookie'];
        if (file_put_contents($ckPath, $content) !== false) {
            $out = array('code' => 200, 'msg' => '保存成功', 'data' => array(
                'size'  => filesize($ckPath),
                'mtime' => date('Y-m-d H:i:s', filemtime($ckPath)),
            ));
        } else {
            $out = array('code' => 500, 'msg' => '写入失败');
        }
        break;

    /* ---------- 清空 Cookie ---------- */
    case 'cookie_clear':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        if (!isset($PLATFORMS[$platform]) || !$PLATFORMS[$platform]['cookie']) {
            $out = array('code' => 400, 'msg' => '该平台无 cookie 文件');
            break;
        }
        $ckPath = SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir'] . '/' . $PLATFORMS[$platform]['cookie'];
        if (is_file($ckPath) && unlink($ckPath)) {
            $out = array('code' => 200, 'msg' => '已清空');
        } else {
            $out = array('code' => 500, 'msg' => '清空失败');
        }
        break;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
