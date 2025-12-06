<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db-config.php';

$log = [];
$errors = [];
$success = [];

// ===== STEP 1: Cek dan Buat Folder Cover =====
$cover_dir = __DIR__ . '/cover/';

$log[] = "Checking cover folder: $cover_dir";

if (!is_dir($cover_dir)) {
    $log[] = "❌ Folder tidak ada. Membuat folder...";
    if (mkdir($cover_dir, 0755, true)) {
        $success[] = "✅ Folder cover berhasil dibuat!";
        $log[] = "Permission set to 0755";
    } else {
        $errors[] = "❌ Gagal membuat folder cover!";
    }
} else {
    $success[] = "✅ Folder cover sudah ada";
    
    // Cek permission
    if (is_writable($cover_dir)) {
        $success[] = "✅ Folder cover writable";
    } else {
        $errors[] = "❌ Folder cover tidak writable! Jalankan: chmod 755 " . $cover_dir;
    }
}

// ===== STEP 2: Buat File .htaccess =====
$htaccess_file = $cover_dir . '.htaccess';

if (!file_exists($htaccess_file)) {
    $log[] = "Creating .htaccess...";
    $htaccess_content = '<FilesMatch "\.(jpg|jpeg|png)$">
  Order Allow,Deny
  Allow from all
</FilesMatch>

<FilesMatch "\.">
  Order Deny,Allow
  Deny from all
</FilesMatch>';
    
    if (file_put_contents($htaccess_file, $htaccess_content)) {
        $success[] = "✅ File .htaccess berhasil dibuat";
    } else {
        $errors[] = "❌ Gagal membuat .htaccess";
    }
} else {
    $success[] = "✅ File .htaccess sudah ada";
}

// ===== STEP 3: Buat Default Cover =====
$default_cover = $cover_dir . 'default-cover.jpg';

if (!file_exists($default_cover)) {
    $log[] = "Creating default-cover.jpg...";
    
    // Buat gambar placeholder dengan GD
    if (extension_loaded('gd')) {
        $width = 400;
        $height = 600;
        $image = imagecreatetruecolor($width, $height);
        
        // Background gradient (biru ke ungu)
        $blue = imagecolorallocate($image, 66, 153, 225);
        $purple = imagecolorallocate($image, 147, 51, 234);
        
        for ($i = 0; $i < $height; $i++) {
            $r = 66 + ($i / $height) * (147 - 66);
            $g = 153 + ($i / $height) * (51 - 153);
            $b = 225 + ($i / $height) * (234 - 225);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $i, $width, $i, $color);
        }
        
        // Text
        $white = imagecolorallocate($image, 255, 255, 255);
        $text = "No Cover\nAvailable";
        imagestring($image, 5, $width/2 - 50, $height/2 - 20, "No Cover", $white);
        imagestring($image, 5, $width/2 - 50, $height/2 + 10, "Available", $white);
        
        if (imagejpeg($image, $default_cover, 90)) {
            $success[] = "✅ default-cover.jpg berhasil dibuat dengan GD";
        } else {
            $errors[] = "❌ Gagal menyimpan default-cover.jpg";
        }
        
        imagedestroy($image);
    } else {
        $log[] = "GD extension not available. Downloading placeholder...";
        
        // Download dari placeholder service
        $placeholder_url = "https://via.placeholder.com/400x600/4299e1/ffffff?text=No+Cover+Available";
        $placeholder_data = @file_get_contents($placeholder_url);
        
        if ($placeholder_data) {
            if (file_put_contents($default_cover, $placeholder_data)) {
                $success[] = "✅ default-cover.jpg berhasil didownload";
            } else {
                $errors[] = "❌ Gagal menyimpan default-cover.jpg";
            }
        } else {
            $errors[] = "❌ Gagal download placeholder. Buat manual!";
        }
    }
} else {
    $success[] = "✅ default-cover.jpg sudah ada";
}

// ===== STEP 4: Cek Database =====
$log[] = "Checking database...";

// Cek apakah kolom 'cover' ada
try {
    $sql = "SHOW COLUMNS FROM books LIKE 'cover'";
    $stmt = $connection->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $log[] = "❌ Kolom 'cover' tidak ada. Menambahkan...";
        $sql = "ALTER TABLE `books` ADD COLUMN `cover` VARCHAR(255) DEFAULT 'default-cover.jpg' AFTER `status`";
        $connection->exec($sql);
        $success[] = "✅ Kolom 'cover' berhasil ditambahkan";
    } else {
        $success[] = "✅ Kolom 'cover' sudah ada";
    }
    
    // Update NULL values
    $sql = "UPDATE books SET cover = 'default-cover.jpg' WHERE cover IS NULL OR cover = ''";
    $affected = $connection->exec($sql);
    if ($affected > 0) {
        $success[] = "✅ Updated $affected records dengan default cover";
    }
    
} catch (PDOException $e) {
    $errors[] = "❌ Database error: " . $e->getMessage();
}

