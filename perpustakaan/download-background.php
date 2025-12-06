<?php
/**
 * Script untuk download background image dari Unsplash
 * Jalankan sekali: php download-background.php
 */

// URL background dari Unsplash
$url = 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80';

// Pastikan folder assets/img/ ada
$img_dir = __DIR__ . '/assets/img/';
if (!is_dir($img_dir)) {
    mkdir($img_dir, 0755, true);
    echo "✓ Folder assets/img/ dibuat\n";
}

// Nama file output
$output_file = $img_dir . 'library-bg.jpg';

echo "Mengunduh background image dari Unsplash...\n";
echo "URL: $url\n\n";

// Download menggunakan file_get_contents
$image_data = @file_get_contents($url);

if ($image_data === false) {
    die("❌ ERROR: Gagal mengunduh gambar. Pastikan koneksi internet aktif!\n");
}

// Simpan ke file
if (file_put_contents($output_file, $image_data)) {
    $file_size = filesize($output_file);
    $file_size_kb = round($file_size / 1024, 2);
    
    echo "✅ SUCCESS!\n";
    echo "File disimpan: $output_file\n";
    echo "Ukuran: $file_size_kb KB\n";
    echo "\nSekarang Anda bisa:\n";
    echo "1. Hapus script ini (download-background.php)\n";
    echo "2. Update file auth/login.php dan auth/register.php\n";
    echo "3. Ganti background-image URL dengan: url('../assets/img/library-bg.jpg')\n";
} else {
    echo "❌ ERROR: Gagal menyimpan file!\n";
    echo "Pastikan folder assets/img/ memiliki permission yang benar.\n";
}
?>