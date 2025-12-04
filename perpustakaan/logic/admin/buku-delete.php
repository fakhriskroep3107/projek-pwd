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

$id = intval($_POST['id'] ?? 0);

if ($id < 1) {
  header("Location: ../../admin/kelola-buku.php?error=ID tidak valid!");
  exit();
}

try {
  // Cek apakah buku sedang dipinjam
  $sql = "SELECT COUNT(*) as total FROM peminjaman WHERE book_id = ? AND status IN ('pending', 'approved')";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  $count = $stmt->fetch()['total'];
  
  if ($count > 0) {
    header("Location: ../../admin/kelola-buku.php?error=Buku tidak bisa dihapus karena sedang dalam peminjaman!");
    exit();
  }
  
  $sql = "DELETE FROM books WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  
  header("Location: ../../admin/kelola-buku.php?success=Buku berhasil dihapus!");
  exit();
} catch (PDOException $e) {
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-buku.php?error=Gagal menghapus buku!");
  exit();
}