// ===== STEP 5: Cek File Consistency =====
$log[] = "Checking file consistency...";

try {
    $sql = "SELECT id, judul, cover FROM books";
    $books = $connection->query($sql)->fetchAll();
    
    $missing_files = [];
    $orphan_files = [];
    
    foreach ($books as $book) {
        if ($book['cover'] && $book['cover'] !== 'default-cover.jpg') {
            $file_path = $cover_dir . $book['cover'];
            if (!file_exists($file_path)) {
                $missing_files[] = $book['cover'] . " (ID: {$book['id']})";
            }
        }
    }
    
    if (count($missing_files) > 0) {
        $errors[] = "❌ File tidak ditemukan di database: " . implode(', ', $missing_files);
    } else {
        $success[] = "✅ Semua file di database ada di folder";
    }
    
    // Cek orphan files
    $db_files = array_column($books, 'cover');
    $actual_files = array_diff(scandir($cover_dir), ['.', '..', '.htaccess', 'default-cover.jpg']);
    
    foreach ($actual_files as $file) {
        if (!in_array($file, $db_files)) {
            $orphan_files[] = $file;
        }
    }
    
    if (count($orphan_files) > 0) {
        $errors[] = "⚠️ File tidak ada di database (bisa dihapus): " . implode(', ', $orphan_files);
    } else {
        $success[] = "✅ Tidak ada orphan files";
    }
    
} catch (PDOException $e) {
    $errors[] = "❌ Error checking consistency: " . $e->getMessage();
}

// ===== OUTPUT HASIL =====
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quick Fix - Cover Path</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
      <h1 class="text-3xl font-bold mb-6 text-blue-600">🔧 Quick Fix Results</h1>
      
      <!-- Success Messages -->
      <?php if (count($success) > 0): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded">
        <h2 class="font-bold text-lg text-green-800 mb-3">✅ Success</h2>
        <ul class="space-y-1">
          <?php foreach ($success as $msg): ?>
            <li class="text-green-700">• <?= $msg ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      
      <!-- Error Messages -->
      <?php if (count($errors) > 0): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded">
        <h2 class="font-bold text-lg text-red-800 mb-3">❌ Errors</h2>
        <ul class="space-y-1">
          <?php foreach ($errors as $msg): ?>
            <li class="text-red-700">• <?= $msg ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      
      <!-- Log Messages -->
      <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded">
        <h2 class="font-bold text-lg text-gray-800 mb-3">📝 Process Log</h2>
        <pre class="text-sm text-gray-600 whitespace-pre-wrap"><?= implode("\n", $log) ?></pre>
      </div>
      
      <!-- File List -->
      <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
        <h2 class="font-bold text-lg text-blue-800 mb-3">📁 Files in Cover Folder</h2>
        <ul class="space-y-1 text-sm">
          <?php
          if (is_dir($cover_dir)) {
            $files = array_diff(scandir($cover_dir), ['.', '..']);
            if (count($files) > 0) {
              foreach ($files as $file) {
                $size = filesize($cover_dir . $file);
                $size_kb = round($size / 1024, 2);
                echo "<li class='text-blue-700'>• <span class='font-mono'>{$file}</span> <span class='text-gray-500'>({$size_kb} KB)</span></li>";
              }
            } else {
              echo "<li class='text-gray-500'>Folder kosong</li>";
            }
          }
          ?>
        </ul>
      </div>
      
      <!-- Next Steps -->
      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
        <h2 class="font-bold text-lg text-yellow-800 mb-3">⚡ Next Steps</h2>
        <ol class="list-decimal list-inside space-y-2 text-yellow-700">
          <li>Test upload cover via Admin → Kelola Buku → Tambah Buku</li>
          <li>Test tampilan cover via Anggota → Koleksi Buku</li>
          <li>Jika masih error, jalankan <a href="anggota/test-cover-path.php" class="underline">test-cover-path.php</a></li>
          <li>Hapus file ini setelah selesai testing!</li>
        </ol>
      </div>
      
      <div class="mt-6 flex gap-3">
        <a href="admin/dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
          → Admin Dashboard
        </a>
        <a href="anggota/dashboard.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
          → Anggota Dashboard
        </a>
        <a href="anggota/test-cover-path.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">
          → Test Path
        </a>
      </div>
    </div>
  </div>
</body>
</html>