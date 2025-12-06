<?php
// Script untuk migrasi password lama (plain text) ke hash
// Jalankan file ini SATU KALI SAJA setelah update sistem

require_once __DIR__ . '/db-config.php';

echo "=== MIGRASI PASSWORD KE HASH ===\n\n";

try {
  $connection->beginTransaction();
  
  // Migrasi password admin
  echo "Migrasi password Admin...\n";
  $sql = "SELECT id, email, password FROM admin";
  $admins = $connection->query($sql)->fetchAll();
  
  foreach ($admins as $admin) {
    // Cek apakah password sudah di-hash
    if (password_get_info($admin['password'])['algo'] === null) {
      // Password masih plain text, hash sekarang
      $hashed = password_hash($admin['password'], PASSWORD_DEFAULT);
      $sql = "UPDATE admin SET password = ? WHERE id = ?";
      $stmt = $connection->prepare($sql);
      $stmt->execute([$hashed, $admin['id']]);
      echo "✓ Admin {$admin['email']} - password di-hash\n";
    } else {
      echo "○ Admin {$admin['email']} - sudah di-hash\n";
    }
  }
  
  // Migrasi password anggota
  echo "\nMigrasi password Anggota...\n";
  $sql = "SELECT id, email, password FROM anggota";
  $anggotas = $connection->query($sql)->fetchAll();
  
  foreach ($anggotas as $anggota) {
    // Cek apakah password sudah di-hash
    if (password_get_info($anggota['password'])['algo'] === null) {
      // Password masih plain text, hash sekarang
      $hashed = password_hash($anggota['password'], PASSWORD_DEFAULT);
      $sql = "UPDATE anggota SET password = ? WHERE id = ?";
      $stmt = $connection->prepare($sql);
      $stmt->execute([$hashed, $anggota['id']]);
      echo "✓ Anggota {$anggota['email']} - password di-hash\n";
    } else {
      echo "○ Anggota {$anggota['email']} - sudah di-hash\n";
    }
  }
  
  $connection->commit();
  
  echo "\n=== MIGRASI SELESAI ===\n";
  echo "Semua password berhasil di-hash!\n";
  echo "\nCATATAN:\n";
  echo "- User lama tetap bisa login dengan password lama mereka\n";
  echo "- Password baru akan otomatis di-hash saat registrasi atau update\n";
  echo "- Hapus file ini setelah migrasi selesai untuk keamanan\n";
  
} catch (PDOException $e) {
  $connection->rollBack();
  echo "ERROR: " . $e->getMessage() . "\n";
}