<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../auth/login.php");
  exit();
}

require_once __DIR__ . '/../../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../admin/kelola-peminjaman.php");
  exit();
}

$id = intval($_POST['id'] ?? 0);
$catatan = trim($_POST['catatan'] ?? '');

if ($id < 1 || empty($catatan)) {
  header("Location: ../../admin/kelola-peminjaman.php?error=Alasan penolakan wajib diisi!");
  exit();
}

try {
  // Check peminjaman exists and pending
  $sql = "SELECT * FROM peminjaman WHERE id = ? AND status = 'pending'";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  $peminjaman = $stmt->fetch();
  
  if (!$peminjaman) {
    header("Location: ../../admin/kelola-peminjaman.php?error=Peminjaman tidak ditemukan atau sudah diproses!");
    exit();
  }
  
  // Update status peminjaman to rejected
  $sql = "UPDATE peminjaman SET status = 'rejected', catatan_admin = ? WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$catatan, $id]);
  
  header("Location: ../../admin/kelola-peminjaman.php?success=Peminjaman berhasil ditolak!");
  exit();
} catch (PDOException $e) {
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-peminjaman.php?error=Gagal menolak peminjaman!");
  exit();
}
