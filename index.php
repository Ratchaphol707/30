<?php
require_once 'auth.php';
require_once 'db.php';

// Query stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 5")->fetchColumn();
$totalSales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales")->fetchColumn();

// Recent 5 sales
$recentSales = $pdo->query("
    SELECT s.id, s.sale_date, s.total_amount,
           GROUP_CONCAT(p.name SEPARATOR ', ') AS products,
           SUM(si.quantity) AS total_items
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    LEFT JOIN products p ON si.product_id = p.id
    GROUP BY s.id
    ORDER BY s.sale_date DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NexGen Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h1>⚡ NEXGEN STORE</h1>
            <span>Inventory System</span>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php" class="active"><span class="icon">📊</span> <span>Dashboard</span></a></li>
            <li><a href="products.php"><span class="icon">📦</span> <span>Products</span></a></li>
            <li><a href="sales.php"><span class="icon">💰</span> <span>Sales</span></a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php">🚪 Logout (<?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?>)</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h2>Dashboard</h2>
            <p>ภาพรวมร้านค้าอุปกรณ์เกมมิ่ง</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card accent">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?= number_format($totalProducts) ?></div>
                <div class="stat-label">สินค้าทั้งหมด</div>
            </div>
            <div class="stat-card <?= $lowStock > 0 ? 'danger' : 'success' ?>">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?= number_format($lowStock) ?></div>
                <div class="stat-label">สินค้า Stock ต่ำ (&lt;5)</div>
            </div>
            <div class="stat-card cyan">
                <div class="stat-icon">💰</div>
                <div class="stat-value">฿<?= number_format($totalSales, 2) ?></div>
                <div class="stat-label">ยอดขายรวม</div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="card">
            <div class="card-header">
                <h3>🧾 ยอดขายล่าสุด</h3>
                <a href="sales.php" class="btn btn-primary btn-sm">ดูทั้งหมด →</a>
            </div>
            <div class="table-wrapper">
                <?php if (count($recentSales) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>วันที่</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ยอดรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><?= $sale['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></td>
                            <td><?= htmlspecialchars($sale['products'] ?? '-') ?></td>
                            <td><span class="badge badge-accent"><?= $sale['total_items'] ?? 0 ?> ชิ้น</span></td>
                            <td class="price">฿<?= number_format($sale['total_amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="icon">🛒</div>
                    <p>ยังไม่มีข้อมูลการขาย</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
