<?php
require_once 'admin_auth.php';

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid admin username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - PG Life</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #1e293b; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: #fff; border-radius: 10px; padding: 40px; width: 100%; max-width: 380px; }
        .login-box h3 { margin-bottom: 25px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h3>🔐 PG Life Admin</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark btn-block">Login</button>
        </form>
        <p class="text-center text-muted mt-3" style="font-size:13px;">
            <a href="../index.php">&larr; Back to site</a>
        </p>
    </div>
</body>
</html>
