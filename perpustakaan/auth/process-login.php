<?php
session_start();
require_once __DIR__ . '/../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: login.php");
  exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = $_POST['role'] ?? '';

// Validasi input
if (empty($email) || empty($password) || empty($role)) {
  header("Location: login.php?error=Semua field harus diisi!");
  exit();
}

// Validasi role
if (!in_array($role, ['admin', 'anggota'])) {
  header("Location: login.php?error=Role tidak valid!");
  exit();
}

try {
  // Tentukan tabel berdasarkan role
  $table = ($role === 'admin') ? 'admin' : 'anggota';
  
  // Query untuk mengecek user
  $sql = "SELECT * FROM $table WHERE email = ? LIMIT 1";
  $statement = $connection->prepare($sql);
  $statement->execute([$email]);
  
  $user = $statement->fetch(PDO::FETCH_ASSOC);
  
  // Cek apakah user ditemukan
  if (!$user) {
    header("Location: login.php?error=Email atau password salah!");
    exit();
  }
  
  // Verifikasi password
  // Cek apakah password menggunakan hash atau plain text (backward compatibility)
  $password_valid = false;
  
  if (password_get_info($user['password'])['algo'] !== null) {
    // Password sudah di-hash
    $password_valid = password_verify($password, $user['password']);
  } else {
    // Password masih plain text (untuk data lama)
    $password_valid = ($password === $user['password']);
  }
  
  if (!$password_valid) {
    header("Location: login.php?error=Email atau password salah!");
    exit();
  }
  
  // Login berhasil - Set session
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['nama'] = $user['nama'];
  $_SESSION['email'] = $user['email'];
  $_SESSION['role'] = $role;
  $_SESSION['foto'] = $user['foto'] ?? 'default.jpg';
  
  // Redirect berdasarkan role
  if ($role === 'admin') {
    header("Location: ../admin/dashboard.php");
  } else {
    header("Location: ../anggota/dashboard.php");
  }
  exit();
  
} catch (PDOException $e) {
  error_log("Login error: " . $e->getMessage());
  header("Location: login.php?error=Terjadi kesalahan sistem. Silakan coba lagi.");
  exit();
}