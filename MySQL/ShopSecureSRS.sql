-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: shopsecuredb
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `customerID` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','manager') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customerID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (3,'Thabang','mohalethabang77@gmail.com','$2y$10$WClIgXUM.kq3ofguKkUvJexz/E3DY/eOMk0QqKcVQ/AltfOt4VaO2','customer','2025-10-08 09:05:08'),(6,'Manager Admin','manager@srs.com','$2y$10$HQ5gFPhcGyO0MqYNwoLEr.mhHOFAJi1vGHf4tC/cXeea2UIxkupGu','manager','2025-10-08 10:10:08');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `itemID` int NOT NULL AUTO_INCREMENT,
  `orderID` int NOT NULL,
  `productID` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`itemID`),
  KEY `orderID` (`orderID`),
  KEY `productID` (`productID`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (3,1,7,1,75.00),(4,1,8,1,49.99),(6,2,2,1,45.50),(7,2,1,1,199.99),(8,2,5,1,299.99),(9,2,6,1,199.50),(11,4,5,1,299.99),(12,5,6,1,890.00),(13,5,5,1,799.99),(14,6,8,1,549.99),(15,6,7,1,480.00),(16,6,48,1,45.99),(17,7,6,1,890.00),(18,7,9,1,2999.99),(19,7,11,1,1399.95);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `orderID` int NOT NULL AUTO_INCREMENT,
  `customerID` int NOT NULL,
  `totalAmount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`orderID`),
  KEY `customerID` (`customerID`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customers` (`customerID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,3,215.98,'confirmed','2025-10-08 10:00:01'),(2,3,750.97,'confirmed','2025-10-08 10:00:23'),(3,6,85.00,'confirmed','2025-10-08 10:34:33'),(4,6,299.99,'confirmed','2025-10-13 07:45:52'),(5,3,1689.99,'confirmed','2025-10-14 07:19:53'),(6,3,1075.98,'confirmed','2025-10-14 07:49:52'),(7,3,5289.94,'confirmed','2025-10-14 07:56:02');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `productID` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stockQty` int NOT NULL DEFAULT '0',
  `category` varchar(50) NOT NULL,
  PRIMARY KEY (`productID`),
  KEY `idx_category` (`category`),
  KEY `idx_stock` (`stockQty`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Smartwatch X5','Latest model with health monitoring.',599.99,49,'Electronics'),(2,'Coffee Maker 9000','Programmable 12-cup coffee machine.',1249.99,14,'Home Appliances'),(5,'Smartwatch X5 Pro','Latest model with AMOLED display and ECG monitoring.',799.99,42,'Electronics'),(6,'Noise-Cancelling Headphones','Premium over-ear headphones with 40-hour battery.',890.00,57,'Electronics'),(7,'4K Ultra HD Webcam','High-resolution camera for professional streaming and video calls.',480.00,13,'Electronics'),(8,'Portable Bluetooth Speaker','Waterproof speaker with 15-hour playtime and deep bass.',549.99,108,'Electronics'),(9,'27-inch Curved Monitor','QHD resolution, 144Hz refresh rate, ideal for gaming.',1500.00,21,'Electronics'),(10,'Wireless Charging Pad (Fast)','15W fast charging pad compatible with all major phones.',399.99,90,'Electronics'),(11,'Mini Drone with HD Camera','Foldable design with GPS and 20-minute flight time.',1399.95,17,'Electronics'),(12,'Mechanical Keyboard (RGB)','Full-size keyboard with tactile switches and customizable RGB lighting.',256.95,35,'Electronics'),(13,'External SSD 1TB (USB 3.1)','Ultra-fast portable solid-state drive for backup and storage.',799.99,55,'Electronics'),(14,'VR Headset Lite','Entry-level virtual reality headset for mobile gaming.',2300.00,40,'Electronics'),(15,'Mesh WiFi System (3-Pack)','Eliminate dead zones with whole-home coverage.',1499.99,15,'Electronics'),(16,'E-Reader 6-inch Paperwhite','Glare-free screen with adjustable warm light.',229.00,30,'Electronics'),(17,'Robotic Vacuum Cleaner','Self-charging vacuum with smart mapping and app control.',350.00,15,'Home Goods'),(18,'Premium Blender 10-speed','Heavy-duty blender for smoothies and soups.',799.99,30,'Home Goods'),(19,'Digital Kitchen Scale','Accurate scale with metric and imperial measurements.',150.00,150,'Home Goods'),(20,'Weighted Blanket 15 lbs','Therapeutic blanket for anxiety and better sleep.',165.00,40,'Home Goods'),(21,'Air Fryer 4 Litre','Healthy cooking with less oil. Digital touchscreen.',399.99,25,'Home Goods'),(22,'Bamboo Bedding Set (Queen)','Sustainable, ultra-soft, and hypoallergenic sheets.',650.00,50,'Home Goods'),(23,'Smart Thermostat','Energy-saving thermostat learning your schedule.',139.99,12,'Home Goods'),(24,'Aromatherapy Diffuser Kit','Ultrasonic diffuser with 6 essential oils.',120.00,75,'Home Goods'),(25,'Cast Iron Skillet 12 inch','Pre-seasoned, excellent heat retention.',299.99,60,'Home Goods'),(26,'Set of 4 Bar Stools','Modern design, adjustable height, chrome base.',150.00,10,'Home Goods'),(27,'Memory Foam Pillow (Set of 2)','Contour support for side and back sleepers.',55.00,80,'Home Goods'),(28,'Mountain Bike - Trail Series','Lightweight aluminum frame with 24-speed gears.',450.00,8,'Sports & Outdoors'),(29,'Yoga Mat Deluxe 6mm','High-density padding, non-slip texture.',74.99,95,'Sports & Outdoors'),(30,'Resistance Band Set (5-Pack)','Perfect for home workouts and physical therapy.',130.50,180,'Sports & Outdoors'),(32,'Digital Skipping Rope','Tracks jumps, time, and calories burned.',219.99,100,'Sports & Outdoors'),(33,'Running Shoes - Zephyr Model','Lightweight cushioning for long-distance running.',110.00,30,'Sports & Outdoors'),(34,'Insulated Water Bottle 32oz','Keeps drinks cold for 24 hours, hot for 12.',30.00,130,'Sports & Outdoors'),(35,'Basketball - Official Size 7','Durable composite cover for indoor/outdoor use.',90.00,55,'Sports & Outdoors'),(36,'Headlamp 300 Lumens','Rechargeable LED headlamp with adjustable focus.',80.00,70,'Sports & Outdoors'),(37,'Portable Hammock with Stand','Quick assembly, perfect for patio or garden.',75.00,14,'Sports & Outdoors'),(48,'Classic Sunglasses','Polarized lenses, UV400 protection, matte black frame.',45.99,149,'Accessories'),(49,'Leather Belt (Brown)','Genuine leather belt with antique brass buckle.',39.50,80,'Accessories'),(50,'Silk Scarf (Printed)','Large square scarf with abstract floral print.',25.00,120,'Accessories'),(51,'Winter Knit Beanie','Warm, soft acrylic beanie with cuff.',18.00,200,'Accessories'),(52,'Gloves (Touchscreen)','Wool blend gloves with conductive fingertips.',22.99,180,'Accessories'),(53,'Canvas Backpack','Durable canvas rucksack with laptop sleeve.',249.99,50,'Accessories'),(54,'Travel Wallet','RFID blocking wallet for passports and cards.',109.99,110,'Accessories'),(55,'Umbrella (Folding)','Compact folding umbrella with automatic open/close.',30.00,250,'Accessories'),(56,'Keychain Multi-tool','Small stainless steel keychain with bottle opener and knife.',64.99,300,'Accessories'),(57,'Baseball Cap (Logo)','Adjustable cotton twill cap with embroidered logo.',24.99,140,'Accessories');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-16 10:28:30
