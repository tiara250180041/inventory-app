<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fitur Pencarian
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? OR p.sku LIKE ? ORDER BY p.id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
}
$products = $stmt->fetchAll();

// Hitung Statistik
$statsStmt = $pdo->query("SELECT COUNT(*) as total_jenis, SUM(stock) as total_stok, SUM(price * stock) as total_aset FROM products");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Sistem Inventory Barang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #fdf2f8; min-height: 100vh; background-image: radial-gradient(at 10% 10%, rgba(244, 114, 182, 0.15) 0px, transparent 50%), radial-gradient(at 90% 90%, rgba(192, 132, 252, 0.15) 0px, transparent 50%); padding: 30px; }
        
        .container { max-width: 95%; margin: 0 auto; }
        
        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 20px 30px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1); margin-bottom: 30px; }
        .nav-brand { font-size: 18px; font-weight: 700; color: #db2777; display: flex; align-items: center; gap: 8px; }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { text-decoration: none; color: #4b5563; font-weight: 600; font-size: 14px; transition: color 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: #db2777; }
        .logout-btn { background: #fee2e2; color: #ef4444; padding: 8px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; text-decoration: none; transition: all 0.2s; }
        .logout-btn:hover { background: #ef4444; color: white; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1); }
        .stat-card h3 { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 8px; }
        .stat-card .value { font-size: 20px; font-weight: 700; color: #db2777; }

        /* Main Card */
        .card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        h2 { color: #374151; font-size: 20px; font-weight: 700; }
        
        /* Search & Button */
        .search-add { display: flex; gap: 10px; align-items: center; }
        .search-form { display: flex; gap: 8px; align-items: center; }
        input[type="text"] { padding: 10px 14px; border: 1px solid #fbcfe8; border-radius: 12px; font-size: 13px; outline: none; background: #fff; color: #374151; width: 220px; }
        input[type="text"]:focus { border-color: #db2777; box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.1); }
        
        .btn-search { background: #fce7f3; color: #db2777; border: none; padding: 10px 16px; border-radius: 12px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-search:hover { background: #fbcfe8; }
        .btn-search svg { width: 15px; height: 15px; stroke: #0284c7; }

        .btn-add { background: linear-gradient(135deg, #f472b6 0%, #db2777 100%); color: white; text-decoration: none; padding: 10px 18px; border-radius: 12px; font-weight: 600; font-size: 13px; box-shadow: 0 4px 15px rgba(219, 39, 119, 0.25); transition: all 0.2s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(219, 39, 119, 0.4); }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px 16px; text-align: left; font-size: 14px; }
        th { background: rgba(244, 114, 182, 0.1); color: #4b5563; font-weight: 600; border-bottom: 2px solid rgba(244, 114, 182, 0.2); }
        tr:not(:last-child) td { border-bottom: 1px solid rgba(244, 114, 182, 0.1); }
        td { color: #374151; }
        .badge { background: #fce7f3; color: #db2777; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-block; }
        
        /* Badge Status Stok Kritis Terpisah di Kolom Samping */
        .badge-warning { background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-block; }
        .badge-safe { color: #9ca3af; font-size: 13px; font-weight: 500; }
        
        /* Action Buttons */
        .action-btns { display: flex; gap: 8px; }
        .btn-edit { background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-edit:hover { background: #bae6fd; }
        .btn-delete { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-delete:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 30px; color: #6b7280; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <div class="navbar">
            <div class="nav-brand">🌸 Sistem Inventory Barang</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="products.php" class="active">Kelola Produk</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Statistik Ringkas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Jenis Produk</h3>
                <div class="value"><?= number_format($stats['total_jenis'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <h3>Akumulasi Stok</h3>
                <div class="value"><?= number_format($stats['total_stok'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <h3>Estimasi Nilai Aset</h3>
                <div class="value">Rp <?= number_format($stats['total_aset'] ?? 0, 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Main Card & Table -->
        <div class="card">
            <div class="card-header">
                <h2>Daftar Inventaris Barang</h2>
                <div class="search-add">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Cari nama atau SKU..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            Cari
                        </button>
                    </form>
                    <a href="product_add.php" class="btn-add">+ Tambah Produk Baru</a>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Satuan</th>
                        <th>Stok</th>
                        <th>Status Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $row): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($row['sku']) ?></b></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><span class="badge"><?= htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori') ?></span></td>
                                <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                                <td><b><?= number_format($row['stock']) ?></b></td>
                                <td>
                                    <?php if ($row['stock'] <= 5): ?>
                                        <span class="badge-warning">🚨 Stok Kritis</span>
                                    <?php else: ?>
                                        <span class="badge-safe">Aman</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="product_edit.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                        <a href="product_delete.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">Belum ada data produk yang terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>