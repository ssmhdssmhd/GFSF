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
    /* ---------- 生成 Cookie ---------- */
    case 'cookie_gen':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        if (!isset($PLATFORMS[$platform]) || !$PLATFORMS[$platform]['cookie']) {
            $out = array('code' => 400, 'msg' => '该平台无 cookie 文件');
            break;
        }
        // 可直接自动执行、返回 cookie 文本的生成脚本（扫码登录类脚本无法 CLI 自动化）
        $GEN_SCRIPTS = array(
            'bl' => 'blgetck.php',   // 校验并返回 B 站 cookie
            'mg' => 'mggetck.php',   // 返回芒果 ticket/cookie
        );
        if (!isset($GEN_SCRIPTS[$platform])) {
            $out = array('code' => 400, 'msg' => '该平台无自动生成脚本（扫码登录请手动操作后保存）');
            break;
        }
        $dirReal    = realpath(SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir']);
        $scriptReal = realpath($dirReal . '/' . $GEN_SCRIPTS[$platform]);
        if ($dirReal === false || $scriptReal === false || strpos($scriptReal, $dirReal) !== 0 || !is_file($scriptReal)) {
            $out = array('code' => 500, 'msg' => '生成脚本不存在');
            break;
        }
        $runner = __DIR__ . '/runner.php';
        $cmd = 'cd ' . escapeshellarg(dirname($scriptReal))
             . ' && echo "" | timeout 30 php ' . escapeshellarg($runner) . ' '
             . escapeshellarg(dirname($scriptReal)) . ' '
             . escapeshellarg(basename($scriptReal)) . ' 2>&1';
        $t0  = microtime(true);
        $ret = shell_exec($cmd);
        $cost = round(microtime(true) - $t0, 3);
        $ret = trim((string)$ret);
        // 判定结果
        if (mb_strpos($ret, 'Cookieout') !== false || mb_strpos($ret, 'Cookie失效') !== false) {
            $out = array('code' => 200, 'msg' => 'Cookie 已失效（检测到失效标记），请扫码登录或手动更新', 'script' => $GEN_SCRIPTS[$platform], 'cost_s' => $cost, 'saved' => false, 'output' => $ret);
            break;
        }
        $isBad = ($ret === '');
        foreach (array('解析失败', '超时', '为空', '获取转跳链接失败') as $w) {
            if (mb_strpos($ret, $w) !== false) { $isBad = true; break; }
        }
        if ($isBad) {
            $out = array('code' => 200, 'msg' => '生成失败（输出无效）', 'script' => $GEN_SCRIPTS[$platform], 'cost_s' => $cost, 'saved' => false, 'output' => $ret);
            break;
        }
        // 保存到平台 cookie 文件
        $ckPath = $dirReal . '/' . $PLATFORMS[$platform]['cookie'];
        if (file_put_contents($ckPath, $ret) !== false) {
            $out = array('code' => 200, 'msg' => '生成并保存成功', 'script' => $GEN_SCRIPTS[$platform], 'cost_s' => $cost, 'saved' => true, 'output' => $ret, 'data' => array('size' => strlen($ret), 'mtime' => date('Y-m-d H:i:s', filemtime($ckPath))));
        } else {
            $out = array('code' => 500, 'msg' => '写入失败', 'script' => $GEN_SCRIPTS[$platform], 'cost_s' => $cost, 'saved' => false, 'output' => $ret);
        }
        break;
    /* ---------- 扫码登录：获取二维码 ---------- */
    case 'qr_login':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        if ($platform === 'bl') {
            $api = 'https://passport.bilibili.com/x/passport-login/web/qrcode/generate';
            $res = adminCurl($api);
            $j = json_decode($res, true);
            if (isset($j['code']) && $j['code'] === 0 && !empty($j['data']['qrcode_key'])) {
                $out = array('code' => 200, 'msg' => 'ok', 'data' => array('key' => $j['data']['qrcode_key'], 'url' => $j['data']['url']));
            } else {
                $out = array('code' => 500, 'msg' => '生成二维码失败：' . (isset($j['message']) ? $j['message'] : '接口异常'));
            }
        } elseif ($platform === 'mg') {
            $api = 'https://nuc.api.mgtv.com/GetMultiPic?_support=10000000&deviceid=78aae3d2-f2ff-4e2c-a6f2-a976d295de03&appVersion=pcweb_umd-6.3.0&dname=&src=mgtv&invoker=pcweb&noBinding=0';
            $res = adminCurl($api);
            $j = json_decode($res, true);
            if (isset($j['code']) && $j['code'] === 200 && !empty($j['data']['rcode'])) {
                $out = array('code' => 200, 'msg' => 'ok', 'data' => array('key' => $j['data']['rcode'], 'url' => $j['data']['url']));
            } else {
                $out = array('code' => 500, 'msg' => '生成二维码失败：' . (isset($j['msg']) ? $j['msg'] : '接口异常'));
            }
        } else {
            $out = array('code' => 400, 'msg' => '该平台暂不支持扫码登录');
        }
        break;

    /* ---------- 扫码登录：轮询扫码结果并保存 Cookie ---------- */
    case 'qr_poll':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        $key      = isset($_POST['key']) ? trim($_POST['key']) : '';
        if ($platform === '' || $key === '' || strlen($key) > 128) {
            $out = array('code' => 400, 'msg' => '参数错误');
            break;
        }
        $cookies = '';
        if ($platform === 'bl') {
            $api = 'https://passport.bilibili.com/x/passport-login/web/qrcode/poll?qrcode_key=' . urlencode($key);
            list($header, $body) = adminCurlRaw($api);
            $j = json_decode($body, true);
            $status = isset($j['data']['code']) ? (int)$j['data']['code'] : -1;
            // data.code: 0=成功 86038=过期 86101=已扫码待确认 86090=未扫码
            preg_match_all('/^Set-Cookie:\s*([^;]+);/mi', $header, $m);
            $cookies = implode(';', $m[1]) . ';';
            if ($status === 0 && $cookies !== ';') {
                $ckPath = SCRIPTS_ROOT . '/bl/ck.txt';
                file_put_contents($ckPath, $cookies);
                $out = array('code' => 200, 'msg' => '登录成功，Cookie 已保存', 'data' => array('status' => $status, 'ck' => $cookies, 'size' => strlen($cookies)));
            } elseif ($status === 86038) {
                $out = array('code' => 200, 'msg' => '二维码已过期，请重新获取', 'data' => array('status' => $status));
            } elseif ($status === 86101) {
                $out = array('code' => 200, 'msg' => '已扫码，请在手机上确认登录', 'data' => array('status' => $status));
            } else {
                $out = array('code' => 200, 'msg' => '等待扫码…', 'data' => array('status' => $status));
            }
        } elseif ($platform === 'mg') {
            $api = 'https://nuc.api.mgtv.com/GetQrcodeResult?invoker=pcweb&rcode=' . urlencode($key);
            list($header, $body) = adminCurlRaw($api);
            $j = json_decode($body, true);
            $status = isset($j['code']) ? (int)$j['code'] : -1;
            preg_match_all('/^Set-Cookie:\s*([^;]+);/mi', $header, $m);
            $cookies = implode(';', $m[1]) . ';';
            if ($status === 200 && !empty($j['data']['ticket'])) {
                $cookies .= 'ticket:' . $j['data']['ticket'] . ';';
                $ckPath = SCRIPTS_ROOT . '/mg/ck.txt';
                file_put_contents($ckPath, $cookies);
                $out = array('code' => 200, 'msg' => '登录成功，Cookie 已保存', 'data' => array('status' => $status, 'ck' => $cookies, 'size' => strlen($cookies)));
            } else {
                $out = array('code' => 200, 'msg' => '等待扫码…', 'data' => array('status' => $status));
            }
        } else {
            $out = array('code' => 400, 'msg' => '该平台暂不支持扫码登录');
        }
        break;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

/** 通用 cURL（返回正文） */
function adminCurl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
/** 通用 cURL（返回 [header, body]，用于捕获 Set-Cookie） */
function adminCurlRaw($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
    $raw = curl_exec($ch);
    $hs  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return array(substr($raw, 0, $hs), substr($raw, $hs));
}
