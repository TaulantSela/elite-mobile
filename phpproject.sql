-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 19, 2019 at 01:33 PM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phpproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `categoryid` int(50) NOT NULL,
  `categoryname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`categoryid`, `categoryname`) VALUES
(1, 'Apple'),
(2, 'Samsung'),
(3, 'Google'),
(4, 'OnePlus'),
(5, 'Nothing');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `productid` int(50) NOT NULL,
  `productname` varchar(50) NOT NULL,
  `categoryid` int(50) NOT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `image` text NOT NULL,
  `info` text,
  `paid` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`productid`, `productname`, `categoryid`, `price`, `image`, `info`, `paid`) VALUES
(1, 'iPhone 16 Pro Max 512GB', 1, '129990', 'apple.png', '<p><strong>Announced:</strong> 2024 September</p>\r\n<p><strong>Display:</strong> 6.9&amp;quot; LTPO Super Retina XDR, 120Hz</p>\r\n<p><strong>Chipset:</strong> Apple A18 Pro with 12-core Neural Engine</p>\r\n<p><strong>Cameras:</strong> 48MP quad system with 5x tetraprism zoom</p>\r\n<p><strong>Battery:</strong> 4700 mAh with 35W USB-C fast charging</p>', 0),
(2, 'iPhone 16 Pro 256GB', 1, '114990', 'apple.png', '<p><strong>Announced:</strong> 2024 September</p>\r\n<p><strong>Display:</strong> 6.3&amp;quot; ProMotion OLED, Ceramic Shield 2</p>\r\n<p><strong>Chipset:</strong> Apple A18 Pro</p>\r\n<p><strong>Cameras:</strong> 48MP main + 12MP ultrawide + 3x telephoto</p>\r\n<p><strong>Battery:</strong> 4200 mAh with 35W USB-C fast charging</p>', 0),
(3, 'Samsung Galaxy S24 Ultra 512GB', 2, '119990', 'samsung.png', '<p><strong>Announced:</strong> 2024 January</p>\r\n<p><strong>Display:</strong> 6.8&amp;quot; QHD+ Dynamic AMOLED 2X, 120Hz</p>\r\n<p><strong>Chipset:</strong> Snapdragon 8 Elite for Galaxy</p>\r\n<p><strong>Cameras:</strong> 200MP quad camera with 5x optical zoom</p>\r\n<p><strong>Battery:</strong> 5000 mAh with 45W wired, 15W wireless</p>', 0),
(4, 'Samsung Galaxy Z Fold6', 2, '149990', 'samsung.png', '<p><strong>Announced:</strong> 2024 July</p>\r\n<p><strong>Display:</strong> 7.6&amp;quot; foldable LTPO AMOLED + 6.3&amp;quot; cover</p>\r\n<p><strong>Chipset:</strong> Snapdragon 8 Elite</p>\r\n<p><strong>Cameras:</strong> 50MP triple system with 3x optical zoom</p>\r\n<p><strong>Battery:</strong> 4700 mAh with 45W wired, 15W wireless</p>', 0),
(5, 'Google Pixel 9 Pro XL', 3, '99990', 'google.png', '<p><strong>Announced:</strong> 2024 October</p>\r\n<p><strong>Display:</strong> 6.8&amp;quot; LTPO OLED, 120Hz</p>\r\n<p><strong>Chipset:</strong> Google Tensor G4</p>\r\n<p><strong>Cameras:</strong> 50MP triple Pixel HDR+ with 6x optical zoom</p>\r\n<p><strong>Battery:</strong> 5100 mAh with 45W wired, 23W wireless</p>', 0),
(6, 'Google Pixel 9a', 3, '54990', 'google.png', '<p><strong>Announced:</strong> 2024 May</p>\r\n<p><strong>Display:</strong> 6.2&amp;quot; OLED, 120Hz</p>\r\n<p><strong>Chipset:</strong> Google Tensor G3</p>\r\n<p><strong>Cameras:</strong> 64MP dual camera with Super Res Zoom</p>\r\n<p><strong>Battery:</strong> 4700 mAh with 30W fast charging</p>', 0),
(7, 'OnePlus 13 512GB', 4, '88990', 'HTB1xpGqSXXXXXXcXXXXq6xXFXXXA_470x.jpg', '<p><strong>Announced:</strong> 2024 December</p>\r\n<p><strong>Display:</strong> 6.82&amp;quot; 2K OLED, 120Hz</p>\r\n<p><strong>Chipset:</strong> Snapdragon 8 Gen 4</p>\r\n<p><strong>Cameras:</strong> 50MP triple Hasselblad system with periscope zoom</p>\r\n<p><strong>Battery:</strong> 5400 mAh with 120W SUPERVOOC</p>', 0),
(8, 'OnePlus Open 2', 4, '139990', 'HTB1xpGqSXXXXXXcXXXXq6xXFXXXA_470x.jpg', '<p><strong>Announced:</strong> 2025 February</p>\r\n<p><strong>Display:</strong> 7.9&amp;quot; Flexi-fluid AMOLED + 6.3&amp;quot; cover</p>\r\n<p><strong>Chipset:</strong> Snapdragon 8 Gen 4</p>\r\n<p><strong>Cameras:</strong> 48MP triple with 6x lossless zoom</p>\r\n<p><strong>Battery:</strong> 5200 mAh with 100W SUPERVOOC</p>', 0),
(9, 'Nothing Phone (3) 12/256GB', 5, '59990', '52152078.jpg', '<p><strong>Announced:</strong> 2024 July</p>\r\n<p><strong>Display:</strong> 6.7&amp;quot; OLED, 120Hz Glyph Interface 2.0</p>\r\n<p><strong>Chipset:</strong> Snapdragon 8s Gen 3</p>\r\n<p><strong>Cameras:</strong> 50MP dual with TrueLens Engine</p>\r\n<p><strong>Battery:</strong> 5000 mAh with 70W wired, 15W wireless</p>', 0),
(10, 'Nothing Phone (2a) Special Edition', 5, '41990', '52152078.jpg', '<p><strong>Announced:</strong> 2024 March</p>\r\n<p><strong>Display:</strong> 6.7&amp;quot; AMOLED, 120Hz</p>\r\n<p><strong>Chipset:</strong> MediaTek Dimensity 9200+</p>\r\n<p><strong>Cameras:</strong> 50MP dual with Night Vision 2.0</p>\r\n<p><strong>Battery:</strong> 5000 mAh with 45W fast charging</p>', 0),
(11, 'iPhone 16', 1, '96990', 'apple.png', '<p><strong>Announced:</strong> 2024 September</p>\r\n<p><strong>Display:</strong> 6.1&amp;quot; Super Retina XDR, 120Hz</p>\r\n<p><strong>Chipset:</strong> Apple A18</p>\r\n<p><strong>Cameras:</strong> 48MP dual with 2x optical zoom</p>\r\n<p><strong>Battery:</strong> 4200 mAh with 35W USB-C fast charging</p>', 0),
(12, 'Samsung Galaxy S24+', 2, '99990', 'samsung.png', '<p><strong>Announced:</strong> 2024 January</p>\r\n<p><strong>Display:</strong> 6.7&amp;quot; FHD+ Dynamic AMOLED 2X, 120Hz</p>\r\n<p><strong>Chipset:</strong> Snapdragon 8 Elite / Exynos 2400</p>\r\n<p><strong>Cameras:</strong> 50MP triple with 3x optical zoom</p>\r\n<p><strong>Battery:</strong> 4900 mAh with 45W wired, 15W wireless</p>', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`) VALUES
('elijon', 'elijon123'),
('taulant', 'taulant');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`categoryid`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`productid`),
  ADD KEY `categoryid` (`categoryid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `categoryid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `productid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `category` (`categoryid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
