<section class="sticky top-0 bg-gray-100 pt-4 z-10">
  <div class="flex items-center justify-between bg-white rounded p-4 shadow-lg border">
    <div class="flex items-center space-x-4">
      <h1 class="text-lg font-bold text-blue-600">Admin Panel</h1>
    </div>
    <div class="flex items-center space-x-4">
      <div class="text-right">
        <h4 class="text-sm font-medium"><?= $_SESSION['nama'] ?></h4>
        <p class="text-xs text-gray-500"><?= $_SESSION['email'] ?></p>
      </div>
      <a href="../auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
        Logout
      </a>
    </div>
  </div>
</section>
