<?php
require_once __DIR__ . '/../db-config.php';

// Get sample book
$sql = "SELECT * FROM books LIMIT 1";
$book = $connection->query($sql)->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Cover Path</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-3xl font-bold mb-6 text-blue-600">🔍 Debug Cover Path</h1>
    
    <!-- Current Directory Info -->
    <div class="mb-6 p-4 bg-blue-50 rounded border border-blue-200">
      <h2 class="font-bold text-lg mb-3">📂 Directory Information</h2>
      <table class="w-full text-sm">
        <tr>
          <td class="font-medium py-1">Current File:</td>
          <td class="font-mono bg-white px-2 py-1 rounded"><?= __FILE__ ?></td>
        </tr>
        <tr>
          <td class="font-medium py-1">__DIR__:</td>
          <td class="font-mono bg-white px-2 py-1 rounded"><?= __DIR__ ?></td>
        </tr>
        <tr>
          <td class="font-medium py-1">Document Root:</td>
          <td class="font-mono bg-white px-2 py-1 rounded"><?= $_SERVER['DOCUMENT_ROOT'] ?></td>
        </tr>
      </table>
    </div>

    <!-- Path Testing -->
    <div class="mb-6 p-4 bg-yellow-50 rounded border border-yellow-200">
      <h2 class="font-bold text-lg mb-3">🧪 Path Testing</h2>
      
      <?php
      $cover_file = $book['cover'] ?? 'default-cover.jpg';
      
      // Test berbagai path
      $paths = [
        'Relative (..)' => '../cover/' . $cover_file,
        'Relative (../../)' => '../../cover/' . $cover_file,
        'Absolute (__DIR__)' => __DIR__ . '/../cover/' . $cover_file,
        'Absolute (DOCUMENT_ROOT)' => $_SERVER['DOCUMENT_ROOT'] . '/perpustakaan/cover/' . $cover_file,
      ];
      
      echo "<table class='w-full text-sm'>";
      echo "<thead><tr class='border-b'><th class='text-left py-2'>Path Type</th><th class='text-left py-2'>Path</th><th class='text-left py-2'>Exists?</th></tr></thead>";
      echo "<tbody>";
      
      foreach ($paths as $type => $path) {
        // Cek file exists (untuk absolute path)
        $exists = false;
        $css_class = 'bg-red-100 text-red-800';
        
        if (strpos($path, __DIR__) === 0 || strpos($path, $_SERVER['DOCUMENT_ROOT']) === 0) {
          $exists = file_exists($path);
          $css_class = $exists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        } else {
          $css_class = 'bg-gray-100 text-gray-800';
          $exists = '(check via browser)';
        }
        
        echo "<tr class='border-b'>";
        echo "<td class='py-2 font-medium'>$type</td>";
        echo "<td class='py-2 font-mono text-xs bg-white px-2 rounded'>" . htmlspecialchars($path) . "</td>";
        echo "<td class='py-2'><span class='px-2 py-1 rounded text-xs font-medium $css_class'>" . ($exists === true ? '✅ YES' : ($exists === false ? '❌ NO' : $exists)) . "</span></td>";
        echo "</tr>";
      }
      
      echo "</tbody></table>";
      ?>
    </div>

    <!-- Folder Check -->
    <div class="mb-6 p-4 bg-purple-50 rounded border border-purple-200">
      <h2 class="font-bold text-lg mb-3">📁 Folder Structure Check</h2>
      
      <?php
      $cover_dir = __DIR__ . '/../cover/';
      $folder_exists = is_dir($cover_dir);
      $folder_writable = is_writable($cover_dir);
      
      echo "<div class='space-y-2'>";
      echo "<p><span class='font-medium'>Cover Folder:</span> <code class='bg-white px-2 py-1 rounded'>$cover_dir</code></p>";
      echo "<p><span class='font-medium'>Exists:</span> <span class='px-2 py-1 rounded text-xs font-medium " . ($folder_exists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') . "'>" . ($folder_exists ? '✅ YES' : '❌ NO') . "</span></p>";
      echo "<p><span class='font-medium'>Writable:</span> <span class='px-2 py-1 rounded text-xs font-medium " . ($folder_writable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') . "'>" . ($folder_writable ? '✅ YES' : '❌ NO') . "</span></p>";
      
      if ($folder_exists) {
        $files = scandir($cover_dir);
        $files = array_diff($files, ['.', '..', '.htaccess']);
        
        echo "<div class='mt-4'>";
        echo "<p class='font-medium mb-2'>Files in folder (" . count($files) . "):</p>";
        echo "<ul class='list-disc list-inside space-y-1 text-sm'>";
        foreach ($files as $file) {
          $file_size = filesize($cover_dir . $file);
          $file_size_kb = round($file_size / 1024, 2);
          echo "<li><span class='font-mono'>$file</span> <span class='text-gray-500'>($file_size_kb KB)</span></li>";
        }
        echo "</ul>";
        echo "</div>";
      }
      echo "</div>";
      ?>
    </div>

    <!-- Database Check -->
    <div class="mb-6 p-4 bg-green-50 rounded border border-green-200">
      <h2 class="font-bold text-lg mb-3">💾 Database Check</h2>
      
      <?php
      $sql = "SELECT id, judul, cover FROM books LIMIT 5";
      $books = $connection->query($sql)->fetchAll();
      
      echo "<table class='w-full text-sm'>";
      echo "<thead><tr class='border-b'><th class='text-left py-2'>ID</th><th class='text-left py-2'>Judul</th><th class='text-left py-2'>Cover Filename</th><th class='text-left py-2'>File Exists?</th></tr></thead>";
      echo "<tbody>";
      
      foreach ($books as $b) {
        $file_path = __DIR__ . '/../cover/' . $b['cover'];
        $exists = file_exists($file_path);
        $css = $exists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        
        echo "<tr class='border-b'>";
        echo "<td class='py-2'>{$b['id']}</td>";
        echo "<td class='py-2'>" . htmlspecialchars(substr($b['judul'], 0, 30)) . "...</td>";
        echo "<td class='py-2 font-mono text-xs'>" . htmlspecialchars($b['cover']) . "</td>";
        echo "<td class='py-2'><span class='px-2 py-1 rounded text-xs font-medium $css'>" . ($exists ? '✅ YES' : '❌ NO') . "</span></td>";
        echo "</tr>";
      }
      
      echo "</tbody></table>";
      ?>
    </div>

    <!-- Visual Test -->
    <div class="mb-6 p-4 bg-pink-50 rounded border border-pink-200">
      <h2 class="font-bold text-lg mb-3">🖼️ Visual Test</h2>
      <p class="mb-4 text-sm text-gray-600">Coba load gambar dengan berbagai path:</p>
      
      <div class="grid grid-cols-2 gap-4">
        <?php
        $test_file = $book['cover'] ?? 'default-cover.jpg';
        $test_paths = [
          '../cover/' . $test_file,
          '../../cover/' . $test_file,
        ];
        
        foreach ($test_paths as $idx => $test_path) {
          echo "<div class='border rounded p-3'>";
          echo "<p class='text-xs font-mono mb-2 text-gray-600'>" . htmlspecialchars($test_path) . "</p>";
          echo "<img src='$test_path' alt='Test' class='w-full h-48 object-cover rounded border' onerror='this.parentElement.innerHTML=\"<div class=\\\"w-full h-48 bg-red-100 border border-red-300 rounded flex items-center justify-center text-red-600 font-bold\\\">❌ FAILED TO LOAD</div>\"'>";
          echo "</div>";
        }
        ?>
      </div>
    </div>

    <!-- Recommendations -->
    <div class="p-4 bg-gray-50 rounded border border-gray-300">
      <h2 class="font-bold text-lg mb-3">💡 Recommendations</h2>
      <ol class="list-decimal list-inside space-y-2 text-sm">
        <li>Pastikan folder <code class="bg-white px-2 py-1 rounded">perpustakaan/cover/</code> sudah dibuat</li>
        <li>Cek permission folder (Linux: <code class="bg-white px-2 py-1 rounded">chmod 755 cover</code>)</li>
        <li>Upload file <code class="bg-white px-2 py-1 rounded">default-cover.jpg</code> ke folder cover</li>
        <li>Gunakan path: <code class="bg-white px-2 py-1 rounded font-bold text-green-600">../cover/filename.jpg</code> untuk file di <code>anggota/buku.php</code></li>
        <li>Pastikan nama file di database sesuai dengan file di folder</li>
      </ol>
    </div>

    <div class="mt-6 text-center">
      <a href="buku.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-block">
        ← Kembali ke Daftar Buku
      </a>
    </div>
  </div>
</body>
</html>