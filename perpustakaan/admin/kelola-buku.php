<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

// Pagination & Search
$search = $_POST['search'] ?? '';
$page = $_POST['page'] ?? 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$searchQuery = $search ? "WHERE judul LIKE :search OR kategori LIKE :search OR pengarang LIKE :search OR penerbit LIKE :search" : "";

// Count total
$sql = "SELECT COUNT(*) as total FROM books $searchQuery";
$stmt = $connection->prepare($sql);
if ($search) $stmt->execute([':search' => "%$search%"]);
else $stmt->execute();
$totalData = $stmt->fetch()['total'];
$totalPages = ceil($totalData / $perPage) ?: 1;

// Get data
$sql = "SELECT * FROM books $searchQuery ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $connection->prepare($sql);
if ($search) $stmt->execute([':search' => "%$search%"]);
else $stmt->execute();
$books = $stmt->fetchAll();

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center justify-between mt-4">
    <h1 class="text-2xl font-bold">Kelola Buku</h1>
    <a href="kelola-buku-add.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
      + Tambah Buku
    </a>
  </div>
</section>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
    <span class="block sm:inline"><?= htmlspecialchars($_GET['success']) ?></span>
  </div>
<?php endif; ?>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <!-- Search -->
  <form method="POST" class="mb-6">
    <div class="flex gap-2">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Cari judul, kategori, pengarang, penerbit...">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
        Cari
      </button>
      <?php if ($search): ?>
        <a href="kelola-buku.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
          Reset
        </a>
      <?php endif; ?>
    </div>
  </form>

  <!-- Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-3 text-left">No</th>
          <th class="px-4 py-3 text-left">Judul</th>
          <th class="px-4 py-3 text-left">Kategori</th>
          <th class="px-4 py-3 text-left">Pengarang</th>
          <th class="px-4 py-3 text-left">Penerbit</th>
          <th class="px-4 py-3 text-left">Tahun</th>
          <th class="px-4 py-3 text-left">Stok</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php if (empty($books)): ?>
          <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Tidak ada data</td></tr>
        <?php else: ?>
          <?php foreach ($books as $i => $book): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3"><?= $offset + $i + 1 ?></td>
              <td class="px-4 py-3 font-medium"><?= htmlspecialchars($book['judul']) ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($book['kategori']) ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($book['pengarang']) ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($book['penerbit']) ?></td>
              <td class="px-4 py-3"><?= $book['tahun_terbit'] ?></td>
              <td class="px-4 py-3"><?= $book['jumlah_stok'] ?></td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs font-medium
                  <?= $book['status'] == 'Tersedia' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                  <?= $book['status'] ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-center gap-2">
                  <a href="kelola-buku-edit.php?id=<?= $book['id'] ?>" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                    Edit
                  </a>
                  <form method="POST" action="../logic/admin/buku-delete.php" class="inline"
                    onsubmit="return confirm('Yakin hapus buku ini?')">
                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                      Hapus
                    </button>
                  </form>
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
</div>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>