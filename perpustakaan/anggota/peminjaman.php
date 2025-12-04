<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'anggota') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

$anggota_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

$whereConditions = ["p.anggota_id = :anggota_id"];
$params = [':anggota_id' => $anggota_id];

if ($filter !== 'all') {
  $whereConditions[] = "p.status = :status";
  $params[':status'] = $filter;
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

$sql = "SELECT p.*, b.judul, b.pengarang, b.penerbit, b.kategori
        FROM peminjaman p
        INNER JOIN books b ON p.book_id = b.id
        $whereClause
        ORDER BY p.tgl_request DESC";
$stmt = $connection->prepare($sql);
$stmt->execute($params);
$peminjamans = $stmt->fetchAll();

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center mt-4">
    <h1 class="text-2xl font-bold">Riwayat Peminjaman</h1>
  </div>
</section>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4">
    <?= htmlspecialchars($_GET['success']) ?>
  </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4">
    <?= htmlspecialchars($_GET['error']) ?>
  </div>
<?php endif; ?>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <!-- Filter -->
  <div class="mb-6 flex gap-2 flex-wrap">
    <a href="?filter=all" class="px-4 py-2 rounded-lg text-sm <?= $filter == 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
      Semua
    </a>
    <a href="?filter=pending" class="px-4 py-2 rounded-lg text-sm <?= $filter == 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
      Pending
    </a>
    <a href="?filter=approved" class="px-4 py-2 rounded-lg text-sm <?= $filter == 'approved' ? 'bg-green-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
      Disetujui
    </a>
    <a href="?filter=rejected" class="px-4 py-2 rounded-lg text-sm <?= $filter == 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
      Ditolak
    </a>
    <a href="?filter=returned" class="px-4 py-2 rounded-lg text-sm <?= $filter == 'returned' ? 'bg-purple-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
      Dikembalikan
    </a>
  </div>

  <!-- List -->
  <?php if (empty($peminjamans)): ?>
    <p class="text-gray-500 text-center py-12">Belum ada riwayat peminjaman</p>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($peminjamans as $p): ?>
        <?php
        $statusColors = [
          'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
          'approved' => 'bg-green-100 text-green-800 border-green-300',
          'rejected' => 'bg-red-100 text-red-800 border-red-300',
          'returned' => 'bg-purple-100 text-purple-800 border-purple-300'
        ];
        $statusLabels = [
          'pending' => 'Menunggu Approval',
          'approved' => 'Disetujui - Sedang Dipinjam',
          'rejected' => 'Ditolak',
          'returned' => 'Sudah Dikembalikan'
        ];
        ?>
        <div class="border rounded-lg p-4 hover:shadow-md transition <?= $statusColors[$p['status']] ?>">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                  <?= htmlspecialchars($p['kategori']) ?>
                </span>
                <span class="px-2 py-1 rounded text-xs font-medium <?= $statusColors[$p['status']] ?>">
                  <?= $statusLabels[$p['status']] ?>
                </span>
              </div>
              <h3 class="font-bold text-lg"><?= htmlspecialchars($p['judul']) ?></h3>
              <p class="text-sm text-gray-600"><?= htmlspecialchars($p['pengarang']) ?> • <?= htmlspecialchars($p['penerbit']) ?></p>
              <div class="mt-2 text-sm">
                <p class="text-gray-600">
                  <span class="font-medium">Request:</span> <?= date('d M Y, H:i', strtotime($p['tgl_request'])) ?>
                </p>
                <p class="text-gray-600">
                  <span class="font-medium">Periode:</span> 
                  <?= date('d M Y', strtotime($p['tgl_pinjam'])) ?> - <?= date('d M Y', strtotime($p['tgl_kembali'])) ?>
                </p>
                <?php if ($p['catatan_admin']): ?>
                  <p class="text-gray-700 mt-1">
                    <span class="font-medium">Catatan Admin:</span> <?= htmlspecialchars($p['catatan_admin']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
            
            <?php if ($p['status'] == 'pending'): ?>
              <div>
                <form method="POST" action="../logic/anggota/peminjaman-cancel.php"
                  onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                    Batalkan
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>