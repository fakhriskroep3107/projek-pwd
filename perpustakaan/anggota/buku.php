<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'anggota') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

$search = $_POST['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$page = $_POST['page'] ?? 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ["status = 'Tersedia'"];
$params = [];

if ($search) {
  $whereConditions[] = "(judul LIKE :search OR pengarang LIKE :search OR penerbit LIKE :search)";
  $params[':search'] = "%$search%";
}

if ($kategori) {
  $whereConditions[] = "kategori = :kategori";
  $params[':kategori'] = $kategori;
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

// Get kategori list
$sql = "SELECT DISTINCT kategori FROM books ORDER BY kategori";
$kategoris = $connection->query($sql)->fetchAll(PDO::FETCH_COLUMN);

// Count total
$sql = "SELECT COUNT(*) as total FROM books $whereClause";
$stmt = $connection->prepare($sql);
$stmt->execute($params);
$totalData = $stmt->fetch()['total'];
$totalPages = ceil($totalData / $perPage) ?: 1;

// Get books
$sql = "SELECT * FROM books $whereClause ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $connection->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center justify-between mt-4">
    <h1 class="text-2xl font-bold">Koleksi Buku</h1>
  </div>
</section>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4">
    <?= htmlspecialchars($_GET['success']) ?>
  </div>
<?php endif; ?>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <!-- Filter & Search -->
  <div class="mb-6 flex flex-wrap gap-4">
    <!-- Kategori Filter -->
    <div class="flex gap-2 flex-wrap">
      <a href="buku.php" class="px-4 py-2 rounded-lg text-sm <?= empty($kategori) ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
        Semua
      </a>
      <?php foreach ($kategoris as $kat): ?>
        <a href="?kategori=<?= urlencode($kat) ?>" 
          class="px-4 py-2 rounded-lg text-sm <?= $kategori == $kat ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
          <?= htmlspecialchars($kat) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Search -->
    <form method="POST" class="flex-1 flex gap-2">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Cari judul, pengarang, penerbit...">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Cari</button>
    </form>
  </div>

  <!-- Books Grid -->
  <?php if (empty($books)): ?>
    <p class="text-gray-500 text-center py-12">Tidak ada buku ditemukan</p>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php foreach ($books as $book): ?>
        <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
          <div class="bg-gradient-to-br from-blue-500 to-purple-600 h-48 flex items-center justify-center">
            <svg class="w-20 h-20 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
            </svg>
          </div>
          <div class="p-4">
            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"><?= htmlspecialchars($book['kategori']) ?></span>
            <h3 class="font-bold text-lg mt-2 mb-1 line-clamp-2"><?= htmlspecialchars($book['judul']) ?></h3>
            <p class="text-sm text-gray-600 mb-1"><?= htmlspecialchars($book['pengarang']) ?></p>
            <p class="text-xs text-gray-500"><?= htmlspecialchars($book['penerbit']) ?> • <?= $book['tahun_terbit'] ?></p>
            <div class="mt-4">
              <button onclick="showPinjamModal(<?= $book['id'] ?>, '<?= htmlspecialchars($book['judul']) ?>', '<?= htmlspecialchars($book['pengarang']) ?>')"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                Pinjam Buku
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-8">
      <p class="text-sm text-gray-600">Total: <strong><?= $totalData ?></strong> buku</p>
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
  <?php endif; ?>
</div>

<!-- Modal Pinjam -->
<div id="pinjamModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
    <h3 class="text-xl font-bold mb-4">Ajukan Peminjaman</h3>
    <form method="POST" action="../logic/anggota/peminjaman-request.php">
      <input type="hidden" name="book_id" id="book_id">
      <div class="bg-gray-50 p-3 rounded mb-4">
        <p class="font-medium" id="modal_judul"></p>
        <p class="text-sm text-gray-600" id="modal_pengarang"></p>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Tanggal Pinjam</label>
        <input type="date" name="tgl_pinjam" required min="<?= date('Y-m-d') ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Tanggal Kembali</label>
        <input type="date" name="tgl_kembali" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="closeModal()" 
          class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
          Batal
        </button>
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
          Ajukan
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function showPinjamModal(id, judul, pengarang) {
  document.getElementById('book_id').value = id;
  document.getElementById('modal_judul').textContent = judul;
  document.getElementById('modal_pengarang').textContent = pengarang;
  document.getElementById('pinjamModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('pinjamModal').classList.add('hidden');
}

document.getElementById('pinjamModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>