<?php
require_once 'db.php';

$message = '';
$messageType = '';

// Handle new sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sell') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    if ($product_id > 0 && $quantity > 0) {
        // Check stock
        $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            $message = 'ไม่พบสินค้า';
            $messageType = 'danger';
        } elseif ($product['stock_quantity'] < $quantity) {
            $message = 'สต็อกไม่เพียงพอ (เหลือ ' . $product['stock_quantity'] . ' ชิ้น)';
            $messageType = 'danger';
        } else {
            try {
                $pdo->beginTransaction();

                $total = $product['price'] * $quantity;

                // Create sale record
                $stmt = $pdo->prepare("INSERT INTO sales (total_amount) VALUES (?)");
                $stmt->execute([$total]);
                $saleId = $pdo->lastInsertId();

                // Create sale item
                $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$saleId, $product_id, $quantity, $product['price']]);

                // Deduct stock
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->execute([$quantity, $product_id]);

                $pdo->commit();
                $message = 'บันทึกการขายเรียบร้อย — ' . htmlspecialchars($product['name']) . ' x' . $quantity . ' = ฿' . number_format($total, 2);
                $messageType = 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } else {
        $message = 'กรุณาเลือกสินค้าและจำนวน';
        $messageType = 'danger';
    }
}

// Fetch products with stock > 0
$availableProducts = $pdo->query("SELECT id, name, price, stock_quantity FROM products WHERE stock_quantity > 0 ORDER BY name")->fetchAll();

// Fetch all sales history
$salesHistory = $pdo->query("
    SELECT s.id, s.sale_date, s.total_amount,
           GROUP_CONCAT(CONCAT(p.name, ' x', si.quantity) SEPARATOR ', ') AS items,
           SUM(si.quantity) AS total_items
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    LEFT JOIN products p ON si.product_id = p.id
    GROUP BY s.id
    ORDER BY s.sale_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales — Gaming Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h1>🎮 Gaming Store</h1>
            <span>Inventory System</span>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php"><span class="icon">📊</span> <span>Dashboard</span></a></li>
            <li><a href="products.php"><span class="icon">📦</span> <span>Products</span></a></li>
            <li><a href="sales.php" class="active"><span class="icon">💰</span> <span>Sales</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h2>การขาย</h2>
            <p>บันทึกการขายสินค้าและดูประวัติ</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $messageType === 'success' ? '✅' : '❌' ?> <?= $message ?>
        </div>
        <?php endif; ?>

        <!-- Sell Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3>🛒 ขายสินค้า</h3>
            </div>
            <div class="modal-body">
                <form method="POST" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="action" value="sell">
                    <div class="form-group" style="flex: 2; min-width: 200px; margin-bottom: 0;">
                        <label>เลือกสินค้า *</label>
                        <select name="product_id" class="form-control" required id="productSelect" onchange="updatePrice()">
                            <option value="">-- เลือกสินค้า --</option>
                            <?php foreach ($availableProducts as $p): ?>
                            <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-stock="<?= $p['stock_quantity'] ?>">
                                <?= htmlspecialchars($p['name']) ?> — ฿<?= number_format($p['price'], 2) ?> (เหลือ <?= $p['stock_quantity'] ?> ชิ้น)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 120px; margin-bottom: 0;">
                        <label>จำนวน *</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required id="quantityInput" onchange="updatePrice()">
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                        <label>ยอดรวม</label>
                        <div class="form-control" style="background: var(--bg-tertiary); font-weight: 700; color: var(--accent);" id="totalDisplay">฿0.00</div>
                    </div>
                    <div style="margin-bottom: 0;">
                        <button type="submit" class="btn btn-success" style="height: 44px;">💰 บันทึกการขาย</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sales History -->
        <div class="card">
            <div class="card-header">
                <h3>📋 ประวัติการขาย (<?= count($salesHistory) ?> รายการ)</h3>
            </div>
            <div class="table-wrapper">
                <?php if (count($salesHistory) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>วันที่</th>
                            <th>รายการ</th>
                            <th>จำนวน</th>
                            <th>ยอดรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salesHistory as $sale): ?>
                        <tr>
                            <td><?= $sale['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></td>
                            <td><?= htmlspecialchars($sale['items'] ?? '-') ?></td>
                            <td><span class="badge badge-accent"><?= $sale['total_items'] ?? 0 ?> ชิ้น</span></td>
                            <td class="price">฿<?= number_format($sale['total_amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="icon">💰</div>
                    <p>ยังไม่มีประวัติการขาย</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function updatePrice() {
            const select = document.getElementById('productSelect');
            const qty = parseInt(document.getElementById('quantityInput').value) || 0;
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option?.dataset?.price) || 0;
            const stock = parseInt(option?.dataset?.stock) || 0;
            const total = price * qty;

            document.getElementById('totalDisplay').textContent = '฿' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // Limit quantity to stock
            const qtyInput = document.getElementById('quantityInput');
            if (stock > 0) {
                qtyInput.max = stock;
            }
        }
    </script>

</body>
</html>
