<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Anggota - Perpustakaan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f7f7f7; }
    .active { background: linear-gradient(118deg, #7367f0, rgba(115, 103, 240, 0.7)); color: white; box-shadow: 0 0 10px 1px rgb(115 103 240 / 70%); }
  </style>
</head>
<body>
  <div class="flex text-gray-700">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <section class="w-full mx-5">
      <?php require_once __DIR__ . '/navbar.php'; ?>