-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: albertos_pizza_db
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `branch_id` int NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'A.S FURTUNA','Unit 3 TP Bldg. A.S. Fortuna St. Banilad, Cebu City'),(2,'B. RODRIGUEZ (Main)','15 B. Rodriguez St., Capitol Site, Cebu City'),(3,'CAPITOL','OsmeÃ±a Blvd., Cebu City'),(4,'MABOLO','Pope John Paul II Avenue, Mabolo, Cebu City'),(5,'BULACAO - PARDO','Bulacao, Pardo, Cebu City'),(6,'BASAK - PARDO','Basak, Pardo, Cebu City'),(7,'P.DEL ROSARIO','P. Del Rosario St., Cebu City'),(8,'PUNTA, LABANGON','F. Llamas St., Cebu City'),(9,'TALAMBAN','Talamban, Cebu City'),(10,'A.C CORTES','Mandaue City'),(11,'CANDUMAN','Mandaue City'),(12,'TIPOLO','Mandaue City'),(13,'BASAK - LAPU-LAPU','Lapu-Lapu City'),(14,'CORDOVA','Cordova, Cebu'),(15,'PUSOK, LAPU-LAPU CITY','Lapu-Lapu City'),(16,'POBLACION, LAPU-LAPU CITY','Lapu-Lapu City'),(17,'SOONG, MACTAN','Lapu-Lapu City'),(18,'DUMLOG, TALISAY','Talisay City'),(19,'LARAY TALISAY CITY','Talisay City'),(20,'TALISAY-TABUNOK','Talisay City'),(21,'ARGAO','Cebu'),(22,'BANTAYAN','Cebu'),(23,'BALAMBAN','Cebu'),(24,'BARILI','Cebu'),(25,'BOGO CITY','Cebu'),(26,'CARCAR CITY','Cebu'),(27,'CARMEN','Cebu'),(28,'CONSOLACION','Cebu'),(29,'DAANBANTAYAN','Cebu'),(30,'DANAO CITY','Cebu'),(31,'LILOAN','Cebu'),(32,'LUTOPAN','Toledo City'),(33,'MINGLANILLA','Cebu'),(34,'NAGA','Cebu'),(35,'SAN REMIGIO','Cebu'),(36,'SIBONGA','Cebu'),(37,'TOLEDO CITY','Cebu'),(38,'ANTIQUE','Antique'),(39,'BALASAN','Iloilo'),(40,'BAROTAC NUEVO','Iloilo'),(41,'BORACAY','Aklan'),(42,'GEN. LUNA','Iloilo City'),(43,'JARO','Iloilo City'),(44,'KALIBO','Aklan'),(45,'MOLO','Iloilo City'),(46,'POTOTAN','Iloilo'),(47,'ROXAS CITY','Capiz'),(48,'SARA','Iloilo'),(49,'TAFT NORTH','Iloilo City'),(50,'TAGBAK','Iloilo City'),(51,'BACOLOD CITY','Negros Occidental'),(52,'CERVANTES','Dumaguete City'),(53,'DUMAGUETE (DARO)','Dumaguete City'),(54,'BAYAWAN','Negros Oriental'),(55,'SAN CARLOS CITY','Negros Occidental'),(56,'SIQUIJOR','Siquijor'),(57,'BOLTON','Davao City'),(58,'BAJADA','Davao City'),(59,'DIGOS CITY','Davao del Sur'),(60,'KAWAYAN','Davao City'),(61,'MATI','Davao Oriental'),(62,'MATINA','Davao City'),(63,'PANABO','Davao del Norte'),(64,'STA. CRUZ','Davao del Sur'),(65,'TAGUM CITY','Davao del Norte'),(66,'CALUMPANG','General Santos'),(67,'LAGAO 1','General Santos'),(68,'LAGAO 2','General Santos'),(69,'KIDAPAWAN','Cotabato'),(70,'KORONADAL','South Cotabato'),(71,'MALAKAS','General Santos'),(72,'CARMEN - CDO','Cagayan de Oro'),(73,'DIVISORIA','Cagayan de Oro'),(74,'EL SALVADOR','Misamis Oriental'),(75,'GINGOOG','Misamis Oriental'),(76,'TIBANGA','Iligan City'),(77,'OZAMIZ','Misamis Occidental'),(78,'TUBOD','Iligan City'),(79,'TAGOLOAN','Misamis Oriental'),(80,'VALENCIA','Bukidnon'),(81,'MALAYBALAY','Bukidnon'),(82,'DAPITAN','Zamboanga del Norte'),(83,'DIPOLOG','Zamboanga del Norte'),(84,'PAGADIAN','Zamboanga del Sur'),(85,'SINDANGAN','Zamboanga del Norte'),(86,'BUTUAN 1','Butuan City'),(87,'BUTUAN 2','Butuan City'),(88,'SURIGAO','Surigao City'),(89,'BAYBAY','Leyte'),(90,'CARIGARA','Leyte'),(91,'HILONGOS','Leyte'),(92,'MAASIN','Southern Leyte'),(93,'SOGOD','Southern Leyte'),(94,'NAVAL','Biliran'),(95,'ORMOC','Leyte'),(96,'PALO','Leyte'),(97,'TACLOBAN 1','Leyte'),(98,'TACLOBAN 2','Leyte'),(99,'BORONGAN','Eastern Samar'),(100,'CALBAYOG','Samar'),(101,'CATBALOGAN','Samar'),(102,'TAGBILARAN 1','Bohol'),(103,'TAGBILARAN 2','Bohol'),(104,'TUBIGON','Bohol'),(105,'VISAYAS AVENUE','Quezon City');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Bestsellers'),(3,'House Specialties'),(2,'Kiddies Favorites'),(5,'New Flavors'),(4,'Other Flavors'),(9,'Random'),(8,'Samples');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `pizza_name` varchar(255) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `cheese` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,'Beef And Mushroom','11\"','Quickmelt',220.00,1,220.00),(2,1,'Pizza Supreme','11\"','Quickmelt',195.00,1,195.00),(3,2,'Aloha','11\"','Quickmelt',200.00,1,200.00),(4,2,'Pizza Supreme','11\"','Quickmelt',195.00,1,195.00),(5,3,'Loaded Hawaiian','11\"','Mozzarella',260.00,2,520.00),(6,3,'Surf And Turf','11\"','Mozzarella',330.00,1,330.00),(7,4,'Hawaiian','9\"','Quickmelt',145.00,1,145.00),(8,4,'Sisig Twist','11\"','Mozzarella',260.00,1,260.00),(9,4,'Albertos Full House','11\"','Mozzarella',300.00,3,900.00),(10,4,'Hawaiian','9\"','Mozzarella',165.00,2,330.00),(11,5,'Hawaiian','9\"','Quickmelt',145.00,1,145.00),(12,5,'Surf And Turf','11\"','Quickmelt',310.00,2,620.00),(13,5,'Mango Graham','11\"','Mozzarella',240.00,1,240.00),(14,5,'Shrimp And Mushroom','11\"','Mozzarella',250.00,3,750.00),(15,6,'Chocomallow','9\"','Quickmelt',170.00,1,170.00),(16,6,'Bacon Mushroom','9\"','Quickmelt',170.00,1,170.00),(17,6,'Garden Express','9\"','Quickmelt',125.00,1,125.00),(18,7,'Surf And Turf','11\"','Mozzarella',330.00,3,990.00),(19,7,'Salad Pizza','11\"','Mozzarella',315.00,3,945.00),(20,8,'Aloha','9\"','Quickmelt',150.00,1,150.00),(21,8,'Cookies N Cheese','9\"','Quickmelt',110.00,1,110.00),(22,9,'Meaty Royale','9\"','Quickmelt',0.00,1,0.00),(23,9,'Aloha','9\"','Quickmelt',150.00,1,150.00),(24,9,'Ham And Egg','9\"','Quickmelt',180.00,1,180.00),(25,10,'Oreo Pina','9\"','Quickmelt',115.00,1,115.00),(26,10,'Chizzo Trio','9\"','Quickmelt',150.00,1,150.00),(27,10,'Pizza Tropicana','11\"','Mozzarella',280.00,1,280.00),(28,11,'Salad Pizza','11\"','Quickmelt',295.00,1,295.00),(29,11,'Ham Delight','9\"','Mozzarella',160.00,1,160.00),(30,12,'All Pepperoni','9\"','Quickmelt',200.00,1,200.00),(31,12,'Royal Rumble','11\"','Quickmelt',260.00,1,260.00),(32,12,'Mango Bacon','11\"','Mozzarella',285.00,3,855.00),(33,13,'Hawaiian','9\"','Quickmelt',145.00,1,145.00),(34,13,'Sisig Twist','11\"','Mozzarella',260.00,1,260.00),(35,13,'Albertos Full House','11\"','Mozzarella',300.00,3,900.00),(36,13,'Hawaiian','9\"','Mozzarella',165.00,2,330.00),(37,14,'Oreo Pina','9\"','Quickmelt',115.00,1,115.00),(38,14,'Chizzo Trio','9\"','Quickmelt',150.00,1,150.00),(39,14,'Pizza Tropicana','11\"','Mozzarella',280.00,1,280.00),(40,15,'Salad Pizza','11\"','Quickmelt',295.00,1,295.00),(41,15,'Ham Delight','9\"','Mozzarella',160.00,1,160.00),(42,16,'Meaty Royale','9\"','Quickmelt',0.00,1,0.00),(43,16,'Aloha','9\"','Quickmelt',150.00,1,150.00),(44,16,'Ham And Egg','9\"','Quickmelt',180.00,1,180.00),(45,17,'Hawaiian','9\"','Quickmelt',145.00,1,145.00),(46,17,'Sisig Twist','11\"','Mozzarella',260.00,1,260.00),(47,17,'Albertos Full House','11\"','Mozzarella',300.00,3,900.00),(48,17,'Hawaiian','9\"','Mozzarella',165.00,2,330.00),(49,18,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(50,19,'Hawaiian','9','Quickmelt',145.00,1,145.00),(51,19,'Pizza Supreme','9','Quickmelt',145.00,3,435.00),(52,20,'Hawaiian','9','Quickmelt',145.00,1,145.00),(53,20,'Pizza Supreme','9','Quickmelt',145.00,3,435.00),(54,21,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(55,22,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(56,22,'Surf And Turf','11','Mozzarella',330.00,1,330.00),(57,23,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(58,24,'Hawaiian','9','Quickmelt',145.00,1,145.00),(59,24,'Mango Bacon','11','Mozzarella',285.00,1,285.00),(60,25,'Oreo Piña','9','Quickmelt',120.00,2,240.00),(61,26,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(62,27,'All Pepperoni','9','Quickmelt',200.00,1,200.00),(63,28,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(64,29,'All Pepperoni','9','Quickmelt',200.00,1,200.00),(65,30,'All Pepperoni','9','Quickmelt',200.00,1,200.00),(66,31,'Hawaiian','9','Quickmelt',145.00,1,145.00),(67,32,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(68,32,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),(69,33,'Hawaiian','9','Quickmelt',145.00,1,145.00),(70,34,'Hawaiian','9','Quickmelt',145.00,1,145.00),(71,35,'Chogburizo','9','Quickmelt',175.00,1,175.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `branch_id` int DEFAULT NULL,
  `address` text,
  `order_type` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `driver_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,NULL,'Guest','09876543210','gil.zivra@gmail.com',93,'Zone 5','PICK-UP','CASH',415.00,'cancelled','2026-05-20 12:21:46',NULL,NULL),(2,4,'customer2','09876543213','customer2@gmail.com',93,'Zone 5','PICK-UP','CASH',395.00,'pending','2026-05-20 14:49:30',NULL,NULL),(3,NULL,'Moxie','09876543213','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','ONLINE',850.00,'pending','2026-05-20 15:07:49',NULL,'2026-05-26 04:33:41'),(4,NULL,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','CASH',1635.00,'delivered','2026-05-20 15:17:52',9,'2026-05-25 07:02:07'),(5,NULL,'Moxie3','09876543218','Moxie3@gmail.com',92,'Zone 5','PICK-UP','ONLINE',1755.00,'pending','2026-05-20 15:28:16',NULL,'2026-05-26 04:33:41'),(6,4,'customer2','09876543213','customer2@gmail.com',25,'Zone 1, Baybay, Leyte','PICK-UP','ONLINE',465.00,'pending','2026-05-20 23:51:15',NULL,'2026-05-26 04:33:41'),(7,4,'customer2','09876543213','customer2@gmail.com',99,'Zone 5','PICK-UP','ONLINE',1935.00,'pending','2026-05-20 23:52:34',NULL,'2026-05-26 04:33:41'),(8,NULL,'Doxie','09876543219','Doxie@gmail.com',41,'Zone 5','DELIVERY','ONLINE',260.00,'pending','2026-05-20 23:58:13',NULL,'2026-05-26 04:33:41'),(9,NULL,'Doug','09098877664','',23,'Zone 5','PICK-UP','ONLINE',330.00,'completed','2026-05-21 00:32:31',NULL,'2026-05-26 04:33:41'),(10,NULL,'Dolly','09876509876','Dolly@gmail.com',21,'Zone 5','PICK-UP','ONLINE',545.00,'completed','2026-05-21 00:57:32',NULL,'2026-05-26 04:33:41'),(11,4,'customer2','09876543213','customer2@gmail.com',89,'Zone 5','DELIVERY','ONLINE',455.00,'completed','2026-05-21 00:59:08',NULL,'2026-05-26 04:33:41'),(12,NULL,'Chicharon','09876543217','Chii@gmail.com',99,'Zone 5','PICK-UP','CASH',1315.00,'pending','2026-05-21 03:09:43',NULL,NULL),(13,NULL,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','CASH',1635.00,'out_for_delivery','2026-05-21 06:36:30',9,'2026-05-25 07:02:11'),(14,NULL,'Dolly','09876509876','Dolly@gmail.com',21,'Zone 5','PICK-UP','ONLINE',545.00,'pending','2026-05-23 02:59:38',NULL,'2026-05-26 04:33:41'),(15,2,'customer2','09876543213','customer2@gmail.com',89,'Zone 5','on','ONLINE',455.00,'pending','2026-05-23 05:57:17',NULL,'2026-05-26 04:33:41'),(16,2,'Doug','09098877664','',23,'Zone 5','on','ONLINE',330.00,'pending','2026-05-24 01:26:41',NULL,'2026-05-26 04:33:41'),(17,2,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','on','CASH',1635.00,'pending','2026-05-24 01:30:52',NULL,NULL),(18,NULL,'Zivra','09876543211','gil.zivra@gmail.com',41,'0','PICK-UP','CASH',145.00,'completed','2026-05-24 19:40:01',NULL,NULL),(19,NULL,'Zzz','09876543232','',99,'0','PICK-UP','CASH',580.00,'completed','2026-05-24 19:42:24',NULL,NULL),(20,2,'Zzz','09876543232','',99,'0','on','on',580.00,'completed','2026-05-24 19:45:38',NULL,NULL),(21,1,'customer1','09876543210','customer1@gmail.com',99,'0','PICK-UP','CASH',145.00,'completed','2026-05-25 04:03:24',NULL,NULL),(22,2,'customer1','09876543210','customer1@gmail.com',99,'0','PICK-UP','CASH',475.00,'completed','2026-05-25 05:50:12',NULL,NULL),(23,2,'Zivra','09876543211','gil.zivra@gmail.com',41,'0','PICK-UP','CASH',145.00,'completed','2026-05-25 06:19:41',NULL,NULL),(24,NULL,'Horn','09123123123','',93,'0','DELIVERY','ONLINE',430.00,'pending','2026-05-25 06:33:52',NULL,'2026-05-26 04:33:41'),(25,NULL,'JoJo','09876565445','',93,'0','DELIVERY','ONLINE',240.00,'delivered','2026-05-26 03:06:47',9,'2026-05-26 04:40:32'),(26,NULL,'Jen','09876543121','Jen@gmail.com',89,'0','PICK-UP','CASH',145.00,'pending','2026-05-26 03:18:03',NULL,NULL),(27,2,'Joy','09876543213','Joy@email.com',89,'0','PICK-UP','CASH',200.00,'completed','2026-05-26 03:28:30',NULL,NULL),(28,NULL,'Dove','09123124534','Dove@gmail.com',24,'Zone 5','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:01:24',NULL,'2026-05-26 04:33:41'),(29,NULL,'Kiin','09871235435','Kiin@gmail.com',25,'Sample Zone','DELIVERY','ONLINE',200.00,'completed','2026-05-26 04:03:59',NULL,'2026-05-26 04:33:41'),(30,2,'Kiin','09871235435','Kiin@gmail.com',25,'Sample Zone','DELIVERY','CASH',200.00,'completed','2026-05-26 04:05:05',NULL,NULL),(31,NULL,'frog','09876364564','',89,'','PICK-UP','ONLINE',145.00,'pending','2026-05-26 04:20:05',NULL,NULL),(32,NULL,'devil','09745634534','devil@emailcom',57,'Zone 5','DELIVERY','ONLINE',290.00,'pending','2026-05-26 04:21:18',NULL,NULL),(33,NULL,'fet','09867854634','',25,'Sample Zone','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:22:11',NULL,'2026-05-26 04:33:41'),(34,NULL,'CHET','09784356234','',25,'Sample Zone','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:25:06',NULL,NULL),(35,NULL,'Del','09873423413','',59,'Sample Zone','DELIVERY','ONLINE',175.00,'pending','2026-05-26 05:36:01',NULL,NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pizza_variants`
