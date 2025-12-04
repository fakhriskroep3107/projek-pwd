<?php
session_start();
require_once __DIR__ . '/../db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: register.php");
  exit();
}

// Ambil dan bersihkan input
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$gender = $_POST['gender'] ?? '';
$no_telepon = trim($_POST['no_telepon'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

// Validasi input kosong
if (empty($nama) || empty($email) || empty($password) || empty($gender) || empty($alamat)) {
  $query = http_build_query(compact('nama', 'email', 'gender', 'no_telepon', 'alamat'));
  header("Location: register.php?error=Semua field wajib diisi!&$query");
  exit();
}

// Validasi email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $query = http_build_query(compact('nama', 'email', 'gender', 'no_telepon', 'alamat'));
  header("Location: register.php?error=Format email tidak valid!&$query");
  exit();
}

// Validasi password minimal 6 karakter
if (strlen($password) < 6) {
  $query = http_build_query(compact('nama', 'email', 'gender', 'no_telepon', 'alamat'));
  header("Location: register.php?error=Password minimal 6 karakter!&$query");
  exit();
}

// Validasi password match
if ($password !== $confirm_password) {
  $query = http_build_query(compact('nama', 'email', 'gender', 'no_telepon', 'alamat'));
  header("Location: register.php?error=Password tidak cocok!&$query");
  exit();
}

try {
  // Cek email sudah terdaftar atau belum
  $sql = "SELECT id FROM anggota WHERE email = ?";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$email]);
  
  if ($stmt->fetch()) {
    $query = http_build_query(compact('nama', 'email', 'gender', 'no_telepon', 'alamat'));
    header("Location: register.php?error=Email sudah terdaftar! Silakan gunakan email lain.&$query");
    exit();
  }
  
  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  
  // Insert data anggota baru
  $sql = "INSERT INTO anggota (nama, email, password, gender, no_telepon, alamat, status, foto) 
          VALUES (?, ?, ?, ?, ?, ?, 'Tidak Meminjam', 'default-anggota.jpg')";
  $stmt = $connection->prepare($sql);
  $stmt->execute([$nama, $email, $hashed_password, $gender, $no_telepon, $alamat]);
  
  // Auto login setelah registrasi
  $anggota_id = $connection->lastInsertId();
  
  $_SESSION['user_id'] = $anggota_id;
  $_SESSION['nama'] = $nama;
  $_SESSION['email'] = $email;
  $_SESSION['role'] = 'anggota';
  $_SESSION['foto'] = 'default-anggota.jpg';
  
  // Redirect ke dashboard anggota
  header("Location: ../anggota/dashboard.php?success=Registrasi berhasil! Selamat datang.");
  exit();
  
} catch (PDOException $e) {
  error_log("Registration error: " . $e->getMessage());
  $query = http_build_query(compact('nama', 'email', 'gender', 'no_telepon', 'alamat'));
  header("Location: register.php?error=Terjadi kesalahan sistem. Silakan coba lagi.&$query");
  exit();
}