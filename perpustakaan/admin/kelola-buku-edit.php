<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

require_once __DIR__ . '/../db-config.php';

$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
  header("Location: kelola-buku.php?error=Buku tidak ditemukan");
  exit();
}

require_once __DIR__ . '/layout/top.php';
?>

<section>
  <div class="flex items-center mt-4">
    <h1 class="text-2xl font-bold">Edit Buku</h1>
  </div>
</section>

<div class="bg-white rounded-lg p-6 my-6 shadow-lg">
  <form method="POST" action="../logic/admin/buku-edit.php" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="id" value="<?= $book['id'] ?>">
    <input type="hidden" name="old_cover" value="<?= $book['cover'] ?>">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-2">Judul Buku *</label>
        <input type="text" name="judul" required value="<?= htmlspecialchars($book['judul']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Kategori *</label>
        <input type="text" name="kategori" required value="<?= htmlspecialchars($book['kategori']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Pengarang *</label>
        <input type="text" name="pengarang" required value="<?= htmlspecialchars($book['pengarang']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Penerbit *</label>
        <input type="text" name="penerbit" required value="<?= htmlspecialchars($book['penerbit']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" value="<?= $book['tahun_terbit'] ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">ISBN</label>
        <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Jumlah Stok *</label>
        <input type="number" name="jumlah_stok" required value="<?= $book['jumlah_stok'] ?>"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-2">Status *</label>
        <select name="status" required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="Tersedia" <?= $book['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
          <option value="Dipinjam" <?= $book['status'] == 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
          <option value="Tidak Tersedia" <?= $book['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
        </select>
      </div>
      
      <!-- Upload Cover Buku -->
      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-2">Cover Buku</label>
        
        <!-- Current Cover -->
        <?php if (!empty($book['cover']) && $book['cover'] !== 'default-cover.jpg'): ?>
          <div class="mb-3">
            <p class="text-sm text-gray-600 mb-2">Cover saat ini:</p>
            <img src="../cover/<?= htmlspecialchars($book['cover']) ?>" alt="Current Cover" 
              class="h-40 w-auto object-cover rounded border">
          </div>
        <?php endif; ?>
        
        <input type="file" name="cover" accept="image/jpg,image/jpeg,image/png"
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          onchange="previewImage(event)">
        <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG. Max 2MB. Kosongkan jika tidak ingin mengubah.</p>
        
        <!-- Preview New Image -->
        <div id="imagePreview" class="mt-3 hidden">
          <p class="text-sm text-gray-600 mb-2">Preview cover baru:</p>
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
        Update Buku
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