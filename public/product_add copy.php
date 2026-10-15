<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil kategori untuk pilihan dropdown
$stmtCat = $pdo->query("SELECT * FROM categories");
$categories = $stmtCat->fetchAll();

// Logika otomatis mencari SKU urut berikutnya yang unik
$nextSku = 'ATK 001';
try {
    $stmtMax = $pdo->query("SELECT sku FROM products ORDER BY id DESC LIMIT 1");
    $lastProduct = $stmtMax->fetch();
    if ($lastProduct && preg_match('/^(.*?)(\d+)$/', $lastProduct['sku'], $matches)) {
        $prefix = $matches[1];
        $num = intval($matches[2]) + 1;
        $nextSku = $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
        
        // Cek apakah SKU tersebut sudah ada di database, jika ada terus dinaikkan
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        do {
            $stmtCheck->execute([$nextSku]);
            if ($stmtCheck->fetchColumn() > 0) {
                $num++;
                $nextSku = $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
            } else {
                break;
            }
        } while (true);
    }
} catch (Exception $e) {
    // Default jika tabel kosong
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $category_id = trim($_POST['category_id']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);

    if (empty($sku) || empty($name) || empty($category_id) || empty($price) || empty($stock)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (sku, name, category_id, price, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sku, $name, $category_id, $price, $stock]);
            header("Location: products.php");
            exit;
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "SKU '$sku' sudah terdaftar, gunakan SKU lain!";
            } else {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Sistem Inventory Barang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: #fdf2f8; min-height: 100vh; background-image: radial-gradient(at 10% 10%, rgba(244, 114, 182, 0.15) 0px, transparent 50%), radial-gradient(at 90% 90%, rgba(192, 132, 252, 0.15) 0px, transparent 50%); padding: 30px; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(244, 114, 182, 0.25); padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1); }
        h2 { color: #374151; font-size: 22px; font-weight: 700; margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #4b5563; font-size: 14px; font-weight: 600; }
        input, select { width: 100%; padding: 12px 16px; border: 1px solid #fbcfe8; border-radius: 12px; font-size: 14px; outline: none; background: #fff; color: #374151; }
        input:focus, select:focus { border-color: #db2777; box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.1); }
        .btn-group { display: flex; gap: 15px; margin-top: 30px; }
        button { flex: 1; background: linear-gradient(135deg, #f472b6 0%, #db2777 100%); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 4px 15px rgba(219, 39, 119, 0.25); transition: all 0.2s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(219, 39, 119, 0.4); }
        .btn-back { background: #f3f4f6; color: #4b5563; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-weight: 600; font-size: 14px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); }
        .btn-back:hover { background: #e5e7eb; }
        .error { background-color: #ffe4e6; color: #e11d48; border: 1px solid #fecdd3; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>🌸 Tambah Produk Baru</h2>
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>SKU (Kode Barang)</label>
                    <input type="text" name="sku" value="<?= htmlspecialchars($nextSku) ?>" required>
                </div>
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga Satuan (Rp)</label>
                    <input type="number" name="price" required min="0">
                </div>
                <div class="form-group">
                    <label>Jumlah Stok</label>
                    <input type="number" name="stock" required min="0">
                </div>
                <div class="btn-group">
                    <a href="products.php" class="btn-back">Batal</a>
                    <button type="submit">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>