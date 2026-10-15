<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';

check_auth();

// Tambah Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $address]);
        header("Location: suppliers.php");
        exit;
    }
}

// Ambil semua data supplier
$suppliers = $db->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Supplier - Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Kembali ke Dashboard</a>
        <h2>Kelola Data Supplier</h2>

        <!-- Form Tambah Supplier -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="mb-3">Tambah Supplier Baru</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" placeholder="Nama Supplier" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="phone" class="form-control" placeholder="No. Telepon">
                </div>
                <div class="col-md-4">
                    <input type="text" name="address" class="form-control" placeholder="Alamat">
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_supplier" class="btn btn-primary w-100">Simpan</button>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Supplier -->
        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Supplier</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($suppliers as $sup): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= e($sup['name']) ?></td>
                    <td><?= e($sup['phone']) ?></td>
                    <td><?= e($sup['address']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>