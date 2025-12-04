<?php
try {
  $host = 'localhost';
  $port = 3306;
  $database = 'perpustakaan_2level';
  $username = 'root';
  $password = '';

  $connection = new PDO("mysql:host=$host:$port;dbname=$database", $username, $password);
  $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Koneksi database gagal: " . $e->getMessage());
}
?>