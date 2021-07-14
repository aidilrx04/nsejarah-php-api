-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2021 at 03:09 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nsejarah`
--
CREATE DATABASE IF NOT EXISTS `nsejarah` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `nsejarah`;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE IF NOT EXISTS `guru` (
  `g_id` int(30) UNSIGNED NOT NULL AUTO_INCREMENT,
  `g_nokp` varchar(30) NOT NULL,
  `g_nama` varchar(255) NOT NULL,
  `g_katalaluan` varchar(255) NOT NULL,
  `g_jenis` enum('admin','guru') DEFAULT 'guru',
  PRIMARY KEY (`g_id`),
  UNIQUE KEY `g_nokp` (`g_nokp`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`g_id`, `g_nokp`, `g_nama`, `g_katalaluan`, `g_jenis`) VALUES
(1, '333333333333', 'Samad', '123', 'guru'),
(2, '444444444444', 'Aidil Admin', '123', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `jawapan`
--

CREATE TABLE IF NOT EXISTS `jawapan` (
  `j_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `j_soalan` int(10) UNSIGNED NOT NULL,
  `j_teks` text NOT NULL,
  PRIMARY KEY (`j_id`),
  KEY `j_soalan` (`j_soalan`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jawapan`
--

INSERT INTO `jawapan` (`j_id`, `j_soalan`, `j_teks`) VALUES
(3, 2, 'Hidupan di air tidak dipengaruhi oleh graviti'),
(4, 2, 'Manalah saya tahu.'),
(5, 3, 'Cicak - Kuiz'),
(6, 3, 'Ikan - Kuiz'),
(7, 4, 'Hidupan di air tidak dipengaruhi oleh graviti - Kuiz'),
(8, 4, 'Manalah saya tahu. - Kuiz'),
(9, 6, '123'),
(10, 6, '1234'),
(11, 6, '12345'),
(12, 6, '123456'),
(100, 37, 'Cicak'),
(101, 37, 'Ikan');

-- --------------------------------------------------------

--
-- Table structure for table `jawapan_murid`
--

CREATE TABLE IF NOT EXISTS `jawapan_murid` (
  `jm_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `jm_murid` int(10) UNSIGNED NOT NULL,
  `jm_soalan` int(10) UNSIGNED NOT NULL,
  `jm_jawapan` int(10) UNSIGNED DEFAULT NULL,
  `jm_status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`jm_id`),
  KEY `jm_murid` (`jm_murid`),
  KEY `jm_soalan` (`jm_soalan`),
  KEY `jm_jawapan` (`jm_jawapan`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jawapan_murid`
--

INSERT INTO `jawapan_murid` (`jm_id`, `jm_murid`, `jm_soalan`, `jm_jawapan`, `jm_status`) VALUES
(134, 1, 2, 4, 0),
(135, 1, 6, 12, 1),
(136, 1, 37, 100, 1);

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE IF NOT EXISTS `kelas` (
  `k_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `k_nama` varchar(255) NOT NULL,
  PRIMARY KEY (`k_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`k_id`, `k_nama`) VALUES
(1, 'Amanah'),
(2, 'Bestari'),
(3, 'Cemerlang'),
(4, 'Dinamik'),
(5, 'Harmoni'),
(6, 'Harmoni'),
(7, 'Harmoni'),
(8, 'NaeNae');

-- --------------------------------------------------------

--
-- Table structure for table `kelas_tingkatan`
--

CREATE TABLE IF NOT EXISTS `kelas_tingkatan` (
  `kt_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kt_ting` tinyint(1) UNSIGNED NOT NULL,
  `kt_kelas` int(10) UNSIGNED NOT NULL,
  `kt_guru` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`kt_id`),
  KEY `kt_kelas` (`kt_kelas`),
  KEY `kt_guru` (`kt_guru`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kelas_tingkatan`
--

INSERT INTO `kelas_tingkatan` (`kt_id`, `kt_ting`, `kt_kelas`, `kt_guru`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 2),
(3, 1, 2, 1),
(4, 2, 2, 2),
(5, 1, 3, 2),
(8, 1, 8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `kuiz`
--

CREATE TABLE IF NOT EXISTS `kuiz` (
  `kz_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kz_nama` varchar(255) NOT NULL,
  `kz_guru` int(10) UNSIGNED NOT NULL,
  `kz_ting` int(10) UNSIGNED DEFAULT NULL,
  `kz_tarikh` date NOT NULL,
  `kz_jenis` enum('kuiz','latihan') DEFAULT 'kuiz',
  `kz_masa` int(5) DEFAULT NULL,
  PRIMARY KEY (`kz_id`),
  KEY `kz_guru` (`kz_guru`),
  KEY `kz_ting` (`kz_ting`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kuiz`
--

INSERT INTO `kuiz` (`kz_id`, `kz_nama`, `kz_guru`, `kz_ting`, `kz_tarikh`, `kz_jenis`, `kz_masa`) VALUES
(1, 'Gunggung', 1, 1, '2025-04-02', 'latihan', NULL),
(2, 'Bab 3: Kuiz GUNGGUNG', 1, 1, '2021-06-16', 'kuiz', 12);

-- --------------------------------------------------------

--
-- Table structure for table `murid`
--

CREATE TABLE IF NOT EXISTS `murid` (
  `m_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `m_nokp` varchar(30) NOT NULL,
  `m_nama` varchar(255) NOT NULL,
  `m_katalaluan` varchar(255) NOT NULL,
  `m_kelas` int(30) UNSIGNED NOT NULL,
  PRIMARY KEY (`m_id`,`m_nokp`),
  UNIQUE KEY `m_nokp` (`m_nokp`),
  KEY `m_kelas` (`m_kelas`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `murid`
--

INSERT INTO `murid` (`m_id`, `m_nokp`, `m_nama`, `m_katalaluan`, `m_kelas`) VALUES
(1, '111111111111', 'Soupeed', '123', 1),
(2, '222222222222', 'Sudin', '123', 2);

-- --------------------------------------------------------

--
-- Table structure for table `skor_murid`
--

CREATE TABLE IF NOT EXISTS `skor_murid` (
  `sm_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sm_murid` int(10) UNSIGNED NOT NULL,
  `sm_kuiz` int(10) UNSIGNED NOT NULL,
  `sm_skor` double(5,2) NOT NULL,
  PRIMARY KEY (`sm_id`),
  KEY `sm_murid` (`sm_murid`),
  KEY `sm_kuiz` (`sm_kuiz`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `skor_murid`
--

INSERT INTO `skor_murid` (`sm_id`, `sm_murid`, `sm_kuiz`, `sm_skor`) VALUES
(1, 1, 1, 50.00),
(2, 2, 1, 50.00),
(3, 1, 2, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `soalan`
--

CREATE TABLE IF NOT EXISTS `soalan` (
  `s_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `s_kuiz` int(10) UNSIGNED NOT NULL,
  `s_teks` text NOT NULL,
  `s_gambar` text DEFAULT NULL,
  PRIMARY KEY (`s_id`),
  KEY `s_kuiz` (`s_kuiz`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `soalan`
--

INSERT INTO `soalan` (`s_id`, `s_kuiz`, `s_teks`, `s_gambar`) VALUES
(2, 1, 'Mengapakah saiz hidupan laut lebih besar daripada daratan', NULL),
(3, 2, 'Apakah nama haiwan ini. - Kuiz', 'https://upload.wikimedia.org/wikipedia/commons/1/1c/YosriCicak.jpg'),
(4, 2, 'Mengapakah saiz hidupan laut lebih besar daripada daratan - Kuiz', NULL),
(6, 1, 'Your mom', NULL),
(37, 1, 'Apakah nama haiwan ini?', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `soalan_jawapan`
--

CREATE TABLE IF NOT EXISTS `soalan_jawapan` (
  `sj_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sj_soalan` int(10) UNSIGNED NOT NULL,
  `sj_jawapan` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`sj_id`),
  KEY `sj_soalan` (`sj_soalan`),
  KEY `sj_jawapan` (`sj_jawapan`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `soalan_jawapan`
--

INSERT INTO `soalan_jawapan` (`sj_id`, `sj_soalan`, `sj_jawapan`) VALUES
(2, 2, 3),
(3, 3, 5),
(4, 4, 7),
(5, 6, 12),
(37, 37, 100);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jawapan`
--
ALTER TABLE `jawapan`
  ADD CONSTRAINT `jawapan_ibfk_1` FOREIGN KEY (`j_soalan`) REFERENCES `soalan` (`s_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jawapan_murid`
--
ALTER TABLE `jawapan_murid`
  ADD CONSTRAINT `jawapan_murid_ibfk_1` FOREIGN KEY (`jm_murid`) REFERENCES `murid` (`m_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jawapan_murid_ibfk_2` FOREIGN KEY (`jm_soalan`) REFERENCES `soalan` (`s_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jawapan_murid_ibfk_3` FOREIGN KEY (`jm_jawapan`) REFERENCES `jawapan` (`j_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kelas_tingkatan`
--
ALTER TABLE `kelas_tingkatan`
  ADD CONSTRAINT `kelas_tingkatan_ibfk_1` FOREIGN KEY (`kt_kelas`) REFERENCES `kelas` (`k_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kelas_tingkatan_ibfk_2` FOREIGN KEY (`kt_guru`) REFERENCES `guru` (`g_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kuiz`
--
ALTER TABLE `kuiz`
  ADD CONSTRAINT `kuiz_ibfk_1` FOREIGN KEY (`kz_guru`) REFERENCES `guru` (`g_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kuiz_ibfk_2` FOREIGN KEY (`kz_ting`) REFERENCES `kelas_tingkatan` (`kt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `murid`
--
ALTER TABLE `murid`
  ADD CONSTRAINT `murid_ibfk_1` FOREIGN KEY (`m_kelas`) REFERENCES `kelas_tingkatan` (`kt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `skor_murid`
--
ALTER TABLE `skor_murid`
  ADD CONSTRAINT `skor_murid_ibfk_1` FOREIGN KEY (`sm_murid`) REFERENCES `murid` (`m_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `skor_murid_ibfk_2` FOREIGN KEY (`sm_kuiz`) REFERENCES `kuiz` (`kz_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `soalan`
--
ALTER TABLE `soalan`
  ADD CONSTRAINT `soalan_ibfk_1` FOREIGN KEY (`s_kuiz`) REFERENCES `kuiz` (`kz_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `soalan_jawapan`
--
ALTER TABLE `soalan_jawapan`
  ADD CONSTRAINT `soalan_jawapan_ibfk_1` FOREIGN KEY (`sj_soalan`) REFERENCES `soalan` (`s_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `soalan_jawapan_ibfk_2` FOREIGN KEY (`sj_jawapan`) REFERENCES `jawapan` (`j_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
