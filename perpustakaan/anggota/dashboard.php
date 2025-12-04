<?php
session_start();

// Pengecekan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'anggota') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

// Statistik anggota
$anggota_id = $_SESSION['user_id'];

// Hitung total peminjaman aktif
$sql = "SELECT COUNT(*) as total FROM peminjaman WHERE anggota_id = ? AND status = 'approved'";
$stmt = $connection->prepare($sql);
$stmt->execute([$anggota_id]);
$aktif = $stmt->fetch()['total'];

// Hitung total pending
$sql = "SELECT COUNT(*) as total FROM peminjaman WHERE anggota_id = ? AND status = 'pending'";
$stmt = $connection->prepare($sql);
$stmt->execute([$anggota_id]);
$pending = $stmt->fetch()['total'];

// Hitung total riwayat
$sql = "SELECT COUNT(*) as total FROM peminjaman WHERE anggota_id = ? AND status IN ('returned', 'rejected')";
$stmt = $connection->prepare($sql);
$stmt->execute([$anggota_id]);
$riwayat = $stmt->fetch()['total'];

// Get recent peminjaman
$sql = "SELECT p.*, b.judul, b.pengarang, b.penerbit 
        FROM peminjaman p
        INNER JOIN books b ON p.book_id = b.id
        WHERE p.anggota_id = ?
        ORDER BY p.tgl_request DESC
        LIMIT 5";
$stmt = $connection->prepare($sql);
$stmt->execute([$anggota_id]);
$recent = $stmt->fetchAll();

require_once __DIR__ . '/layout/top.php';
?>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4">
    <?= htmlspecialchars($_GET['success']) ?>
  </div>
<?php endif; ?>

<section>
  <div class="flex items-center mt-4">
    <h1 class="text-2xl font-bold">Dashboard</h1>
  </div>
</section>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-green-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Sedang Dipinjam</p>
        <h3 class="text-3xl font-bold text-green-600"><?= $aktif ?></h3>
      </div>
      <div class="bg-green-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
        </svg>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-yellow-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Menunggu Approval</p>
        <h3 class="text-3xl font-bold text-yellow-600"><?= $pending ?></h3>
      </div>
      <div class="bg-yellow-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg p-6 shadow-lg border-l-4 border-purple-500">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-600 text-sm">Total Riwayat</p>
        <h3 class="text-3xl font-bold text-purple-600"><?= $riwayat ?></h3>
      </div>
      <div class="bg-purple-100 p-4 rounded-full">
        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>
  </div>
</div>

<!-- Welcome -->
<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <h2 class="text-xl font-bold mb-2">Selamat Datang, <?= $_SESSION['nama'] ?>!</h2>
  <p class="text-gray-600">
    Anda dapat menelusuri koleksi buku kami dan mengajukan peminjaman melalui menu di sidebar.
  </p>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <h2 class="text-xl font-bold mb-4">Aktivitas Terakhir</h2>
  <?php if (empty($recent)): ?>
    <p class="text-gray-500 text-center py-8">Belum ada aktivitas peminjaman</p>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($recent as $item): ?>
        <?php
        $statusColors = [
          'pending' => 'bg-yellow-100 text-yellow-800',
          'approved' => 'bg-green-100 text-green-800',
          'rejected' => 'bg-red-100 text-red-800',
          'returned' => 'bg-purple-100 text-purple-800'
        ];
        $statusLabels = [
          'pending' => 'Pending',
          'approved' => 'Disetujui',
          'rejected' => 'Ditolak',
          'returned' => 'Dikembalikan'
        ];
        ?>
        <div class="flex items-center justify-between border-b pb-3">
          <div class="flex-1">
            <h3 class="font-medium"><?= htmlspecialchars($item['judul']) ?></h3>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($item['pengarang']) ?></p>
            <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($item['tgl_request'])) ?></p>
          </div>
          <span class="px-3 py-1 rounded text-xs font-medium <?= $statusColors[$item['status']] ?>">
            <?= $statusLabels[$item['status']] ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>

