<?php
/**
 * GFSF 算法后台 · 登录页
 */
require __DIR__ . '/config.php';

session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        session_regenerate_id(true);
        $_SESSION['login']   = true;
        $_SESSION['user']    = $user;
        $_SESSION['login_at'] = date('Y-m-d H:i:s');
        header('Location: index.php');
        exit;
    }
    $error = '用户名或密码错误';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GFSF 算法管理后台 · 登录</title>
<link rel="icon" href="https://cdn.jsdelivr.net/gh/ssmhdssmhd/MXLOGO@main/favicon/favicon.ico">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-body">
  <div class="login-card">
    <img class="login-logo" src="https://cdn.jsdelivr.net/gh/ssmhdssmhd/MXLOGO@main/web/web-logo.svg" alt="GFSF">
    <h1>GFSF 算法管理后台</h1>
    <p class="login-sub">算法脚本控制台 · Cookie 管理</p>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" action="login.php" autocomplete="off">
      <label>用户名
        <input type="text" name="username" required autofocus>
      </label>
      <label>密码
        <input type="password" name="password" required>
      </label>
      <button type="submit" class="btn btn-primary btn-block">登 录</button>
    </form>
    <p class="login-foot">© 2026 射手沫蝴蝶 · GFSF 算法项目</p>
  </div>
</body>
</html>
