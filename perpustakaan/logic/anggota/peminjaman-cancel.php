<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'anggota') {
  header("Location: ../../auth/login.php");
  exit();
}

require_once __DIR__ . '/../../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../anggota/peminjaman.php");
  exit();
}

$anggota_id = $_SESSION['user_id'];
$id = intval($_POST['id'] ?? 0);

if ($id < 1) {
  header("Location: ../../anggota/peminjaman.php?error=ID tidak valid!");
  exit();
}

try {
  // Cek apakah peminjaman milik anggota ini dan status pending
  $sql = "SELECT * FROM peminjaman WHERE id = ? AND anggota_id = ? AND status = 'pending'";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id, $anggota_id]);
  $peminjaman = $stmt->fetch();
  
  if (!$peminjaman) {
    header("Location: ../../anggota/peminjaman.php?error=Peminjaman tidak ditemukan atau tidak bisa dibatalkan!");
    exit();
  }
  
  // Delete peminjaman
  $sql = "DELETE FROM peminjaman WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  
  header("Location: ../../anggota/peminjaman.php?success=Pengajuan peminjaman berhasil dibatalkan!");
  exit();
  
} catch (PDOException $e) {
  error_log("Cancel error: " . $e->getMessage());
  header("Location: ../../anggota/peminjaman.php?error=Gagal membatalkan peminjaman!");
  exit();
}