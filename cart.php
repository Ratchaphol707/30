<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once 'db.php';

$message = '';
$messageType = '';

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
        $message = 'ลบสินค้าออกจากตะกร้าแล้ว';
        $messageType = 'danger';
    }
}

// Handle Update Quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    foreach ($_POST['quantities'] ?? [] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if (isset($_SESSION['cart'][$pid])) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $_SESSION['cart'][$pid]['quantity'] = $qty;
            }
        }
    }
    $message = 'อัปเดตตะกร้าเรียบร้อย';
    $messageType = 'success';
}

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    if (!empty($_SESSION['cart'])) {
        try {
            $pdo->beginTransaction();

            $totalAmount = 0;

            // Validate stock for all items
            foreach ($_SESSION['cart'] as $pid => $item) {
                $stmt = $pdo->prepare("SELECT id, stock_quantity, price FROM products WHERE id = ?");
                $stmt->execute([$pid]);
                $product = $stmt->fetch();

                if (!$product || $product['stock_quantity'] < $item['quantity']) {
                    throw new Exception('สินค้า ' . $item['name'] . ' สต็อกไม่เพียงพอ');
                }
                $totalAmount += $product['price'] * $item['quantity'];
            }

            // Create sale
            $stmt = $pdo->prepare("INSERT INTO sales (total_amount) VALUES (?)");
            $stmt->execute([$totalAmount]);
            $saleId = $pdo->lastInsertId();

            // Create sale items + deduct stock
            foreach ($_SESSION['cart'] as $pid => $item) {
                $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$pid]);
                $product = $stmt->fetch();

                $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$saleId, $pid, $item['quantity'], $product['price']]);

                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $pid]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];
            $message = '🎉 สั่งซื้อสำเร็จ! ยอดรวม ฿' . number_format($totalAmount, 2) . ' (Order #' . $saleId . ')';
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Calculate totals
$cartTotal = 0;
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
    $cartCount += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart — NexGen Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; }

        .shop-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .shop-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, var(--accent), var(--accent-secondary), transparent);
            opacity: 0.5;
        }

        .shop-logo {
            font-family: 'Orbitron', monospace;
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .shop-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .shop-nav a {
            color: var(--text-secondary);
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .shop-nav a:hover { color: var(--accent); }

        .cart-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .cart-title {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(90deg, var(--text-primary), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cart-item {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
        }

        .cart-item:hover {
            border-color: var(--accent);
        }

        .cart-item .item-name {
            flex: 1;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }

        .cart-item .item-price {
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            color: var(--accent-secondary);
            min-width: 120px;
            text-align: right;
        }

        .cart-item .qty-input {
            width: 65px;
            padding: 0.5rem;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            text-align: center;
        }

        .cart-item .qty-input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .cart-item .remove-btn {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .cart-item .remove-btn:hover {
            text-shadow: var(--neon-red);
        }

        .cart-summary {
            background: var(--bg-secondary);
            border: 1px solid var(--accent);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: var(--neon-red);
        }

        .cart-summary .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .cart-summary .total-label {
            font-family: 'Orbitron', monospace;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cart-summary .total-value {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent);
            text-shadow: 0 0 20px var(--accent-glow);
        }

        .cart-summary .cart-actions {
            display: flex;
            gap: 1rem;
        }

        .cart-summary .cart-actions form,
        .cart-summary .cart-actions a {
            flex: 1;
        }

        .cart-summary .btn { width: 100%; justify-content: center; }

        .shop-footer {
            background: var(--bg-secondary);
            border-top: 1px solid var(--border);
            padding: 2rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
            margin-top: 3rem;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="shop-header">
        <a href="shop.php" class="shop-logo">⚡ NEXGEN STORE</a>
        <nav class="shop-nav">
            <a href="shop.php">🏪 Shop</a>
            <a href="cart.php">🛒 Cart (<?= $cartCount ?>)</a>
            <a href="login.php">🔐 Admin</a>
        </nav>
    </header>

    <div class="cart-container">
        <h2 class="cart-title">🛒 ตะกร้าสินค้า</h2>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 1.5rem;">
            <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
        </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
        <div class="card">
            <div class="empty-state">
                <div class="icon">🛒</div>
                <p>ตะกร้าว่างเปล่า</p>
                <br>
                <a href="shop.php" class="btn btn-primary">🏪 เลือกซื้อสินค้า</a>
            </div>
        </div>
        <?php else: ?>

        <form method="POST">
            <input type="hidden" name="action" value="update">
            <?php foreach ($_SESSION['cart'] as $pid => $item): ?>
            <div class="cart-item">
                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div>
                    <input type="number" name="quantities[<?= $pid ?>]" value="<?= $item['quantity'] ?>" min="0" class="qty-input">
                </div>
                <div class="item-price">฿<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                <a href="cart.php?remove=<?= $pid ?>" class="remove-btn" title="ลบ">✕</a>
            </div>
            <?php endforeach; ?>

            <div class="cart-summary">
                <div class="total-row">
                    <span class="total-label">ยอดรวม</span>
                    <span class="total-value">฿<?= number_format($cartTotal, 2) ?></span>
                </div>
                <div class="cart-actions">
                    <button type="submit" class="btn btn-cyber">🔄 อัปเดตตะกร้า</button>
                </div>
            </div>
        </form>

        <div class="cart-summary" style="border-color: var(--success); box-shadow: none; margin-top: 1rem;">
            <div class="cart-actions">
                <a href="shop.php" class="btn btn-cyber" style="text-align:center;">🏪 เลือกซื้อต่อ</a>
                <form method="POST" style="flex:1;">
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('ยืนยันการสั่งซื้อ?')" style="width:100%;justify-content:center;">💳 สั่งซื้อเลย</button>
                </form>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <footer class="shop-footer">
        <p>© 2026 NexGen Store — Gaming Gear ระดับโปร</p>
    </footer>

</body>
</html>
