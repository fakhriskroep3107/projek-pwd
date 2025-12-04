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

$id = intval($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$gender = $_POST['gender'] ?? '';
$no_telepon = trim($_POST['no_telepon'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$status = $_POST['status'] ?? 'Tidak Meminjam';

// Validasi
if ($id < 1 || empty($nama) || empty($email) || empty($gender) || empty($alamat)) {
  header("Location: ../../admin/kelola-anggota-edit.php?id=$id&error=Semua field wajib diisi!");
  exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: ../../admin/kelola-anggota-edit.php?id=$id&error=Email tidak valid!");
  exit();
}

if (!empty($password) && strlen($password) < 6) {
  header("Location: ../../admin/kelola-anggota-edit.php?id=$id&error=Password minimal 6 karakter!");
  exit();
}

try {
  // Cek email duplikat (kecuali email sendiri)
  $sql = "SELECT id FROM anggota WHERE email = ? AND id != ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$email, $id]);
  if ($stmt->fetch()) {
    header("Location: ../../admin/kelola-anggota-edit.php?id=$id&error=Email sudah digunakan anggota lain!");
    exit();
  }

  // Update dengan atau tanpa password
  if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE anggota SET nama=?, email=?, password=?, gender=?, no_telepon=?, alamat=?, status=? WHERE id=?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$nama, $email, $hashed_password, $gender, $no_telepon, $alamat, $status, $id]);
  } else {
    $sql = "UPDATE anggota SET nama=?, email=?, gender=?, no_telepon=?, alamat=?, status=? WHERE id=?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$nama, $email, $gender, $no_telepon, $alamat, $status, $id]);
  }
  
  header("Location: ../../admin/kelola-anggota.php?success=Anggota berhasil diupdate!");
  exit();
} catch (PDOException $e) {
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-anggota-edit.php?id=$id&error=Gagal mengupdate anggota!");
  exit();
}

