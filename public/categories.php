<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';

check_auth();

// Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: categories.php");
        exit;
    }
}

// Ambil semua data kategori
$categories = $db->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-secondary mb-3">&larr; Kembali ke Dashboard</a>
        <h2>Kelola Kategori Produk</h2>

        <div class="card p-4 shadow-sm mb-4">
            <form method="POST" class="row g-3">
                <div class="col-auto">
                    <input type="text" name="name" class="form-control" placeholder="Nama Kategori Baru" required>
                </div>
                <div class="col-auto">
                    <button type="submit" name="add_category" class="btn btn-primary">Tambah Kategori</button>
                </div>
            </form>
        </div>

        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= e($cat['name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>z