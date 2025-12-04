<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'anggota') {
  header("Location: ../../auth/login.php");
  exit();
}

require_once __DIR__ . '/../../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../anggota/buku.php");
  exit();
}

$anggota_id = $_SESSION['user_id'];
$book_id = intval($_POST['book_id'] ?? 0);
$tgl_pinjam = $_POST['tgl_pinjam'] ?? '';
$tgl_kembali = $_POST['tgl_kembali'] ?? '';

// Validasi input
if ($book_id < 1 || empty($tgl_pinjam) || empty($tgl_kembali)) {
  header("Location: ../../anggota/buku.php?error=Data tidak lengkap!");
  exit();
}

// Validasi tanggal
$pinjam = new DateTime($tgl_pinjam);
$kembali = new DateTime($tgl_kembali);
$today = new DateTime();

if ($pinjam < $today->setTime(0,0,0)) {
  header("Location: ../../anggota/buku.php?error=Tanggal pinjam tidak boleh kurang dari hari ini!");
  exit();
}

if ($kembali <= $pinjam) {
  header("Location: ../../anggota/buku.php?error=Tanggal kembali harus lebih dari tanggal pinjam!");
  exit();
}

try {
  $connection->beginTransaction();
  
  // Cek apakah buku tersedia
  $sql = "SELECT * FROM books WHERE id = ? AND status = 'Tersedia'";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$book_id]);
  $book = $stmt->fetch();
  
  if (!$book) {
    $connection->rollBack();
    header("Location: ../../anggota/buku.php?error=Buku tidak tersedia!");
    exit();
  }
  
  // Cek apakah anggota sedang meminjam atau ada pending
  $sql = "SELECT COUNT(*) as total FROM peminjaman 
          WHERE anggota_id = ? AND status IN ('pending', 'approved')";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$anggota_id]);
  $active = $stmt->fetch()['total'];
  
  if ($active > 0) {
    $connection->rollBack();
    header("Location: ../../anggota/buku.php?error=Anda masih memiliki peminjaman aktif atau pending. Selesaikan dulu sebelum meminjam lagi!");
    exit();
  }
  
  // Insert peminjaman baru
  $sql = "INSERT INTO peminjaman (anggota_id, book_id, tgl_pinjam, tgl_kembali, tgl_request, status) 
          VALUES (?, ?, ?, ?, NOW(), 'pending')";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$anggota_id, $book_id, $tgl_pinjam, $tgl_kembali]);
  
  $connection->commit();
  
  header("Location: ../../anggota/peminjaman.php?success=Pengajuan peminjaman berhasil dikirim! Menunggu approval admin.");
  exit();
  
} catch (PDOException $e) {
  $connection->rollBack();
  error_log("Request error: " . $e->getMessage());
  header("Location: ../../anggota/buku.php?error=Gagal mengajukan peminjaman!");
  exit();
}

