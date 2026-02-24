<?php
session_start();

// If already logged in, redirect to shop
if (isset($_SESSION['customer_id'])) {
    header('Location: shop.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT id, email, password, full_name FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_name'] = $customer['full_name'];
            
            // Redirect back to cart if they came from there
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
            } else {
                header('Location: shop.php');
            }
            exit;
        } else {
            $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'กรุณากรอกข้อมูลให้ครบ';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login — NexGen Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <h1>⚡ NEXGEN STORE</h1>
                <p>Welcome Back, Gamer!</p>
            </div>

            <?php if ($error): ?>
            <div class="login-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="your@email.com" autofocus>
                </div>
                <div class="form-group">
                    <label>🔒 Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary">🔓 เข้าสู่ระบบ</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem; font-family: 'Inter', sans-serif; font-size: 0.9rem; color: var(--text-muted);">
                ยังไม่มีบัญชี? <a href="register.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">สมัครสมาชิกเลย</a>
            </div>
            
            <div style="text-align: center; margin-top: 1rem;">
                <a href="shop.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.8rem;">⬅️ กลับหน้าหลัก</a>
            </div>
        </div>
    </div>

</body>
</html>
