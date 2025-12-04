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

if ($id < 1) {
  header("Location: ../../admin/kelola-peminjaman.php?error=ID tidak valid!");
  exit();
}

try {
  $connection->beginTransaction();
  
  // Get peminjaman data
  $sql = "SELECT * FROM peminjaman WHERE id = ? AND status = 'approved'";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  $peminjaman = $stmt->fetch();
  
  if (!$peminjaman) {
    $connection->rollBack();
    header("Location: ../../admin/kelola-peminjaman.php?error=Peminjaman tidak ditemukan atau sudah dikembalikan!");
    exit();
  }
  
  // Hitung denda jika terlambat
  $tgl_kembali = new DateTime($peminjaman['tgl_kembali']);
  $tgl_dikembalikan = new DateTime();
  $selisih = $tgl_dikembalikan->diff($tgl_kembali);
  $denda = 0;
  $keterangan = 'Dikembalikan tepat waktu';
  
  if ($tgl_dikembalikan > $tgl_kembali) {
    $hari_terlambat = $selisih->days;
    $denda = $hari_terlambat * 1000; // Rp 1000 per hari
    $keterangan = "Terlambat $hari_terlambat hari. Denda: Rp " . number_format($denda, 0, ',', '.');
  }
  
  // Insert ke tabel pengembalian
  $sql = "INSERT INTO pengembalian (peminjaman_id, anggota_id, book_id, tgl_pinjam, tgl_kembali, tgl_dikembalikan, denda, keterangan)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $connection->prepare($sql);
  $stmt->execute([
    $peminjaman['id'],
    $peminjaman['anggota_id'],
    $peminjaman['book_id'],
    $peminjaman['tgl_pinjam'],
    $peminjaman['tgl_kembali'],
    $tgl_dikembalikan->format('Y-m-d'),
    $denda,
    $keterangan
  ]);
  
  // Update status peminjaman to returned
  $sql = "UPDATE peminjaman SET status = 'returned' WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$id]);
  
  // Update status anggota ke Tidak Meminjam
  $sql = "UPDATE anggota SET status = 'Tidak Meminjam' WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$peminjaman['anggota_id']]);
  
  // Update status buku ke Tersedia
  $sql = "UPDATE books SET status = 'Tersedia' WHERE id = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$peminjaman['book_id']]);
  
  $connection->commit();
  
  $msg = "Buku berhasil dikembalikan!";
  if ($denda > 0) {
    $msg .= " Denda: Rp " . number_format($denda, 0, ',', '.');
  }
  
  header("Location: ../../admin/kelola-peminjaman.php?success=" . urlencode($msg));
  exit();
} catch (PDOException $e) {
  $connection->rollBack();
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-peminjaman.php?error=Gagal memproses pengembalian!");
  exit();
}