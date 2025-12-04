<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

$filter = $_GET['filter'] ?? 'all';
$search = $_POST['search'] ?? '';
$page = $_POST['page'] ?? 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = [];
$params = [];

if ($filter !== 'all') {
  $whereConditions[] = "p.status = :status";
  $params[':status'] = $filter;
}

if ($search) {
  $whereConditions[] = "(a.nama LIKE :search OR b.judul LIKE :search)";
  $params[':search'] = "%$search%";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Count total
$sql = "SELECT COUNT(*) as total FROM peminjaman p 
        INNER JOIN anggota a ON p.anggota_id = a.id
        INNER JOIN books b ON p.book_id = b.id
        $whereClause";
$stmt = $connection->prepare($sql);
$stmt->execute($params);
$totalData = $stmt->fetch()['total'];
$totalPages = ceil($totalData / $perPage) ?: 1;

// Get data
$sql = "SELECT p.*, a.nama as nama_anggota, a.email as email_anggota, 
        b.judul as judul_buku, b.pengarang, b.penerbit
        FROM peminjaman p
        INNER JOIN anggota a ON p.anggota_id = a.id
        INNER JOIN books b ON p.book_id = b.id
        $whereClause
        ORDER BY p.tgl_request DESC, p.id DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $connection->prepare($sql);
$stmt->execute($params);
$peminjamans = $stmt->fetchAll();

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center justify-between mt-4">
    <h1 class="text-2xl font-bold">Kelola Peminjaman</h1>
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
  <!-- Filter & Search -->
  <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
    <!-- Filter Status -->
    <div class="flex gap-2">
      <a href="?filter=all" class="px-4 py-2 rounded-lg <?= $filter == 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
        Semua
      </a>
      <a href="?filter=pending" class="px-4 py-2 rounded-lg <?= $filter == 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
        Pending
      </a>
      <a href="?filter=approved" class="px-4 py-2 rounded-lg <?= $filter == 'approved' ? 'bg-green-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
        Disetujui
      </a>
      <a href="?filter=rejected" class="px-4 py-2 rounded-lg <?= $filter == 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
        Ditolak
      </a>
      <a href="?filter=returned" class="px-4 py-2 rounded-lg <?= $filter == 'returned' ? 'bg-purple-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
        Dikembalikan
      </a>
    </div>

    <!-- Search -->
    <form method="POST" class="flex gap-2">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Cari nama anggota atau judul buku...">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Cari</button>
    </form>
  </div>

  <!-- Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-3 text-left">No</th>
          <th class="px-4 py-3 text-left">Anggota</th>
          <th class="px-4 py-3 text-left">Buku</th>
          <th class="px-4 py-3 text-left">Tgl Request</th>
          <th class="px-4 py-3 text-left">Tgl Pinjam</th>
          <th class="px-4 py-3 text-left">Tgl Kembali</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php if (empty($peminjamans)): ?>
          <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada data</td></tr>
        <?php else: ?>
          <?php foreach ($peminjamans as $i => $p): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3"><?= $offset + $i + 1 ?></td>
              <td class="px-4 py-3">
                <div class="font-medium"><?= htmlspecialchars($p['nama_anggota']) ?></div>
                <div class="text-xs text-gray-500"><?= htmlspecialchars($p['email_anggota']) ?></div>
              </td>
              <td class="px-4 py-3">
                <div class="font-medium"><?= htmlspecialchars($p['judul_buku']) ?></div>
                <div class="text-xs text-gray-500"><?= htmlspecialchars($p['pengarang']) ?></div>
              </td>
              <td class="px-4 py-3 text-xs"><?= date('d/m/Y H:i', strtotime($p['tgl_request'])) ?></td>
              <td class="px-4 py-3"><?= date('d/m/Y', strtotime($p['tgl_pinjam'])) ?></td>
              <td class="px-4 py-3"><?= date('d/m/Y', strtotime($p['tgl_kembali'])) ?></td>
              <td class="px-4 py-3">
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
                <span class="px-2 py-1 rounded text-xs font-medium <?= $statusColors[$p['status']] ?>">
                  <?= $statusLabels[$p['status']] ?>
                </span>
                <?php if ($p['catatan_admin']): ?>
                  <div class="text-xs text-gray-500 mt-1" title="<?= htmlspecialchars($p['catatan_admin']) ?>">
                    📝 <?= htmlspecialchars(substr($p['catatan_admin'], 0, 30)) ?>...
                  </div>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-center gap-2">
                  <?php if ($p['status'] == 'pending'): ?>
                    <!-- Approve Button -->
                    <button onclick="showApproveModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_anggota']) ?>', '<?= htmlspecialchars($p['judul_buku']) ?>')"
                      class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
                      ✓ Setujui
                    </button>
                    <!-- Reject Button -->
                    <button onclick="showRejectModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_anggota']) ?>', '<?= htmlspecialchars($p['judul_buku']) ?>')"
                      class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                      ✗ Tolak
                    </button>
                  <?php elseif ($p['status'] == 'approved'): ?>
                    <form method="POST" action="../logic/admin/peminjaman-return.php"
                      onsubmit="return confirm('Konfirmasi buku sudah dikembalikan?')">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs">
                        📥 Kembalikan
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-gray-400 text-xs">-</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="flex items-center justify-between mt-6">
    <p class="text-sm text-gray-600">Total: <strong><?= $totalData ?></strong> peminjaman</p>
    <div class="flex gap-1">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <form method="POST" class="inline">
          <input type="hidden" name="page" value="<?= $i ?>">
          <input type="hidden" name="search" value="<?= $search ?>">
          <button type="submit" class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
            <?= $i ?>
          </button>
        </form>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <h3 class="text-xl font-bold mb-4">Setujui Peminjaman</h3>
    <form method="POST" action="../logic/admin/peminjaman-approve.php">
      <input type="hidden" name="id" id="approve_id">
      <p class="mb-4">Setujui peminjaman untuk:</p>
      <div class="bg-gray-50 p-3 rounded mb-4">
        <p class="font-medium" id="approve_anggota"></p>
        <p class="text-sm text-gray-600" id="approve_buku"></p>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Catatan (opsional)</label>
        <textarea name="catatan" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Tambahkan catatan jika diperlukan..."></textarea>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="closeModals()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
          Batal
        </button>
        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
          Setujui
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <h3 class="text-xl font-bold mb-4">Tolak Peminjaman</h3>
    <form method="POST" action="../logic/admin/peminjaman-reject.php">
      <input type="hidden" name="id" id="reject_id">
      <p class="mb-4">Tolak peminjaman untuk:</p>
      <div class="bg-gray-50 p-3 rounded mb-4">
        <p class="font-medium" id="reject_anggota"></p>
        <p class="text-sm text-gray-600" id="reject_buku"></p>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Alasan Penolakan *</label>
        <textarea name="catatan" required rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Jelaskan alasan penolakan..."></textarea>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="closeModals()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
          Batal
        </button>
        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
          Tolak
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function showApproveModal(id, anggota, buku) {
  document.getElementById('approve_id').value = id;
  document.getElementById('approve_anggota').textContent = anggota;
  document.getElementById('approve_buku').textContent = buku;
  document.getElementById('approveModal').classList.remove('hidden');
}

function showRejectModal(id, anggota, buku) {
  document.getElementById('reject_id').value = id;
  document.getElementById('reject_anggota').textContent = anggota;
  document.getElementById('reject_buku').textContent = buku;
  document.getElementById('rejectModal').classList.remove('hidden');
}

function closeModals() {
  document.getElementById('approveModal').classList.add('hidden');
  document.getElementById('rejectModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
  if (e.target === this) closeModals();
});
document.getElementById('rejectModal').addEventListener('click', function(e) {
  if (e.target === this) closeModals();
});
</script>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>