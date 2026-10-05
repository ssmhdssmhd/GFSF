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
        if ($platform === 'tx') {
            /* 腾讯视频：QQ 扫码登录（官方 ptlogin2 流程） */
            $jar = tempnam(sys_get_temp_dir(), 'gfsf_tx_' . session_id());
            // 1) 访问登录页获取 login_sig
            $ch = curl_init('https://xui.ptlogin2.qq.com/cgi-bin/xlogin?appid=716027021&s_url=' . urlencode('https://v.qq.com/'));
            curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
            curl_exec($ch);
            curl_close($ch);
            // 2) 获取二维码图片，捕获 qrsig 与 login_sig
            $ms = (int)(microtime(true) * 1000);
            $api = 'https://ssl.ptlogin2.qq.com/ptqrshow?appid=716027021&e=2&l=M&s=3&d=72&v=4&t=' . $ms . '&da=1&pt_3rd_aid=0';
            $ch = curl_init($api);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_REFERER, 'https://xui.ptlogin2.qq.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
            $qrImg = curl_exec($ch);
            curl_close($ch);
            $qrsig = ''; $loginSig = '';
            if (is_file($jar)) {
                foreach (file($jar) as $line) {
                    $line = trim($line);
                    if (preg_match('/\tqrsig\t(.+)$/', $line, $m))        $qrsig = $m[1];
                    if (preg_match('/\tpt_login_sig\t(.+)$/', $line, $m)) $loginSig = $m[1];
                }
            }
            if ($qrImg === false || $qrsig === '') {
                @unlink($jar);
                $out = array('code' => 500, 'msg' => '生成二维码失败（QQ 接口受限，可能是服务器 IP 被限制）');
                break;
            }
            $_SESSION['tx_jar']      = $jar;
            $_SESSION['tx_qrsig']    = $qrsig;
            $_SESSION['tx_login_sig']= $loginSig;
            $out = array('code' => 200, 'msg' => 'ok', 'data' => array(
                'key' => $qrsig,
                'url' => $api,
                'img' => 'data:image/png;base64,' . base64_encode($qrImg),
            ));
        } elseif ($platform === 'bl') {
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
        } elseif ($platform === 'iqy' || $platform === 'youku') {
            /* 爱奇艺 / 优酷：官方扫码接口已升级为加密 SDK，无法服务端自动生成二维码
             * 引导使用云端获取或 Cookie 管理手动保存 */
            $out = array('code' => 400, 'msg' => '该平台官方扫码接口已加密，暂不支持自动扫码登录；请使用「云端获取」拉取 Cookie，或在浏览器登录后复制 Cookie 到「Cookie 管理」保存');
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
        if ($platform === 'tx') {
            /* 腾讯视频：QQ 扫码轮询（ptqrlogin） */
            $jar = isset($_SESSION['tx_jar']) ? $_SESSION['tx_jar'] : '';
            if (!is_file($jar) || $_SESSION['tx_qrsig'] !== $key) {
                $out = array('code' => 400, 'msg' => '二维码会话已失效，请重新获取');
                break;
            }
            $loginSig  = isset($_SESSION['tx_login_sig']) ? $_SESSION['tx_login_sig'] : '';
            $ptqrtoken = qqHash33($key);
            $ts  = time();
            $ms  = (int)(microtime(true) * 1000);
            $api = 'https://ssl.ptlogin2.qq.com/ptqrlogin?u1=' . urlencode('https://v.qq.com/')
                 . '&ptqrtoken=' . $ptqrtoken
                 . '&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-' . $ts
                 . '&js_ver=22070110&js_type=1&login_sig=' . urlencode($loginSig)
                 . '&pt_uistyle=40&aid=716027021&da=1&has_onekey=1&_=' . $ms;
            $ch = curl_init($api);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_REFERER, 'https://xui.ptlogin2.qq.com/');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
            $res = curl_exec($ch);
            curl_close($ch);
            // 响应形如 ptuiCB('0','0','{jump}','0','登录成功!','qq号')
            if (preg_match("/ptuiCB\\('([^']*)','[^']*','([^']*)','[^']*','([^']*)'/", (string)$res, $m)) {
                $code = $m[1]; $jump = $m[2]; $msg = $m[3];
                if ($code === '0' && $jump !== '') {
                    $cookies = captureQqCookies($jump, $jar);
                    if ($cookies === '' || $cookies === ';') {
                        $out = array('code' => 500, 'msg' => '登录成功但未捕获到 Cookie（' . $msg . '）', 'data' => array('status' => 0));
                        break;
                    }
                    $ckPath = SCRIPTS_ROOT . '/tx/qqck.txt';
                    if (file_put_contents($ckPath, $cookies) !== false) {
                        @unlink($jar);
                        unset($_SESSION['tx_jar'], $_SESSION['tx_qrsig'], $_SESSION['tx_login_sig']);
                        $out = array('code' => 200, 'msg' => '登录成功，Cookie 已保存', 'data' => array('status' => 0, 'ck' => $cookies, 'size' => strlen($cookies)));
                    } else {
                        $out = array('code' => 500, 'msg' => '写入 Cookie 失败');
                    }
                } elseif ($code === '65') {
                    $out = array('code' => 200, 'msg' => '等待扫码…', 'data' => array('status' => 65));
                } elseif ($code === '66') {
                    $out = array('code' => 200, 'msg' => '已扫码，请在手机上确认登录', 'data' => array('status' => 66));
                } elseif ($code === '67') {
                    @unlink($jar);
                    unset($_SESSION['tx_jar'], $_SESSION['tx_qrsig'], $_SESSION['tx_login_sig']);
                    $out = array('code' => 200, 'msg' => '二维码已过期，请重新获取', 'data' => array('status' => 67, 'expired' => true));
                } else {
                    $out = array('code' => 200, 'msg' => '登录未成功：' . $msg, 'data' => array('status' => $code));
                }
            } else {
                $out = array('code' => 500, 'msg' => '轮询接口返回异常', 'data' => array('output' => mb_substr((string)$res, 0, 200)));
            }
        } elseif ($platform === 'bl') {
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

    /* ---------- 云端获取 Cookie（从 GitHub cookies/ 拉取） ---------- */
    case 'cookie_pull':
        $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
        if (!isset($PLATFORMS[$platform]) || !$PLATFORMS[$platform]['cookie']) {
            $out = array('code' => 400, 'msg' => '该平台无 cookie 文件');
            break;
        }
        $url = 'https://raw.githubusercontent.com/' . GH_REPO . '/' . GH_BRANCH . '/cookies/' . $platform . '.txt';
        $res = trim((string)adminCurl($url));
        if ($res === '' || strlen($res) < 10) {
            $out = array('code' => 500, 'msg' => 'GitHub 未找到该平台 cookie（cookies/' . $platform . '.txt 为空或不存在）', 'data' => array('url' => $url));
            break;
        }
        $dirReal = realpath(SCRIPTS_ROOT . '/' . $PLATFORMS[$platform]['dir']);
        $ckPath  = $dirReal . '/' . $PLATFORMS[$platform]['cookie'];
        if (file_put_contents($ckPath, $res) === false) {
            $out = array('code' => 500, 'msg' => '写入本地失败');
            break;
        }
        // 调用平台校验脚本确认有效性（blcheckck.php / mgcheckck.php）
        $check = array('ok' => false, 'output' => '');
        $CHECK_SCRIPTS = array('bl' => 'blcheckck.php', 'mg' => 'mgcheckck.php');
        if (isset($CHECK_SCRIPTS[$platform]) && is_file($dirReal . '/' . $CHECK_SCRIPTS[$platform])) {
            $runner = __DIR__ . '/runner.php';
            $cmd = 'cd ' . escapeshellarg($dirReal)
                 . ' && echo "" | timeout 20 php ' . escapeshellarg($runner) . ' '
                 . escapeshellarg($dirReal) . ' ' . escapeshellarg($CHECK_SCRIPTS[$platform]) . ' 2>&1';
            $ret = trim((string)shell_exec($cmd));
            $jj  = json_decode($ret, true);
            $check = array(
                'ok'     => is_array($jj) && isset($jj['code']) && (int)$jj['code'] === 200,
                'output' => mb_substr($ret, 0, 300),
            );
        }
        $out = array('code' => 200, 'msg' => '已从 GitHub 获取并保存到本地', 'data' => array(
            'size'  => strlen($res),
            'mtime' => date('Y-m-d H:i:s', filemtime($ckPath)),
            'check' => $check,
            'url'   => $url,
        ));
        break;

    /* ---------- 在线更新：检查 GitHub 最新版本 ---------- */
    case 'update_check':
        $remoteVer = trim((string)adminCurl('https://raw.githubusercontent.com/' . GH_REPO . '/' . GH_BRANCH . '/VERSION'));
        $localVer  = (is_file(__DIR__ . '/VERSION')) ? trim((string)file_get_contents(__DIR__ . '/VERSION')) : '';
        $remoteReadme = (string)adminCurl('https://raw.githubusercontent.com/' . GH_REPO . '/' . GH_BRANCH . '/README.md');
        $out = array('code' => 200, 'msg' => 'ok', 'data' => array(
            'local'        => $localVer,
            'remote'       => $remoteVer,
            'has_update'   => ($remoteVer !== '' && $remoteVer !== $localVer),
            'remote_readme'=> mb_substr($remoteReadme, 0, 3500),
        ));
        break;

    /* ---------- 在线更新：从 GitHub 拉取最新代码并覆盖 ---------- */
    case 'update_run':
        $zipUrl = 'https://codeload.github.com/' . GH_REPO . '/zip/refs/heads/' . GH_BRANCH;
        $tmp    = sys_get_temp_dir() . '/gfsf_update_' . time();
        @mkdir($tmp, 0777, true);
        $zipFile = $tmp . '/update.zip';

        // 下载 zip
        $ch = curl_init($zipUrl);
        $fp = fopen($zipFile, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if ($httpCode !== 200 || filesize($zipFile) < 1000) {
            @unlink($zipFile);
            $out = array('code' => 500, 'msg' => '下载代码失败（HTTP ' . $httpCode . '）');
            break;
        }

        // 解压（优先 PHP ZipArchive，其次系统 unzip）
        $extracted = unzipArchive($zipFile, $tmp);
        if ($extracted === null || !is_dir($extracted)) {
            @unlink($zipFile);
            $out = array('code' => 500, 'msg' => '解压失败：请安装 PHP zip 扩展或系统 unzip 命令');
            break;
        }

        // 备份本地（排除 cookies/.uploads/.git/backups）
        $bkDir = __DIR__ . '/backups/update_' . date('Ymd_His');
        @mkdir($bkDir, 0777, true);
        recursiveCopy(__DIR__, $bkDir, array('cookies', '.uploads', '.git', 'backups'));

        // 覆盖本地（保留：cookies/.uploads/.git/backups/config.php）
        recursiveCopy($extracted, __DIR__, array('cookies', '.uploads', '.git', 'backups', 'config.php'));

        // 清理临时文件
        deleteDir($tmp);
        $out = array('code' => 200, 'msg' => '更新完成：已从 GitHub 拉取最新代码覆盖本地（保留 cookies/、backups/ 与本地 config.php）', 'data' => array(
            'backup' => 'backups/' . basename($bkDir),
        ));
        break;

    /* ---------- 手动升级：上传 ZIP 压缩包覆盖升级 ---------- */
    case 'update_upload':
        if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            $out = array('code' => 400, 'msg' => '未收到上传文件（请选择 .zip 压缩包）');
            break;
        }
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $out = array('code' => 400, 'msg' => '上传失败：错误码 ' . $f['error']);
            break;
        }
        if ($f['size'] < 100 || $f['size'] > 209715200) { // 100B ~ 200MB
            $out = array('code' => 400, 'msg' => '压缩包大小不合法（100B ~ 200MB）');
            break;
        }
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            $out = array('code' => 400, 'msg' => '仅支持 .zip 压缩包');
            break;
        }

        // 复制上传文件到临时目录并解压（避免占用上传临时文件）
        $tmp = sys_get_temp_dir() . '/gfsf_manual_' . time() . '_' . mt_rand(1000, 9999);
        @mkdir($tmp, 0777, true);
        $zipFile = $tmp . '/upgrade.zip';
        if (!move_uploaded_file($f['tmp_name'], $zipFile)) {
            deleteDir($tmp);
            $out = array('code' => 500, 'msg' => '保存上传文件失败（请检查目录权限）');
            break;
        }
        $extracted = unzipArchive($zipFile, $tmp);
        if ($extracted === null || !is_dir($extracted)) {
            deleteDir($tmp);
            $out = array('code' => 500, 'msg' => '解压失败：请确认是有效的 zip 压缩包（需 PHP zip 扩展或系统 unzip 命令）');
            break;
        }

        // 安全检查：压缩包内不能包含敏感/危险文件（拒绝可被脚本执行的非常规文件）
        $forbidden = array('composer.lock', '.env', 'config.local.php');
        foreach ($forbidden as $fb) {
            if (file_exists($extracted . '/' . $fb)) {
                deleteDir($tmp);
                $out = array('code' => 400, 'msg' => '压缩包包含受限文件 ' . $fb . '，已取消升级');
                break;
            }
        }

        // 备份本地（排除 cookies/.uploads/.git/backups）
        $bkDir = __DIR__ . '/backups/manual_' . date('Ymd_His');
        @mkdir($bkDir, 0777, true);
        recursiveCopy(__DIR__, $bkDir, array('cookies', '.uploads', '.git', 'backups'));

        // 覆盖本地（保留：cookies/.uploads/.git/backups/config.php）
        recursiveCopy($extracted, __DIR__, array('cookies', '.uploads', '.git', 'backups', 'config.php'));

        // 清理临时文件
        deleteDir($tmp);
        $out = array('code' => 200, 'msg' => '手动升级完成：已解压并覆盖本地代码（保留 cookies/、backups/ 与本地 config.php）', 'data' => array(
            'backup' => 'backups/' . basename($bkDir),
        ));
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

