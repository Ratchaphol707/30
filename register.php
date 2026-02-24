<?php
session_start();

if (isset($_SESSION['customer_id'])) {
    header('Location: shop.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if ($full_name && $email && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error = 'รหัสผ่านไม่ตรงกัน';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'อีเมลนี้ถูกใช้งานแล้ว';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO customers (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$full_name, $email, $hashed_password, $phone])) {
                    $success = 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ';
                } else {
                    $error = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
                }
            }
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
    <title>Register — NexGen Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-card { max-width: 500px; }
    </style>
</head>
<body class="login-page">

    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <h1>⚡ NEXGEN STORE</h1>
                <p>Join the Pro Community</p>
            </div>

            <?php if ($error): ?>
            <div class="login-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="login-error" style="border-color: var(--success); color: var(--success);">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>👤 Full Name</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Enter full name" value="<?= htmlspecialchars($full_name ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="your@email.com" value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>📱 Phone (Optional)</label>
                    <input type="text" name="phone" class="form-control" placeholder="0XXXXXXXXX" value="<?= htmlspecialchars($phone ?? '') ?>">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>🔒 Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label>🔒 Confirm</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Confirm">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">🚀 สมัครสมาชิก</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem; font-family: 'Inter', sans-serif; font-size: 0.9rem; color: var(--text-muted);">
                มีบัญชีอยู่แล้ว? <a href="customer-login.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">เข้าสู่ระบบ</a>
            </div>
        </div>
    </div>

</body>
</html>
