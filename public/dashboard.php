<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil Statistik Utama
$statsStmt = $pdo->query("SELECT COUNT(*) as total_jenis, SUM(stock) as total_stok, SUM(price * stock) as total_aset FROM products");
$stats = $statsStmt->fetch();

// Ambil Produk dengan Stok Kritis (Batas stok <= 5)
$criticalLimit = 5;
$criticalStmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock <= ? ORDER BY p.stock ASC");
$criticalStmt->execute([$criticalLimit]);
$criticalProducts = $criticalStmt->fetchAll();
$totalCritical = count($criticalProducts);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventory Barang</title>
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

        /* Stats Grid (4 Kolom) */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1); }
        .stat-card h3 { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 8px; }
        .stat-card .value { font-size: 22px; font-weight: 700; color: #db2777; }
        .stat-card.alert .value { color: #ef4444; }

        /* Card Section */
        .card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1); }
        h2 { color: #374151; font-size: 20px; font-weight: 700; margin-bottom: 20px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px 16px; text-align: left; font-size: 14px; }
        th { background: rgba(239, 68, 68, 0.1); color: #991b1b; font-weight: 600; border-bottom: 2px solid rgba(239, 68, 68, 0.2); }
        tr:not(:last-child) td { border-bottom: 1px solid rgba(244, 114, 182, 0.1); }
        td { color: #374151; }
        .badge-alert { background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; display: inline-block; }
        .empty-state { text-align: center; padding: 30px; color: #059669; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <div class="navbar">
            <div class="nav-brand">🌸 Sistem Inventory Barang</div>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="products.php">Kelola Produk</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Statistik Grid -->
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
            <div class="stat-card alert">
                <h3>Stok Kritis (&le; 5)</h3>
                <div class="value"><?= number_format($totalCritical) ?> Barang</div>
            </div>
        </div>

        <!-- Tabel Peringatan Stok Kritis -->
        <div class="card">
            <h2>🚨 Peringatan Stok Barang Menipis (Kritis)</h2>
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Sisa Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($totalCritical > 0): ?>
                        <?php foreach ($criticalProducts as $row): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($row['sku']) ?></b></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori') ?></td>
                                <td><b><?= number_format($row['stock']) ?></b></td>
                                <td><span class="badge-alert">Stok Menipis</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">🎉 Aman! Belum ada produk yang mencapai batas stok kritis.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>