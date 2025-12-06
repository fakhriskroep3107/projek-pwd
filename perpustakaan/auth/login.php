<?php
session_start();

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
  } else {
    header("Location: ../anggota/dashboard.php");
  }
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Perpustakaan</title>
  
  <!-- CSS Utama - 100% Offline -->
  <link rel="stylesheet" href="../assets/css/style.css">
  
  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }
    .bg-img {
      background-image: url('../assets/img/library-bg.jpg');
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center;
    }
  </style>
</head>

<body>
  <div class="flex min-h-screen overflow-y-auto">
    <!-- Left - Image -->
    <div class="hidden md:block md:w-5/12 bg-img shrink-0">
      <div class="bg-black/40 h-full flex justify-center items-center">
        <div class="text-white text-center">
          <h1 class="text-4xl font-bold mb-4">Perpustakaan Digital</h1>
          <p class="text-xl">Sistem Manajemen Perpustakaan Modern</p>
        </div>
      </div>
    </div>

    <!-- Right - Login Form -->
    <div class="w-full md:w-7/12 bg-gradient-to-br from-blue-50 to-indigo-100 pb-10 flex items-center justify-center">
      <div class="w-full max-w-md px-8">
        <!-- Logo/Title -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-bold text-blue-600 mb-2">Login</h1>
          <p class="text-gray-600">Silakan login untuk melanjutkan</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-xl p-8">
          <form action="process-login.php" method="POST" class="space-y-5">
            
            <!-- Role Selection -->
            <div>
              <label class="block text-gray-700 font-medium mb-2">Login Sebagai</label>
              <div class="flex space-x-4">
                <label class="flex items-center cursor-pointer">
                  <input type="radio" name="role" value="admin" class="w-4 h-4 text-blue-600" required>
                  <span class="ml-2 text-gray-700">Admin</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input type="radio" name="role" value="anggota" class="w-4 h-4 text-blue-600" checked required>
                  <span class="ml-2 text-gray-700">Anggota</span>
                </label>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
              <input 
                type="email" 
                id="email" 
                name="email" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Masukkan email"
                required>
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Masukkan password"
                required>
            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <span class="block sm:inline"><?= htmlspecialchars($_GET['error']) ?></span>
              </div>
            <?php endif; ?>

            <!-- Submit Button -->
            <button 
              type="submit" 
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
              Login
            </button>

            <!-- Register Link -->
            <div class="text-center pt-4 border-t border-gray-200">
              <p class="text-sm text-gray-600">
                Belum punya akun? 
                <a href="register.php" class="text-blue-600 hover:text-blue-700 font-medium">Daftar sekarang</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>