--

DROP TABLE IF EXISTS `pizza_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pizza_variants` (
  `variant_id` int NOT NULL AUTO_INCREMENT,
  `pizza_id` int NOT NULL,
  `size` int NOT NULL,
  `cheese` varchar(50) NOT NULL,
  `price` decimal(6,2) NOT NULL,
  PRIMARY KEY (`variant_id`),
  KEY `pizza_id` (`pizza_id`),
  CONSTRAINT `pizza_variants_ibfk_1` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pizza_variants`
--

LOCK TABLES `pizza_variants` WRITE;
/*!40000 ALTER TABLE `pizza_variants` DISABLE KEYS */;
INSERT INTO `pizza_variants` VALUES (1,1,9,'Quickmelt',145.00),(2,1,9,'Mozzarella',165.00),(3,1,11,'Quickmelt',195.00),(4,1,11,'Mozzarella',215.00),(9,3,9,'Quickmelt',110.00),(11,3,9,'Mozzarella',130.00),(13,3,11,'Quickmelt',150.00),(15,3,11,'Mozzarella',170.00),(17,4,9,'Quickmelt',115.00),(18,4,9,'Mozzarella',135.00),(19,4,11,'Quickmelt',155.00),(20,4,11,'Mozzarella',175.00),(21,5,9,'Quickmelt',120.00),(22,5,9,'Mozzarella',135.00),(23,5,11,'Quickmelt',155.00),(24,5,11,'Mozzarella',175.00),(25,6,9,'Quickmelt',153.00),(26,6,9,'Mozzarella',155.00),(27,6,11,'Quickmelt',185.00),(28,6,11,'Mozzarella',205.00),(29,7,9,'Quickmelt',140.00),(30,7,9,'Mozzarella',160.00),(31,7,11,'Quickmelt',190.00),(32,7,11,'Mozzarella',210.00),(33,8,9,'Quickmelt',170.00),(34,8,9,'Mozzarella',190.00),(35,8,11,'Quickmelt',220.00),(36,8,11,'Mozzarella',240.00),(37,9,9,'Quickmelt',180.00),(38,9,9,'Mozzarella',200.00),(39,9,11,'Quickmelt',230.00),(40,9,11,'Mozzarella',250.00),(41,9,9,'Quickmelt',180.00),(42,9,9,'Mozzarella',200.00),(43,9,11,'Quickmelt',230.00),(44,9,11,'Mozzarella',250.00),(45,10,9,'Quickmelt',150.00),(46,10,9,'Mozzarella',150.00),(47,10,11,'Quickmelt',200.00),(48,10,11,'Mozzarella',200.00),(49,11,11,'Quickmelt',220.00),(50,11,11,'Mozzarella',240.00),(51,12,9,'Quickmelt',175.00),(52,12,9,'Mozzarella',195.00),(53,12,11,'Quickmelt',225.00),(54,12,11,'Mozzarella',245.00),(55,13,9,'Quickmelt',180.00),(56,13,9,'Mozzarella',200.00),(57,13,11,'Quickmelt',230.00),(58,13,11,'Mozzarella',250.00),(59,14,9,'Quickmelt',205.00),(60,14,9,'Mozzarella',225.00),(61,14,11,'Quickmelt',255.00),(62,14,11,'Mozzarella',275.00),(63,15,11,'Quickmelt',230.00),(64,15,11,'Mozzarella',250.00),(65,16,11,'Quickmelt',240.00),(66,16,11,'Mozzarella',260.00),(67,17,11,'Quickmelt',260.00),(68,17,11,'Mozzarella',280.00),(69,18,11,'Quickmelt',265.00),(70,18,11,'Mozzarella',285.00),(71,19,11,'Quickmelt',265.00),(72,19,11,'Mozzarella',285.00),(73,20,11,'Quickmelt',265.00),(74,20,11,'Mozzarella',285.00),(75,21,11,'Quickmelt',295.00),(76,21,11,'Mozzarella',315.00),(77,22,11,'Quickmelt',310.00),(78,22,11,'Mozzarella',330.00),(79,23,11,'Quickmelt',360.00),(80,23,11,'Mozzarella',380.00),(81,24,11,'Quickmelt',325.00),(82,24,11,'Mozzarella',325.00),(83,25,9,'Quickmelt',145.00),(84,1,9,'Quickmelt',145.00),(85,25,9,'Mozzarella',165.00),(86,1,9,'Mozzarella',165.00),(87,25,11,'Quickmelt',195.00),(88,1,11,'Quickmelt',195.00),(89,25,11,'Mozzarella',215.00),(90,1,11,'Mozzarella',215.00),(91,26,9,'Quickmelt',145.00),(92,26,9,'Mozzarella',165.00),(93,26,11,'Quickmelt',195.00),(94,26,11,'Mozzarella',215.00),(95,27,9,'Quickmelt',150.00),(96,27,9,'Mozzarella',170.00),(97,27,11,'Quickmelt',200.00),(98,27,11,'Mozzarella',220.00),(99,28,9,'Quickmelt',170.00),(100,28,9,'Mozzarella',190.00),(101,28,11,'Quickmelt',220.00),(102,28,11,'Mozzarella',240.00),(103,29,9,'Quickmelt',200.00),(104,29,9,'Mozzarella',220.00),(105,29,11,'Quickmelt',250.00),(106,29,11,'Mozzarella',270.00),(107,30,11,'Quickmelt',240.00),(108,30,11,'Mozzarella',260.00),(109,31,11,'Quickmelt',270.00),(110,31,11,'Mozzarella',290.00),(111,32,11,'Quickmelt',280.00),(112,32,11,'Mozzarella',300.00),(113,33,11,'Quickmelt',320.00),(114,33,11,'Mozzarella',340.00),(115,34,9,'Quickmelt',125.00),(116,34,9,'Mozzarella',145.00),(117,34,11,'Quickmelt',175.00),(118,34,11,'Mozzarella',195.00),(119,35,9,'Quickmelt',155.00),(120,35,9,'Mozzarella',175.00),(121,35,11,'Quickmelt',205.00),(122,35,11,'Mozzarella',225.00),(123,36,9,'Quickmelt',160.00),(124,36,9,'Mozzarella',180.00),(125,36,11,'Quickmelt',210.00),(126,36,11,'Mozzarella',230.00),(127,37,9,'Quickmelt',160.00),(128,37,9,'Mozzarella',180.00),(129,37,11,'Quickmelt',210.00),(130,37,11,'Mozzarella',230.00),(131,38,9,'Quickmelt',160.00),(132,38,9,'Mozzarella',185.00),(133,38,11,'Quickmelt',215.00),(134,38,11,'Mozzarella',235.00),(135,39,9,'Quickmelt',170.00),(136,39,9,'Mozzarella',190.00),(137,39,11,'Quickmelt',220.00),(138,39,11,'Mozzarella',240.00),(139,40,9,'Quickmelt',170.00),(140,40,9,'Mozzarella',190.00),(141,40,11,'Quickmelt',220.00),(142,40,11,'Mozzarella',240.00),(143,41,9,'Quickmelt',180.00),(144,41,9,'Mozzarella',205.00),(145,41,11,'Quickmelt',230.00),(146,41,11,'Mozzarella',250.00),(147,42,9,'Quickmelt',185.00),(148,42,9,'Mozzarella',205.00),(149,42,11,'Quickmelt',235.00),(150,42,11,'Mozzarella',255.00),(151,43,9,'Quickmelt',200.00),(152,43,9,'Mozzarella',220.00),(153,43,11,'Quickmelt',250.00),(154,43,11,'Mozzarella',270.00),(155,44,9,'Quickmelt',210.00),(156,44,9,'Mozzarella',230.00),(157,44,11,'Quickmelt',260.00),(158,44,11,'Mozzarella',280.00),(159,45,11,'Quickmelt',240.00),(160,45,11,'Mozzarella',260.00),(161,46,11,'Quickmelt',285.00),(162,46,11,'Mozzarella',305.00),(163,47,11,'Quickmelt',225.00),(164,47,11,'Mozzarella',245.00),(165,48,11,'Quickmelt',260.00),(166,48,11,'Mozzarella',280.00),(175,51,9,'Quickmelt',100.00),(176,51,11,'Quickmelt',0.00),(177,51,9,'Mozzarella',110.00),(178,51,11,'Mozzarella',0.00),(183,53,9,'Quickmelt',0.00),(184,53,11,'Quickmelt',150.00),(185,53,9,'Mozzarella',0.00),(186,53,11,'Mozzarella',180.00),(191,55,9,'Quickmelt',110.00),(192,55,11,'Quickmelt',120.00),(193,55,9,'Mozzarella',140.00),(194,55,11,'Mozzarella',159.00);
/*!40000 ALTER TABLE `pizza_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pizza_variants_temp`
--

DROP TABLE IF EXISTS `pizza_variants_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pizza_variants_temp` (
  `pizza_name` varchar(100) DEFAULT NULL,
  `size` int DEFAULT NULL,
  `cheese` varchar(50) DEFAULT NULL,
  `price` decimal(6,2) DEFAULT NULL,
  `pizza_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pizza_variants_temp`
--

LOCK TABLES `pizza_variants_temp` WRITE;
/*!40000 ALTER TABLE `pizza_variants_temp` DISABLE KEYS */;
INSERT INTO `pizza_variants_temp` VALUES ('Cookies N Cheese',9,'Quickmelt',110.00,'CNC9Q'),('Cookies N Cheese',9,'Mozzarella',130.00,'CNC9M'),('Cookies N Cheese',11,'Quickmelt',150.00,'CNC11Q'),('Cookies N Cheese',11,'Mozzarella',170.00,'CNC11M'),('Creamy Cheese',9,'Quickmelt',115.00,'CC9Q'),('Creamy Cheese',9,'Mozzarella',135.00,'CC9M'),('Creamy Cheese',11,'Quickmelt',155.00,'CC11Q'),('Creamy Cheese',11,'Mozzarella',175.00,'CC11M'),('Oreo Pina',9,'Quickmelt',115.00,'OP9Q'),('Oreo Pina',9,'Mozzarella',135.00,'OP9M'),('Oreo Pina',11,'Quickmelt',155.00,'OP11Q'),('Oreo Pina',11,'Mozzarella',175.00,'OP11M'),('Yummy Hotdog',9,'Quickmelt',153.00,'YH9Q'),('Yummy Hotdog',9,'Mozzarella',155.00,'YH9M'),('Yummy Hotdog',11,'Quickmelt',185.00,'YH11Q'),('Yummy Hotdog',11,'Mozzarella',205.00,'YH11M'),('Ham Delight',9,'Quickmelt',140.00,'HD9Q'),('Ham Delight',9,'Mozzarella',160.00,'HD9M'),('Ham Delight',11,'Quickmelt',190.00,'HD11Q'),('Ham Delight',11,'Mozzarella',210.00,'HD11M'),('Chocomallow',9,'Quickmelt',170.00,'CMLW9Q'),('Chocomallow',9,'Mozzarella',190.00,'CMLW9M'),('Chocomallow',11,'Quickmelt',220.00,'CMLW11Q'),('Chocomallow',11,'Mozzarella',240.00,'CMLW11M'),('Ham And Egg',9,'Quickmelt',180.00,'HNE9Q'),('Ham And Egg',9,'Mozzarella',200.00,'HNE9M'),('Ham And Egg',11,'Quickmelt',230.00,'HNE11Q'),('Ham And Egg',11,'Mozzarella',250.00,'HNE11M'),('Ham And Egg',9,'Quickmelt',180.00,'HNE9Q'),('Ham And Egg',9,'Mozzarella',200.00,'HNE9M'),('Ham And Egg',11,'Quickmelt',230.00,'HNE11Q'),('Ham And Egg',11,'Mozzarella',250.00,'HNE11M'),('Chizzo Trio',9,'Quickmelt',150.00,'CT9Q'),('Chizzo Trio',9,'Mozzarella',150.00,'CT9M'),('Chizzo Trio',11,'Quickmelt',200.00,'CT11Q'),('Chizzo Trio',11,'Mozzarella',200.00,'CT11M'),('Mango Graham',11,'Quickmelt',220.00,'MG11Q'),('Mango Graham',11,'Mozzarella',240.00,'MG11M'),('Chogburizo',9,'Quickmelt',175.00,'CHGBRZ9Q'),('Chogburizo',9,'Mozzarella',195.00,'CHGBRZ9M'),('Chogburizo',11,'Quickmelt',225.00,'CHGBRZ11Q'),('Chogburizo',11,'Mozzarella',245.00,'CHGBRZ11M'),('Buffalo Chicken',9,'Quickmelt',180.00,'BC9Q'),('Buffalo Chicken',9,'Mozzarella',200.00,'BC9M'),('Buffalo Chicken',11,'Quickmelt',230.00,'BC11Q'),('Buffalo Chicken',11,'Mozzarella',250.00,'BC11M'),('Beef Shawarma',9,'Quickmelt',205.00,'BS9Q'),('Beef Shawarma',9,'Mozzarella',225.00,'BS9M'),('Beef Shawarma',11,'Quickmelt',255.00,'BS11Q'),('Beef Shawarma',11,'Mozzarella',275.00,'BS11M'),('Shrimp And Mushroom',11,'Quickmelt',230.00,'SAM11Q'),('Shrimp And Mushroom',11,'Mozzarella',250.00,'SAM11M'),('Sisig Twist',11,'Quickmelt',240.00,'SSGT11Q'),('Sisig Twist',11,'Mozzarella',260.00,'SSGT11M'),('Royal Rumble',11,'Quickmelt',260.00,'RR11Q'),('Royal Rumble',11,'Mozzarella',280.00,'RR11M'),('Spanish Sardines',11,'Quickmelt',265.00,'SPAS11Q'),('Spanish Sardines',11,'Mozzarella',285.00,'SPAS11M'),('Anchovy Pizza',11,'Quickmelt',265.00,'ANCHP11Q'),('Anchovy Pizza',11,'Mozzarella',285.00,'ANCHP11M'),('Mango Bacon',11,'Quickmelt',265.00,'MB11Q'),('Mango Bacon',11,'Mozzarella',285.00,'MB11M'),('Salad Pizza',11,'Quickmelt',295.00,'SP11Q'),('Salad Pizza',11,'Mozzarella',315.00,'SP11M'),('Surf And Turf',11,'Quickmelt',310.00,'SAT11Q'),('Surf And Turf',11,'Mozzarella',330.00,'SAT11M'),('Pizza D Marina',11,'Quickmelt',360.00,'PDM11Q'),('Pizza D Marina',11,'Mozzarella',380.00,'PDM11M'),('Royal Flush',11,'Quickmelt',325.00,'RF11Q'),('Royal Flush',11,'Mozzarella',325.00,'RF11M'),('Pizza Supreme',9,'Quickmelt',145.00,'PS9Q'),('Pizza Supreme',9,'Mozzarella',165.00,'PS9M'),('Pizza Supreme',11,'Quickmelt',195.00,'PS11Q'),('Pizza Supreme',11,'Mozzarella',215.00,'PS11M'),('Hawaiian',9,'Quickmelt',145.00,'H9Q'),('Hawaiian',9,'Mozzarella',165.00,'H9M'),('Hawaiian',11,'Quickmelt',195.00,'H11Q'),('Hawaiian',11,'Mozzarella',215.00,'H11M'),('Aloha',9,'Quickmelt',150.00,'A9Q'),('Aloha',9,'Mozzarella',170.00,'A9M'),('Aloha',11,'Quickmelt',200.00,'A11Q'),('Aloha',11,'Mozzarella',220.00,'A11M'),('Beef And Mushroom',9,'Quickmelt',170.00,'BAM9Q'),('Beef And Mushroom',9,'Mozzarella',190.00,'BAM9M'),('Beef And Mushroom',11,'Quickmelt',220.00,'BAM11Q'),('Beef And Mushroom',11,'Mozzarella',240.00,'BAM11M'),('All Pepperoni',9,'Quickmelt',200.00,'ALLP9Q'),('All Pepperoni',9,'Mozzarella',220.00,'ALLP9M'),('All Pepperoni',11,'Quickmelt',250.00,'ALLP11Q'),('All Pepperoni',11,'Mozzarella',270.00,'ALLP11M'),('Loaded Hawaiian',11,'Quickmelt',240.00,'LH11Q'),('Loaded Hawaiian',11,'Mozzarella',260.00,'LH11M'),('Meaty Royale',11,'Quickmelt',270.00,'MR11Q'),('Meaty Royale',11,'Mozzarella',290.00,'MR11M'),('Albertos Full House',11,'Quickmelt',280.00,'AFH11Q'),('Albertos Full House',11,'Mozzarella',300.00,'AFH11M'),('Ceamy Cucumber Spinach',11,'Quickmelt',320.00,'CCS11Q'),('Ceamy Cucumber Spinach',11,'Mozzarella',340.00,'CCS11M'),('Garden Express',9,'Quickmelt',125.00,'GEX9Q'),('Garden Express',9,'Mozzarella',145.00,'GEX9M'),('Garden Express',11,'Quickmelt',175.00,'GEX11Q'),('Garden Express',11,'Mozzarella',195.00,'GEX11M'),('Vegetarian',9,'Quickmelt',155.00,'V9Q'),('Vegetarian',9,'Mozzarella',175.00,'V9M'),('Vegetarian',11,'Quickmelt',205.00,'V11Q'),('Vegetarian',11,'Mozzarella',225.00,'V11M'),('All Hungarian',9,'Quickmelt',160.00,'AH9Q'),('All Hungarian',9,'Mozzarella',180.00,'AH9M'),('All Hungarian',11,'Quickmelt',210.00,'AH11Q'),('All Hungarian',11,'Mozzarella',230.00,'AH11M'),('Beef Pepperoni',9,'Quickmelt',160.00,'BP9Q'),('Beef Pepperoni',9,'Mozzarella',180.00,'BP9M'),('Beef Pepperoni',11,'Quickmelt',210.00,'BP11Q'),('Beef Pepperoni',11,'Mozzarella',230.00,'BP11M'),('Pizza Burger',9,'Quickmelt',160.00,'PB9Q'),('Pizza Burger',9,'Mozzarella',185.00,'PB9M'),('Pizza Burger',11,'Quickmelt',215.00,'PB11Q'),('Pizza Burger',11,'Mozzarella',235.00,'PB11M'),('Chicken Garlic',9,'Quickmelt',170.00,'CG9Q'),('Chicken Garlic',9,'Mozzarella',190.00,'CG9M'),('Chicken Garlic',11,'Quickmelt',220.00,'CG11Q'),('Chicken Garlic',11,'Mozzarella',240.00,'CG11M'),('Bacon Mushroom',9,'Quickmelt',170.00,'BM9Q'),('Bacon Mushroom',9,'Mozzarella',190.00,'BM9M'),('Bacon Mushroom',11,'Quickmelt',220.00,'BM11Q'),('Bacon Mushroom',11,'Mozzarella',240.00,'BM11M'),('Tuna Garlic',9,'Quickmelt',180.00,'TG9Q'),('Tuna Garlic',9,'Mozzarella',205.00,'TG9M'),('Tuna Garlic',11,'Quickmelt',230.00,'TG11Q'),('Tuna Garlic',11,'Mozzarella',250.00,'TG11M'),('Three Of A Kind',9,'Quickmelt',185.00,'3K9Q'),('Three Of A Kind',9,'Mozzarella',205.00,'3K9M'),('Three Of A Kind',11,'Quickmelt',235.00,'3K11Q'),('Three Of A Kind',11,'Mozzarella',255.00,'3K11M'),('Chessy Krainer',9,'Quickmelt',200.00,'CK9Q'),('Chessy Krainer',9,'Mozzarella',220.00,'CK9M'),('Chessy Krainer',11,'Quickmelt',250.00,'CK11Q'),('Chessy Krainer',11,'Mozzarella',270.00,'CK11M'),('Meatlovers Deluxe',9,'Quickmelt',210.00,'MD9Q'),('Meatlovers Deluxe',9,'Mozzarella',230.00,'MD9M'),('Meatlovers Deluxe',11,'Quickmelt',260.00,'MD11Q'),('Meatlovers Deluxe',11,'Mozzarella',280.00,'MD11M'),('Spinach N Chicken Pizza',11,'Quickmelt',240.00,'SNCP11Q'),('Spinach N Chicken Pizza',11,'Mozzarella',260.00,'SNCP11M'),('Loaded Pepperoni',11,'Quickmelt',285.00,'LP11Q'),('Loaded Pepperoni',11,'Mozzarella',305.00,'LP11M'),('Spicy Meatzza',11,'Quickmelt',225.00,'SM11Q'),('Spicy Meatzza',11,'Mozzarella',245.00,'SM11M'),('Pizza Tropicana',11,'Quickmelt',260.00,'PT11Q'),('Pizza Tropicana',11,'Mozzarella',280.00,'PT11M');
/*!40000 ALTER TABLE `pizza_variants_temp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pizzas`
--

DROP TABLE IF EXISTS `pizzas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pizzas` (
  `pizza_id` int NOT NULL AUTO_INCREMENT,
  `pizza_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `ingredients` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `stock` int DEFAULT '0',
  PRIMARY KEY (`pizza_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pizzas`
--

LOCK TABLES `pizzas` WRITE;
/*!40000 ALTER TABLE `pizzas` DISABLE KEYS */;
INSERT INTO `pizzas` VALUES (1,'Pizza Supreme','Bestsellers','Pork pepperoni, bacon, mushroom, onions, pineapple tidbits, black olives, green bell pepper','menu/Bestsellers/Pizza Supreme.png',8),(3,'Cookies N Cheese','Kiddies Favorites','Crushed Oreo Cookies','menu/Kiddies Favorites/Cookies N Cheese.png',10),(4,'Creamy Cheese','Kiddies Favorites','Cream Cheese','menu/Kiddies Favorites/Creamy Cheese.png',10),(5,'Oreo Piña','Kiddies Favorites','crushed Oreo cookies with pineapple tidbits','menu/Kiddies Favorites/Oreo Pina.png',10),(6,'Yummy Hotdog','Kiddies Favorites','All hotdog','menu/Kiddies Favorites/Yummy Hotdog.png',5),(7,'Ham Delights','Kiddies Favorites','All ham','menu/Kiddies Favorites/Ham Delight.png',5),(8,'Chocomallow','Kiddies Favorites','Choco stick, crushed oreo, marshmallow, & choco syrup','menu/Kiddies Favorites/Chocomallow.png',10),(9,'Ham And Egg','Kiddies Favorites','Ham, ham sausage, hamonado, egg, tomatoes, & onions','menu/Kiddies Favorites/Ham And Egg.png',0),(10,'Chizzo Trio','Kiddies Favorites','Quickmelt, Mozzarella, & Cheddar cheese','menu/Kiddies Favorites/Chizzo Trio.png',0),(11,'Mango Graham','Kiddies Favorites','Cream Cheese, Mango, & crushed Graham creackers','menu/Kiddies Favorites/Mango Graham.png',0),(12,'Chogburizo','House Specialties','Chorizo de Cebu, ham sausage, & onions','menu/House Specialties/Chogburizo.png',20),(13,'Buffalo Chicken','House Specialties','Chicken minced, pineapple tidbits, onions, red bell pepper, & AP buffalo sauce','menu/House Specialties/Buffalo Chicken.png',0),(14,'Beef Shawarma','House Specialties','Ground beef, cucumber, lettuce, tomatoes, onions, & AP shawarma sauce','menu/House Specialties/Beef Shawarma.png',0),(15,'Shrimp And Mushroom','House Specialties','Shrimp, mushroom, red bell pepper, tomatoes, onions, & garlic bits','menu/House Specialties/Shrimp And Mushroom.png',5),(16,'Sisig Twist','House Specialties','Pork sisig, onions, red bell pepper, garlic bits, calamansi, & AP sisig sauce','menu/House Specialties/Sisig Twist.png',0),(17,'Royal Rumble','House Specialties','Beef pepperoni, ham, salami, hungarian sausage, cheese krainer, pork, pepperoni, hotdog, chicken hotdog, hamonado, shrimp, chicken minced, crab stick, cucumber, onions, pineapple tidbits, black olives, muchroom, & AP Rumble Sauce','menu/House Specialties/Royal Rumble.png',0),(18,'Spanish Sardines','House Specialties','Spanish sardines, carrots, pickle, tomatoes & onions','menu/House Specialties/Spanish Sardines.png',0),(19,'Anchovy Pizza','House Specialties','Anchovies, beef pepperoni, black olives, red bell pepper, tomatoes, onions, & garlic bits','menu/House Specialties/Anchovy Pizza.png',0),(20,'Mango Bacon','House Specialties','Cream cheese, mango, bacon, & green bell pepper','menu/House Specialties/Mango Bacon.png',10),(21,'Salad Pizza','House Specialties','Crab stick, spinach, cucumber, onions, tomatoes, black olives, cheddar cheese, garlic bits, lettuce, boiled egg, & AP salad dressing','menu/House Specialties/Salad Pizza.png',0),(22,'Surf And Turf','House Specialties','Corned beef, ground beef, tuna flakes, crab stick, shrimp, onions, & garlic bits','menu/House Specialties/Surf And Turf.png',9),(23,'Pizza D Marina','House Specialties','Spanish sardines, tuna flakes, crab stick, anchovies, shrimp, carrot, pickles, onions, & tomatoes','menu/House Specialties/Pizza D Marina.png',0),(24,'Royal Flush','House Specialties','Cheese krainer, pork pepperoni, hungarian sausage, ham sausage, hotdog, chicken hotdog, hamonado, ground beef, chicken minced, tuna flakes, ham, chorizo de Cebu, bacon, salami, & cheddar cheese','menu/House Specialties/Royal Flush.png',0),(25,'Pizza Supreme','Bestsellers','Pork pepperoni, bacon, mushroom, onions, pineapple tidbits, black olives, & green bell pepper','menu/Bestsellers/Pizza Supreme.png',8),(26,'Hawaiian','Bestsellers','Ham, bacon, pineapple tidbits, mushroom, onions, & green bell pepper','menu/Bestsellers/Hawaiian.png',9),(27,'Aloha','Bestsellers','Ham sausage, lots of pineapple tidbits, mushrrom, & green bell pepper','menu/Bestsellers/Aloha.png',10),(28,'Beef And Mushroom','Bestsellers','Ground beef, mushroom, red bell pepper, & onions','menu/Bestsellers/Beef And Mushroom.png',0),(29,'All Pepperoni','Bestsellers','Pork pepperoni with AP hot sauce','menu/Bestsellers/All Pepperoni.png',8),(30,'Loaded Hawaiian','Bestsellers','Ham, bacon, pineapple tidbits, mushroom, green bell pepper, & mozzarella cheese','menu/Bestsellers/Loaded Hawaiian.png',0),(31,'Meaty Royale','Bestsellers','Hungarian sausage, pork pepperoni, salami, ham, bacon, & mozzarella cheese','menu/Bestsellers/Meaty Royale.png',0),(32,'Albertos Full House','Bestsellers','Ham, salami, hungarian sausage, bacon, pork pepperoni, chicken hotdog, ground beef, chicken minced, mushroom, pineapple tidbits, black olives, onions, tomatoes, red  & green bell pepper','menu/Bestsellers/Albertos Full House.png',0),(33,'Ceamy Cucumber Spinach','Bestsellers','Cream cheese mix, spinach, cucumber, & garlic bits','menu/Bestsellers/Ceamy Cucumber Spinach.png',0),(34,'Garden Express','Other Flavors','Mushroom, pineapple tidbits, black olives, onions, tomatoes, red & green bell pepper','menu/Other Flavors/Garden Express.png',0),(35,'Vegetarian','Other Flavors','Cucumber, lettuce, tomatoes, mushroom, onions, black olives, red & green bell pepper','menu/Other Flavors/Vegetarian.png',0),(36,'All Hungarian','Other Flavors','Hungarian sausage w/ AP hot sauce','menu/Other Flavors/All Hungarian.png',0),(37,'Beef Pepperoni','Other Flavors','Beef pepperoni','menu/Other Flavors/Beef Pepperoni.png',0),(38,'Pizza Burger','Other Flavors','Bacon, ground beef, mushroom, tomatoes, & onions','menu/Other Flavors/Pizza Burger.png',0),(39,'Chicken Garlic','Other Flavors','Chicken hotdog, chicken minced, tomatoes, & onions','menu/Other Flavors/Chicken Garlic.png',0),(40,'Bacon Mushroom','Other Flavors','Bacon, mushroom, tomatoes & onions','menu/Other Flavors/Bacon Mushroom.png',0),(41,'Tuna Garlic','Other Flavors','Tuna flakes, tomatoes, & onions','menu/Other Flavors/Tuna Garlic.png',0),(42,'Three Of A Kind','Other Flavors','Ground beef, chicken minced, & tuna flakes','menu/Other Flavors/Three Of A Kind.png',0),(43,'Chessy Krainer','Other Flavors','All cheese krainer sausage','menu/Other Flavors/Chessy Krainer.png',0),(44,'Meatlovers Deluxe','Other Flavors','Ham, salami, hungarian sausage, pork pepperoni, beef pepperoni, bacon, ground beef, onions,  red & green bell pepper','menu/Other Flavors/Meatlovers Deluxe.png',0),(45,'Spinach N Chicken Pizza','Other Flavors','Chicken minced, spinach, garlic bits, & AP spinach white sauce','menu/Other Flavors/Spinach N Chicken Pizza.png',10),(46,'Loaded Pepperoni','Other Flavors','Beef pepperoni, pork pepperoni, & mozzarella cheese','menu/Other Flavors/Loaded Pepperoni.png',0),(47,'Spicy Meatzza','New Flavors','Chorizo de Cebu, hamonado, hungarian sausage, beef pepperoni, onions, red bell pepper, tomatoes, & AP ruble sauce','menu/New Flavors/Spicy Meatzza.png',0),(48,'Pizza Tropicana','New Flavors','Chorizo de Cebu, spam, pineapple tidbits','menu/New Flavors/Pizza Tropicana.png',0),(51,'Sample','Samples','samples','menu/Samples/Default.png',0),(53,'Random','Random','random','menu/Random/Default.png',0),(55,'Random Pizza','Random','Randomized ingredients.','menu/Random/Default.png',0);
/*!40000 ALTER TABLE `pizzas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','customer','driver') NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `mobile_number` varchar(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'customer1','customer1','customer','2000-01-01','Female','09876543210','customer1@gmail.com','2026-05-04 13:50:57'),(2,'cashier1','cashier1','cashier','1999-01-01','Other','09876543211','cashier1@gmail.com','2026-05-04 13:50:57'),(3,'admin1','admin1','admin','1998-01-01','Female','09876543212','admin1@gmail.com','2026-05-04 13:50:57'),(4,'customer2','customer2','customer','2001-01-01','Male','09876543213','customer2@gmail.com','2026-05-04 15:35:15'),(8,'sample4','sample4','cashier','2010-12-16','Female','98765432112','sample4@gmail.com','2026-05-25 01:16:11'),(9,'driver1','driver1','driver','2009-10-17','Male','09876543211','driver1@gmail.com','2026-05-25 01:56:49'),(10,'customer3','customer3','customer','1989-12-28','Other','09876543876','customer3@gmail.com','2026-05-25 07:35:41'),(11,'customer4','customer4','customer','2008-11-13','Female','09875644134','customer4@gmail.com','2026-05-25 14:10:06'),(12,'JoJo','JoJo','customer','2009-03-19','Other','09876576541','JoJo@email.com','2026-05-26 03:08:49');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-26 20:02:58
