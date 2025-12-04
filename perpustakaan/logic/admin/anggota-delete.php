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

if ($id < 1) {
  header("Location: ../../admin/kelola-anggota.php?error=ID tidak valid!");
  exit();
}

try {
  // Cek apakah anggota sedang meminjam
  $sql = "SELECT COUNT(*) as total FROM peminjaman WHERE anggota_id = ? AND status IN ('pending', 'approved')";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  $count = $stmt->fetch()['total'];
  
  if ($count > 0) {
    header("Location: ../../admin/kelola-anggota.php?error=Anggota tidak bisa dihapus karena masih memiliki peminjaman aktif!");
    exit();
  }
  
  $sql = "DELETE FROM anggota WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  
  header("Location: ../../admin/kelola-anggota.php?success=Anggota berhasil dihapus!");
  exit();
} catch (PDOException $e) {
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-anggota.php?error=Gagal menghapus anggota!");
  exit();
}