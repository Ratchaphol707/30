<?php
session_start();

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once 'db.php';

$message = '';
$messageType = '';

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_to_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($product_id > 0 && $quantity > 0) {
            $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if ($product && $product['stock_quantity'] >= $quantity) {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'quantity' => $quantity
                    ];
                }
                $message = 'เพิ่ม ' . htmlspecialchars($product['name']) . ' x' . $quantity . ' ลงตะกร้าแล้ว';
                $messageType = 'success';
            }
        }
    }
}

// Fetch all products
$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock_quantity > 0
    ORDER BY p.name
")->fetchAll();

// Cart count
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — NexGen Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: block; }

        /* ===== Shop Header ===== */
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

        .shop-nav a:hover {
            color: var(--accent);
        }

        .cart-badge {
            position: relative;
            font-size: 1.3rem;
        }

        .cart-badge .count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: var(--accent);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== Hero Banner ===== */
        .shop-hero {
            text-align: center;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .shop-hero h1 {
            font-family: 'Orbitron', monospace;
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(90deg, var(--text-primary), var(--accent), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .shop-hero p {
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }

        /* ===== Product Grid ===== */
        .shop-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 3rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-6px);
            border-color: var(--accent);
            box-shadow: 0 12px 40px var(--accent-glow), var(--neon-red);
        }

        .product-card .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: var(--bg-primary);
            display: block;
        }

        .product-card .product-image-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, var(--bg-primary), var(--bg-tertiary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }

        .product-card .product-info {
            padding: 1.25rem;
        }

        .product-card .product-category {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-secondary);
            font-family: 'Inter', sans-serif;
            margin-bottom: 0.5rem;
        }

        .product-card .product-name {
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            margin-bottom: 0.4rem;
            color: var(--text-primary);
        }

        .product-card .product-desc {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-card .product-price {
            font-family: 'Orbitron', monospace;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent);
        }

        .product-card .stock-info {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
        }

        .product-card .add-to-cart {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .product-card .qty-input {
            width: 60px;
            padding: 0.5rem;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            text-align: center;
        }

        .product-card .qty-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px var(--accent-glow);
        }

        .product-card .btn-add {
            flex: 1;
            padding: 0.55rem 1rem;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-card .btn-add:hover {
            background: var(--accent-hover);
            box-shadow: 0 4px 16px var(--accent-glow), var(--neon-red);
        }

        /* ===== Shop Alert ===== */
        .shop-alert {
            max-width: 1200px;
            margin: 0 auto 1.5rem;
            padding: 0 2rem;
        }

        /* ===== Footer ===== */
        .shop-footer {
            background: var(--bg-secondary);
            border-top: 1px solid var(--border);
            padding: 2rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>

    <!-- Shop Header -->
    <header class="shop-header">
        <a href="shop.php" class="shop-logo">⚡ NEXGEN STORE</a>
        <nav class="shop-nav">
            <a href="shop.php">🏪 Shop</a>
            <a href="cart.php" class="cart-badge">
                🛒
                <?php if ($cartCount > 0): ?>
                <span class="count"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <span style="color: var(--text-primary); font-size: 0.85rem;">👤 <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                <a href="logout-customer.php" style="color: var(--danger); font-size: 0.8rem;">🏃 Logout</a>
            <?php else: ?>
                <a href="customer-login.php">🔑 Login</a>
            <?php endif; ?>
            <a href="login.php" title="Admin Login">⚙️</a>
        </nav>
    </header>

    <!-- Hero -->
    <section class="shop-hero">
        <h1>⚡ GAMING GEAR</h1>
        <p>อุปกรณ์เกมมิ่งระดับโปร สำหรับทุกสนามแข่ง</p>
    </section>

    <!-- Alert -->
    <?php if ($message): ?>
    <div class="shop-alert">
        <div class="alert alert-<?= $messageType ?>">
            <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Product Grid -->
    <div class="shop-container">
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
            <div class="product-card">
                <?php if ($p['image_url']): ?>
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-image">
                <?php else: ?>
                    <div class="product-image-placeholder">🎮</div>
                <?php endif; ?>
                <div class="product-info">
                    <div class="product-category"><?= htmlspecialchars($p['category_name']) ?></div>
                    <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></div>
                    <div class="product-footer">
                        <div class="product-price">฿<?= number_format($p['price'], 2) ?></div>
                        <div class="stock-info">เหลือ <?= $p['stock_quantity'] ?> ชิ้น</div>
                    </div>
                    <form method="POST" class="add-to-cart">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock_quantity'] ?>" class="qty-input">
                        <button type="submit" class="btn-add">🛒 หยิบใส่ตะกร้า</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="shop-footer">
        <p>© 2026 NexGen Store — Gaming Gear ระดับโปร</p>
    </footer>

</body>
</html>
