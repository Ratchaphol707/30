<?php
require_once 'db.php';

$message = '';
$messageType = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'ลบสินค้าเรียบร้อยแล้ว';
    $messageType = 'danger';
}

// Handle ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name && $category_id > 0 && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, stock_quantity, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category_id, $price, $stock, $description]);
        $message = 'เพิ่มสินค้าเรียบร้อยแล้ว';
        $messageType = 'success';
    } else {
        $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        $messageType = 'danger';
    }
}

// Handle EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($id > 0 && $name && $category_id > 0 && $price > 0) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, price=?, stock_quantity=?, description=? WHERE id=?");
        $stmt->execute([$name, $category_id, $price, $stock, $description, $id]);
        $message = 'แก้ไขสินค้าเรียบร้อยแล้ว';
        $messageType = 'success';
    } else {
        $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        $messageType = 'danger';
    }
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Fetch products with category names
$products = $pdo->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — Gaming Store</title>
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
            <li><a href="products.php" class="active"><span class="icon">📦</span> <span>Products</span></a></li>
            <li><a href="sales.php"><span class="icon">💰</span> <span>Sales</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h2>จัดการสินค้า</h2>
            <p>เพิ่ม แก้ไข ลบ สินค้าในร้าน</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $messageType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                <h3>📦 รายการสินค้า (<?= count($products) ?>)</h3>
                <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่มสินค้า</button>
            </div>
            <div class="table-wrapper">
                <?php if (count($products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อสินค้า</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา</th>
                            <th>Stock</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                            <td><span class="badge badge-accent"><?= htmlspecialchars($p['category_name']) ?></span></td>
                            <td class="price">฿<?= number_format($p['price'], 2) ?></td>
                            <td>
                                <span class="<?= $p['stock_quantity'] < 5 ? 'stock-low' : 'stock-ok' ?>">
                                    <?= $p['stock_quantity'] ?> ชิ้น
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm" onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">✏️ แก้ไข</button>
                                <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบสินค้านี้?')">🗑️ ลบ</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📦</div>
                    <p>ยังไม่มีสินค้า — กดปุ่ม "เพิ่มสินค้า" เพื่อเริ่มต้น</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h3>➕ เพิ่มสินค้าใหม่</h3>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label>ชื่อสินค้า *</label>
                        <input type="text" name="name" class="form-control" required placeholder="เช่น RTX 4090">
                    </div>
                    <div class="form-group">
                        <label>หมวดหมู่ *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ราคา (฿) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>จำนวน Stock</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>รายละเอียด</label>
                        <textarea name="description" class="form-control" placeholder="รายละเอียดเพิ่มเติม..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">💾 บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h3>✏️ แก้ไขสินค้า</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>ชื่อสินค้า *</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>หมวดหมู่ *</label>
                        <select name="category_id" id="edit-category" class="form-control" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ราคา (฿) *</label>
                        <input type="number" name="price" id="edit-price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>จำนวน Stock</label>
                        <input type="number" name="stock_quantity" id="edit-stock" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <label>รายละเอียด</label>
                        <textarea name="description" id="edit-description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeModal('editModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">💾 บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        function openEditModal(product) {
            document.getElementById('edit-id').value = product.id;
            document.getElementById('edit-name').value = product.name;
            document.getElementById('edit-category').value = product.category_id;
            document.getElementById('edit-price').value = product.price;
            document.getElementById('edit-stock').value = product.stock_quantity;
            document.getElementById('edit-description').value = product.description || '';
            openModal('editModal');
        }
        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });
    </script>

</body>
</html>
