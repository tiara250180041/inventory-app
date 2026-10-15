<?php
// public/stock/in.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_login();

$error = '';
$success = '';

// Ambil daftar produk untuk pilihan select dropdown
$stmt_prod = $pdo->query("SELECT id, name, sku, stock FROM products ORDER BY name ASC");
$products = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = trim($_POST['product_id'] ?? '');
    $quantity   = trim($_POST['quantity'] ?? '');
    $notes      = sanitize_input($_POST['notes'] ?? '');
    $user_id    = $_SESSION['user_id'] ?? 1;

    // Validasi input
    if (!validate_required($product_id) || !validate_positive_number($quantity)) {
        $error = "Pilih produk dan masukkan jumlah barang masuk yang valid!";
    } else {
        try {
            // Mulai Database Transaction
            $pdo->beginTransaction();

            // 1. Cek produk eksis dengan row locking
            $stmt_check = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
            $stmt_check->execute([$product_id]);
            $product = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Produk tidak ditemukan.");
            }

            $current_stock = (int)$product['stock'];
            $qty_in = (int)$quantity;

            // 2. Tambah stok di tabel products
            $new_stock = $current_stock + $qty_in;
            $stmt_update = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt_update->execute([$new_stock, $product_id]);

            // 3. Catat ke tabel histori transaksi (Opsional jika tabel sudah ada)
            // $stmt_trans = $pdo->prepare("INSERT INTO stock_transactions (product_id, type, quantity, notes, user_id, created_at) VALUES (?, 'IN', ?, ?, ?, NOW())");
            // $stmt_trans->execute([$product_id, $qty_in, $notes, $user_id]);

            // Commit transaksi
            $pdo->commit();
            $success = "Transaksi barang masuk berhasil disimpan! Stok bertambah.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal memproses transaksi: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Barang Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Form Barang Masuk (Stock In)</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= e($error); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= e($success); ?></div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Pilih Produk</label>
                                <select name="product_id" id="product_id" class="form-control" required>
                                    <option value="">-- Pilih Produk & Cek Stok --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id']; ?>">
                                            <?= e($p['name']); ?> (SKU: <?= e($p['sku']); ?>) - Stok: <?= $p['stock']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantity" class="form-label">Jumlah Masuk (Tambah Stok)</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Keterangan / Sumber Pasokan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100">Simpan Barang Masuk</button>
                            <a href="../dashboard.php" class="btn btn-secondary w-100 mt-2">Kembali ke Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>