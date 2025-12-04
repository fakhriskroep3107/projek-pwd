<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<aside class="w-64 bg-white h-screen sticky top-0 left-0 shadow-lg">
  <div class="p-6">
    <h1 class="text-2xl font-bold text-blue-600">Perpustakaan</h1>
    <p class="text-xs text-gray-500">Admin Panel</p>
  </div>
  <nav class="mt-6">
    <a href="dashboard.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
      <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
      </svg>
      Dashboard
    </a>
    <a href="kelola-buku.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= in_array($currentPage, ['kelola-buku.php', 'kelola-buku-add.php', 'kelola-buku-edit.php']) ? 'active' : '' ?>">
      <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
      </svg>
      Kelola Buku
    </a>
    <a href="kelola-anggota.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= in_array($currentPage, ['kelola-anggota.php', 'kelola-anggota-add.php', 'kelola-anggota-edit.php']) ? 'active' : '' ?>">
      <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
      </svg>
      Kelola Anggota
    </a>
    <a href="kelola-peminjaman.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= $currentPage == 'kelola-peminjaman.php' ? 'active' : '' ?>">
      <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
      </svg>
      Kelola Peminjaman
    </a>
  </nav>
</aside>
