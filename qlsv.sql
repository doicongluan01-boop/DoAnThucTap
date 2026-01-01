-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th1 01, 2026 lúc 04:21 PM
-- Phiên bản máy phục vụ: 5.7.31
-- Phiên bản PHP: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlsv`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_sach_sinh_vien`
--

DROP TABLE IF EXISTS `danh_sach_sinh_vien`;
CREATE TABLE IF NOT EXISTS `danh_sach_sinh_vien` (
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Lop` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nhom_id` int(11) DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SDT` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nhom` int(11) DEFAULT NULL,
  `huong_de_tai` text COLLATE utf8mb4_unicode_ci,
  `gvhd` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ghichu` text COLLATE utf8mb4_unicode_ci,
  `gvpb_id` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `MatKhau` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '123456',
  `da_gap_gv` tinyint(1) DEFAULT '0' COMMENT '0=chưa gặp, 1=đã gặp',
  `diem_50` decimal(4,2) DEFAULT NULL COMMENT 'Điểm giữa kỳ - GVHD chấm',
  `diem_baove` decimal(4,2) DEFAULT NULL COMMENT 'Điểm bảo vệ - Hội đồng chấm',
  `diem_tong` decimal(4,2) GENERATED ALWAYS AS (((coalesce(`diem_50`,0) * 0.5) + (coalesce(`diem_baove`,0) * 0.5))) STORED,
  `hoidong_id` int(11) DEFAULT NULL,
  `duoc_bao_ve` tinyint(4) DEFAULT '1' COMMENT '1: BV, 0: KBV, 2: DC50%',
  PRIMARY KEY (`id`,`MaSV`),
  UNIQUE KEY `idx_id_duy_nhat` (`id`),
  UNIQUE KEY `idx_masv_unique` (`MaSV`),
  KEY `idx_gvpb` (`gvpb_id`),
  KEY `idx_nhom_id` (`nhom_id`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_sach_sinh_vien`
--

INSERT INTO `danh_sach_sinh_vien` (`HoTen`, `id`, `MaSV`, `Lop`, `nhom_id`, `Email`, `SDT`, `nhom`, `huong_de_tai`, `gvhd`, `ghichu`, `gvpb_id`, `created_at`, `MatKhau`, `da_gap_gv`, `diem_50`, `diem_baove`, `hoidong_id`, `duoc_bao_ve`) VALUES
('Phạm Tôn Thuận', 119, 'DH52001281', 'D20-TH04', NULL, NULL, NULL, 3, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Hoàng Linh', 120, 'DH52111212', 'D21_TH11', NULL, 'DH52111212@student.stu.edu.vn', '0941412077', 32, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'LAM001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Việt Phương', 121, 'DH52111579', 'D21_TH09', NULL, 'DH52111579@student.stu.edu.vn', '0978699529', 38, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'TUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Tấn Phi', 122, 'DH52111486', 'D21_TH09', NULL, 'DH52111486@student.stu.edu.vn', '0703760626', 36, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'LONG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Trần Hoàng Long', 123, 'DH52106740', 'D21_TH03', NULL, NULL, NULL, 18, 'Ứng dụng trên Mobile', NULL, NULL, 'NGHI001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Bùi Phi Hùng', 124, 'DH52106130', 'D21_TH01', NULL, 'DH52106130@student.stu.edu.vn', '0394126389', 17, 'Ứng dụng trên Mobile', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Viết Long', 125, 'DH52111240', 'D21_TH08', NULL, NULL, NULL, 33, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'ĐUC002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Sơn Dương', 126, 'DH52110733', 'D21_TH11', NULL, 'DH52110733@student.stu.edu.vn', '0826464186', 25, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Lê Trần Ngọc Như', 127, 'DH52111445', 'D21_TH09', NULL, NULL, NULL, 36, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'BACH001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Lâm Tuấn Kiệt', 128, 'DH52111171', 'D21_TH10', NULL, 'DH52111171@student.stu.edu.vn', '0941693505', 31, 'Xây dựng website quản lý thư viện', NULL, NULL, 'ĐUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Lê Quốc Khoa', 129, 'DH52111143', 'D21_TH10', NULL, NULL, NULL, 31, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'KHA001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Trần Minh Khánh', 130, 'DH52111118', 'D21_TH10', NULL, NULL, NULL, 31, 'Xây dựng web bán trang sức', NULL, NULL, 'VU001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Ngọc Thanh Tuệ', 131, 'DH52112019', 'D21_TH08', NULL, 'DH52112019@student.stu.edu.vn', '0907355548', 44, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trương Minh Khải', 132, 'DH52111085', 'D21_TH08', NULL, 'DH52111085@student.stu.edu.vn', '0835359010', 29, 'WEBSITE tin tức', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Lê Tuấn', 133, 'DH52000682', 'D20_TH03', NULL, 'DH52000682@student.stu.edu.vn', '0777789336', 2, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Phạm Ngọc Đông', 134, 'DH52001330', 'D20_TH03', NULL, 'DH52001330@student.stu.edu.vn', '0366468307', 3, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'KIET001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Lương Triều Vỹ', 135, 'DH52112127', 'D21_TH08', NULL, 'DH52112127@student.stu.edu.vn', NULL, 45, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'KIET001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Chí Phong', 136, 'DH52111491', 'D21_TH10', NULL, 'DH52111491@student.stu.edu.vn', '0903073250', 37, 'Website quản lý kho', NULL, NULL, 'SANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Mai Lâm Quang Khánh', 137, 'DH52111115', 'D21_TH10', NULL, 'DH52111115@student.stu.edu.vn', '0707347324', 30, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'DUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Huỳnh Tấn Nhớ', 138, 'DH52111439', 'D21_TH13', NULL, 'DH52111439@student.stu.edu.vn', '0977979791', 35, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'VINH001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Lưu Hoàng Phúc', 139, 'DH52111531', 'D21_TH13', NULL, 'DH52111531@student.stu.edu.vn', '0396895104', 38, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Giang Nhật Long', 140, 'DH52111224', 'D21_TH13', NULL, 'DH52111224@student.stu.edu.vn', '0856639637', 32, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'DUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Thanh Hậu', 141, 'DH52101228', 'D21_TH07', NULL, NULL, NULL, 13, 'Xây dựng web bán trang sức', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Đặng Gia Bảo', 144, 'DH52108711', 'D21_TH06', NULL, NULL, NULL, 21, 'Ứng dụng trên Web', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Hà Trần Hoàng Anh', 145, 'DH52108862', 'D21_TH06', NULL, NULL, NULL, 22, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'KIET001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Nguyễn Công Chi', 146, 'DH52003543', 'D20_TH05', NULL, 'DH52003543@student.stu.edu.vn', '0523261143', 7, 'Website quản lý kho', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Dương Văn Minh Tâm', 147, 'DH52111704', 'D21_TH12', NULL, NULL, NULL, 40, 'Ứng dụng Java', NULL, NULL, 'TUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Trần Ngọc Tú', 149, 'DH52113150', 'D21_TH11', NULL, NULL, NULL, 47, 'Ứng dụng trên Mobile', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Phạm Duy Thắng', 150, 'DH52007161', 'D20-TH11', NULL, 'DH52007161@student.stu.edu.vn', '0335444058', 12, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'HUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Trương Văn Liêu', 151, 'DH52111204', 'D21_TH08', NULL, 'DH52111204@student.stu.edu.vn', '0393726628', 32, 'WEBSITE tin tức', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Trương Thanh Đông', 152, 'DH52110812', 'D21_TH11', NULL, 'DH52110812@student.stu.edu.vn', '0706766557', 27, 'WEBSITE tin tức', NULL, NULL, 'HUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Lê Trần Trọng Phúc', 153, 'DH52111529', 'D21_TH10', NULL, 'DH52111529@student.stu.edu.vn', '0946129499', 37, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'LONG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Ngô Đức Trần Cường', 154, 'DH52110659', 'D21_TH11', NULL, NULL, NULL, 24, 'Website quản lý kho', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Phan Đức Thắng', 155, 'DH52113047', 'D21_TH14', NULL, 'DH52113047@student.stu.edu.vn', '0949985490', 46, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'VU001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Lê Đoàn Anh Quân', 156, 'DH52112944', 'D21_TH11', NULL, 'DH52112944@student.stu.edu.vn', '0866603591', 46, 'Xây dựng website quản lý thư viện', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Nguyễn Đăng Khoa', 157, 'DH52104108', 'D21_TH02', NULL, 'DH52104108@student.stu.edu.vn', '0938240431', 15, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'DUY001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Trần Nguyễn Minh Hiếu', 158, 'DH52110924', 'D21_TH13', NULL, 'DH52110924@student.stu.edu.vn', '0936049080', 27, 'Ứng dụng trên Web', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Nguyễn Minh Triều', 159, 'DH52001900', 'D20_TH01', NULL, 'DH52001900@student.stu.edu.vn', '0899052420', 4, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'THIN001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trần Hà Xuân Thịnh', 160, 'DH52105312', 'D21_TH02', NULL, 'DH52105312@student.stu.edu.vn', '0349573458', 17, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'TRUO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Bùi Minh Phúc', 161, 'DH52103682', 'D21_TH01', NULL, 'DH52103682@student.stu.edu.vn', '0359128746', 15, 'Ứng dụng trên Web', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Ngọc Thiện', 162, 'DH52107203', 'D21_TH01', NULL, 'DH52107203@student.stu.edu.vn', '0962419209', 19, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'THIN001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Thành Lợi', 163, 'DH52113745', 'D21_TH14', NULL, NULL, NULL, 48, 'Xây dựng web bán trang sức', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Võ Văn Phát', 164, 'DH52111482', 'D21_TH09', NULL, 'DH52111482@student.stu.edu.vn', '0937689655', 36, 'WEBSITE tin tức', NULL, NULL, 'BACH001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Trần Nguyễn Hoàng Quân', 166, 'DH52111612', 'D21_TH10', NULL, 'DH52111612@student.stu.edu.vn', '0911341117', 38, 'Ứng dụng trên Web', NULL, NULL, 'SANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Nguyễn Hòa Lợi', 167, 'DH52111263', 'D21_TH14', NULL, NULL, NULL, 33, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Nguyễn Tấn Huy', 168, 'DH52005851', 'D20_TH08', NULL, 'DH52005851@student.stu.edu.vn', '0919202108', 9, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'NGHI001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Huỳnh Minh Khoa', 169, 'DH52007089', 'D20_TH11', NULL, 'DH52007089@student.stu.edu.vn', '0898175595', 11, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'TRUO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Đỗ Quốc Khánh', 170, 'DH52111112', 'D21_TH10', NULL, 'DH52111112@student.stu.edu.vn', '0983062644', 30, 'Website bán điện thoại', NULL, NULL, 'DUY001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Trần Hoàng Vương', 171, 'DH52112118', 'D21_TH13', NULL, 'DH52112118@student.stu.edu.vn', '0987038840', 45, 'Ứng dụng trên Web', NULL, NULL, 'SANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Tuấn Vũ', 172, 'DH52002202', 'D20_TH02', NULL, NULL, NULL, 5, 'Xây dựng web bán trang sức', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Nguyễn Thanh Hải', 173, 'DH52003489', 'D20_TH03', NULL, NULL, NULL, 6, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'NGHI001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Minh Trường', 174, 'DH52111976', 'D21_TH13', NULL, 'DH52111976@student.stu.edu.vn', '0939024432', 43, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Văn Tài', 175, 'DH52111695', 'D21_TH13', NULL, 'DH52111695@student.stu.edu.vn', '0985141631', 40, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'DUY001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Phan Tuấn Dũng', 176, 'DH52103137', 'D21_TH01', NULL, 'DH52103137@student.stu.edu.vn', '0357716720', 14, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'THIN001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trần Thủy Tiên', 177, 'DH52111881', 'D21_TH08', NULL, 'DH52111881@student.stu.edu.vn', '0327458490', 43, 'WEBSITE tin tức', NULL, NULL, 'DUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Đoàn Thị Yến Bình', 178, 'DH52108380', 'D21_TH06', NULL, 'DH52108380@student.stu.edu.vn', '0824108001', 20, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Lê Minh Kiệt', 179, 'Dh52113292', 'D21_TH08', NULL, 'DH52113292@student.stu.edu.vn', '0937733385', 47, 'Xây dựng website quản lý thư viện', NULL, NULL, 'DUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trần Trọng Nhân', 180, 'DH52111411', 'D21_TH08', NULL, 'DH52111411@student.stu.edu.vn', '02723867856', 35, 'WEBSITE tin tức', NULL, NULL, 'DUY001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Tấn Đạt', 181, 'DH52110780', 'D21_TH08', NULL, NULL, NULL, 25, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Lê Thị Ly Ly', 182, 'DH52104298', 'D21_TH08', NULL, 'DH52104298@student.stu.edu.vn', '0339519874', 15, 'Xây dựng website bán quần áo', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Phạm Hữu Chí', 183, 'DH52103511', 'D21_TH01', NULL, 'DH52103511@student.stu.edu.vn', '0385920397', 14, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'HUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Trần Phạm Thanh Phong', 184, 'DH52109230', 'D21_TH07', NULL, NULL, NULL, 22, 'Ứng dụng trên Web', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Huỳnh Quốc Duy', 185, 'DH52113016', 'D21_TH14', NULL, 'DH52113016@student.stu.edu.vn', '0362949286', 46, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'SANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trần Thái Duy', 186, 'DH52113526', 'D21_TH11', NULL, 'DH52113526@student.stu.edu.vn', '0935183461', 48, 'Ứng dụng trên Web', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Đinh Quang Thịnh', 187, 'DH52112786', 'D21_TH10', NULL, 'DH52112786@student.stu.edu.vn', '0931487603', 45, 'Website bán điện thoại', NULL, NULL, 'BACH001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Lê Anh Tài', 188, 'DH52111681', 'D21_TH10', NULL, 'DH52111681@student.stu.edu.vn', '0967788246', 39, 'Xây dựng website quản lý thư viện', NULL, NULL, 'LONG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trần Minh Hưng', 189, 'DH52111067', 'D21_TH11', NULL, 'DH52111067@student.stu.edu.vn', '0932078352', 29, 'Ứng dụng trên Mobile', NULL, NULL, 'ĐUC002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Trần Như Nguyện', 190, 'DH52007186', 'D20_TH10', NULL, 'DH52007186@student.stu.edu.vn', '0388065951', 12, 'Ứng dụng trên Web', NULL, NULL, 'ĐUC002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Lưu Thị Thanh Thảo', 191, 'DH52004272', 'D20_TH06', NULL, 'DH52004272@student.stu.edu.vn', '0329824880', 8, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'DUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Lê Quang Nhân', 192, 'DH52111401', 'D21_TH08', NULL, 'DH52111401@student.stu.edu.vn', '0393638193', 34, 'Xây dựng website quản lý thư viện', NULL, NULL, 'SANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Công Tấn', 193, 'DH52111720', 'D21_TH10', NULL, 'DH52111720@student.stu.edu.vn', NULL, 41, 'Website quản lý kho', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 10, 1),
('Nguyễn Chí Thiện', 194, 'DH52111794', 'D21_TH13', NULL, 'DH52111794@student.stu.edu.vn', '0979286060', 41, 'Website quản lý kho', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 9, 1),
('Nguyễn Mậu An', 195, 'DH52110534', 'D21_TH08', NULL, 'DH52110534@student.stu.edu.vn', '0343513046', 23, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'KHA001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Trần Nguyễn Minh Quân', 196, 'DH52105342', 'D21_TH05', NULL, 'DH52105342@student.stu.edu.vn', '0388073445', 17, 'Ứng dụng trên Web', NULL, NULL, 'LAM001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Thanh Vân', 197, 'DH52107801', 'D21_TH05', NULL, 'DH52107801@student.stu.edu.vn', '0349442507', 20, 'Xây dựng web bán trang sức', NULL, NULL, 'THIN001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Lâm Đình Tuấn', 198, 'DH52112002', 'D21_TH14', NULL, 'DH52112002@student.stu.edu.vn', '0906673427', 43, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'DUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Lê Minh Thảo', 199, 'DH52111756', 'D21_TH13', NULL, 'DH52111756@student.stu.edu.vn', '0522731750', 41, 'Xây dựng website quản lý thư viện', NULL, NULL, 'NGHI001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Trường Giang', 200, 'DH52106804', 'D21_TH04', NULL, NULL, NULL, 18, 'Xây dựng web bán trang sức', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Hữu Trường', 201, 'DH52001904', 'D20_TH01', NULL, 'DH52001904@student.stu.edu.vn', '0855021202', 4, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Tăng Cẩm Đạt', 202, 'DH52110786', 'D21_TH08', NULL, NULL, NULL, 26, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Duy Bản', 203, 'DH52101856', 'D21_TH03', NULL, 'DH52101856@student.stu.edu.vn', '0342271703', 13, 'Website quản lý kho', NULL, NULL, 'LAM002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Ngô Duy Tùng', 204, 'DH52104582', 'D21_TH03', NULL, 'DH52104582@student.stu.edu.vn', '0946809362', 16, 'Website quản lý kho', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Lâm Tuấn Khoa', 205, 'DH52006575', 'D20_TH09', NULL, 'DH52006575@student.stu.edu.vn', '0355002372', 11, 'Xây dựng website quản lý thư viện', NULL, NULL, 'ĐUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Trần Vân Uyển', 206, 'DH52006237', 'D20_TH09', NULL, 'DH52006237@student.stu.edu.vn', '0963476850', 10, 'Xây dựng web bán trang sức', NULL, NULL, 'TUNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nhữ Quốc Anh', 207, 'DH52104887', 'D21_TH05', NULL, 'DH52104887@student.stu.edu.vn', '0856143299', 16, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Phạm Nguyễn Hoàng Khang', 208, 'DH52005891', 'D20_TH07', NULL, 'DH52005891@student.stu.edu.vn', '0833485997', 10, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Đinh Phạm Phú Khang', 209, 'DH52108453', 'D21_TH05', NULL, 'DH52108453@student.stu.edu.vn', '0778715658', 21, 'Ứng dụng trên Web', NULL, NULL, 'LAM001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Huỳnh Trần Anh Quốc', 210, 'DH52003145', 'D20_TH01', NULL, NULL, NULL, 6, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'ĐUC002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Quốc Đại', 211, 'DH52110742', 'D21_TH14', NULL, 'DH52110742@student.stu.edu.vn', '0898366249', 25, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Đào Thành Đạt', 212, 'DH52005747', 'D20_TH06', NULL, 'DH52005747@student.stu.edu.vn', '0522939018', 9, 'Ứng dụng trên Web', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Lê Trung Nam', 213, 'DH52001037', 'D20_TH01', NULL, NULL, NULL, 3, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'BACH001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Văn Huy', 214, 'DH52107926', 'D21_TH05', NULL, NULL, NULL, 20, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'DUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Nguyễn Thành Tỷ Phú', 215, 'Dh52111509', 'D21_TH10', NULL, 'DH52111509@student.stu.edu.vn', '0767392039', 37, 'Xây dựng web bán trang sức', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Phan Văn Việt', 216, 'DH52003563', 'D20_TH03', NULL, 'DH52003563@student.stu.edu.vn', '0934487805', 7, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'TRUO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Hình Tân Hiệp', 217, 'DH51903563', 'D19-TH05', NULL, NULL, NULL, 1, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Lê Tôn Bảo', 218, 'DH52110593', 'D21_TH13', NULL, 'DH52110593@student.stu.edu.vn', '0949965772', 24, 'Xây dựng website bán quần áo', NULL, NULL, 'ĐUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 11, 1),
('Mai Trần Duy Anh', 219, 'DH52110553', 'D21_TH13', NULL, 'DH52110553@student.stu.edu.vn', '0947657637', 23, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Trịnh Phát Đạt', 220, 'DH52110793', 'D21_TH08', NULL, 'DH52110793@student.stu.edu.vn', '0977336644', 26, 'WEBSITE tin tức', NULL, NULL, 'THIN001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Nguyễn Đình Hòa', 221, 'DH52110935', 'D21_TH13', NULL, 'DH52110935@student.stu.edu.vn', '0888254294', 28, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'VU001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Nguyễn Đăng Quyền', 222, 'DH52111637', 'D21_TH10', NULL, 'DH52111637@student.stu.edu.vn', '0815804376', 39, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'LAM001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Nguyễn Đăng Hải', 223, 'DH52110857', 'D21_TH08', NULL, 'DH52110857@student.stu.edu.vn', '0909523075', 27, 'Website quản lý kho', NULL, NULL, 'VU001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Bùi Hữu Thuận', 224, 'DH52111843', 'D21_TH07', NULL, NULL, NULL, 42, 'Ứng dụng Java', NULL, NULL, 'KHA001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Trần Hoàng Minh', 225, 'DH52111321', 'D21_TH07', NULL, NULL, NULL, 34, 'Ứng dụng trên Web', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Trần Đông Vi', 226, 'DH51904876', 'D19_TH02', NULL, NULL, NULL, 2, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'KIET001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Đinh Nguyễn Tuấn', 227, 'DH52107697', 'D21_TH03', NULL, 'DH52107697@student.stu.edu.vn', '0976588770', 19, 'Ứng dụng trên Mobile', NULL, NULL, 'ĐUC001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Ngô Định Thế', 228, 'DH51904519', 'D19_TH05', NULL, NULL, NULL, 1, 'Website quản lý kho', NULL, NULL, 'NGHI001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Lương Hiếu Thuận', 229, 'DH52111847', 'D21_TH08', NULL, 'DH52111847@student.stu.edu.vn', '0965629532', 42, 'Xây dựng web bán đồ ăn vặt', NULL, NULL, 'KIET001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Hoàng Hữu Lê Chinh', 230, 'DH52108517', 'D21_TH05', NULL, 'DH52108517@student.stu.edu.vn', '0898671245', 21, 'Xây dựng website quản lý phòng trọ', NULL, NULL, 'VU002', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Võ Thành Long', 231, 'DH52111245', 'D21_TH10', NULL, 'DH52111245@student.stu.edu.vn', '0937369772', 33, 'WEBSITE tin tức', NULL, NULL, 'VINH001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Lê Nguyễn Trọng Tài', 232, 'DH52111682', 'D21_TH14', NULL, NULL, NULL, 39, 'Xây dựng website quản lý thư viện', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Thái Tấn Tài', 233, 'DH52111700', 'D21_TH09', NULL, 'DH52111700@student.stu.edu.vn', '0353004163', 40, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'BANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Thị Minh Thư', 234, 'DH52111863', 'D21_TH10', NULL, 'DH52111863@student.stu.edu.vn', '097473170', 42, 'Xây dựng web bán trang sức', NULL, NULL, 'TRUO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Nguyễn Thị Nhung', 235, 'DH52111441', 'D21_TH09', NULL, 'DH52111441@student.stu.edu.vn', '0359439628', 35, 'Xây dựng web bán trang sức', NULL, NULL, 'KHA001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Nguyễn Anh Quốc', 236, 'DH52104425', 'D21-TH07', NULL, NULL, NULL, 16, 'Website quản lý kho', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Trần Minh Tú', 237, 'DH52107408', 'D21_TH02', NULL, 'DH52107408@student.stu.edu.vn', '0772911890', 19, 'Ứng dụng trên Web', NULL, NULL, 'HA421', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Quách Thái Hùng', 238, 'DH52101465', 'D21_TH02', NULL, 'DH52101465@student.stu.edu.vn', '0947252595', 13, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 13, 1),
('Phạm Thị Ánh Hồng', 239, 'DH52101979', 'D21_TH02', NULL, 'DH52101979@student.stu.edu.vn', '0976747106', 14, 'Xây dựng app quản lý bán hàng cho quán cafe', NULL, NULL, 'LONG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 8, 1),
('Nguyễn Đình Vinh', 240, 'DH52112079', 'D21_TH14', NULL, 'DH52112079@student.stu.edu.vn', '0383731640', 44, 'Ứng dụng quản lý quán cà phê/nhà hàng (POS System)', NULL, NULL, 'ƯNG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 14, 1),
('Nguyễn Ngọc Ân', 241, 'DH52110581', 'D21_TH13', NULL, 'DH52110581@student.stu.edu.vn', '0921266924', 23, 'Xây dựng website bán linh kiện máy tính', NULL, NULL, 'SANG001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1),
('Ong Văn Mến', 242, 'DH52111293', 'D21_TH12', NULL, 'DH52111293@student.stu.edu.vn', '0933331843', 34, 'Xây dựng ứng dụng học ngoại ngữ', NULL, NULL, 'ĐEO001', '2025-12-12 08:10:53', '123456', 0, NULL, NULL, 12, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giang_vien`
--

DROP TABLE IF EXISTS `giang_vien`;
CREATE TABLE IF NOT EXISTS `giang_vien` (
  `MaGV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DienThoai` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Khoa` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ChucVu` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `MatKhau` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '123456',
  `id` int(11) DEFAULT NULL,
  PRIMARY KEY (`MaGV`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_hoten` (`HoTen`),
  KEY `idx_khoa` (`Khoa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `giang_vien`
--

INSERT INTO `giang_vien` (`MaGV`, `HoTen`, `Email`, `DienThoai`, `Khoa`, `ChucVu`, `created_at`, `MatKhau`, `id`) VALUES
('BACH001', 'Ngô Xuân Bách', NULL, '0933535435', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('BANG001', 'Bùi Nhật Bằng', NULL, '0908537246', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('DUC001', 'Đoàn Trình Dục', NULL, '0346844259', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('DUNG001', 'Lê Thị Mỹ Dung', NULL, '0983228426', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('DUY001', 'Trịnh Thanh Duy', NULL, '0903124481', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('ĐEO001', 'Dương Văn Đeo', NULL, '0915597407', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('ĐUC001', 'Huỳnh Quang Đức', NULL, '0983937975', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('ĐUC002', 'Lê Triệu Ngọc Đức', NULL, '0909723222', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('HA421', 'Nguyễn Thị Ngân Hà', NULL, '0969224686', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('HUNG001', 'Trần Văn Hùng', NULL, '0903762632', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('KHA001', 'Hồ Đình Khả', NULL, '0945775532', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('KIET001', 'Nguyễn Thường Kiệt', NULL, '0125792004', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('LAM001', 'Khuất Bá Duy Lâm', NULL, '0985177146', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('LAM002', 'Nguyễn Ngọc Lâm', NULL, '0666299796', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('LONG001', 'Nguyễn Hồng Bửu Long', NULL, '0985187289', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('NGHI001', 'Nguyễn Trọng Nghĩa', NULL, '0908497757', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('SANG001', 'Nguyễn Minh Sang', NULL, '0934041469', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('THIN001', 'Nguyễn Trần Phúc Thịnh', NULL, '0919304021', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('TRUO001', 'Trần Quốc Trường', NULL, '0908855654', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('TUNG001', 'Nguyễn Thanh Tùng', NULL, '0935210058', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('ƯNG001', 'Trần Vũ Hoàng Ưng', NULL, '0837618279', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('VINH001', 'Lương An Vinh', NULL, '0935288419', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('VU001', 'Hà Anh Vũ', NULL, '0908663380', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL),
('VU002', 'Mai Vân Phương Vũ', NULL, '0909858859', 'CNTT', 'Giảng viên', '2025-12-04 18:21:49', '123456', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoidong`
--

DROP TABLE IF EXISTS `hoidong`;
CREATE TABLE IF NOT EXISTS `hoidong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaGV` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ten_hoidong` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chu_tich` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thu_ky` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uy_vien1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uy_vien2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phong` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thoigian` datetime DEFAULT NULL,
  `ghi_chu` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `MaGV` (`MaGV`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hoidong`
--

INSERT INTO `hoidong` (`id`, `MaGV`, `ten_hoidong`, `chu_tich`, `thu_ky`, `uy_vien1`, `uy_vien2`, `phong`, `thoigian`, `ghi_chu`) VALUES
(8, NULL, 'Hội đồng 1', 'Bùi Nhật Bằng', 'Dương Văn Đeo', 'Đoàn Trình Dục', 'Hà Anh Vũ', '103', '2025-12-20 19:48:00', NULL),
(9, NULL, 'Hội đồng 2', 'Hồ Đình Khả', 'Huỳnh Quang Đức', 'Khuất Bá Duy Lâm', 'Lê Thị Mỹ Dung', '303', '2025-12-20 19:48:00', NULL),
(10, NULL, 'Hội đồng 3', 'Bùi Nhật Bằng', 'Dương Văn Đeo', 'Hồ Đình Khả', 'Nguyễn Ngọc Lâm', '304', '2025-12-21 19:50:00', NULL),
(11, NULL, 'Hội đồng 4', 'Nguyễn Thị Ngân Hà', 'Nguyễn Thường Kiệt', 'Nguyễn Ngọc Lâm', 'Nguyễn Hồng Bửu Long', '102', '2025-12-22 19:51:00', NULL),
(12, NULL, 'Hội đồng 5', 'Lê Triệu Ngọc Đức', 'Nguyễn Ngọc Lâm', 'Đoàn Trình Dục', 'Huỳnh Quang Đức', '302', '2025-12-24 19:51:00', NULL),
(13, NULL, 'Hội đồng 6', 'Nguyễn Minh Sang', 'Mai Vân Phương Vũ', 'Nguyễn Ngọc Lâm', 'Nguyễn Thanh Tùng', '404', '2025-12-20 19:55:00', NULL),
(14, NULL, 'Hội đồng 7', 'Mai Vân Phương Vũ', 'Lê Triệu Ngọc Đức', 'Nguyễn Thanh Tùng', 'Nguyễn Minh Sang', '304', '2025-12-21 20:06:00', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhiem_vu`
--

DROP TABLE IF EXISTS `nhiem_vu`;
CREATE TABLE IF NOT EXISTS `nhiem_vu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nhom_id` int(11) NOT NULL,
  `MaGV` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tieu_de` text COLLATE utf8mb4_unicode_ci,
  `ngay_giao` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `noi_dung` text COLLATE utf8mb4_unicode_ci,
  `file_word` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `MaGV` (`MaGV`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhiem_vu`
--

INSERT INTO `nhiem_vu` (`id`, `nhom_id`, `MaGV`, `tieu_de`, `ngay_giao`, `deadline`, `noi_dung`, `file_word`, `created_at`) VALUES
(3, 17, 'DUC001', 'Web bán quần áo', '2025-12-12', '2025-12-13', 'Xây dựng CSDL\nXây dựng website theo công nghệ đã tìm hiểu', 'NhiemVu_Nhom17_20251212_065515.doc', '2025-12-12 13:55:15'),
(4, 9, 'BANG001', '', '2025-12-12', '0000-00-00', 'Xây dựng CSDL', 'NhiemVu_Nhom9_20251212_083526.doc', '2025-12-12 15:35:26'),
(5, 10, 'BANG001', '', '2025-12-12', '2025-12-13', 'Tìm hiểu các công nghệ lập trình web\nXây dựng CSDL\nTìm hiểu laravel', 'NhiemVu_Nhom10_20251212_083754.doc', '2025-12-12 15:37:54'),
(7, 11, 'BANG001', '', '2025-12-12', '2025-12-13', 'Tìm hiểu về React', 'NhiemVu_Nhom11_20251212_083849.doc', '2025-12-12 15:38:49'),
(8, 12, 'BANG001', '', '2025-12-12', '2025-12-13', 'xây dựng giao diện hoàn chỉnh', 'NhiemVu_Nhom12_20251212_083914.doc', '2025-12-12 15:39:14'),
(9, 13, 'BANG001', '', '2025-12-12', '2025-12-13', 'viết thêm các chức năng', 'NhiemVu_Nhom13_20251212_083940.doc', '2025-12-12 15:39:40'),
(11, 8, 'DUC001', '', '2025-12-12', '2025-12-13', 'Xây dựng website theo công nghệ đã tìm hiểu', 'NhiemVu_Nhom8_20251212_085639.doc', '2025-12-12 15:56:39'),
(12, 16, 'DUC001', '', '2025-12-12', '0000-00-00', 'Xây dựng website theo công nghệ đã tìm hiểu', 'NhiemVu_Nhom16_20251212_085717.doc', '2025-12-12 15:57:17'),
(14, 25, 'DUC001', '', '2025-12-12', '2025-12-13', 'Xây dựng website theo công nghệ đã tìm hiểu', 'NhiemVu_Nhom25_20251212_085741.doc', '2025-12-12 15:57:41'),
(15, 26, 'DUNG001', '', '2025-12-12', '2025-12-13', 'Xây dựng website theo công nghệ đã tìm hiểu', 'NhiemVu_Nhom26_20251212_090237.doc', '2025-12-12 16:02:37'),
(16, 7, 'DUC001', 'rô bờ lốc', '2025-12-27', '2025-12-31', 'Tìm hiểu nghiệp vụ\nXây dựng CSDL\nXây dựng website\nTìm hiểu nghiệp vụ một cửa hàng bán cá cảnh và cây thủy sinh\nTìm hiểu các công nghệ lập trình web\nXây dựng CSDL\nXây dựng website theo công nghệ đã tìm hiểu', 'NhiemVu_Nhom7_20251227_173028.docx', '2025-12-28 00:30:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhom`
--

DROP TABLE IF EXISTS `nhom`;
CREATE TABLE IF NOT EXISTS `nhom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_nhom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `giang_vien_huong_dan_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `huong_de_tai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `da_gap_gv` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_gv` (`giang_vien_huong_dan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhom`
--

INSERT INTO `nhom` (`id`, `ten_nhom`, `giang_vien_huong_dan_id`, `huong_de_tai`, `created_at`, `da_gap_gv`) VALUES
(1, 'Nhóm mặc định', 'BANG001', NULL, '2025-12-11 17:24:17', 1),
(2, 'Nhóm mặc định', 'DUC001', NULL, '2025-12-11 17:26:16', 1),
(3, 'Nhóm mặc định', 'VU001', NULL, '2025-12-11 17:26:38', 0),
(4, 'Nhóm mặc định', 'ĐUC002', NULL, '2025-12-11 17:27:00', 0),
(5, 'Nhóm mặc định', 'BACH001', NULL, '2025-12-11 17:27:08', 0),
(6, 'Nhóm mặc định', 'NGHI001', NULL, '2025-12-11 17:46:52', 0),
(7, 'Nhóm 1', 'DUC001', NULL, '2025-12-11 18:22:34', 1),
(8, 'Nhóm 2', 'DUC001', NULL, '2025-12-11 18:23:09', 1),
(9, 'Nhóm 1', 'BANG001', NULL, '2025-12-11 18:23:57', 1),
(10, 'Nhóm 2', 'BANG001', NULL, '2025-12-11 18:24:04', 1),
(11, 'nhóm 3', 'BANG001', NULL, '2025-12-11 18:24:37', 1),
(12, 'Nhóm 4', 'BANG001', NULL, '2025-12-11 18:24:54', 1),
(13, 'Nhóm 5', 'BANG001', NULL, '2025-12-11 18:25:10', 1),
(14, 'Nhóm 6', 'BANG001', NULL, '2025-12-11 18:25:32', 1),
(15, 'Nhóm 7', 'BANG001', NULL, '2025-12-11 18:25:46', 1),
(16, 'Nhóm 3', 'DUC001', NULL, '2025-12-12 01:35:56', 1),
(17, 'Nhóm 4', 'DUC001', NULL, '2025-12-12 01:36:09', 1),
(18, 'Nhóm mặc định', 'ĐEO001', NULL, '2025-12-12 08:18:25', 0),
(19, 'Nhóm mặc định', 'KHA001', NULL, '2025-12-12 08:19:46', 0),
(20, 'Nhóm mặc định', 'ĐUC001', NULL, '2025-12-12 08:19:58', 0),
(21, 'Nhóm mặc định', 'LAM001', NULL, '2025-12-12 08:20:14', 0),
(22, 'Nhóm mặc định', 'DUNG001', NULL, '2025-12-12 08:20:25', 0),
(23, 'Nhóm mặc định', 'VINH001', NULL, '2025-12-12 08:21:15', 0),
(24, 'Nhóm mặc định', 'LONG001', NULL, '2025-12-12 08:22:02', 0),
(25, 'Nhóm 5', 'DUC001', NULL, '2025-12-12 08:52:56', 1),
(26, 'Nhóm 1', 'DUNG001', NULL, '2025-12-12 09:00:07', 0),
(27, 'Nhóm 2', 'DUNG001', NULL, '2025-12-12 09:00:16', 0),
(28, 'Nhóm 3', 'DUNG001', NULL, '2025-12-12 09:00:24', 0),
(29, 'Nhóm 4', 'DUNG001', NULL, '2025-12-12 09:00:31', 0),
(30, 'Nhóm 5', 'DUNG001', NULL, '2025-12-12 09:00:40', 0),
(31, 'Nhóm 1', 'ĐEO001', NULL, '2025-12-12 09:04:54', 0),
(32, 'Nhóm 2', 'ĐEO001', NULL, '2025-12-12 09:04:59', 0),
(33, 'Nhóm 3', 'ĐEO001', NULL, '2025-12-12 09:05:06', 0),
(34, 'Nhóm 4', 'ĐEO001', NULL, '2025-12-12 09:05:16', 0),
(35, 'Nhóm 5', 'ĐEO001', NULL, '2025-12-12 09:05:23', 0),
(36, 'Nhóm 1', 'VU001', NULL, '2025-12-12 09:07:28', 0),
(37, 'Nhóm 2', 'VU001', NULL, '2025-12-12 09:07:32', 0),
(38, 'Nhóm 3', 'VU001', NULL, '2025-12-12 09:07:42', 0),
(39, 'Nhóm 4', 'VU001', NULL, '2025-12-12 09:07:48', 0),
(40, 'Nhóm 5', 'VU001', NULL, '2025-12-12 09:07:54', 0),
(41, 'Nhóm 1', 'KHA001', NULL, '2025-12-12 09:10:44', 0),
(42, 'Nhóm 2', 'KHA001', NULL, '2025-12-12 09:10:49', 0),
(43, 'Nhóm 3', 'KHA001', NULL, '2025-12-12 09:10:56', 0),
(44, 'Nhóm 4', 'KHA001', NULL, '2025-12-12 09:11:03', 0),
(45, 'Nhóm 5', 'KHA001', NULL, '2025-12-12 09:11:10', 0),
(46, 'Nhóm 1', 'ĐUC001', NULL, '2025-12-12 09:12:42', 0),
(47, 'Nhóm 2', 'ĐUC001', NULL, '2025-12-12 09:12:46', 0),
(48, 'Nhóm 4', 'ĐUC001', NULL, '2025-12-12 09:12:53', 0),
(49, 'Nhóm 3', 'ĐUC001', NULL, '2025-12-12 09:13:04', 0),
(50, 'Nhóm 5', 'ĐUC001', NULL, '2025-12-12 09:13:17', 0),
(51, 'Nhóm 1', 'LAM001', NULL, '2025-12-12 09:15:14', 0),
(52, 'Nhóm 2', 'LAM001', NULL, '2025-12-12 09:15:20', 0),
(53, 'Nhóm 3', 'LAM001', NULL, '2025-12-12 09:15:27', 0),
(54, 'Nhóm 4', 'LAM001', NULL, '2025-12-12 09:15:33', 0),
(55, 'Nhóm 5', 'LAM001', NULL, '2025-12-12 09:15:41', 0),
(56, 'Nhóm 1', 'ĐUC002', NULL, '2025-12-12 09:17:15', 0),
(57, 'Nhóm 2', 'ĐUC002', NULL, '2025-12-12 09:17:19', 0),
(58, 'Nhóm 3', 'ĐUC002', NULL, '2025-12-12 09:17:25', 0),
(59, 'Nhóm 4', 'ĐUC002', NULL, '2025-12-12 09:17:32', 0),
(60, 'Nhóm 5', 'ĐUC002', NULL, '2025-12-12 09:17:38', 0),
(61, 'Nhóm 1', 'VINH001', NULL, '2025-12-12 09:19:28', 1),
(62, 'Nhóm 2', 'VINH001', NULL, '2025-12-12 09:19:35', 1),
(63, 'Nhóm 4', 'VINH001', NULL, '2025-12-12 09:19:42', 1),
(64, 'Nhóm 3', 'VINH001', NULL, '2025-12-12 09:19:50', 1),
(65, 'Nhóm 5', 'VINH001', NULL, '2025-12-12 09:19:55', 1),
(66, 'Nhóm 1', 'BACH001', NULL, '2025-12-12 09:21:34', 0),
(67, 'Nhóm 2', 'BACH001', NULL, '2025-12-12 09:21:38', 0),
(68, 'Nhóm 3', 'BACH001', NULL, '2025-12-12 09:21:49', 0),
(69, 'Nhóm 4', 'BACH001', NULL, '2025-12-12 09:22:01', 0),
(70, 'Nhóm 5', 'BACH001', NULL, '2025-12-12 09:22:11', 0),
(71, 'Nhóm 1', 'LONG001', NULL, '2025-12-12 09:23:27', 0),
(72, 'Nhóm 2', 'LONG001', NULL, '2025-12-12 09:23:32', 0),
(73, 'Nhóm 3', 'LONG001', NULL, '2025-12-12 09:23:39', 0),
(74, 'Nhóm 4', 'LONG001', NULL, '2025-12-12 09:23:46', 0),
(75, 'Nhóm 5', 'LONG001', NULL, '2025-12-12 09:23:54', 0),
(76, 'Nhóm mặc định', 'VU002', NULL, '2025-12-20 14:58:38', 0),
(77, 'Nhóm 1', 'VU002', NULL, '2025-12-20 18:16:53', 0),
(78, 'Nhóm 2', 'VU002', NULL, '2025-12-20 18:17:00', 0),
(79, 'Nhóm 3', 'VU002', NULL, '2025-12-20 18:17:07', 0),
(80, 'Nhóm 4', 'VU002', NULL, '2025-12-20 18:17:15', 0),
(81, 'Nhóm 5', 'VU002', NULL, '2025-12-20 18:17:23', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phancong_gvhd`
--

DROP TABLE IF EXISTS `phancong_gvhd`;
CREATE TABLE IF NOT EXISTS `phancong_gvhd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaSV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaGV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayPhan` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `MaSV` (`MaSV`),
  KEY `fk_pc_gv` (`MaGV`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phancong_gvhd`
--

INSERT INTO `phancong_gvhd` (`id`, `MaSV`, `MaGV`, `NgayPhan`) VALUES
(1, 'DH52111843', 'BANG001', '2025-12-11 03:02:52'),
(2, 'DH52103682', 'BANG001', '2025-12-11 03:02:52'),
(3, 'DH52106130', 'BANG001', '2025-12-11 03:02:52'),
(8, 'DH52111704', 'BANG001', '2025-12-11 03:02:52'),
(9, 'DH52108711', 'BANG001', '2025-12-11 03:02:52'),
(10, 'DH52005747', 'BANG001', '2025-12-11 03:02:52'),
(11, 'DH52107697', 'ĐEO001', '2025-12-11 03:28:28'),
(12, 'DH52108453', 'ĐEO001', '2025-12-11 03:28:28'),
(13, 'DH52112786', 'ĐEO001', '2025-12-11 03:28:28'),
(16, 'DH52111112', 'ĐEO001', '2025-12-11 03:28:28'),
(17, 'DH52108380', 'ĐEO001', '2025-12-11 03:28:28'),
(18, 'DH52111224', 'ĐEO001', '2025-12-11 03:28:28'),
(19, 'DH52108862', 'ĐEO001', '2025-12-11 03:28:28'),
(20, 'DH51903563', 'ĐEO001', '2025-12-11 03:28:28'),
(21, 'DH52111439', 'BANG001', '2025-12-11 22:05:23'),
(23, 'DH52111171', 'BANG001', '2025-12-11 22:05:24'),
(25, 'DH52111445', 'BANG001', '2025-12-11 22:05:24'),
(26, 'DH52000682', 'BANG001', '2025-12-11 22:05:24'),
(27, 'DH52112127', 'BANG001', '2025-12-11 22:05:24'),
(28, 'DH52111531', 'BANG001', '2025-12-11 22:05:24'),
(29, 'DH52111115', 'BANG001', '2025-12-11 22:05:24'),
(30, 'DH52111491', 'BANG001', '2025-12-11 22:05:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phan_cong_phan_bien`
--

DROP TABLE IF EXISTS `phan_cong_phan_bien`;
CREATE TABLE IF NOT EXISTS `phan_cong_phan_bien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nhom_id` int(11) NOT NULL,
  `giang_vien_phan_bien_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngay_phan_cong` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `nhom_id` (`nhom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phan_thuoc_nhom`
--

DROP TABLE IF EXISTS `phan_thuoc_nhom`;
CREATE TABLE IF NOT EXISTS `phan_thuoc_nhom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nhom_id` int(11) NOT NULL,
  `sinh_vien_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sv_nhom` (`nhom_id`,`sinh_vien_id`),
  KEY `phan_thuoc_nhom_ibfk_2` (`sinh_vien_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1009 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phan_thuoc_nhom`
--

INSERT INTO `phan_thuoc_nhom` (`id`, `nhom_id`, `sinh_vien_id`) VALUES
(930, 7, 124),
(929, 7, 161),
(932, 8, 162),
(931, 8, 169),
(912, 9, 210),
(911, 9, 217),
(914, 10, 133),
(913, 10, 213),
(916, 11, 201),
(915, 11, 228),
(917, 12, 159),
(918, 12, 172),
(919, 13, 134),
(920, 13, 226),
(933, 16, 150),
(934, 16, 183),
(936, 17, 119),
(935, 17, 239),
(814, 25, 176),
(819, 25, 238),
(970, 26, 128),
(969, 26, 170),
(972, 27, 136),
(971, 27, 137),
(973, 28, 129),
(974, 28, 130),
(862, 30, 121),
(869, 30, 231),
(863, 30, 233),
(922, 31, 205),
(921, 31, 212),
(924, 32, 146),
(923, 32, 191),
(925, 33, 168),
(926, 33, 173),
(927, 34, 206),
(928, 34, 208),
(810, 35, 190),
(802, 35, 216),
(938, 36, 204),
(937, 36, 227),
(940, 37, 157),
(939, 37, 203),
(941, 38, 200),
(942, 38, 207),
(944, 39, 123),
(943, 39, 160),
(830, 40, 196),
(823, 40, 237),
(945, 41, 144),
(946, 41, 209),
(948, 42, 145),
(947, 42, 178),
(950, 43, 141),
(949, 43, 230),
(951, 44, 197),
(952, 44, 214),
(839, 45, 184),
(840, 45, 225),
(954, 46, 182),
(953, 46, 224),
(956, 47, 195),
(955, 47, 223),
(959, 48, 202),
(960, 48, 220),
(958, 49, 125),
(957, 49, 181),
(848, 50, 132),
(849, 50, 151),
(961, 51, 179),
(962, 51, 192),
(963, 52, 127),
(964, 52, 229),
(966, 53, 131),
(965, 53, 135),
(967, 54, 122),
(968, 54, 235),
(860, 55, 164),
(854, 55, 177),
(852, 55, 180),
(975, 56, 187),
(976, 56, 188),
(977, 57, 153),
(978, 57, 154),
(979, 58, 193),
(980, 58, 222),
(981, 59, 126),
(982, 59, 215),
(873, 60, 166),
(877, 60, 234),
(983, 61, 147),
(984, 61, 156),
(986, 62, 120),
(985, 62, 219),
(990, 63, 149),
(989, 63, 189),
(987, 64, 241),
(988, 64, 242),
(881, 65, 152),
(886, 65, 186),
(1001, 66, 185),
(1002, 66, 198),
(1003, 67, 232),
(1004, 67, 236),
(1006, 68, 167),
(1005, 68, 240),
(1008, 69, 163),
(1007, 69, 211),
(908, 70, 155),
(901, 70, 171),
(992, 77, 138),
(991, 77, 140),
(993, 78, 199),
(994, 78, 218),
(995, 79, 139),
(996, 79, 194),
(998, 80, 174),
(997, 80, 221),
(1000, 81, 158),
(999, 81, 175);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qlsv_bienban`
--

DROP TABLE IF EXISTS `qlsv_bienban`;
CREATE TABLE IF NOT EXISTS `qlsv_bienban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaSV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenFile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayNop` date NOT NULL,
  `GhiChu` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `MaSV` (`MaSV`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `qlsv_bienban`
--

INSERT INTO `qlsv_bienban` (`id`, `MaSV`, `TenFile`, `NgayNop`, `GhiChu`, `created_at`) VALUES
(5, 'DH52106130', '1765466651_BaoCao_DoAn_ChiTiet.docx', '2025-12-11', 'aaaaaaaaaaaaaa', '2025-12-11 15:24:11'),
(6, 'DH52103682', '1765528394_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 08:33:14'),
(7, 'DH52111843', '1765528394_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 08:33:14'),
(8, 'DH52106130', '1765528419_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 08:33:39'),
(9, 'DH52111704', '1765528419_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 08:33:39'),
(10, 'DH52108711', '1765528440_BaoCao_DoAn_ChiTiet.docx', '2025-12-12', '', '2025-12-12 08:34:00'),
(11, 'DH52005747', '1765528440_BaoCao_DoAn_ChiTiet.docx', '2025-12-12', '', '2025-12-12 08:34:00'),
(12, 'DH52108453', '1765528456_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 08:34:16'),
(13, 'DH52107697', '1765528456_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 08:34:16'),
(14, 'DH52111112', '1765528474_B__o_c__o_________n_CNPM.docx', '2025-12-12', '', '2025-12-12 08:34:34'),
(15, 'DH52112786', '1765528474_B__o_c__o_________n_CNPM.docx', '2025-12-12', '', '2025-12-12 08:34:34'),
(16, 'DH52111171', '1765529646_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 08:54:06'),
(17, 'DH52006575', '1765529646_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 08:54:06'),
(18, 'Dh52113292', '1765529678_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 08:54:38'),
(19, 'DH52111756', '1765529678_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 08:54:38'),
(20, 'DH52111401', '1765529691_BaoCao_DoAn_ChiTiet.docx', '2025-12-12', '', '2025-12-12 08:54:51'),
(21, 'DH52111682', '1765529691_BaoCao_DoAn_ChiTiet.docx', '2025-12-12', '', '2025-12-12 08:54:51'),
(22, 'DH52112944', '1765529706_B__o_c__o_________n_CNPM.docx', '2025-12-12', '', '2025-12-12 08:55:06'),
(23, 'DH52111681', '1765529706_B__o_c__o_________n_CNPM.docx', '2025-12-12', '', '2025-12-12 08:55:06'),
(24, 'DH52104298', '1765529718_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 08:55:18'),
(25, 'DH52110593', '1765529718_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 08:55:18'),
(26, 'DH52101228', '1765530082_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 09:01:22'),
(27, 'DH52006237', '1765530082_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 09:01:22'),
(28, 'DH52113745', '1765530091_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 09:01:31'),
(29, 'Dh52111509', '1765530091_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 09:01:31'),
(30, 'DH52107801', '1765530113_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 09:01:53'),
(31, 'DH52111863', '1765530113_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 09:01:53'),
(32, 'DH52111118', '1765530122_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 09:02:02'),
(33, 'DH52111441', '1765530122_BaoCao_DoAn_WebPhim.docx', '2025-12-12', '', '2025-12-12 09:02:02'),
(34, 'DH52002202', '1765530141_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 09:02:21'),
(35, 'DH52106804', '1765530141_BaoCao_DoAn_DaMoRong.docx', '2025-12-12', '', '2025-12-12 09:02:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qlsv_diem`
--

DROP TABLE IF EXISTS `qlsv_diem`;
CREATE TABLE IF NOT EXISTS `qlsv_diem` (
  `MaSV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaMH` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NamHoc` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HocKy` int(11) NOT NULL,
  `DiemLan1` float DEFAULT NULL,
  `DiemLan2` float DEFAULT NULL,
  `DiemLan3` float DEFAULT NULL,
  PRIMARY KEY (`MaSV`,`MaMH`,`NamHoc`,`HocKy`),
  KEY `fk_diem_monhoc` (`MaSV`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `qlsv_diem`
--

INSERT INTO `qlsv_diem` (`MaSV`, `MaMH`, `NamHoc`, `HocKy`, `DiemLan1`, `DiemLan2`, `DiemLan3`) VALUES
('DH51903563', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH51904519', 'LVTN', '2025', 2, 7.4, 7, 0),
('DH51904876', 'LVTN', '2025', 2, 8.4, 9, 0),
('DH52000682', 'LVTN', '2025', 2, 7.8, 7, 0),
('DH52001037', 'LVTN', '2025', 2, 7.8, 7, 0),
('DH52001281', 'LVTN', '2025', 2, 8.2, 9, 0),
('DH52001330', 'LVTN', '2025', 2, 8.4, 9, 0),
('DH52001900', 'LVTN', '2025', 2, 8.2, 6, 0),
('DH52001904', 'LVTN', '2025', 2, 7.4, 7, 0),
('DH52002202', 'LVTN', '2025', 2, 8.2, 6, 0),
('DH52003145', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH52003489', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH52003543', 'LVTN', '2025', 2, 7.4, 6, 0),
('DH52003563', 'LVTN', '2025', 2, 8.8, 7, 0),
('DH52004272', 'LVTN', '2025', 2, 7.4, 6, 0),
('DH52005747', 'LVTN', '2025', 2, 7.6, 5.5, 0),
('DH52005851', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH52005891', 'LVTN', '2025', 2, 8.2, 10, 0),
('DH52006237', 'LVTN', '2025', 2, 8.2, 10, 0),
('DH52006575', 'LVTN', '2025', 2, 7.6, 5.5, 0),
('DH52007089', 'LVTN', '2025', 2, 7.6, 8, 0),
('DH52007161', 'LVTN', '2025', 2, 8.4, 9, 0),
('DH52007186', 'LVTN', '2025', 2, 8.8, 7, 0),
('DH52101228', 'LVTN', '2025', 2, 7.4, 7, 0),
('DH52101465', 'LVTN', '2025', 2, 7.6, 7, 0),
('DH52101856', 'LVTN', '2025', 2, 8.2, 8, 0),
('DH52101979', 'LVTN', '2025', 2, 8.2, 9, 0),
('DH52103137', 'LVTN', '2025', 2, 7.6, 7, 0),
('DH52103511', 'LVTN', '2025', 2, 8.4, 9, 0),
('DH52103682', 'LVTN', '2025', 2, 8.6, 8, 0),
('DH52104108', 'LVTN', '2025', 2, 8.2, 8, 0),
('DH52104298', 'LVTN', '2025', 2, 7.4, 9.5, 0),
('DH52104425', 'LVTN', '2025', 2, 7.2, 7.5, 0),
('DH52104582', 'LVTN', '2025', 2, 7.6, 9, 0),
('DH52104887', 'LVTN', '2025', 2, 8, 6.7, 0),
('DH52105312', 'LVTN', '2025', 2, 7, 9, 0),
('DH52105342', 'LVTN', '2025', 2, 8, 7, 0),
('DH52106130', 'LVTN', '2025', 2, 8.6, 8, 0),
('DH52106740', 'LVTN', '2025', 2, 7, 9, 0),
('DH52106804', 'LVTN', '2025', 2, 8, 6.7, 0),
('DH52107203', 'LVTN', '2025', 2, 7.6, 8, 0),
('DH52107408', 'LVTN', '2025', 2, 8, 7, 0),
('DH52107697', 'LVTN', '2025', 2, 7.6, 9, 0),
('DH52107801', 'LVTN', '2025', 2, 8.2, 9, 0),
('DH52107926', 'LVTN', '2025', 2, 8.2, 9, 0),
('DH52108380', 'LVTN', '2025', 2, 7, 7, 0),
('DH52108453', 'LVTN', '2025', 2, 8.2, 8, 0),
('DH52108517', 'LVTN', '2025', 2, 7.4, 7, 0),
('DH52108711', 'LVTN', '2025', 2, 8.2, 8, 0),
('DH52108862', 'LVTN', '2025', 2, 7, 7, 0),
('DH52109230', 'LVTN', '2025', 2, 8.6, 8, 0),
('DH52110534', 'LVTN', '2025', 2, 8, 7, 0),
('DH52110553', 'LVTN', '2025', 2, 8, 7.7, 0),
('DH52110581', 'LVTN', '2025', 2, 7.6, 5, 0),
('DH52110593', 'LVTN', '2025', 2, 7.6, 9, 0),
('DH52110659', 'LVTN', '2025', 2, 7.4, 8, 0),
('DH52110733', 'LVTN', '2025', 2, 7.2, 6.7, 0),
('DH52110742', 'LVTN', '2025', 2, 8, 7.4, 0),
('DH52110780', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH52110786', 'LVTN', '2025', 2, 8.2, 9, 0),
('DH52110793', 'LVTN', '2025', 2, 8.2, 9, 0),
('DH52110812', 'LVTN', '2025', 2, 8.2, 7, 0),
('DH52110857', 'LVTN', '2025', 2, 8, 7, 0),
('DH52110924', 'LVTN', '2025', 2, 6.8, 7, 0),
('DH52110935', 'LVTN', '2025', 2, 7.6, 7.3, 0),
('DH52111067', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH52111085', 'LVTN', '2025', 2, 8.2, 8.5, 0),
('DH52111112', 'LVTN', '2025', 2, 7.8, 6.5, 0),
('DH52111115', 'LVTN', '2025', 2, 7.4, 6, 0),
('DH52111118', 'LVTN', '2025', 2, 7.2, 7, 0),
('DH52111143', 'LVTN', '2025', 2, 7.2, 7, 0),
('DH52111171', 'LVTN', '2025', 2, 7.8, 6.5, 0),
('DH52111204', 'LVTN', '2025', 2, 8.2, 8.5, 0),
('DH52111212', 'LVTN', '2025', 2, 8, 7.7, 0),
('DH52111224', 'LVTN', '2025', 2, 7.6, 9, 0),
('DH52111240', 'LVTN', '2025', 2, 7.8, 8, 0),
('DH52111245', 'LVTN', '2025', 2, 8.4, 5, 0),
('DH52111263', 'LVTN', '2025', 2, 7.6, 6.5, 0),
('DH52111293', 'LVTN', '2025', 2, 7.6, 5, 0),
('DH52111321', 'LVTN', '2025', 2, 8.6, 8, 0),
('DH52111401', 'LVTN', '2025', 2, 7.2, 9, 0),
('DH52111411', 'LVTN', '2025', 2, 8, 6, 0),
('DH52111439', 'LVTN', '2025', 2, 7.6, 9, 0),
('DH52111441', 'LVTN', '2025', 2, 7.4, 7.6, 0),
('DH52111445', 'LVTN', '2025', 2, 7.6, 6.6, 0),
('DH52111482', 'LVTN', '2025', 2, 8, 6, 0),
('DH52111486', 'LVTN', '2025', 2, 7.4, 7.6, 0),
('DH52111491', 'LVTN', '2025', 2, 7.4, 6, 0),
('Dh52111509', 'LVTN', '2025', 2, 7.2, 6.7, 0),
('DH52111529', 'LVTN', '2025', 2, 7.4, 8, 0),
('DH52111531', 'LVTN', '2025', 2, 7.2, 6, 0),
('DH52111579', 'LVTN', '2025', 2, 8.4, 5, 0),
('DH52111612', 'LVTN', '2025', 2, 8.4, 6, 0),
('DH52111637', 'LVTN', '2025', 2, 7, 8.8, 0),
('DH52111681', 'LVTN', '2025', 2, 7, 6, 0),
('DH52111682', 'LVTN', '2025', 2, 7.2, 7.5, 0),
('DH52111695', 'LVTN', '2025', 2, 6.8, 7, 0),
('DH52111700', 'LVTN', '2025', 2, 8.4, 5, 0),
('DH52111704', 'LVTN', '2025', 2, 7.8, 6, 0),
('DH52111720', 'LVTN', '2025', 2, 7, 8.8, 0),
('DH52111756', 'LVTN', '2025', 2, 7.6, 9, 0),
('DH52111794', 'LVTN', '2025', 2, 7.2, 6, 0),
('DH52111843', 'LVTN', '2025', 2, 7.4, 9.5, 0),
('DH52111847', 'LVTN', '2025', 2, 7.6, 6.6, 0),
('DH52111863', 'LVTN', '2025', 2, 8.4, 6, 0),
('DH52111881', 'LVTN', '2025', 2, 8, 6, 0),
('DH52111976', 'LVTN', '2025', 2, 7.6, 7.3, 0),
('DH52112002', 'LVTN', '2025', 2, 6.4, 9.2, 0),
('DH52112019', 'LVTN', '2025', 2, 7.8, 6, 0),
('DH52112079', 'LVTN', '2025', 2, 7.6, 6.5, 0),
('DH52112118', 'LVTN', '2025', 2, 8.4, 7, 0),
('DH52112127', 'LVTN', '2025', 2, 7.8, 6, 0),
('DH52112786', 'LVTN', '2025', 2, 7, 6, 0),
('DH52112944', 'LVTN', '2025', 2, 7.8, 6, 0),
('DH52113016', 'LVTN', '2025', 2, 6.4, 9.2, 0),
('DH52113047', 'LVTN', '2025', 2, 8.4, 7, 0),
('DH52113150', 'LVTN', '2025', 2, 7.8, 8, 0),
('Dh52113292', 'LVTN', '2025', 2, 7.2, 9, 0),
('DH52113526', 'LVTN', '2025', 2, 8.2, 7, 0),
('DH52113745', 'LVTN', '2025', 2, 8, 7.4, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qlsv_diem_gvhd`
--

DROP TABLE IF EXISTS `qlsv_diem_gvhd`;
CREATE TABLE IF NOT EXISTS `qlsv_diem_gvhd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DiemHD` float NOT NULL,
  `NhanXet` text COLLATE utf8mb4_unicode_ci,
  `NgayNhap` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `DiemNoiDung` float DEFAULT '0',
  `DiemChuyenCan` float NOT NULL,
  `DiemTrinhBay` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `MaSV` (`MaSV`)
) ENGINE=InnoDB AUTO_INCREMENT=264 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `qlsv_diem_gvhd`
--

INSERT INTO `qlsv_diem_gvhd` (`id`, `MaSV`, `DiemHD`, `NhanXet`, `NgayNhap`, `created_at`, `DiemNoiDung`, `DiemChuyenCan`, `DiemTrinhBay`) VALUES
(14, 'DH52103682', 6, 'nhóm làm khá tốt', '2025-12-12', '2025-12-12 08:40:29', 7, 8, 8),
(15, 'DH52111843', 6, 'nhóm làm khá tốt', '2025-12-12', '2025-12-12 08:40:29', 7, 8, 8),
(16, 'DH52106130', 9, 'nhóm làm tốt các công đoạn', '2025-12-12', '2025-12-12 08:41:00', 9, 8, 7),
(17, 'DH52111704', 9, 'nhóm làm tốt các công đoạn', '2025-12-12', '2025-12-12 08:41:00', 9, 8, 7),
(18, 'DH52108711', 8, '', '2025-12-12', '2025-12-12 08:41:26', 9, 7, 7),
(19, 'DH52005747', 8, '', '2025-12-12', '2025-12-12 08:41:26', 9, 7, 7),
(20, 'DH52108453', 8, '', '2025-12-12', '2025-12-12 08:41:35', 9, 7, 7),
(21, 'DH52107697', 8, '', '2025-12-12', '2025-12-12 08:41:35', 9, 7, 7),
(22, 'DH52111171', 9, '', '2025-12-12', '2025-12-12 08:58:05', 7, 6, 8),
(23, 'DH52006575', 9, '', '2025-12-12', '2025-12-12 08:58:05', 7, 6, 8),
(24, 'DH52111401', 9, '', '2025-12-12', '2025-12-12 08:58:25', 8, 8, 8),
(25, 'DH52111682', 9, '', '2025-12-12', '2025-12-12 08:58:25', 8, 8, 8),
(26, 'DH52112944', 9, '', '2025-12-12', '2025-12-12 08:58:35', 9, 8, 7),
(27, 'DH52111681', 9, '', '2025-12-12', '2025-12-12 08:58:35', 9, 8, 7),
(28, 'DH52104298', 9, '', '2025-12-12', '2025-12-12 08:58:42', 9, 8, 6),
(29, 'DH52110593', 9, '', '2025-12-12', '2025-12-12 08:58:42', 9, 8, 6),
(30, 'DH52101228', 6, '', '2025-12-12', '2025-12-12 09:03:08', 9, 8, 7),
(31, 'DH52006237', 6, '', '2025-12-12', '2025-12-12 09:03:08', 9, 8, 7),
(32, 'DH52113745', 6, '', '2025-12-12', '2025-12-12 09:03:14', 7, 8, 7),
(33, 'Dh52111509', 6, '', '2025-12-12', '2025-12-12 09:03:14', 7, 8, 7),
(34, 'DH52113745', 6, '', '2025-12-12', '2025-12-12 09:03:16', 7, 8, 7),
(35, 'Dh52111509', 6, '', '2025-12-12', '2025-12-12 09:03:16', 7, 8, 7),
(36, 'DH52107801', 6, '', '2025-12-12', '2025-12-12 09:03:26', 6, 8, 7),
(37, 'DH52111863', 6, '', '2025-12-12', '2025-12-12 09:03:26', 6, 8, 7),
(38, 'DH52111118', 6, '', '2025-12-12', '2025-12-12 09:03:33', 6, 8, 8),
(39, 'DH52111441', 6, '', '2025-12-12', '2025-12-12 09:03:33', 6, 8, 8),
(40, 'DH52002202', 9, '', '2025-12-12', '2025-12-12 09:03:44', 6, 8, 8),
(41, 'DH52106804', 9, '', '2025-12-12', '2025-12-12 09:03:44', 6, 8, 8),
(42, 'DH52111224', 7, '', '2025-12-12', '2025-12-12 09:06:34', 7, 8, 9),
(43, 'DH52108380', 7, '', '2025-12-12', '2025-12-12 09:06:34', 7, 8, 9),
(44, 'DH52108862', 8, '', '2025-12-12', '2025-12-12 09:06:42', 7, 8, 9),
(45, 'DH51903563', 8, '', '2025-12-12', '2025-12-12 09:06:42', 7, 8, 9),
(46, 'DH52007089', 8, '', '2025-12-12', '2025-12-12 09:06:49', 7, 9, 9),
(47, 'DH52108517', 8, '', '2025-12-12', '2025-12-12 09:06:49', 7, 9, 9),
(48, 'DH52111439', 8, '', '2025-12-12', '2025-12-12 09:07:05', 7, 9, 6),
(49, 'DH52113016', 8, '', '2025-12-12', '2025-12-12 09:07:05', 7, 9, 6),
(50, 'DH52112002', 8, '', '2025-12-12', '2025-12-12 09:07:11', 7, 9, 9),
(51, 'DH52003145', 8, '', '2025-12-12', '2025-12-12 09:07:11', 7, 9, 9),
(52, 'DH52111445', 6, '', '2025-12-12', '2025-12-12 09:08:51', 8, 9, 7),
(53, 'DH52111529', 6, '', '2025-12-12', '2025-12-12 09:08:51', 8, 9, 7),
(54, 'DH52111445', 6, '', '2025-12-12', '2025-12-12 09:08:56', 9, 9, 7),
(55, 'DH52111529', 6, '', '2025-12-12', '2025-12-12 09:08:56', 9, 9, 7),
(56, 'DH52000682', 6, '', '2025-12-12', '2025-12-12 09:09:03', 7, 9, 7),
(57, 'DH52001037', 6, '', '2025-12-12', '2025-12-12 09:09:03', 7, 9, 7),
(58, 'DH52112127', 6, '', '2025-12-12', '2025-12-12 09:09:09', 9, 9, 7),
(59, 'DH52111847', 6, '', '2025-12-12', '2025-12-12 09:09:09', 9, 9, 7),
(60, 'DH52111531', 6, '', '2025-12-12', '2025-12-12 09:09:14', 9, 9, 9),
(61, 'DH52004272', 6, '', '2025-12-12', '2025-12-12 09:09:14', 9, 9, 9),
(62, 'DH52111115', 6, '', '2025-12-12', '2025-12-12 09:09:19', 9, 9, 7),
(63, 'DH52110553', 6, '', '2025-12-12', '2025-12-12 09:09:19', 9, 9, 7),
(64, 'DH52104582', 6, '', '2025-12-12', '2025-12-12 09:11:50', 8, 9, 7),
(65, 'DH51904519', 6, '', '2025-12-12', '2025-12-12 09:11:50', 8, 9, 7),
(66, 'DH52110659', 9, '', '2025-12-12', '2025-12-12 09:11:56', 8, 9, 7),
(67, 'DH52104425', 9, '', '2025-12-12', '2025-12-12 09:11:56', 8, 9, 7),
(68, 'DH52111491', 9, '', '2025-12-12', '2025-12-12 09:12:01', 8, 9, 7),
(69, 'DH52111794', 9, '', '2025-12-12', '2025-12-12 09:12:01', 8, 9, 7),
(70, 'DH52111491', 9, '', '2025-12-12', '2025-12-12 09:12:04', 8, 9, 7),
(71, 'DH52111794', 9, '', '2025-12-12', '2025-12-12 09:12:04', 8, 9, 7),
(72, 'DH52003543', 9, '', '2025-12-12', '2025-12-12 09:12:11', 7, 9, 7),
(73, 'DH52111720', 9, '', '2025-12-12', '2025-12-12 09:12:11', 7, 9, 7),
(74, 'DH52101856', 9, '', '2025-12-12', '2025-12-12 09:12:17', 7, 9, 8),
(75, 'DH52110857', 9, '', '2025-12-12', '2025-12-12 09:12:17', 7, 9, 8),
(76, 'DH52104108', 8, '', '2025-12-12', '2025-12-12 09:14:04', 7, 8, 9),
(77, 'DH52111637', 8, '', '2025-12-12', '2025-12-12 09:14:04', 7, 8, 9),
(78, 'DH52104108', 8, '', '2025-12-12', '2025-12-12 09:14:06', 7, 8, 9),
(79, 'DH52111637', 8, '', '2025-12-12', '2025-12-12 09:14:06', 7, 8, 9),
(80, 'DH52110935', 8, '', '2025-12-12', '2025-12-12 09:14:13', 5, 8, 9),
(81, 'DH52112079', 8, '', '2025-12-12', '2025-12-12 09:14:13', 5, 8, 9),
(82, 'DH52111143', 8, '', '2025-12-12', '2025-12-12 09:14:21', 5, 8, 8),
(83, 'DH52001904', 8, '', '2025-12-12', '2025-12-12 09:14:21', 5, 8, 8),
(84, 'DH52111212', 8, '', '2025-12-12', '2025-12-12 09:14:27', 7, 8, 8),
(85, 'DH52111263', 8, '', '2025-12-12', '2025-12-12 09:14:27', 7, 8, 8),
(86, 'DH52001900', 8, '', '2025-12-12', '2025-12-12 09:14:30', 7, 8, 8),
(87, 'DH52110534', 8, '', '2025-12-12', '2025-12-12 09:14:30', 7, 8, 8),
(88, 'DH52001900', 8, '', '2025-12-12', '2025-12-12 09:14:32', 7, 8, 8),
(89, 'DH52110534', 8, '', '2025-12-12', '2025-12-12 09:14:32', 7, 8, 8),
(90, 'DH52111976', 7, '', '2025-12-12', '2025-12-12 09:16:33', 9, 8, 6),
(91, 'DH52110581', 7, '', '2025-12-12', '2025-12-12 09:16:33', 9, 8, 6),
(92, 'DH52112019', 7, '', '2025-12-12', '2025-12-12 09:16:39', 9, 8, 8),
(93, 'DH52107203', 7, '', '2025-12-12', '2025-12-12 09:16:39', 9, 8, 8),
(94, 'DH52112019', 7, '', '2025-12-12', '2025-12-12 09:16:43', 9, 8, 9),
(95, 'DH52107203', 7, '', '2025-12-12', '2025-12-12 09:16:43', 9, 8, 9),
(96, 'DH52110733', 7, '', '2025-12-12', '2025-12-12 09:16:48', 9, 8, 8),
(97, 'DH52110742', 7, '', '2025-12-12', '2025-12-12 09:16:48', 9, 8, 8),
(98, 'DH52005851', 9, '', '2025-12-12', '2025-12-12 09:16:52', 9, 8, 8),
(99, 'DH52110780', 9, '', '2025-12-12', '2025-12-12 09:16:52', 9, 8, 8),
(100, 'DH52111486', 10, '', '2025-12-12', '2025-12-12 09:16:59', 9, 8, 8),
(101, 'DH52003489', 10, '', '2025-12-12', '2025-12-12 09:16:59', 9, 8, 8),
(102, 'DH52111695', 6, '', '2025-12-12', '2025-12-12 09:18:10', 9, 8, 7),
(103, 'DH52107926', 6, '', '2025-12-12', '2025-12-12 09:18:10', 9, 8, 7),
(104, 'DH52111579', 6, '', '2025-12-12', '2025-12-12 09:18:18', 4, 8, 7),
(105, 'DH52111240', 6, '', '2025-12-12', '2025-12-12 09:18:18', 4, 8, 7),
(106, 'DH52111579', 6, '', '2025-12-12', '2025-12-12 09:18:20', 4, 8, 7),
(107, 'DH52111240', 6, '', '2025-12-12', '2025-12-12 09:18:20', 4, 8, 7),
(108, 'DH52104887', 6, '', '2025-12-12', '2025-12-12 09:18:24', 4, 8, 6),
(109, 'DH52111293', 6, '', '2025-12-12', '2025-12-12 09:18:24', 4, 8, 6),
(110, 'DH52007161', 7, '', '2025-12-12', '2025-12-12 09:18:30', 4, 8, 6),
(111, 'DH52103511', 7, '', '2025-12-12', '2025-12-12 09:18:30', 4, 8, 6),
(112, 'DH52001330', 7, '', '2025-12-12', '2025-12-12 09:18:34', 4, 8, 6),
(113, 'DH52005891', 7, '', '2025-12-12', '2025-12-12 09:18:34', 4, 8, 6),
(114, 'DH52001281', 8, '', '2025-12-12', '2025-12-12 09:20:29', 8, 8, 9),
(115, 'DH52101979', 8, '', '2025-12-12', '2025-12-12 09:20:29', 8, 8, 9),
(116, 'DH52113047', 8, '', '2025-12-12', '2025-12-12 09:20:38', 8, 7, 9),
(117, 'DH52103137', 8, '', '2025-12-12', '2025-12-12 09:20:38', 8, 7, 9),
(118, 'DH52110786', 8, '', '2025-12-12', '2025-12-12 09:20:45', 6, 7, 9),
(119, 'DH52111700', 8, '', '2025-12-12', '2025-12-12 09:20:45', 6, 7, 9),
(120, 'DH52003563', 8, '', '2025-12-12', '2025-12-12 09:20:49', 6, 7, 8),
(121, 'DH52101465', 8, '', '2025-12-12', '2025-12-12 09:20:49', 6, 7, 8),
(122, 'DH52105312', 10, '', '2025-12-12', '2025-12-12 09:20:56', 6, 7, 8),
(123, 'DH51904876', 10, '', '2025-12-12', '2025-12-12 09:20:56', 6, 7, 8),
(124, 'DH52007186', 6, '', '2025-12-12', '2025-12-12 09:22:58', 8, 9, 7),
(125, 'DH52105342', 6, '', '2025-12-12', '2025-12-12 09:22:58', 8, 9, 7),
(126, 'DH52109230', 9, '', '2025-12-12', '2025-12-12 09:24:36', 9, 8, 7),
(127, 'DH52113526', 9, '', '2025-12-12', '2025-12-12 09:24:36', 9, 8, 7),
(128, 'DH52111881', 9, '', '2025-12-12', '2025-12-12 09:24:42', 9, 8, 7),
(129, 'DH52111411', 9, '', '2025-12-12', '2025-12-12 09:24:42', 9, 8, 7),
(130, 'DH52111881', 9, '', '2025-12-12', '2025-12-12 09:24:46', 9, 8, 7),
(131, 'DH52111411', 9, '', '2025-12-12', '2025-12-12 09:24:46', 9, 8, 7),
(132, 'DH52111085', 9, '', '2025-12-12', '2025-12-12 09:24:49', 9, 8, 7),
(133, 'DH52110793', 9, '', '2025-12-12', '2025-12-12 09:24:49', 9, 8, 7),
(134, 'DH52111204', 9, '', '2025-12-12', '2025-12-12 09:24:52', 9, 8, 7),
(135, 'DH52110812', 9, '', '2025-12-12', '2025-12-12 09:24:52', 9, 8, 7),
(136, 'DH52111482', 9, '', '2025-12-12', '2025-12-12 09:24:57', 9, 8, 8),
(137, 'DH52111245', 9, '', '2025-12-12', '2025-12-12 09:24:57', 9, 8, 8),
(138, 'DH52104582', 6, '', '2025-12-20', '2025-12-20 18:02:47', 8, 7, 9),
(139, 'DH52107697', 6, '', '2025-12-20', '2025-12-20 18:02:47', 8, 7, 9),
(140, 'DH52104108', 8, '', '2025-12-20', '2025-12-20 18:03:19', 9, 8, 7),
(141, 'DH52101856', 8, '', '2025-12-20', '2025-12-20 18:03:19', 9, 8, 7),
(142, 'DH52106804', 8, '', '2025-12-20', '2025-12-20 18:03:27', 9, 7, 7),
(143, 'DH52104887', 8, '', '2025-12-20', '2025-12-20 18:03:27', 9, 7, 7),
(144, 'DH52106740', 8, '', '2025-12-20', '2025-12-20 18:03:35', 8, 7, 4),
(145, 'DH52105312', 8, '', '2025-12-20', '2025-12-20 18:03:35', 8, 7, 4),
(146, 'DH52105342', 8, '', '2025-12-20', '2025-12-20 18:03:42', 8, 7, 9),
(147, 'DH52107408', 8, '', '2025-12-20', '2025-12-20 18:03:42', 8, 7, 9),
(148, 'DH52108711', 9, '', '2025-12-20', '2025-12-20 18:06:27', 8, 9, 7),
(149, 'DH52108453', 9, '', '2025-12-20', '2025-12-20 18:06:27', 8, 9, 7),
(150, 'DH52108862', 9, '', '2025-12-20', '2025-12-20 18:06:34', 6, 7, 7),
(151, 'DH52108380', 9, '', '2025-12-20', '2025-12-20 18:06:34', 6, 7, 7),
(152, 'DH52108862', 9, '', '2025-12-20', '2025-12-20 18:06:35', 6, 7, 7),
(153, 'DH52108380', 9, '', '2025-12-20', '2025-12-20 18:06:35', 6, 7, 7),
(154, 'DH52101228', 9, '', '2025-12-20', '2025-12-20 18:06:42', 6, 7, 9),
(155, 'DH52108517', 9, '', '2025-12-20', '2025-12-20 18:06:42', 6, 7, 9),
(156, 'DH52107801', 9, '', '2025-12-20', '2025-12-20 18:06:48', 8, 7, 9),
(157, 'DH52107926', 9, '', '2025-12-20', '2025-12-20 18:06:48', 8, 7, 9),
(158, 'DH52109230', 9, '', '2025-12-20', '2025-12-20 18:06:56', 8, 9, 9),
(159, 'DH52111321', 9, '', '2025-12-20', '2025-12-20 18:06:56', 8, 9, 9),
(160, 'DH52003145', 9, '', '2025-12-20', '2025-12-20 18:07:24', 8, 7, 7),
(161, 'DH51903563', 9, '', '2025-12-20', '2025-12-20 18:07:24', 8, 7, 7),
(162, 'DH52000682', 9, '', '2025-12-20', '2025-12-20 18:07:32', 8, 8, 7),
(163, 'DH52001037', 9, '', '2025-12-20', '2025-12-20 18:07:32', 8, 8, 7),
(164, 'DH52000682', 9, '', '2025-12-20', '2025-12-20 18:07:36', 8, 8, 6),
(165, 'DH52001037', 9, '', '2025-12-20', '2025-12-20 18:07:36', 8, 8, 6),
(166, 'DH52001904', 9, '', '2025-12-20', '2025-12-20 18:07:43', 7, 8, 6),
(167, 'DH51904519', 9, '', '2025-12-20', '2025-12-20 18:07:43', 7, 8, 6),
(168, 'DH52001900', 9, '', '2025-12-20', '2025-12-20 18:07:48', 9, 8, 6),
(169, 'DH52002202', 9, '', '2025-12-20', '2025-12-20 18:07:48', 9, 8, 6),
(170, 'DH52001330', 9, '', '2025-12-20', '2025-12-20 18:07:55', 9, 8, 7),
(171, 'DH51904876', 9, '', '2025-12-20', '2025-12-20 18:07:55', 9, 8, 7),
(172, 'DH52001330', 9, '', '2025-12-20', '2025-12-20 18:07:56', 9, 8, 7),
(173, 'DH51904876', 9, '', '2025-12-20', '2025-12-20 18:07:56', 9, 8, 7),
(174, 'DH52006575', 8, '', '2025-12-20', '2025-12-20 18:08:32', 8, 7, 7),
(175, 'DH52005747', 8, '', '2025-12-20', '2025-12-20 18:08:32', 8, 7, 7),
(176, 'DH52003543', 8, '', '2025-12-20', '2025-12-20 18:08:38', 8, 6, 7),
(177, 'DH52004272', 8, '', '2025-12-20', '2025-12-20 18:08:38', 8, 6, 7),
(178, 'DH52005851', 8, '', '2025-12-20', '2025-12-20 18:08:46', 9, 6, 7),
(179, 'DH52003489', 8, '', '2025-12-20', '2025-12-20 18:08:46', 9, 6, 7),
(180, 'DH52006237', 8, '', '2025-12-20', '2025-12-20 18:08:51', 9, 6, 9),
(181, 'DH52005891', 8, '', '2025-12-20', '2025-12-20 18:08:51', 9, 6, 9),
(182, 'DH52007186', 8, '', '2025-12-20', '2025-12-20 18:08:56', 9, 9, 9),
(183, 'DH52003563', 8, '', '2025-12-20', '2025-12-20 18:08:56', 9, 9, 9),
(184, 'DH52106130', 9, '', '2025-12-20', '2025-12-20 18:09:18', 8, 9, 9),
(185, 'DH52103682', 9, '', '2025-12-20', '2025-12-20 18:09:18', 8, 9, 9),
(186, 'DH52107203', 6, '', '2025-12-20', '2025-12-20 18:09:27', 7, 9, 9),
(187, 'DH52007089', 6, '', '2025-12-20', '2025-12-20 18:09:27', 7, 9, 9),
(188, 'DH52007161', 6, '', '2025-12-20', '2025-12-20 18:09:32', 9, 9, 9),
(189, 'DH52103511', 6, '', '2025-12-20', '2025-12-20 18:09:32', 9, 9, 9),
(190, 'DH52001281', 6, '', '2025-12-20', '2025-12-20 18:09:38', 9, 9, 8),
(191, 'DH52101979', 6, '', '2025-12-20', '2025-12-20 18:09:38', 9, 9, 8),
(192, 'DH52103137', 6, '', '2025-12-20', '2025-12-20 18:09:44', 9, 6, 8),
(193, 'DH52101465', 6, '', '2025-12-20', '2025-12-20 18:09:44', 9, 6, 8),
(194, 'DH52104298', 7, '', '2025-12-20', '2025-12-20 18:10:42', 7, 8, 8),
(195, 'DH52111843', 7, '', '2025-12-20', '2025-12-20 18:10:42', 7, 8, 8),
(196, 'DH52110534', 7, '', '2025-12-20', '2025-12-20 18:10:51', 9, 7, 8),
(197, 'DH52110857', 7, '', '2025-12-20', '2025-12-20 18:10:51', 9, 7, 8),
(198, 'DH52111240', 7, '', '2025-12-20', '2025-12-20 18:10:57', 9, 7, 7),
(199, 'DH52110780', 7, '', '2025-12-20', '2025-12-20 18:10:57', 9, 7, 7),
(200, 'DH52110786', 9, '', '2025-12-20', '2025-12-20 18:11:02', 9, 7, 7),
(201, 'DH52110793', 9, '', '2025-12-20', '2025-12-20 18:11:02', 9, 7, 7),
(202, 'DH52111085', 9, '', '2025-12-20', '2025-12-20 18:11:05', 9, 7, 7),
(203, 'DH52111204', 9, '', '2025-12-20', '2025-12-20 18:11:05', 9, 7, 7),
(204, 'Dh52113292', 8, '', '2025-12-20', '2025-12-20 18:12:13', 7, 8, 6),
(205, 'DH52111401', 8, '', '2025-12-20', '2025-12-20 18:12:13', 7, 8, 6),
(206, 'DH52111445', 8, '', '2025-12-20', '2025-12-20 18:12:18', 8, 8, 6),
(207, 'DH52111847', 8, '', '2025-12-20', '2025-12-20 18:12:18', 8, 8, 6),
(208, 'DH52112019', 8, '', '2025-12-20', '2025-12-20 18:12:25', 8, 9, 6),
(209, 'DH52112127', 8, '', '2025-12-20', '2025-12-20 18:12:25', 8, 9, 6),
(210, 'DH52111486', 8, '', '2025-12-20', '2025-12-20 18:12:30', 7, 9, 6),
(211, 'DH52111441', 8, '', '2025-12-20', '2025-12-20 18:12:30', 7, 9, 6),
(212, 'DH52111482', 8, '', '2025-12-20', '2025-12-20 18:12:36', 7, 9, 9),
(213, 'DH52111881', 8, '', '2025-12-20', '2025-12-20 18:12:36', 7, 9, 9),
(214, 'DH52111411', 8, '', '2025-12-20', '2025-12-20 18:12:36', 7, 9, 9),
(215, 'DH52111171', 9, '', '2025-12-20', '2025-12-20 18:13:29', 7, 8, 8),
(216, 'DH52111112', 9, '', '2025-12-20', '2025-12-20 18:13:29', 7, 8, 8),
(217, 'DH52111491', 9, '', '2025-12-20', '2025-12-20 18:13:35', 6, 8, 8),
(218, 'DH52111115', 9, '', '2025-12-20', '2025-12-20 18:13:35', 6, 8, 8),
(219, 'DH52111143', 9, '', '2025-12-20', '2025-12-20 18:13:42', 6, 8, 7),
(220, 'DH52111118', 9, '', '2025-12-20', '2025-12-20 18:13:42', 6, 8, 7),
(221, 'DH52111579', 9, '', '2025-12-20', '2025-12-20 18:13:47', 9, 8, 7),
(222, 'DH52111245', 9, '', '2025-12-20', '2025-12-20 18:13:47', 9, 8, 7),
(223, 'DH52111700', 9, '', '2025-12-20', '2025-12-20 18:13:47', 9, 8, 7),
(224, 'DH52112786', 7, '', '2025-12-20', '2025-12-20 18:15:15', 7, 8, 6),
(225, 'DH52111681', 7, '', '2025-12-20', '2025-12-20 18:15:15', 7, 8, 6),
(226, 'DH52111529', 7, '', '2025-12-20', '2025-12-20 18:15:20', 7, 8, 8),
(227, 'DH52110659', 7, '', '2025-12-20', '2025-12-20 18:15:20', 7, 8, 8),
(228, 'DH52111720', 7, '', '2025-12-20', '2025-12-20 18:15:26', 6, 8, 8),
(229, 'DH52111637', 7, '', '2025-12-20', '2025-12-20 18:15:26', 6, 8, 8),
(230, 'DH52110733', 7, '', '2025-12-20', '2025-12-20 18:15:31', 6, 8, 9),
(231, 'Dh52111509', 7, '', '2025-12-20', '2025-12-20 18:15:31', 6, 8, 9),
(232, 'DH52111612', 7, '', '2025-12-20', '2025-12-20 18:15:36', 9, 8, 9),
(233, 'DH52111863', 7, '', '2025-12-20', '2025-12-20 18:15:36', 9, 8, 9),
(234, 'DH52111704', 6, '', '2025-12-20', '2025-12-20 18:16:16', 9, 8, 7),
(235, 'DH52112944', 6, '', '2025-12-20', '2025-12-20 18:16:16', 9, 8, 7),
(236, 'DH52111212', 6, '', '2025-12-20', '2025-12-20 18:16:21', 9, 8, 8),
(237, 'DH52110553', 6, '', '2025-12-20', '2025-12-20 18:16:21', 9, 8, 8),
(238, 'DH52110581', 6, '', '2025-12-20', '2025-12-20 18:16:26', 9, 8, 6),
(239, 'DH52111293', 6, '', '2025-12-20', '2025-12-20 18:16:26', 9, 8, 6),
(240, 'DH52113150', 6, '', '2025-12-20', '2025-12-20 18:16:33', 9, 9, 6),
(241, 'DH52111067', 6, '', '2025-12-20', '2025-12-20 18:16:33', 9, 9, 6),
(242, 'DH52110812', 6, '', '2025-12-20', '2025-12-20 18:16:38', 9, 9, 8),
(243, 'DH52113526', 6, '', '2025-12-20', '2025-12-20 18:16:38', 9, 9, 8),
(244, 'DH52111439', 7, '', '2025-12-20', '2025-12-20 18:17:35', 7, 9, 8),
(245, 'DH52111224', 7, '', '2025-12-20', '2025-12-20 18:17:35', 7, 9, 8),
(246, 'DH52111756', 7, '', '2025-12-20', '2025-12-20 18:17:40', 7, 9, 8),
(247, 'DH52110593', 7, '', '2025-12-20', '2025-12-20 18:17:40', 7, 9, 8),
(248, 'DH52111531', 7, '', '2025-12-20', '2025-12-20 18:17:45', 7, 9, 6),
(249, 'DH52111794', 7, '', '2025-12-20', '2025-12-20 18:17:45', 7, 9, 6),
(250, 'DH52111976', 7, '', '2025-12-20', '2025-12-20 18:17:52', 7, 9, 8),
(251, 'DH52110935', 7, '', '2025-12-20', '2025-12-20 18:17:52', 7, 9, 8),
(252, 'DH52110924', 7, '', '2025-12-20', '2025-12-20 18:17:58', 5, 9, 8),
(253, 'DH52111695', 7, '', '2025-12-20', '2025-12-20 18:17:58', 5, 9, 8),
(254, 'DH52113016', 7, '', '2025-12-20', '2025-12-20 18:18:45', 7, 6, 5),
(255, 'DH52112002', 7, '', '2025-12-20', '2025-12-20 18:18:45', 7, 6, 5),
(256, 'DH52111682', 7, '', '2025-12-20', '2025-12-20 18:18:50', 7, 6, 9),
(257, 'DH52104425', 7, '', '2025-12-20', '2025-12-20 18:18:50', 7, 6, 9),
(258, 'DH52111263', 7, '', '2025-12-20', '2025-12-20 18:18:57', 7, 8, 9),
(259, 'DH52112079', 7, '', '2025-12-20', '2025-12-20 18:18:57', 7, 8, 9),
(260, 'DH52113745', 7, '', '2025-12-20', '2025-12-20 18:19:04', 8, 8, 9),
(261, 'DH52110742', 7, '', '2025-12-20', '2025-12-20 18:19:04', 8, 8, 9),
(262, 'DH52113047', 9, '', '2025-12-20', '2025-12-20 18:19:12', 8, 8, 9),
(263, 'DH52112118', 9, '', '2025-12-20', '2025-12-20 18:19:12', 8, 8, 9);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qlsv_diem_gvpb`
--

DROP TABLE IF EXISTS `qlsv_diem_gvpb`;
CREATE TABLE IF NOT EXISTS `qlsv_diem_gvpb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nhom_id` int(11) NOT NULL,
  `MaSV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaGV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `diem_noi_dung` float DEFAULT '0',
  `diem_hinh_thuc` float DEFAULT '0',
  `diem_sang_tao` float DEFAULT '0',
  `diem_tong` float DEFAULT '0',
  `nhan_xet_chung` text COLLATE utf8mb4_unicode_ci,
  `uu_diem` text COLLATE utf8mb4_unicode_ci,
  `thieu_sot` text COLLATE utf8mb4_unicode_ci,
  `cau_hoi` text COLLATE utf8mb4_unicode_ci,
  `ket_luan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_cham` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_gvpb_sv` (`MaSV`,`MaGV`,`nhom_id`),
  KEY `idx_nhom` (`nhom_id`),
  KEY `idx_gv` (`MaGV`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `qlsv_diem_gvpb`
--

INSERT INTO `qlsv_diem_gvpb` (`id`, `nhom_id`, `MaSV`, `MaGV`, `diem_noi_dung`, `diem_hinh_thuc`, `diem_sang_tao`, `diem_tong`, `nhan_xet_chung`, `uu_diem`, `thieu_sot`, `cau_hoi`, `ket_luan`, `ngay_cham`, `created_at`, `updated_at`) VALUES
(13, 7, 'DH52103682', 'ĐEO001', 0.1, 0.4, 0.4, 0.9, '', '', '', '', NULL, NULL, '2025-12-27 16:18:15', '2025-12-27 16:18:15'),
(14, 7, 'DH52106130', 'ĐEO001', 0.5, 0.5, 0.4, 1.4, '', '', '', '', NULL, NULL, '2025-12-27 16:18:15', '2025-12-27 16:18:15'),
(15, 9, 'DH51903563', 'ĐEO001', 7, 7, 7, 21, '7', '7', '7', '7', NULL, NULL, '2025-12-27 16:45:56', '2025-12-27 16:45:56'),
(16, 9, 'DH52003145', 'ĐEO001', 7, 7, 67, 81, '7', '7', '7', '7', NULL, NULL, '2025-12-27 16:45:56', '2025-12-27 16:45:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `qlsv_diem_hoidong`
--

DROP TABLE IF EXISTS `qlsv_diem_hoidong`;
CREATE TABLE IF NOT EXISTS `qlsv_diem_hoidong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaSV` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DiemBaoVe` decimal(4,2) DEFAULT NULL,
  `NhanXet` text COLLATE utf8mb4_unicode_ci,
  `NgayCham` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `MaSV` (`MaSV`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `qlsv_diem_hoidong`
--

INSERT INTO `qlsv_diem_hoidong` (`id`, `MaSV`, `DiemBaoVe`, `NhanXet`, `NgayCham`) VALUES
(61, 'DH52110924', '7.00', '', '2025-12-21 01:19:56'),
(62, 'DH52111695', '7.00', '', '2025-12-21 01:19:56'),
(63, 'DH52111976', '7.30', '', '2025-12-21 01:20:03'),
(64, 'DH52110935', '7.30', '', '2025-12-21 01:20:03'),
(65, 'DH52111531', '6.00', '', '2025-12-21 01:20:10'),
(66, 'DH52111794', '6.00', '', '2025-12-21 01:20:10'),
(67, 'DH52111756', '9.00', '', '2025-12-21 01:20:15'),
(68, 'DH52110593', '9.00', '', '2025-12-21 01:20:15'),
(69, 'DH52111439', '9.00', '', '2025-12-21 01:20:19'),
(70, 'DH52111224', '9.00', '', '2025-12-21 01:20:19'),
(71, 'DH52106130', '8.00', '', '2025-12-21 01:21:48'),
(72, 'DH52103682', '8.00', '', '2025-12-21 01:21:48'),
(73, 'DH52107203', '8.00', '', '2025-12-21 01:21:54'),
(74, 'DH52007089', '8.00', '', '2025-12-21 01:21:54'),
(75, 'DH52003145', '8.00', '', '2025-12-21 01:22:00'),
(76, 'DH51903563', '8.00', '', '2025-12-21 01:22:00'),
(77, 'DH52001904', '7.00', '', '2025-12-21 01:22:09'),
(78, 'DH51904519', '7.00', '', '2025-12-21 01:22:09'),
(79, 'DH52000682', '7.00', '', '2025-12-21 01:22:16'),
(80, 'DH52001037', '7.00', '', '2025-12-21 01:22:16'),
(81, 'DH52001900', '6.00', '', '2025-12-21 01:22:23'),
(82, 'DH52002202', '6.00', '', '2025-12-21 01:22:23'),
(83, 'DH52001330', '9.00', '', '2025-12-21 01:22:31'),
(84, 'DH51904876', '9.00', '', '2025-12-21 01:22:31'),
(85, 'DH52007161', '9.00', '', '2025-12-21 01:22:40'),
(86, 'DH52103511', '9.00', '', '2025-12-21 01:22:40'),
(87, 'DH52001281', '9.00', '', '2025-12-21 01:22:49'),
(88, 'DH52101979', '9.00', '', '2025-12-21 01:22:49'),
(89, 'DH52103137', '7.00', '', '2025-12-21 01:22:56'),
(90, 'DH52101465', '7.00', '', '2025-12-21 01:22:56'),
(91, 'DH52007186', '7.00', '', '2025-12-21 01:23:04'),
(92, 'DH52003563', '7.00', '', '2025-12-21 01:23:04'),
(93, 'DH52113047', '7.00', '', '2025-12-21 01:23:25'),
(94, 'DH52112118', '7.00', '', '2025-12-21 01:23:25'),
(95, 'DH52113745', '7.40', '', '2025-12-21 01:23:33'),
(96, 'DH52110742', '7.40', '', '2025-12-21 01:23:33'),
(97, 'DH52111263', '6.50', '', '2025-12-21 01:23:42'),
(98, 'DH52112079', '6.50', '', '2025-12-21 01:23:42'),
(99, 'DH52111682', '7.50', '', '2025-12-21 01:23:59'),
(100, 'DH52104425', '7.50', '', '2025-12-21 01:23:59'),
(101, 'DH52113016', '9.20', '', '2025-12-21 01:24:07'),
(102, 'DH52112002', '9.20', '', '2025-12-21 01:24:07'),
(103, 'DH52110812', '7.00', '', '2025-12-21 01:38:23'),
(104, 'DH52113526', '7.00', '', '2025-12-21 01:38:23'),
(105, 'DH52111212', '7.70', '', '2025-12-21 01:38:37'),
(106, 'DH52110553', '7.70', '', '2025-12-21 01:38:37'),
(107, 'DH52110581', '5.00', '', '2025-12-21 01:38:46'),
(108, 'DH52111293', '5.00', '', '2025-12-21 01:38:46'),
(109, 'DH52113150', '8.00', '', '2025-12-21 01:38:52'),
(110, 'DH52111067', '8.00', '', '2025-12-21 01:38:52'),
(111, 'DH52111612', '6.00', '', '2025-12-21 01:38:59'),
(112, 'DH52111863', '6.00', '', '2025-12-21 01:38:59'),
(113, 'DH52110733', '6.70', '', '2025-12-21 01:39:08'),
(114, 'Dh52111509', '6.70', '', '2025-12-21 01:39:08'),
(115, 'DH52111529', '8.00', '', '2025-12-21 01:39:17'),
(116, 'DH52110659', '8.00', '', '2025-12-21 01:39:17'),
(117, 'DH52111720', '8.80', '', '2025-12-21 01:39:27'),
(118, 'DH52111637', '8.80', '', '2025-12-21 01:39:27'),
(119, 'DH52112786', '6.00', '', '2025-12-21 01:39:37'),
(120, 'DH52111681', '6.00', '', '2025-12-21 01:39:37'),
(121, 'DH52111482', '6.00', '', '2025-12-21 01:39:43'),
(122, 'DH52111881', '6.00', '', '2025-12-21 01:39:43'),
(123, 'DH52111411', '6.00', '', '2025-12-21 01:39:43'),
(124, 'DH52111486', '7.60', '', '2025-12-21 01:39:52'),
(125, 'DH52111441', '7.60', '', '2025-12-21 01:39:52'),
(126, 'DH52112019', '6.00', '', '2025-12-21 01:39:59'),
(127, 'DH52112127', '6.00', '', '2025-12-21 01:39:59'),
(128, 'DH52111445', '6.60', '', '2025-12-21 01:40:07'),
(129, 'DH52111847', '6.60', '', '2025-12-21 01:40:07'),
(130, 'Dh52113292', '9.00', '', '2025-12-21 01:40:17'),
(131, 'DH52111401', '9.00', '', '2025-12-21 01:40:17'),
(132, 'DH52111085', '8.50', '', '2025-12-21 01:40:27'),
(133, 'DH52111204', '8.50', '', '2025-12-21 01:40:27'),
(134, 'DH52111240', '8.00', '', '2025-12-21 01:40:36'),
(135, 'DH52110780', '8.00', '', '2025-12-21 01:40:36'),
(136, 'DH52110786', '9.00', '', '2025-12-21 01:41:47'),
(137, 'DH52110793', '9.00', '', '2025-12-21 01:41:47'),
(138, 'DH52110534', '7.00', '', '2025-12-21 01:41:51'),
(139, 'DH52110857', '7.00', '', '2025-12-21 01:41:51'),
(140, 'DH52104298', '9.50', '', '2025-12-21 01:41:59'),
(141, 'DH52111843', '9.50', '', '2025-12-21 01:41:59'),
(142, 'DH52109230', '8.00', '', '2025-12-21 01:42:04'),
(143, 'DH52111321', '8.00', '', '2025-12-21 01:42:04'),
(144, 'DH52107801', '9.00', '', '2025-12-21 01:42:08'),
(145, 'DH52107926', '9.00', '', '2025-12-21 01:42:08'),
(146, 'DH52101228', '7.00', '', '2025-12-21 01:42:15'),
(147, 'DH52108517', '7.00', '', '2025-12-21 01:42:15'),
(148, 'DH52108862', '7.00', '', '2025-12-21 01:42:24'),
(149, 'DH52108380', '7.00', '', '2025-12-21 01:42:24'),
(150, 'DH52108711', '8.00', '', '2025-12-21 01:42:29'),
(151, 'DH52108453', '8.00', '', '2025-12-21 01:42:29'),
(152, 'DH52105342', '7.00', '', '2025-12-21 01:42:34'),
(153, 'DH52107408', '7.00', '', '2025-12-21 01:42:34'),
(154, 'DH52106740', '9.00', '', '2025-12-21 01:42:39'),
(155, 'DH52105312', '9.00', '', '2025-12-21 01:42:39'),
(156, 'DH52106804', '6.70', '', '2025-12-21 01:42:47'),
(157, 'DH52104887', '6.70', '', '2025-12-21 01:42:47'),
(158, 'DH52104108', '8.00', '', '2025-12-21 01:42:53'),
(159, 'DH52101856', '8.00', '', '2025-12-21 01:42:53'),
(160, 'DH52104582', '9.00', '', '2025-12-21 01:43:01'),
(161, 'DH52107697', '9.00', '', '2025-12-21 01:43:01'),
(162, 'DH52006237', '10.00', '', '2025-12-21 01:43:07'),
(163, 'DH52005891', '10.00', '', '2025-12-21 01:43:07'),
(164, 'DH52005851', '8.00', '', '2025-12-21 01:43:12'),
(165, 'DH52003489', '8.00', '', '2025-12-21 01:43:12'),
(166, 'DH52003543', '6.00', '', '2025-12-21 01:43:20'),
(167, 'DH52004272', '6.00', '', '2025-12-21 01:43:20'),
(168, 'DH52006575', '5.50', '', '2025-12-21 01:43:26'),
(169, 'DH52005747', '5.50', '', '2025-12-21 01:43:26'),
(170, 'DH52111579', '5.00', '', '2025-12-21 01:43:31'),
(171, 'DH52111245', '5.00', '', '2025-12-21 01:43:31'),
(172, 'DH52111700', '5.00', '', '2025-12-21 01:43:31'),
(173, 'DH52111143', '7.00', '', '2025-12-21 01:43:36'),
(174, 'DH52111118', '7.00', '', '2025-12-21 01:43:36'),
(175, 'DH52111491', '6.00', '', '2025-12-21 01:44:23'),
(176, 'DH52111115', '6.00', '', '2025-12-21 01:44:23'),
(177, 'DH52111171', '6.50', '', '2025-12-21 01:44:29'),
(178, 'DH52111112', '6.50', '', '2025-12-21 01:44:29'),
(179, 'DH52111704', '6.00', '', '2025-12-21 01:56:07'),
(180, 'DH52112944', '6.00', '', '2025-12-21 01:56:07');

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `danh_sach_sinh_vien`
--
ALTER TABLE `danh_sach_sinh_vien`
  ADD CONSTRAINT `fk_sinhvien_nhom` FOREIGN KEY (`nhom_id`) REFERENCES `nhom` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `hoidong`
--
ALTER TABLE `hoidong`
  ADD CONSTRAINT `fk_hd_gv` FOREIGN KEY (`MaGV`) REFERENCES `giang_vien` (`MaGV`);

--
-- Các ràng buộc cho bảng `nhiem_vu`
--
ALTER TABLE `nhiem_vu`
  ADD CONSTRAINT `fk_nv_gv` FOREIGN KEY (`MaGV`) REFERENCES `giang_vien` (`MaGV`);

--
-- Các ràng buộc cho bảng `nhom`
--
ALTER TABLE `nhom`
  ADD CONSTRAINT `nhom_ibfk_1` FOREIGN KEY (`giang_vien_huong_dan_id`) REFERENCES `giang_vien` (`MaGV`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `phancong_gvhd`
--
ALTER TABLE `phancong_gvhd`
  ADD CONSTRAINT `fk_pcgvhd_sv` FOREIGN KEY (`MaSV`) REFERENCES `danh_sach_sinh_vien` (`MaSV`);

--
-- Các ràng buộc cho bảng `phan_thuoc_nhom`
--
ALTER TABLE `phan_thuoc_nhom`
  ADD CONSTRAINT `phan_thuoc_nhom_ibfk_1` FOREIGN KEY (`nhom_id`) REFERENCES `nhom` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phan_thuoc_nhom_ibfk_2` FOREIGN KEY (`sinh_vien_id`) REFERENCES `danh_sach_sinh_vien` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `qlsv_bienban`
--
ALTER TABLE `qlsv_bienban`
  ADD CONSTRAINT `fk_bienban_sv` FOREIGN KEY (`MaSV`) REFERENCES `danh_sach_sinh_vien` (`MaSV`);

--
-- Các ràng buộc cho bảng `qlsv_diem`
--
ALTER TABLE `qlsv_diem`
  ADD CONSTRAINT `fk_diem_sinhvien` FOREIGN KEY (`MaSV`) REFERENCES `danh_sach_sinh_vien` (`MaSV`);

--
-- Các ràng buộc cho bảng `qlsv_diem_gvhd`
--
ALTER TABLE `qlsv_diem_gvhd`
  ADD CONSTRAINT `fk_diemgvhd_sinhvien` FOREIGN KEY (`MaSV`) REFERENCES `danh_sach_sinh_vien` (`MaSV`);

--
-- Các ràng buộc cho bảng `qlsv_diem_hoidong`
--
ALTER TABLE `qlsv_diem_hoidong`
  ADD CONSTRAINT `fk_diemhd_sinhvien` FOREIGN KEY (`MaSV`) REFERENCES `danh_sach_sinh_vien` (`MaSV`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
