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

if ($id < 1) {
  header("Location: ../../admin/kelola-peminjaman.php?error=ID tidak valid!");
  exit();
}

try {
  $connection->beginTransaction();
  
  // Get peminjaman data
  $sql = "SELECT * FROM peminjaman WHERE id = ? AND status = 'pending'";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  $peminjaman = $stmt->fetch();
  
  if (!$peminjaman) {
    $connection->rollBack();
    header("Location: ../../admin/kelola-peminjaman.php?error=Peminjaman tidak ditemukan atau sudah diproses!");
    exit();
  }
  
  // Update status peminjaman
  $sql = "UPDATE peminjaman SET status = 'approved', catatan_admin = ? WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$catatan ?: 'Peminjaman disetujui', $id]);
  
  // Update status anggota
  $sql = "UPDATE anggota SET status = 'Sedang Meminjam' WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$peminjaman['anggota_id']]);
  
  // Update status buku
  $sql = "UPDATE books SET status = 'Dipinjam' WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$peminjaman['book_id']]);
  
  $connection->commit();
  
  header("Location: ../../admin/kelola-peminjaman.php?success=Peminjaman berhasil disetujui!");
  exit();
} catch (PDOException $e) {
  $connection->rollBack();
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-peminjaman.php?error=Gagal menyetujui peminjaman!");
  exit();
}
