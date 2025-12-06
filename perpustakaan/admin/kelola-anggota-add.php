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
    <h1 class="text-2xl font-bold">Tambah Buku Baru</h1>
  </div>
</section>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <form method="POST" action="../logic/admin/buku-add.php" enctype="multipart/form-data" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2">Judul Buku *</label>
        <input type="text" name="judul" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Masukkan judul buku">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Kategori *</label>
        <input type="text" name="kategori" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Novel, Fiksi, Komputer, dll">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Pengarang *</label>
        <input type="text" name="pengarang" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nama pengarang">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Penerbit *</label>
        <input type="text" name="penerbit" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Nama penerbit">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" min="1900" max="2099"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="2024">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">ISBN</label>
        <input type="text" name="isbn"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="978-xxx-xxx">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Jumlah Stok *</label>
        <input type="number" name="jumlah_stok" required min="1" value="1"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Status *</label>
        <select name="status" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Tersedia">Tersedia</option>
          <option value="Tidak Tersedia">Tidak Tersedia</option>
        </select>
      </div>
      
      <!-- Upload Cover Buku -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-2">Cover Buku</label>
        <input type="file" name="cover" accept="image/jpg,image/jpeg,image/png"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          onchange="previewImage(event)">
        <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG. Max 2MB</p>
        
        <!-- Preview Image -->
        <div id="imagePreview" class="mt-3 hidden">
          <img id="preview" src="" alt="Preview" class="h-40 w-auto object-cover rounded border">
        </div>
      </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($_GET['error']) ?>
      </div>
    <?php endif; ?>

    <div class="flex gap-3 pt-4">
      <a href="kelola-buku.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
        Batal
      </a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
        Simpan Buku
      </button>
    </div>
  </form>
</div>

<script>
function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('preview');
  const previewContainer = document.getElementById('imagePreview');
  
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      previewContainer.classList.remove('hidden');
    }
    reader.readAsDataURL(file);
  } else {
    previewContainer.classList.add('hidden');
  }
}
</script>

<?php require_once __DIR__ . '/layout/bottom.php'; ?>