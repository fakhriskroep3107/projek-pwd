<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../auth/login.php");
  exit();
}

require_once __DIR__ . '/../../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../admin/kelola-anggota.php");
  exit();
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$gender = $_POST['gender'] ?? '';
$no_telepon = trim($_POST['no_telepon'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

// Validasi
if (empty($nama) || empty($email) || empty($password) || empty($gender) || empty($alamat)) {
  header("Location: ../../admin/kelola-anggota-add.php?error=Semua field wajib diisi!");
  exit();
}

if (strlen($password) < 6) {
  header("Location: ../../admin/kelola-anggota-add.php?error=Password minimal 6 karakter!");
  exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: ../../admin/kelola-anggota-add.php?error=Email tidak valid!");
  exit();
}

try {
  // Cek email sudah ada atau belum
  $sql = "SELECT id FROM anggota WHERE email = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    header("Location: ../../admin/kelola-anggota-add.php?error=Email sudah terdaftar!");
    exit();
  }

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  
  $sql = "INSERT INTO anggota (nama, email, password, gender, no_telepon, alamat, status) 
          VALUES (?, ?, ?, ?, ?, ?, 'Tidak Meminjam')";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$nama, $email, $hashed_password, $gender, $no_telepon, $alamat]);
  
  header("Location: ../../admin/kelola-anggota.php?success=Anggota berhasil ditambahkan!");
  exit();
} catch (PDOException $e) {
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-anggota-add.php?error=Gagal menambahkan anggota!");
  exit();
}
