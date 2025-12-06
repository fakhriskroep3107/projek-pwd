-- phpMyAdmin SQL Dump
-- Database: perpustakaan_2level

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Table structure for table `admin`
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT 'AVA-62693a77ed7051.20097657.jpg',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `admin`
INSERT INTO `admin` (`id`, `nama`, `email`, `password`, `foto`) VALUES
(1, 'Admin Perpustakaan', 'admin@perpus.com', 'admin123', 'AVA-62693a77ed7051.20097657.jpg'),
(2, 'Super Admin', 'superadmin@perpus.com', 'super123', 'AVA-62693a77ed7051.20097657.jpg');

-- Password untuk kedua admin: password

-- --------------------------------------------------------

-- Table structure for table `anggota`
CREATE TABLE `anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('Pria','Wanita') NOT NULL,
  `alamat` text NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `status` enum('Tidak Meminjam','Sedang Meminjam') NOT NULL DEFAULT 'Tidak Meminjam',
  `foto` varchar(255) DEFAULT 'foto-default.jpg',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `anggota`
INSERT INTO `anggota` (`id`, `nama`, `email`, `password`, `gender`, `alamat`, `no_telepon`, `status`, `foto`) VALUES
(1, 'Budi Santoso', 'budi@gmail.com', 'budi123', 'Pria', 'Jl. Merdeka No. 10, Jakarta', '08123456789', 'Tidak Meminjam', 'foto-default.jpg'),
(2, 'Siti Nurhaliza', 'siti@gmail.com', 'siti123', 'Wanita', 'Jl. Sudirman No. 25, Bandung', '08234567890', 'Tidak Meminjam', 'foto-default.jpg'),
(3, 'Ahmad Fauzi', 'ahmad@gmail.com', 'ahmad123', 'Pria', 'Jl. Gatot Subroto No. 5, Surabaya', '08345678901', 'Tidak Meminjam', 'foto-default.jpg');

-- Password untuk semua anggota: password

-- --------------------------------------------------------

-- Table structure for table `books`
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `pengarang` varchar(255) NOT NULL,
  `penerbit` varchar(255) NOT NULL,
  `tahun_terbit` year DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `jumlah_stok` int(11) NOT NULL DEFAULT 1,
  `status` enum('Tersedia','Dipinjam','Tidak Tersedia') NOT NULL DEFAULT 'Tersedia',
  `cover` varchar(255) DEFAULT 'default-cover.jpg',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `books`
INSERT INTO `books` (`id`, `judul`, `kategori`, `pengarang`, `penerbit`, `tahun_terbit`, `isbn`, `jumlah_stok`, `status`, `cover`) VALUES
(1, 'Harry Potter and the Philosopher Stone', 'Fiksi', 'J.K Rowling', 'Media Cipta', 2001, '978-0-7475-3269-9', 3, 'Tersedia', 'default-cover.jpg'),
(2, 'Laskar Pelangi', 'Novel', 'Andrea Hirata', 'Bentang Pustaka', 2005, '978-979-3062-79-2', 5, 'Tersedia', 'default-cover.jpg'),
(3, 'Bumi Manusia', 'Novel Sejarah', 'Pramoedya Ananta Toer', 'Lentera Dipantara', 1980, '978-979-8811-98-5', 2, 'Tersedia', 'default-cover.jpg'),
(4, 'Dilan 1990', 'Romansa', 'Pidi Baiq', 'Media Baca', 2014, '978-602-220-117-3', 4, 'Tersedia', 'default-cover.jpg'),
(5, 'Algoritma dan Pemrograman', 'Komputer', 'Rinaldi Munir', 'Informatika', 2011, '978-979-29-2352-0', 3, 'Tersedia', 'default-cover.jpg');

-- --------------------------------------------------------

-- Table structure for table `peminjaman`
CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `tgl_request` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected','returned') NOT NULL DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anggota_id` (`anggota_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `pengembalian`
CREATE TABLE `pengembalian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peminjaman_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `tgl_dikembalikan` date NOT NULL,
  `denda` decimal(10,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `peminjaman_id` (`peminjaman_id`),
  KEY `anggota_id` (`anggota_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengembalian_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengembalian_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;