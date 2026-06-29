-- Database Kas RT Digital - Versi Revisi Fokus Jimpitan
-- Fitur utama: login bendahara, data warga, jimpitan, pengeluaran, laporan kas

CREATE DATABASE IF NOT EXISTS `kas_rt_digital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kas_rt_digital`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

DROP TABLE IF EXISTS `jimpitan`;
DROP TABLE IF EXISTS `pengeluaran`;
DROP TABLE IF EXISTS `warga`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('bendahara') DEFAULT 'bendahara',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password awal: 12345
-- Setelah login pertama, sistem akan otomatis mengubah password menjadi hash.
INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Bendahara RT', 'bendahara', '12345', 'bendahara');

CREATE TABLE `warga` (
  `id_warga` int(11) NOT NULL AUTO_INCREMENT,
  `nama_warga` varchar(100) NOT NULL,
  `no_rumah` varchar(20) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_warga`),
  UNIQUE KEY `unique_no_rumah` (`no_rumah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `warga` (`id_warga`, `nama_warga`, `no_rumah`, `no_hp`, `status`) VALUES
(1, 'Bapak Ahmad Supriyanto', '01', '081234567890', 'Aktif'),
(2, 'Bapak Slamet Riyadi', '02', '081234567891', 'Aktif'),
(3, 'Bapak Budi Santoso', '03', '081234567892', 'Aktif'),
(4, 'Ibu Siti Aminah', '04', '081234567893', 'Aktif'),
(5, 'Bapak Joko Santoso', '05', '081234567894', 'Aktif');

CREATE TABLE `jimpitan` (
  `id_jimpitan` int(11) NOT NULL AUTO_INCREMENT,
  `id_warga` int(11) NOT NULL,
  `status` enum('Isi','Kosong') NOT NULL DEFAULT 'Isi',
  `nominal` decimal(15,0) NOT NULL DEFAULT 0,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jimpitan`),
  KEY `fk_jimpitan_warga` (`id_warga`),
  CONSTRAINT `fk_jimpitan_warga`
    FOREIGN KEY (`id_warga`)
    REFERENCES `warga` (`id_warga`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jimpitan` (`id_jimpitan`, `id_warga`, `status`, `nominal`, `tanggal`, `keterangan`) VALUES
(1, 1, 'Isi', 2000, '2026-06-01', 'Jimpitan harian'),
(2, 2, 'Isi', 2000, '2026-06-01', 'Jimpitan harian'),
(3, 3, 'Isi', 2000, '2026-06-02', 'Jimpitan harian'),
(4, 4, 'Isi', 2000, '2026-06-02', 'Jimpitan harian'),
(5, 5, 'Isi', 2000, '2026-06-03', 'Jimpitan harian');

CREATE TABLE `pengeluaran` (
  `id_pengeluaran` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengeluaran` varchar(200) NOT NULL,
  `kategori` enum('Kegiatan RT','Kebersihan','Keamanan','Pembangunan','Lainnya') NOT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pengeluaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pengeluaran` (`id_pengeluaran`, `nama_pengeluaran`, `kategori`, `nominal`, `tanggal`, `keterangan`) VALUES
(1, 'Belanja alat kebersihan', 'Kebersihan', 150000, '2026-06-04', 'Sapu, kemoceng, dan alat pel'),
(2, 'Biaya ronda malam', 'Keamanan', 100000, '2026-06-05', 'Konsumsi ronda malam');

COMMIT;
