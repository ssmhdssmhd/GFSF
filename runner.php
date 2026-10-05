<?php
/**
 * GFSF 算法后台 · 脚本执行器 (CLI)
 * 用法: php runner.php <脚本目录> <脚本文件名>
 * url 参数从 STDIN 读取
 */
error_reporting(0);

$dir  = isset($argv[1]) ? $argv[1] : '';
$file = isset($argv[2]) ? $argv[2] : '';

if ($dir === '' || $file === '' || !is_dir($dir) || !is_file($dir . DIRECTORY_SEPARATOR . $file)) {
    echo json_encode(array('code' => 500, 'msg' => '脚本参数错误'), JSON_UNESCAPED_UNICODE);
    exit;
}

// 切换到脚本目录，保证相对路径 (ck.txt 等) 正确加载
chdir($dir);

// 模拟 HTTP 环境
$_GET['url'] = trim(stream_get_contents(STDIN));
$_REQUEST['url'] = $_GET['url']; // 部分脚本使用 $_REQUEST
$_SERVER['REQUEST_URI']  = '/?url=' . rawurlencode($_GET['url']); // 部分脚本从 REQUEST_URI 取参
$_SERVER['HTTP_HOST']    = '127.0.0.1';
$_SERVER['REMOTE_ADDR']  = '127.0.0.1'; // qq.php 有 ip 授权白名单
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';

include $file;
