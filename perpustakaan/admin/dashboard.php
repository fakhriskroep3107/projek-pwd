<?php
session_start();

// Cek apakah user sudah login dan rolenya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

// Hitung statistik
$sql = "SELECT COUNT(*) as total FROM books";
$totalBuku = $connection->query($sql)->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM anggota";
$totalAnggota = $connection->query($sql)->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'pending'";
$pendingPeminjaman = $connection->query($sql)->fetch()['total'];

$sql = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'approved'";
$aktivePeminjaman = $connection->query($sql)->fetch()['total'];

require_once __DIR__ . '/layout/top.php';
?>

<!-- Header -->
<section>
  <div class="flex items-center mt-4">
    <h1 class="text-2xl font-bold">Dashboard Admin</h1>
  </div>
</section>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 my-6">
  <!-- Total Buku -->
  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-blue-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Total Buku</p>
        <h3 class="text-3xl font-bold text-blue-600"><?= $totalBuku ?></h3>
      </div>
      <div class="bg-blue-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- Total Anggota -->
  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-green-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Total Anggota</p>
        <h3 class="text-3xl font-bold text-green-600"><?= $totalAnggota ?></h3>
      </div>
      <div class="bg-green-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- Pending Peminjaman -->
  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-yellow-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Pending Request</p>
        <h3 class="text-3xl font-bold text-yellow-600"><?= $pendingPeminjaman ?></h3>
      </div>
      <div class="bg-yellow-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
  </div>

  <!-- Active Peminjaman -->
  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-purple-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Sedang Dipinjam</p>
        <h3 class="text-3xl font-bold text-purple-600"><?= $aktivePeminjaman ?></h3>
      </div>
      <div class="bg-purple-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
  </div>
</div>

<!-- Welcome Message -->
<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <h2 class="text-xl font-bold mb-4">Selamat Datang, <?= $_SESSION['nama'] ?>!</h2>
  <p class="text-gray-600">
    Anda login sebagai <span class="font-semibold text-blue-600">Administrator</span>. 
    Gunakan menu di sidebar untuk mengelola perpustakaan.
  </p>
</div>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>