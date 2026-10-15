<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/database.php';
check_login();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: products.php");
    exit;
}

// Ambil data produk yang mau diedit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

// Ambil data kategori untuk dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;

    if (empty($sku) || empty($name) || empty($price) || empty($stock)) {
        $error = 'Semua kolom wajib diisi dengan benar!';
    } else {
        $stmt = $pdo->prepare("UPDATE products SET sku = ?, name = ?, category_id = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->execute([$sku, $name, $category_id, $price, $stock, $id]);
        
        header("Location: products.php?msg=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Sistem Inventory Barang</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #fdf2f8;
            color: #4a5568;
            min-height: 100vh;
            padding: 30px;
            background-image: 
                radial-gradient(at 10% 10%, rgba(244, 114, 182, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(192, 132, 252, 0.15) 0px, transparent 50%);
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        /* Navbar Header */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(244, 114, 182, 0.25);
            padding: 18px 30px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(244, 114, 182, 0.1);
            margin-bottom: 30px;
        }

        .navbar h1 {
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #db2777 0%, #9333ea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: #db2777;
            font-weight: 600;
        }

        /* Main Form Card */
        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(244, 114, 182, 0.25);
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(244, 114, 182, 0.1);
        }

        .card-header {
            margin-bottom: 25px;
            border-bottom: 2px solid #fce7f3;
            padding-bottom: 15px;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #374151;
        }

        /* Error Alert */
        .alert-error {
            background-color: #ffe4e6;
            color: #e11d48;
            border: 1px solid #fecdd3;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 8px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #fbcfe8;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            background: #fff;
            color: #374151;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus, .form-select:focus {
            border-color: #db2777;
            box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.1);
        }

        /* Buttons Group */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #f472b6 0%, #db2777 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(219, 39, 119, 0.25);
            transition: all 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(219, 39, 119, 0.4);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #334151;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Top Navbar -->
        <div class="navbar">
            <h1>📦 Sistem Inventory Barang</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="products.php">Kelola Produk</a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <h2>✏️ Edit Data Produk</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="product_edit.php?id=<?= $product['id'] ?>" method="POST">
                <div class="form-group">
                    <label class="form-label">Kode SKU / Barcode</label>
                    <input type="text" name="sku" class="form-input" required value="<?= htmlspecialchars($product['sku']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="name" class="form-input" required value="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Harga Satuan (Rp)</label>
                    <input type="number" name="price" class="form-input" required value="<?= htmlspecialchars($product['price']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Jumlah Stok</label>
                    <input type="number" name="stock" class="form-input" required value="<?= htmlspecialchars($product['stock']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">💾 Perbarui Produk</button>
                    <a href="products.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>