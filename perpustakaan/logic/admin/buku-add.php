<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../auth/login.php");
  exit();
}

require_once __DIR__ . '/../../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../admin/kelola-buku.php");
  exit();
}

$judul = trim($_POST['judul'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$penerbit = trim($_POST['penerbit'] ?? '');
$tahun_terbit = $_POST['tahun_terbit'] ?? null;
$isbn = trim($_POST['isbn'] ?? '');
$jumlah_stok = intval($_POST['jumlah_stok'] ?? 1);
$status = $_POST['status'] ?? 'Tersedia';

// Validasi
if (empty($judul) || empty($kategori) || empty($pengarang) || empty($penerbit)) {
  header("Location: ../../admin/kelola-buku-add.php?error=Semua field wajib diisi!");
  exit();
}

if ($jumlah_stok < 1) {
  header("Location: ../../admin/kelola-buku-add.php?error=Stok minimal 1!");
  exit();
}

// Handle Upload Cover
$cover_name = 'default-cover.jpg';

if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
  $file = $_FILES['cover'];
  $file_name = $file['name'];
  $file_tmp = $file['tmp_name'];
  $file_size = $file['size'];
  $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
  
  // Validasi ekstensi
  $allowed_ext = ['jpg', 'jpeg', 'png'];
  if (!in_array($file_ext, $allowed_ext)) {
    header("Location: ../../admin/kelola-buku-add.php?error=Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.");
    exit();
  }
  
  // Validasi ukuran (max 2MB)
  if ($file_size > 2 * 1024 * 1024) {
    header("Location: ../../admin/kelola-buku-add.php?error=Ukuran file terlalu besar! Maksimal 2MB.");
    exit();
  }
  
  // Generate nama file unik
  $cover_name = 'cover_' . time() . '_' . uniqid() . '.' . $file_ext;
  
  // Path folder cover
  $upload_dir = __DIR__ . '/../../cover/';
  
  // Buat folder jika belum ada
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
  }
  
  // Upload file
  if (!move_uploaded_file($file_tmp, $upload_dir . $cover_name)) {
    header("Location: ../../admin/kelola-buku-add.php?error=Gagal mengupload cover!");
    exit();
  }
}

try {
  $sql = "INSERT INTO books (judul, kategori, pengarang, penerbit, tahun_terbit, isbn, jumlah_stok, status, cover) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$judul, $kategori, $pengarang, $penerbit, $tahun_terbit, $isbn, $jumlah_stok, $status, $cover_name]);
  
  header("Location: ../../admin/kelola-buku.php?success=Buku berhasil ditambahkan!");
  exit();
} catch (PDOException $e) {
  // Hapus file cover jika gagal insert database
  if ($cover_name !== 'default-cover.jpg' && file_exists($upload_dir . $cover_name)) {
    unlink($upload_dir . $cover_name);
  }
  
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-buku-add.php?error=Gagal menambahkan buku!");
  exit();
}