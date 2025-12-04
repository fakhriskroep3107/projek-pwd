<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}
require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center mt-4">
    <h1 class="text-2xl font-bold">Tambah Anggota Baru</h1>
  </div>
</section>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <form method="POST" action="../logic/admin/anggota-add.php" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2">Nama Lengkap *</label>
        <input type="text" name="nama" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nama lengkap anggota">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Email *</label>
        <input type="email" name="email" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="email@example.com">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Password *</label>
        <input type="password" name="password" required minlength="6"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Minimal 6 karakter">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Jenis Kelamin *</label>
        <select name="gender" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">-- Pilih --</option>
          <option value="Pria">Pria</option>
          <option value="Wanita">Wanita</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">No Telepon</label>
        <input type="text" name="no_telepon"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="08xxxxxxxxxx">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Status</label>
        <input type="text" value="Tidak Meminjam" disabled
          class="w-full px-4 py-2 border rounded-lg bg-gray-100">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-2">Alamat Lengkap *</label>
        <textarea name="alamat" required rows="3"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Alamat lengkap anggota"></textarea>
      </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <div class="flex gap-3 pt-4">
      <a href="kelola-anggota.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">Batal</a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Simpan Anggota</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>
