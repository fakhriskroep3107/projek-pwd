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
$judul = trim($_POST['judul'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$penerbit = trim($_POST['penerbit'] ?? '');
$tahun_terbit = $_POST['tahun_terbit'] ?? null;
$isbn = trim($_POST['isbn'] ?? '');
$jumlah_stok = intval($_POST['jumlah_stok'] ?? 1);
$status = $_POST['status'] ?? 'Tersedia';

// Validasi
if ($id < 1 || empty($judul) || empty($kategori) || empty($pengarang) || empty($penerbit)) {
  header("Location: ../../admin/kelola-buku-edit.php?id=$id&error=Semua field wajib diisi!");
  exit();
}

try {
  $sql = "UPDATE books SET judul=?, kategori=?, pengarang=?, penerbit=?, tahun_terbit=?, isbn=?, jumlah_stok=?, status=? WHERE id=?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$judul, $kategori, $pengarang, $penerbit, $tahun_terbit, $isbn, $jumlah_stok, $status, $id]);
  
  header("Location: ../../admin/kelola-buku.php?success=Buku berhasil diupdate!");
  exit();
} catch (PDOException $e) {
  error_log($e->getMessage());
  header("Location: ../../admin/kelola-buku-edit.php?id=$id&error=Gagal mengupdate buku!");
  exit();
}