/** QQ 扫码 hash33（ptqrtoken 计算，与腾讯官方 JS 一致） */
function qqHash33($t) {
    $e = 0;
    $n = strlen($t);
    for ($i = 0; $i < $n; ++$i) {
        $shl = ($e << 5) & 0xFFFFFFFF;
        if ($shl > 0x7FFFFFFF) $shl -= 0x100000000; // 转 32 位有符号
        $e += $shl + ord($t[$i]);
    }
    return 2147483647 & $e;
}

/** 跟随 QQ 登录跳转链，抓取所有 Set-Cookie（含 v.qq.com 域） */
function captureQqCookies($jumpUrl, $jar) {
    $ch = curl_init($jumpUrl);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
    $raw = (string)curl_exec($ch);
    curl_close($ch);
    $cookies = array();
    if (preg_match_all('/^Set-Cookie:\s*([^;=]+)=([^;]*)/mi', $raw, $mm)) {
        foreach ($mm[1] as $i => $k) {
            $cookies[$k] = $mm[2][$i];
        }
    }
    $str = '';
    foreach ($cookies as $k => $v) $str .= $k . '=' . $v . ';';
    return $str;
}

/** 解压 zip 到目录，返回 zip 内第一层目录（仓库根），失败返回 null */
function unzipArchive($zipFile, $dest) {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($dest);
            $zip->close();
            foreach (scandir($dest) as $e) {
                if ($e !== '.' && $e !== '..' && is_dir($dest . '/' . $e)) return $dest . '/' . $e;
            }
            return $dest;
        }
    }
    $cmd = 'unzip -q ' . escapeshellarg($zipFile) . ' -d ' . escapeshellarg($dest) . ' 2>&1';
    shell_exec($cmd);
    foreach (scandir($dest) as $e) {
        if ($e !== '.' && $e !== '..' && is_dir($dest . '/' . $e)) return $dest . '/' . $e;
    }
    return null;
}

/** 递归复制目录；$exclude 为排除的名字（任意层级，基于 basename） */
function recursiveCopy($src, $dst, $exclude = array()) {
    $exclude = array_flip($exclude);
    foreach (scandir($src) as $e) {
        if ($e === '.' || $e === '..' || isset($exclude[$e])) continue;
        $s = $src . '/' . $e;
        $d = $dst . '/' . $e;
        if (is_dir($s)) {
            @mkdir($d, 0777, true);
            recursiveCopy($s, $d, $exclude);
        } else {
            if (!copy($s, $d)) return false;
        }
    }
    return true;
}

/** 递归删除目录 */
function deleteDir($dir) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $e) {
        if ($e === '.' || $e === '..') continue;
        $p = $dir . '/' . $e;
        if (is_dir($p)) deleteDir($p); else @unlink($p);
    }
    @rmdir($dir);
}
