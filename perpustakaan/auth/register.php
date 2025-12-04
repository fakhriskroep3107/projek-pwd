<?php
session_start();

// Jika sudah login, redirect
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi - Perpustakaan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    * { font-family: 'Poppins', sans-serif; }
    .bg-img {
      background-image: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center;
    }
  </style>
</head>

<body>
  <div class="flex min-h-screen overflow-y-auto">
    <!-- Left - Image -->
    <div class="hidden md:block w-5/12 bg-img shrink-0">
      <div class="bg-black/40 h-full flex justify-center items-center">
        <div class="text-white text-center">
          <h1 class="text-4xl font-bold mb-4">Perpustakaan Digital</h1>
          <p class="text-xl">Daftar dan Mulai Membaca</p>
        </div>
      </div>
    </div>

    <!-- Right - Register Form -->
    <div class="w-full md:w-7/12 bg-gradient-to-br from-blue-50 to-indigo-100 pb-10 flex items-center justify-center">
      <div class="w-full max-w-2xl px-8 py-8">
        <!-- Title -->
        <div class="text-center mb-6">
          <h1 class="text-4xl font-bold text-blue-600 mb-2">Registrasi Anggota</h1>
          <p class="text-gray-600">Lengkapi form untuk mendaftar sebagai anggota perpustakaan</p>
        </div>

        <!-- Register Form -->
        <div class="bg-white rounded-lg shadow-xl p-8">
          <form action="process-register.php" method="POST" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nama Lengkap -->
              <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                  Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input 
                  type="text" 
                  id="nama" 
                  name="nama" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Nama lengkap Anda"
                  value="<?= htmlspecialchars($_GET['nama'] ?? '') ?>">
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                  Email <span class="text-red-500">*</span>
                </label>
                <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="email@example.com"
                  value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
              </div>

              <!-- Password -->
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                  Password <span class="text-red-500">*</span>
                </label>
                <input 
                  type="password" 
                  id="password" 
                  name="password" 
                  required
                  minlength="6"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Minimal 6 karakter">
                <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
              </div>

              <!-- Konfirmasi Password -->
              <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                  Konfirmasi Password <span class="text-red-500">*</span>
                </label>
                <input 
                  type="password" 
                  id="confirm_password" 
                  name="confirm_password" 
                  required
                  minlength="6"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Ulangi password">
              </div>

              <!-- Jenis Kelamin -->
              <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                  Jenis Kelamin <span class="text-red-500">*</span>
                </label>
                <select 
                  id="gender" 
                  name="gender" 
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">-- Pilih --</option>
                  <option value="Pria" <?= ($_GET['gender'] ?? '') == 'Pria' ? 'selected' : '' ?>>Pria</option>
                  <option value="Wanita" <?= ($_GET['gender'] ?? '') == 'Wanita' ? 'selected' : '' ?>>Wanita</option>
                </select>
              </div>

              <!-- No Telepon -->
              <div>
                <label for="no_telepon" class="block text-sm font-medium text-gray-700 mb-1">
                  No Telepon
                </label>
                <input 
                  type="text" 
                  id="no_telepon" 
                  name="no_telepon" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="08xxxxxxxxxx"
                  value="<?= htmlspecialchars($_GET['no_telepon'] ?? '') ?>">
              </div>
            </div>

            <!-- Alamat -->
            <div>
              <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">
                Alamat Lengkap <span class="text-red-500">*</span>
              </label>
              <textarea 
                id="alamat" 
                name="alamat" 
                required
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Alamat lengkap Anda"><?= htmlspecialchars($_GET['alamat'] ?? '') ?></textarea>
            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <span class="block sm:inline"><?= htmlspecialchars($_GET['error']) ?></span>
              </div>
            <?php endif; ?>

            <!-- Submit Button -->
            <div class="pt-4">
              <button 
                type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                Daftar Sekarang
              </button>
            </div>

            <!-- Link to Login -->
            <div class="text-center">
              <p class="text-sm text-gray-600">
                Sudah punya akun? 
                <a href="login.php" class="text-blue-600 hover:text-blue-700 font-medium">Login di sini</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
