<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM anggota WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$id]);
$anggota = $stmt->fetch();

if (!$anggota) {
  header("Location: kelola-anggota.php?error=Anggota tidak ditemukan");
  exit();
}

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center mt-4">
    <h1 class="text-2xl font-bold">Edit Anggota</h1>
  </div>
</section>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <form method="POST" action="../logic/admin/anggota-edit.php" class="space-y-4">
    <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2">Nama Lengkap *</label>
        <input type="text" name="nama" required value="<?= htmlspecialchars($anggota['nama']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Email *</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($anggota['email']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Password (Kosongkan jika tidak diubah)</label>
        <input type="password" name="password" minlength="6"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Jenis Kelamin *</label>
        <select name="gender" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Pria" <?= $anggota['gender'] == 'Pria' ? 'selected' : '' ?>>Pria</option>
          <option value="Wanita" <?= $anggota['gender'] == 'Wanita' ? 'selected' : '' ?>>Wanita</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">No Telepon</label>
        <input type="text" name="no_telepon" value="<?= htmlspecialchars($anggota['no_telepon'] ?? '') ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Status</label>
        <select name="status" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Tidak Meminjam" <?= $anggota['status'] == 'Tidak Meminjam' ? 'selected' : '' ?>>Tidak Meminjam</option>
          <option value="Sedang Meminjam" <?= $anggota['status'] == 'Sedang Meminjam' ? 'selected' : '' ?>>Sedang Meminjam</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-2">Alamat Lengkap *</label>
        <textarea name="alamat" required rows="3"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($anggota['alamat']) ?></textarea>
      </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <div class="flex gap-3 pt-4">
      <a href="kelola-anggota.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">Batal</a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update Anggota</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>