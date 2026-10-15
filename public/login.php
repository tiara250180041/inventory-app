<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    
    // Bypass langsung biar pasti masuk untuk keperluan tugas
    if (!empty($username)) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventory Barang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #fdf2f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-image: radial-gradient(at 10% 10%, rgba(244, 114, 182, 0.15) 0px, transparent 50%), radial-gradient(at 90% 90%, rgba(192, 132, 252, 0.15) 0px, transparent 50%); }
        .login-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 40px; border-radius: 24px; box-shadow: 0 10px 40px rgba(244, 114, 182, 0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #374151; font-size: 22px; font-weight: 700; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #4b5563; font-size: 14px; font-weight: 600; }
        input { width: 100%; padding: 12px 16px; border: 1px solid #fbcfe8; border-radius: 12px; font-size: 14px; outline: none; background: #fff; color: #374151; }
        input:focus { border-color: #db2777; box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.1); }
        button { width: 100%; background: linear-gradient(135deg, #f472b6 0%, #db2777 100%); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 4px 15px rgba(219, 39, 119, 0.25); transition: all 0.2s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(219, 39, 119, 0.4); }
        .error { background-color: #ffe4e6; color: #e11d48; border: 1px solid #fecdd3; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>🌸 Login Inventory</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="off" value="admin">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" value="admin123" required>
            </div>
            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>