  Inventory Management System (Aplikasi Manajemen Inventaris)

Aplikasi Web Manajemen Inventaris berbasis **PHP Native** menggunakan arsitektur **MVC-Lite (Model-View-Controller)** dan database **MySQL** dengan driver **PDO (PHP Data Objects)**.

Aplikasi ini dirancang untuk memudahkan pencatatan barang, pengelolaan kategori, supplier, serta pemantauan arus stok masuk dan keluar secara *real-time*.

---

## 📐 Arsitektur & Struktur Folder Project

Proyek ini disusun rapi menggunakan pendekatan arsitektur **MVC-Lite** untuk memisahkan logika koneksi database, helper, dan antarmuka pengguna (*User Interface*):

inventory-app/
├── config/
│   └── database.php           # Konfigurasi koneksi database MySQL menggunakan PDO
├── database/
│   └── schema.sql             # Script SQL untuk membuat struktur tabel & sampel data
├── helpers/
│   ├── auth.php               # Helper untuk manajemen sesi login & autentikasi
│   ├── escape.php             # Helper sanitasi input (XSS Prevention)
│   └── validation.php         # Helper validasi input data
├── public/
│   ├── stock/
│   │   ├── in.php             # Halaman pencatatan stok masuk
│   │   └── out.php            # Halaman pencatatan stok keluar
│   ├── categories.php         # Kelola data kategori barang (CRUD)
│   ├── dashboard.php          # Halaman utama (Statistik & Notifikasi Stok Kritis)
│   ├── login.php              # Halaman masuk sistem
│   ├── logout.php             # Halaman keluar sistem
│   ├── product_add.php        # Form tambah produk baru
│   ├── product_delete.php     # Fungsi hapus data produk
│   ├── product_edit.php       # Form edit data produk
│   ├── products.php           # Daftar & tabel utama kelola produk
│   └── suppliers.php          # Kelola data supplier (CRUD)
└── README.md                  # Dokumentasi proyek

---

## ✨ Fitur Utama Aplikasi

1. **Dashboard Interaktif & Ringkasan Data:**
   - Menampilkan total statistik barang, kategori, supplier, dan transaksi stok.
   - **Alert Stok Kritis:** Peringatan otomatis untuk produk dengan jumlah stok <= 5.

2. **Manajemen Produk (CRUD Complete):**
   - Tambah, lihat, ubah, dan hapus data barang/produk.
   - Pencatatan SKU unik, nama barang, relasi kategori, harga satuan, dan jumlah stok.

3. **Manajemen Kategori & Supplier:**
   - Pengelompokan barang berdasarkan kategori (Alat Tulis Kantor, Kertas & Peralatan Cetak, Elektronik & Gadget, Aksesori Komputer, Perlengkapan Kebersihan, Peralatan Dapur & Pantry, Furnitur & Meja Kantor, Media Penyimpanan).
   - Pendataan supplier/pemasok barang secara lengkap.

4. **Sistem Arus Stok (Stock In & Stock Out):**
   - Pencatatan barang masuk (pembelian/penambahan stok).
   - Pencatatan barang keluar (penjualan/pengurangan stok).
   - Otomatis mengupdate jumlah stok di tabel produk.

5. **Keamanan & Autentikasi:**
   - Proteksi halaman menggunakan Session Management (PHP Session).
   - Menggunakan Prepared Statements (PDO) untuk mencegah **SQL Injection**.
   - Sanitasi input untuk mencegah celah keamanan **XSS (Cross-Site Scripting)**.

---

## 🗄️ Skema Database (MySQL)

Database menggunakan nama **`inventory-app`** (atau nama yang disesuaikan pada `config/database.php`).

CREATE DATABASE IF NOT EXISTS `inventory-app`;
USE `inventory-app`;

-- Tabel Kategori
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
);

-- Tabel Supplier
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `address` TEXT
);

-- Tabel Produk
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(50) UNIQUE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `category_id` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT NOT NULL DEFAULT 0,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
);

-- Tabel Stok Masuk & Keluar
CREATE TABLE IF NOT EXISTS `stock_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `type` ENUM('in', 'out') NOT NULL,
  `quantity` INT NOT NULL,
  `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
);

---

## 🚀 Panduan Instalasi & Jalankan di Lokal (XAMPP)

1. **Persiapan Lingkungan Development:**
   - Pastikan aplikasi **XAMPP** sudah terinstal di komputer/laptop.
   - Jalankan modul **Apache** dan **MySQL** dari XAMPP Control Panel.

2. **Salin Folder Proyek:**
   - Tempatkan folder `inventory-app` ke dalam direktori `htdocs`:
     `C:\xampp\htdocs\inventory-app`

3. **Konfigurasi Database:**
   - Buka browser dan akses **phpMyAdmin** di `http://localhost/phpmyadmin/`.
   - Buat database baru dengan nama `inventory-app`.
   - Impor/jalankan query SQL dari file `database/schema.sql` atau eksekusi query pembuatan tabel di atas.

4. **Akses Aplikasi:**
   - Buka browser dan jalankan link berikut:
     - **Dashboard:** `http://localhost/inventory-app/public/dashboard.php`
     - **Tambah Produk:** `http://localhost/inventory-app/public/product_add.php`
     - **Daftar Produk:** `http://localhost/inventory-app/public/products.php`

---

## 🛠️ Teknologi yang Digunakan

* **Language:** PHP 8.x
* **Database:** MySQL / MariaDB (Driver PDO)
* **Web Server:** Apache (via XAMPP)
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap / Modern Clean UI
* **Development Tools:** Visual Studio Code, phpMyAdmin