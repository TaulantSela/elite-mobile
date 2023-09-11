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
(3, 'Huawei'),
(4, 'Blackberry'),
(5, 'Google');

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
(3, 'iPhone X 256GB', 1, '73990', 'AppleiPhoneX256GB.jpg', '<p><strong>Announced :</strong> <em>2017 September</em></p>\r\n<p><strong> Dimensions :</strong> <em>143.6 x 70.9 x 7.7 mm (5.65 x 2.79 x 0.30 in) </em></p>\r\n<p><strong>OS :</strong> <em>iOS 11.1.1 </em></p>\r\n<p><strong>Sensors:</strong> <em>Face ID, accelerometer, gyro, proximity, compass , barometer</em></p>', 1),
(4, 'iPhone X 64GB', 1, '62690', 'AppleiPhoneX64GB.jpg', '<p><strong>Announced :</strong><em> 2017 September </em></p>\r\n<p><strong>Dimensions :</strong> <em>143.6 x 70.9 x 7.7 mm (5.65 x 2.79 x 0.30 in) </em></p>\r\n<p><strong>OS :</strong> <em>iOS 11.1.1 </em></p>\r\n<p><strong>Sensors:</strong> <em>Face ID, accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(5, 'iPhone 8 Plus 256GB', 1, '57190', 'AppleiPhone8Plus256GB.jpg', '<p><strong>Announced :</strong> <em>2017 September</em></p>\r\n<p><strong> Dimensions :</strong> <em>143.6 x 70.9 x 7.7 mm (5.65 x 2.79 x 0.30 in) </em></p>\r\n<p><strong>OS :</strong><em> iOS 11.1.1 </em></p>\r\n<p><strong>Sensors:</strong> <em>Face ID, accelerometer, gyro, proximity, compass, barometer</em></p>', 1),
(6, 'iPhone 8 Plus 64GB', 1, '49190', 'AppleiPhone8Plus64GB.jpg', '<p><strong>Announced :</strong><em> 2017 September </em></p>\r\n<p><strong>Dimensions :</strong><em> 158.4 x 78.1 x 7.5 mm (6.24 x 3.07 x 0.30 in) </em></p>\r\n<p><strong>OS :</strong><em> iOS 11.1.1 upgradable to iOS 11.2 </em></p>\r\n<p><strong>Sensors:</strong><em> Fingerprint (front-mounted), accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(7, 'iPhone 8 256GB', 1, '46100', 'AppleiPhone8256GB.jpg', '<p><strong>Announced :</strong><em> 2017 September </em></p>\r\n<p><strong>Dimensions :</strong><em> 158.4 x 78.1 x 7.5 mm (6.24 x 3.07 x 0.30 in) </em></p>\r\n<p><strong>OS :</strong><em> iOS 11.1.1 upgradable to iOS 11.2 </em></p>\r\n<p><strong>Sensors:</strong> <em>Fingerprint (front-mounted), accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(8, 'iPhone 8 64GB', 1, '41800', 'AppleiPhone864GB.jpg', '<p><strong>Announced :</strong><em> 2017 September </em></p>\r\n<p><strong>Dimensions :</strong><em>138.4 x 67.3 x 7.3 mm (5.45 x 2.65 x 0.29 in)</em></p>\r\n<p><strong> OS :</strong><em> iOS 11.1.1 upgradable to iOS 11.2 </em></p>\r\n<p><strong>Sensors :</strong><em> Fingerprint (front-mounted), accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(9, 'Samsung Galaxy A8+', 2, '30790', 'SamsungGalaxyA8Plus.jpg', '<p><strong>Announced</strong><em><strong> : </strong>2017 December </em></p>\r\n<p><strong>Dimensions :</strong><em> 159.9 x 75.7 x 8.3 mm (6.30 x 2.98 x 0.33 in) </em></p>\r\n<p><strong>OS :</strong><em> Android 7.1.1 (Nougat) </em></p>\r\n<p><strong>Sensors:</strong><em> Face ID, accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(10, 'Samsung Note 8', 2, '45990', 'SamsungGalaxyNote8.jpg', '<p><strong>Announced :</strong><em> 2017 August </em></p>\r\n<p><strong>Dimensions :</strong><em> 162.5 x 74.8 x 8.6 mm (6.40 x 2.94 x 0.34 in) </em></p>\r\n<p><strong>OS :</strong><em> Android 7.1.1 (Nougat), upgradable to Android 8.0 (Oreo) </em></p>\r\n<p><strong>Sensors:</strong><em> Face ID, accelerometer, gyro, proximity, compass, barometer</em></p>', 1),
(11, 'Samsung Galaxy C7 Pro', 2, '22150', 'SamsungGalaxyC7Pro.jpg', '<p><strong>Announced</strong><em> : 2017 January </em></p>\r\n<p><strong>Dimensions</strong><em> : 156.5 x 77.2 x 7 mm (6.16 x 3.04 x 0.28 in) </em></p>\r\n<p><strong>OS :</strong> <em>Android 6.0.1 (Marshmallow), upgradable to 7.0 (Nougat) </em></p>\r\n<p><strong>Sensors</strong><em>: Face ID, accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(12, 'Samsung Galaxy C5 Pro', 2, '18490', 'SamsungGalaxyC5Pro.jpg', '<p><strong>Announced</strong> <em>: 2017 March </em></p>\r\n<p><strong>Dimensions</strong> <em>: 145.7 x 71.4 x 7 mm (5.74 x 2.81 x 0.28 in) </em></p>\r\n<p><strong>OS</strong> <em>: Android 7.0 (Nougat) </em></p>\r\n<p><strong>Sensors</strong><em>: Face ID, accelerometer, gyro, proximity, compass, barometer</em></p>', 0),
(13, 'Huawei P10', 3, '26490', 'HuaweiP10.jpg', '<p><strong>Announced</strong> <em>: 2017 February </em></p>\r\n<p><strong>Dimensions</strong> <em>: 145.3 x 69.3 x 7 mm (5.72 x 2.73 x 0.28 in)</em></p>\r\n<p><strong>OS</strong> <em>: Android 7.0 (Nougat), planned upgrade to Android 8.0 (Oreo) </em></p>\r\n<p><strong>Sensors</strong><em>: Fingerprint (front-mounted), accelerometer, gyro, proximity, compass</em></p>', 0),
(14, 'Huawei P10 Lite', 3, '14790', 'HuaweiP10lite.jpg', '<p><strong>Announced</strong> <em>: 2017 February </em></p>\r\n<p><strong>Dimensions</strong> <em>: 146.5 x 72 x 7.2 mm (5.77 x 2.83 x 0.28 in) </em></p>\r\n<p><strong>OS</strong> <em>: Android 7.0 (Nougat) </em></p>\r\n<p><strong>Sensors</strong><em>: Fingerprint (front-mounted), accelerometer, gyro, proximity, compass</em></p>', 0),
(15, 'Huawei P8 64GB', 3, '13490', 'HuaweiP864GB.jpg', '<p><strong>Announced</strong> <em>: 2015 April </em></p>\r\n<p><strong>Dimensions</strong> <em>: 144.9 x 72.1 x 6.4 mm (5.70 x 2.84 x 0.25 in) </em></p>\r\n<p><strong>OS</strong> <em>: Android 4.4.2 (KitKat), 5.0.2 (Lollipop), planned upgrade to 6.0 (Marshmallow) </em></p>\r\n<p><strong>Sensors</strong><em>: Fingerprint (front-mounted), accelerometer, gyro, proximity, compass</em></p>', 0),
(16, 'BlackBerry Priv', 4, '30790', 'BlackBerryPriv.jpg', '<p><strong>Announced</strong> <em>: 2015 October </em></p>\r\n<p><strong>Dimensions</strong> <em>: 147 x 77.2 x 9.4 mm (5.79 x 3.04 x 0.37 in) </em></p>\r\n<p><strong>OS</strong> <em>: Android 5.1.1 (Lollipop), upgradable to 6.0.1 (Marshmallow) </em></p>\r\n<p><strong>Sensors</strong><em>: Accelerometer, altimeter, gyro, ToF proximity, compass</em></p>', 0),
(17, 'Google Pixel 2', 5, '300000', 'GooglePixel2.jpg', '<p><strong>Announced</strong> <em>: 2017 October </em></p>\r\n<p><strong>Dimensions</strong> <em>: 145.7 x 69.7 x 7.8 mm (5.74 x 2.74 x 0.31 in) </em></p>\r\n<p><strong>OS</strong> <em>: Android 8.0 </em></p>\r\n<p><strong>Sensors</strong><em>: Fingerprint (rear-mounted), accelerometer, gyro, proximity, compass, barometer</em></p>', 0);

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
