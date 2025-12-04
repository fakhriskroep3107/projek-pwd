<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

$search = $_POST['search'] ?? '';
$page = $_POST['page'] ?? 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$searchQuery = $search ? "WHERE nama LIKE :search OR email LIKE :search OR alamat LIKE :search" : "";

$sql = "SELECT COUNT(*) as total FROM anggota $searchQuery";
$stmt = $connection->prepare($sql);
if ($search) $stmt->execute([':search' => "%$search%"]);
else $stmt->execute();
$totalData = $stmt->fetch()['total'];
$totalPages = ceil($totalData / $perPage) ?: 1;

$sql = "SELECT * FROM anggota $searchQuery ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $connection->prepare($sql);
if ($search) $stmt->execute([':search' => "%$search%"]);
else $stmt->execute();
$anggotas = $stmt->fetchAll();

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center justify-between mt-4">
    <h1 class="text-2xl font-bold">Kelola Anggota</h1>
    <a href="kelola-anggota-add.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
      + Tambah Anggota
    </a>
  </div>
</section>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4">
    <?= htmlspecialchars($_GET['success']) ?>
  </div>
<?php endif; ?>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <form method="POST" class="mb-6">
    <div class="flex gap-2">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Cari nama, email, alamat...">
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Cari</button>
      <?php if ($search): ?>
        <a href="kelola-anggota.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-3 text-left">No</th>
          <th class="px-4 py-3 text-left">Nama</th>
          <th class="px-4 py-3 text-left">Email</th>
          <th class="px-4 py-3 text-left">Gender</th>
          <th class="px-4 py-3 text-left">No Telepon</th>
          <th class="px-4 py-3 text-left">Alamat</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php if (empty($anggotas)): ?>
          <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada data</td></tr>
        <?php else: ?>
          <?php foreach ($anggotas as $i => $anggota): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3"><?= $offset + $i + 1 ?></td>
              <td class="px-4 py-3 font-medium"><?= htmlspecialchars($anggota['nama']) ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($anggota['email']) ?></td>
              <td class="px-4 py-3"><?= $anggota['gender'] ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($anggota['no_telepon'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars(substr($anggota['alamat'], 0, 30)) ?>...</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs font-medium
                  <?= $anggota['status'] == 'Tidak Meminjam' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                  <?= $anggota['status'] ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-center gap-2">
                  <a href="kelola-anggota-edit.php?id=<?= $anggota['id'] ?>" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Edit</a>
                  <form method="POST" action="../logic/admin/anggota-delete.php" class="inline"
                    onsubmit="return confirm('Yakin hapus anggota ini?')">
                    <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="flex items-center justify-between mt-6">
    <p class="text-sm text-gray-600">Total: <strong><?= $totalData ?></strong> anggota</p>
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
