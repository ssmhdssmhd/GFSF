<?php
/**
 * GFSF 算法后台 · 全局配置
 * 版本: v0.0.1 (2026-10-05)
 */

/* ================= 管理员账号 ================= */
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'gf2026admin'); // 首次登录后建议修改

/* ================= 会话 ================= */
define('SESSION_NAME', 'GFSFADMIN');
session_name(SESSION_NAME);

/* ================= 算法脚本根目录 ================= */
// 脚本通过 CLI 子进程执行，cwd 会切换到脚本所在目录，保证 ck.txt 等相对路径正确加载
define('SCRIPTS_ROOT', '/workspace/uploads_sf/算法');

/* ================= 平台配置 =================
 * dir     : 平台目录（相对 SCRIPTS_ROOT）
 * cookie  : cookie 保存文件名（相对平台目录），null 表示无独立 cookie 文件
 */
$PLATFORMS = array(
    'tx'    => array('name' => '腾讯视频', 'dir' => 'tx',    'cookie' => 'qqck.txt'),
    'bl'    => array('name' => '哔哩哔哩', 'dir' => 'bl',    'cookie' => 'ck.txt'),
    'youku' => array('name' => '优酷',     'dir' => 'youku', 'cookie' => null),
    'mg'    => array('name' => '芒果TV',   'dir' => 'mg',    'cookie' => 'ck.txt'),
    'iqy'   => array('name' => '爱奇艺',   'dir' => 'iqy',   'cookie' => 'ck.txt'),
);
