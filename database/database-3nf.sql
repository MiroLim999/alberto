-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: albertos_pizza_db_3nf
-- ------------------------------------------------------
-- Server version	9.3.0

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
INSERT INTO `branches` VALUES (1,'A.S FURTUNA','Unit 3 TP Bldg. A.S. Fortuna St. Banilad, Cebu City'),(2,'B. RODRIGUEZ (Main)','15 B. Rodriguez St., Capitol Site, Cebu City'),(3,'CAPITOL','Osmeña Blvd., Cebu City'),(4,'MABOLO','Pope John Paul II Avenue, Mabolo, Cebu City'),(5,'BULACAO - PARDO','Bulacao, Pardo, Cebu City'),(6,'BASAK - PARDO','Basak, Pardo, Cebu City'),(7,'P.DEL ROSARIO','P. Del Rosario St., Cebu City'),(8,'PUNTA, LABANGON','F. Llamas St., Cebu City'),(9,'TALAMBAN','Talamban, Cebu City'),(10,'A.C CORTES','Mandaue City'),(11,'CANDUMAN','Mandaue City'),(12,'TIPOLO','Mandaue City'),(13,'BASAK - LAPU-LAPU','Lapu-Lapu City'),(14,'CORDOVA','Cordova, Cebu'),(15,'PUSOK, LAPU-LAPU CITY','Lapu-Lapu City'),(16,'POBLACION, LAPU-LAPU CITY','Lapu-Lapu City'),(17,'SOONG, MACTAN','Lapu-Lapu City'),(18,'DUMLOG, TALISAY','Talisay City'),(19,'LARAY TALISAY CITY','Talisay City'),(20,'TALISAY-TABUNOK','Talisay City'),(21,'ARGAO','Cebu'),(22,'BANTAYAN','Cebu'),(23,'BALAMBAN','Cebu'),(24,'BARILI','Cebu'),(25,'BOGO CITY','Cebu'),(26,'CARCAR CITY','Cebu'),(27,'CARMEN','Cebu'),(28,'CONSOLACION','Cebu'),(29,'DAANBANTAYAN','Cebu'),(30,'DANAO CITY','Cebu'),(31,'LILOAN','Cebu'),(32,'LUTOPAN','Toledo City'),(33,'MINGLANILLA','Cebu'),(34,'NAGA','Cebu'),(35,'SAN REMIGIO','Cebu'),(36,'SIBONGA','Cebu'),(37,'TOLEDO CITY','Cebu'),(38,'ANTIQUE','Antique'),(39,'BALASAN','Iloilo'),(40,'BAROTAC NUEVO','Iloilo'),(41,'BORACAY','Aklan'),(42,'GEN. LUNA','Iloilo City'),(43,'JARO','Iloilo City'),(44,'KALIBO','Aklan'),(45,'MOLO','Iloilo City'),(46,'POTOTAN','Iloilo'),(47,'ROXAS CITY','Capiz'),(48,'SARA','Iloilo'),(49,'TAFT NORTH','Iloilo City'),(50,'TAGBAK','Iloilo City'),(51,'BACOLOD CITY','Negros Occidental'),(52,'CERVANTES','Dumaguete City'),(53,'DUMAGUETE (DARO)','Dumaguete City'),(54,'BAYAWAN','Negros Oriental'),(55,'SAN CARLOS CITY','Negros Occidental'),(56,'SIQUIJOR','Siquijor'),(57,'BOLTON','Davao City'),(58,'BAJADA','Davao City'),(59,'DIGOS CITY','Davao del Sur'),(60,'KAWAYAN','Davao City'),(61,'MATI','Davao Oriental'),(62,'MATINA','Davao City'),(63,'PANABO','Davao del Norte'),(64,'STA. CRUZ','Davao del Sur'),(65,'TAGUM CITY','Davao del Norte'),(66,'CALUMPANG','General Santos'),(67,'LAGAO 1','General Santos'),(68,'LAGAO 2','General Santos'),(69,'KIDAPAWAN','Cotabato'),(70,'KORONADAL','South Cotabato'),(71,'MALAKAS','General Santos'),(72,'CARMEN - CDO','Cagayan de Oro'),(73,'DIVISORIA','Cagayan de Oro'),(74,'EL SALVADOR','Misamis Oriental'),(75,'GINGOOG','Misamis Oriental'),(76,'TIBANGA','Iligan City'),(77,'OZAMIZ','Misamis Occidental'),(78,'TUBOD','Iligan City'),(79,'TAGOLOAN','Misamis Oriental'),(80,'VALENCIA','Bukidnon'),(81,'MALAYBALAY','Bukidnon'),(82,'DAPITAN','Zamboanga del Norte'),(83,'DIPOLOG','Zamboanga del Norte'),(84,'PAGADIAN','Zamboanga del Sur'),(85,'SINDANGAN','Zamboanga del Norte'),(86,'BUTUAN 1','Butuan City'),(87,'BUTUAN 2','Butuan City'),(88,'SURIGAO','Surigao City'),(89,'BAYBAY','Leyte'),(90,'CARIGARA','Leyte'),(91,'HILONGOS','Leyte'),(92,'MAASIN','Southern Leyte'),(93,'SOGOD','Southern Leyte'),(94,'NAVAL','Biliran'),(95,'ORMOC','Leyte'),(96,'PALO','Leyte'),(97,'TACLOBAN 1','Leyte'),(98,'TACLOBAN 2','Leyte'),(99,'BORONGAN','Eastern Samar'),(100,'CALBAYOG','Samar'),(101,'CATBALOGAN','Samar'),(102,'TAGBILARAN 1','Bohol'),(103,'TAGBILARAN 2','Bohol'),(104,'TUBIGON','Bohol'),(105,'VISAYAS AVENUE','Quezon City');
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
  UNIQUE KEY `uq_category_name` (`category_name`)
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
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredients` (
  `ingredient_id` int NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(100) NOT NULL,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `uq_ingredient_name` (`ingredient_name`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredients`
--

LOCK TABLES `ingredients` WRITE;
/*!40000 ALTER TABLE `ingredients` DISABLE KEYS */;
INSERT INTO `ingredients` VALUES (66,'All cheese krainer sausage'),(12,'All ham'),(11,'All hotdog'),(52,'Anchovies'),(30,'AP buffalo sauce'),(68,'AP ruble sauce'),(47,'AP Rumble Sauce'),(55,'AP salad dressing'),(34,'AP shawarma sauce'),(39,'AP sisig sauce'),(67,'AP spinach white sauce'),(75,'Asadf'),(74,'Asdasdasd'),(2,'Bacon'),(40,'Beef pepperoni'),(6,'Black olives'),(54,'Boiled egg'),(38,'Calamansi'),(58,'Carrot'),(49,'Carrots'),(24,'Cheddar cheese'),(43,'Cheese krainer'),(45,'Chicken hotdog'),(28,'Chicken minced'),(13,'Choco stick'),(16,'Choco syrup'),(27,'Chorizo de Cebu'),(56,'Corned beef'),(46,'Crab stick'),(9,'Cream Cheese'),(64,'Cream cheese mix'),(26,'Crushed Graham crackers'),(14,'Crushed oreo'),(8,'Crushed Oreo Cookies'),(10,'Crushed Oreo cookies with pineapple tidbits'),(32,'Cucumber'),(20,'Egg'),(36,'Garlic bits'),(7,'Green bell pepper'),(31,'Ground beef'),(17,'Ham'),(18,'Ham sausage'),(19,'Hamonado'),(44,'Hotdog'),(42,'Hungarian sausage'),(65,'Hungarian sausage w/ AP hot sauce'),(33,'Lettuce'),(60,'Lots of pineapple tidbits'),(25,'Mango'),(15,'Marshmallow'),(23,'Mozzarella'),(62,'Mozzarella cheese'),(3,'Mushroom'),(4,'Onions'),(50,'Pickle'),(59,'Pickles'),(5,'Pineapple tidbits'),(1,'Pork pepperoni'),(61,'Pork pepperoni with AP hot sauce'),(37,'Pork sisig'),(22,'Quickmelt'),(71,'Random'),(72,'Randomized ingredients.'),(63,'Red & green bell pepper'),(29,'Red bell pepper'),(41,'Salami'),(70,'Samples'),(35,'Shrimp'),(69,'Spam'),(48,'Spanish sardines'),(53,'Spinach'),(21,'Tomatoes'),(51,'Tomatoes & onions'),(57,'Tuna flakes');
/*!40000 ALTER TABLE `ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_contacts`
--

DROP TABLE IF EXISTS `order_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_contacts` (
  `order_id` int NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  CONSTRAINT `fk_contact_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_contacts`
--

LOCK TABLES `order_contacts` WRITE;
/*!40000 ALTER TABLE `order_contacts` DISABLE KEYS */;
INSERT INTO `order_contacts` VALUES (1,'Guest','09876543210','gil.zivra@gmail.com'),(3,'Moxie','09876543213','Moxie@gmail.com'),(4,'Moxie','09876543215','Moxie@gmail.com'),(5,'Moxie3','09876543218','Moxie3@gmail.com'),(8,'Doxie','09876543219','Doxie@gmail.com'),(9,'Doug','09098877664',NULL),(10,'Dolly','09876509876','Dolly@gmail.com'),(12,'Chicharon','09876543217','Chii@gmail.com'),(13,'Moxie','09876543215','Moxie@gmail.com'),(14,'Dolly','09876509876','Dolly@gmail.com'),(18,'Zivra','09876543211','gil.zivra@gmail.com'),(19,'Zzz','09876543232',NULL),(24,'Horn','09123123123',NULL),(25,'JoJo','09876565445',NULL),(26,'Jen','09876543121','Jen@gmail.com'),(28,'Dove','09123124534','Dove@gmail.com'),(29,'Kiin','09871235435','Kiin@gmail.com'),(31,'frog','09876364564',NULL),(32,'devil','09745634534','devil@emailcom'),(33,'fet','09867854634',NULL),(34,'CHET','09784356234',NULL),(35,'Del','09873423413',NULL),(38,'asdasdasdas','0909090909',NULL),(39,'q123123','456456456',NULL),(40,'juan','0909090909',NULL),(43,'miro ma niga','09876543210',NULL),(44,'adsasdasd','09876543210',NULL),(45,'asdasd','09876543210',NULL),(102,'customer1','09876543210','customer1@gmail.com'),(103,'fetssss','09876543210',NULL),(104,'fetssss','09876543210',NULL),(105,'juan','09090909092',NULL),(107,'customer1','09876543210','customer1@gmail.com'),(108,'customer1','09876543210','customer1@gmail.com');
/*!40000 ALTER TABLE `order_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `variant_id` int NOT NULL,
  `quantity` int NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `fk_item_order` (`order_id`),
  KEY `fk_item_variant` (`variant_id`),
  CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `pizza_variants` (`variant_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,101,1),(2,1,3,1),(3,2,97,1),(4,2,3,1),(5,3,108,2),(6,3,78,1),(7,4,91,1),(8,4,66,1),(9,4,112,3),(10,4,92,2),(11,5,91,1),(12,5,77,2),(13,5,50,1),(14,5,64,3),(15,6,33,1),(16,6,139,1),(17,6,115,1),(18,7,78,3),(19,7,76,3),(20,8,95,1),(21,8,9,1),(23,9,95,1),(24,9,37,1),(25,10,21,1),(26,10,45,1),(27,10,166,1),(28,11,75,1),(29,11,30,1),(30,12,103,1),(31,12,67,1),(32,12,74,3),(33,13,91,1),(34,13,66,1),(35,13,112,3),(36,13,92,2),(37,14,21,1),(38,14,45,1),(39,14,166,1),(40,15,75,1),(41,15,30,1),(43,16,95,1),(44,16,37,1),(45,17,91,1),(46,17,66,1),(47,17,112,3),(48,17,92,2),(49,18,1,1),(50,19,91,1),(51,19,1,3),(52,20,91,1),(53,20,1,3),(54,21,1,1),(55,22,1,1),(56,22,78,1),(57,23,1,1),(58,24,91,1),(59,24,74,1),(60,25,21,2),(61,26,1,1),(62,27,103,1),(63,28,1,1),(64,29,103,1),(65,30,103,1),(66,31,91,1),(67,32,1,1),(68,32,1,1),(69,33,91,1),(70,34,91,1),(71,35,51,1),(100,36,1,1),(101,36,26,1),(103,38,95,1),(104,39,95,1),(105,40,91,1),(106,41,95,1),(107,43,91,1),(108,44,9,1),(109,45,103,1),(110,46,33,1),(111,46,34,1),(112,47,51,1),(200,101,33,1),(201,102,103,1),(202,103,95,1),(203,104,95,1),(204,105,91,1),(205,106,195,20),(206,107,195,20),(207,108,9,1),(208,109,199,12),(209,110,199,1);
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
  `branch_id` int DEFAULT NULL,
  `address` text,
  `order_type` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `driver_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `fk_order_branch` (`branch_id`),
  KEY `fk_order_user` (`user_id`),
  KEY `fk_order_driver` (`driver_id`),
  CONSTRAINT `fk_order_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_order_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,NULL,93,'Zone 5','PICK-UP','CASH','cancelled','2026-05-20 04:21:46',NULL,NULL),(2,4,93,'Zone 5','PICK-UP','CASH','pending','2026-05-20 06:49:30',NULL,NULL),(3,NULL,89,'Zone 1, Baybay, Leyte','DELIVERY','ONLINE','out_for_delivery','2026-05-20 07:07:49',9,'2026-05-27 07:56:59'),(4,NULL,89,'Zone 1, Baybay, Leyte','DELIVERY','CASH','delivered','2026-05-20 07:17:52',9,'2026-05-24 23:02:07'),(5,NULL,92,'Zone 5','PICK-UP','ONLINE','pending','2026-05-20 07:28:16',NULL,'2026-05-25 20:33:41'),(6,4,25,'Zone 1, Baybay, Leyte','PICK-UP','ONLINE','pending','2026-05-20 15:51:15',NULL,'2026-05-25 20:33:41'),(7,4,99,'Zone 5','PICK-UP','ONLINE','pending','2026-05-20 15:52:34',NULL,'2026-05-25 20:33:41'),(8,NULL,41,'Zone 5','DELIVERY','ONLINE','pending','2026-05-20 15:58:13',NULL,'2026-05-25 20:33:41'),(9,NULL,23,'Zone 5','PICK-UP','ONLINE','completed','2026-05-20 16:32:31',NULL,'2026-05-25 20:33:41'),(10,NULL,21,'Zone 5','PICK-UP','ONLINE','completed','2026-05-20 16:57:32',NULL,'2026-05-25 20:33:41'),(11,4,89,'Zone 5','DELIVERY','ONLINE','delivered','2026-05-20 16:59:08',9,'2026-05-27 07:27:35'),(12,NULL,99,'Zone 5','PICK-UP','CASH','pending','2026-05-20 19:09:43',NULL,NULL),(13,NULL,89,'Zone 1, Baybay, Leyte','DELIVERY','CASH','delivered','2026-05-20 22:36:30',9,'2026-05-27 07:03:49'),(14,NULL,21,'Zone 5','PICK-UP','ONLINE','pending','2026-05-22 18:59:38',NULL,'2026-05-25 20:33:41'),(15,2,89,'Zone 5','on','ONLINE','pending','2026-05-22 21:57:17',NULL,'2026-05-25 20:33:41'),(16,2,23,'Zone 5','on','ONLINE','pending','2026-05-23 17:26:41',NULL,'2026-05-25 20:33:41'),(17,2,89,'Zone 1, Baybay, Leyte','on','CASH','pending','2026-05-23 17:30:52',NULL,NULL),(18,NULL,41,'0','PICK-UP','CASH','completed','2026-05-24 11:40:01',NULL,NULL),(19,NULL,99,'0','PICK-UP','CASH','completed','2026-05-24 11:42:24',NULL,NULL),(20,2,99,'0','on','on','completed','2026-05-24 11:45:38',NULL,NULL),(21,1,99,'0','PICK-UP','CASH','completed','2026-05-24 20:03:24',NULL,NULL),(22,2,99,'0','PICK-UP','CASH','completed','2026-05-24 21:50:12',NULL,NULL),(23,2,41,'0','PICK-UP','CASH','completed','2026-05-24 22:19:41',NULL,NULL),(24,NULL,93,'0','DELIVERY','ONLINE','out_for_delivery','2026-05-24 22:33:52',9,'2026-05-27 07:57:02'),(25,NULL,93,'0','DELIVERY','ONLINE','delivered','2026-05-25 19:06:47',9,'2026-05-25 20:40:32'),(26,NULL,89,'0','PICK-UP','CASH','pending','2026-05-25 19:18:03',NULL,NULL),(27,2,89,'0','PICK-UP','CASH','completed','2026-05-25 19:28:30',NULL,NULL),(28,NULL,24,'Zone 5','DELIVERY','ONLINE','pending','2026-05-25 20:01:24',NULL,'2026-05-25 20:33:41'),(29,NULL,25,'Sample Zone','DELIVERY','ONLINE','delivered','2026-05-25 20:03:59',9,'2026-05-27 07:27:45'),(30,2,25,'Sample Zone','DELIVERY','CASH','delivered','2026-05-25 20:05:05',9,'2026-05-27 07:46:59'),(31,NULL,89,'','PICK-UP','ONLINE','pending','2026-05-25 20:20:05',NULL,NULL),(32,NULL,57,'Zone 5','DELIVERY','ONLINE','pending','2026-05-25 20:21:18',NULL,NULL),(33,NULL,25,'Sample Zone','DELIVERY','ONLINE','pending','2026-05-25 20:22:11',NULL,'2026-05-25 20:33:41'),(34,NULL,25,'Sample Zone','DELIVERY','ONLINE','pending','2026-05-25 20:25:06',NULL,NULL),(35,NULL,59,'Sample Zone','DELIVERY','ONLINE','pending','2026-05-25 21:36:01',NULL,NULL),(36,1,93,'','PICK-UP','CASH','cancelled','2026-05-27 03:30:16',NULL,'2026-05-27 06:49:52'),(38,NULL,99,'','PICK-UP','ONLINE','completed','2026-05-27 03:39:54',NULL,'2026-05-27 06:50:26'),(39,NULL,25,'','PICK-UP','CASH','completed','2026-05-27 03:40:58',NULL,'2026-05-27 06:46:06'),(40,NULL,41,'zon3','PICK-UP','CASH','completed','2026-05-27 03:42:16',NULL,'2026-05-27 06:27:30'),(41,1,41,'','PICK-UP','CASH','cancelled','2026-05-27 03:43:58',NULL,'2026-05-27 06:17:26'),(43,NULL,57,'d','DELIVERY','CASH','cancelled','2026-05-27 03:54:58',NULL,'2026-05-27 06:17:23'),(44,NULL,57,'','PICK-UP','ONLINE','cancelled','2026-05-27 03:55:33',NULL,'2026-05-27 06:17:20'),(45,NULL,57,'w','DELIVERY','CASH','cancelled','2026-05-27 03:58:52',NULL,'2026-05-27 06:17:17'),(46,1,99,'','PICK-UP','CASH','cancelled','2026-05-27 04:00:39',NULL,'2026-05-27 06:13:28'),(47,1,57,'','PICK-UP','CASH','cancelled','2026-05-27 04:03:52',NULL,'2026-05-27 06:17:13'),(101,1,57,'s','DELIVERY','CASH','cancelled','2026-05-27 04:56:25',NULL,'2026-05-27 05:53:46'),(102,NULL,41,'','PICK-UP','CASH','completed','2026-05-27 06:02:31',NULL,NULL),(103,NULL,41,'','PICK-UP','CASH','completed','2026-05-27 06:16:31',NULL,'2026-05-27 06:19:33'),(104,NULL,41,'','PICK-UP','CASH','completed','2026-05-27 06:19:33',NULL,NULL),(105,NULL,41,'zon3','PICK-UP','CASH','completed','2026-05-27 06:27:30',NULL,NULL),(106,1,57,'','PICK-UP','CASH','completed','2026-05-27 06:37:21',NULL,'2026-05-27 06:38:05'),(107,NULL,57,'','PICK-UP','CASH','completed','2026-05-27 06:38:05',NULL,NULL),(108,NULL,57,'','PICK-UP','CASH','completed','2026-05-27 06:47:01',NULL,NULL),(109,1,57,'benito faelnar','DELIVERY','CASH','pending','2026-05-27 07:45:55',NULL,NULL),(110,1,41,'asdasdasd','DELIVERY','CASH','pending','2026-05-27 07:49:02',NULL,NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pizza_ingredients`
--

DROP TABLE IF EXISTS `pizza_ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pizza_ingredients` (
  `pizza_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  PRIMARY KEY (`pizza_id`,`ingredient_id`),
  KEY `fk_pi_ingredient` (`ingredient_id`),
  CONSTRAINT `fk_pi_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pi_pizza` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pizza_ingredients`
--

LOCK TABLES `pizza_ingredients` WRITE;
/*!40000 ALTER TABLE `pizza_ingredients` DISABLE KEYS */;
INSERT INTO `pizza_ingredients` VALUES (1,1),(17,1),(24,1),(32,1),(46,1),(1,2),(20,2),(24,2),(26,2),(30,2),(32,2),(38,2),(40,2),(1,3),(15,3),(17,3),(26,3),(27,3),(28,3),(30,3),(32,3),(34,3),(35,3),(38,3),(40,3),(1,4),(9,4),(12,4),(13,4),(14,4),(15,4),(16,4),(17,4),(19,4),(21,4),(22,4),(23,4),(26,4),(28,4),(32,4),(34,4),(35,4),(38,4),(39,4),(41,4),(47,4),(1,5),(13,5),(17,5),(26,5),(30,5),(32,5),(34,5),(48,5),(1,6),(17,6),(19,6),(21,6),(32,6),(34,6),(35,6),(1,7),(20,7),(26,7),(27,7),(30,7),(3,8),(4,9),(11,9),(20,9),(5,10),(6,11),(7,12),(8,13),(8,14),(8,15),(8,16),(9,17),(17,17),(24,17),(26,17),(30,17),(32,17),(9,18),(12,18),(24,18),(27,18),(9,19),(17,19),(24,19),(47,19),(9,20),(9,21),(14,21),(15,21),(19,21),(21,21),(23,21),(32,21),(34,21),(35,21),(38,21),(39,21),(41,21),(47,21),(10,22),(10,23),(10,24),(21,24),(24,24),(11,25),(20,25),(11,26),(12,27),(24,27),(47,27),(48,27),(13,28),(17,28),(24,28),(32,28),(39,28),(42,28),(45,28),(13,29),(15,29),(16,29),(19,29),(28,29),(47,29),(13,30),(14,31),(22,31),(24,31),(28,31),(32,31),(38,31),(42,31),(14,32),(17,32),(21,32),(33,32),(35,32),(14,33),(21,33),(35,33),(14,34),(15,35),(17,35),(22,35),(23,35),(15,36),(16,36),(19,36),(21,36),(22,36),(33,36),(45,36),(16,37),(16,38),(16,39),(17,40),(19,40),(37,40),(46,40),(47,40),(17,41),(24,41),(32,41),(17,42),(24,42),(32,42),(47,42),(17,43),(24,43),(17,44),(24,44),(17,45),(24,45),(32,45),(39,45),(17,46),(21,46),(22,46),(23,46),(17,47),(18,48),(23,48),(18,49),(18,50),(18,51),(40,51),(19,52),(23,52),(21,53),(33,53),(45,53),(21,54),(21,55),(22,56),(22,57),(23,57),(24,57),(41,57),(42,57),(23,58),(23,59),(27,60),(29,61),(30,62),(46,62),(32,63),(34,63),(35,63),(33,64),(36,65),(43,66),(45,67),(47,68),(48,69),(51,70),(53,71),(55,72),(56,74),(57,75);
/*!40000 ALTER TABLE `pizza_ingredients` ENABLE KEYS */;
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
  UNIQUE KEY `uq_variant` (`pizza_id`,`size`,`cheese`),
  CONSTRAINT `fk_variant_pizza` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pizza_variants`
--

LOCK TABLES `pizza_variants` WRITE;
/*!40000 ALTER TABLE `pizza_variants` DISABLE KEYS */;
INSERT INTO `pizza_variants` VALUES (1,1,9,'Quickmelt',145.00),(2,1,9,'Mozzarella',165.00),(3,1,11,'Quickmelt',195.00),(4,1,11,'Mozzarella',215.00),(9,3,9,'Quickmelt',110.00),(11,3,9,'Mozzarella',130.00),(13,3,11,'Quickmelt',150.00),(15,3,11,'Mozzarella',170.00),(17,4,9,'Quickmelt',115.00),(18,4,9,'Mozzarella',135.00),(19,4,11,'Quickmelt',155.00),(20,4,11,'Mozzarella',175.00),(21,5,9,'Quickmelt',120.00),(22,5,9,'Mozzarella',135.00),(23,5,11,'Quickmelt',155.00),(24,5,11,'Mozzarella',175.00),(25,6,9,'Quickmelt',153.00),(26,6,9,'Mozzarella',155.00),(27,6,11,'Quickmelt',185.00),(28,6,11,'Mozzarella',205.00),(29,7,9,'Quickmelt',140.00),(30,7,9,'Mozzarella',160.00),(31,7,11,'Quickmelt',190.00),(32,7,11,'Mozzarella',210.00),(33,8,9,'Quickmelt',170.00),(34,8,9,'Mozzarella',190.00),(35,8,11,'Quickmelt',220.00),(36,8,11,'Mozzarella',240.00),(37,9,9,'Quickmelt',180.00),(38,9,9,'Mozzarella',200.00),(39,9,11,'Quickmelt',230.00),(40,9,11,'Mozzarella',250.00),(45,10,9,'Quickmelt',150.00),(46,10,9,'Mozzarella',150.00),(47,10,11,'Quickmelt',200.00),(48,10,11,'Mozzarella',200.00),(49,11,11,'Quickmelt',220.00),(50,11,11,'Mozzarella',240.00),(51,12,9,'Quickmelt',175.00),(52,12,9,'Mozzarella',195.00),(53,12,11,'Quickmelt',225.00),(54,12,11,'Mozzarella',245.00),(55,13,9,'Quickmelt',180.00),(56,13,9,'Mozzarella',200.00),(57,13,11,'Quickmelt',230.00),(58,13,11,'Mozzarella',250.00),(59,14,9,'Quickmelt',205.00),(60,14,9,'Mozzarella',225.00),(61,14,11,'Quickmelt',255.00),(62,14,11,'Mozzarella',275.00),(63,15,11,'Quickmelt',230.00),(64,15,11,'Mozzarella',250.00),(65,16,11,'Quickmelt',240.00),(66,16,11,'Mozzarella',260.00),(67,17,11,'Quickmelt',260.00),(68,17,11,'Mozzarella',280.00),(69,18,11,'Quickmelt',265.00),(70,18,11,'Mozzarella',285.00),(71,19,11,'Quickmelt',265.00),(72,19,11,'Mozzarella',285.00),(73,20,11,'Quickmelt',265.00),(74,20,11,'Mozzarella',285.00),(75,21,11,'Quickmelt',295.00),(76,21,11,'Mozzarella',315.00),(77,22,11,'Quickmelt',310.00),(78,22,11,'Mozzarella',330.00),(79,23,11,'Quickmelt',360.00),(80,23,11,'Mozzarella',380.00),(81,24,11,'Quickmelt',325.00),(82,24,11,'Mozzarella',325.00),(91,26,9,'Quickmelt',145.00),(92,26,9,'Mozzarella',165.00),(93,26,11,'Quickmelt',195.00),(94,26,11,'Mozzarella',215.00),(95,27,9,'Quickmelt',150.00),(96,27,9,'Mozzarella',170.00),(97,27,11,'Quickmelt',200.00),(98,27,11,'Mozzarella',220.00),(99,28,9,'Quickmelt',170.00),(100,28,9,'Mozzarella',190.00),(101,28,11,'Quickmelt',220.00),(102,28,11,'Mozzarella',240.00),(103,29,9,'Quickmelt',200.00),(104,29,9,'Mozzarella',220.00),(105,29,11,'Quickmelt',250.00),(106,29,11,'Mozzarella',270.00),(107,30,11,'Quickmelt',240.00),(108,30,11,'Mozzarella',260.00),(111,32,11,'Quickmelt',280.00),(112,32,11,'Mozzarella',300.00),(113,33,11,'Quickmelt',320.00),(114,33,11,'Mozzarella',340.00),(115,34,9,'Quickmelt',125.00),(116,34,9,'Mozzarella',145.00),(117,34,11,'Quickmelt',175.00),(118,34,11,'Mozzarella',195.00),(119,35,9,'Quickmelt',155.00),(120,35,9,'Mozzarella',175.00),(121,35,11,'Quickmelt',205.00),(122,35,11,'Mozzarella',225.00),(123,36,9,'Quickmelt',160.00),(124,36,9,'Mozzarella',180.00),(125,36,11,'Quickmelt',210.00),(126,36,11,'Mozzarella',230.00),(127,37,9,'Quickmelt',160.00),(128,37,9,'Mozzarella',180.00),(129,37,11,'Quickmelt',210.00),(130,37,11,'Mozzarella',230.00),(131,38,9,'Quickmelt',160.00),(132,38,9,'Mozzarella',185.00),(133,38,11,'Quickmelt',215.00),(134,38,11,'Mozzarella',235.00),(135,39,9,'Quickmelt',170.00),(136,39,9,'Mozzarella',190.00),(137,39,11,'Quickmelt',220.00),(138,39,11,'Mozzarella',240.00),(139,40,9,'Quickmelt',170.00),(140,40,9,'Mozzarella',190.00),(141,40,11,'Quickmelt',220.00),(142,40,11,'Mozzarella',240.00),(143,41,9,'Quickmelt',180.00),(144,41,9,'Mozzarella',205.00),(145,41,11,'Quickmelt',230.00),(146,41,11,'Mozzarella',250.00),(147,42,9,'Quickmelt',185.00),(148,42,9,'Mozzarella',205.00),(149,42,11,'Quickmelt',235.00),(150,42,11,'Mozzarella',255.00),(151,43,9,'Quickmelt',200.00),(152,43,9,'Mozzarella',220.00),(153,43,11,'Quickmelt',250.00),(154,43,11,'Mozzarella',270.00),(159,45,11,'Quickmelt',240.00),(160,45,11,'Mozzarella',260.00),(161,46,11,'Quickmelt',285.00),(162,46,11,'Mozzarella',305.00),(163,47,11,'Quickmelt',225.00),(164,47,11,'Mozzarella',245.00),(165,48,11,'Quickmelt',260.00),(166,48,11,'Mozzarella',280.00),(175,51,9,'Quickmelt',100.00),(176,51,11,'Quickmelt',0.00),(177,51,9,'Mozzarella',110.00),(178,51,11,'Mozzarella',0.00),(183,53,9,'Quickmelt',0.00),(184,53,11,'Quickmelt',150.00),(185,53,9,'Mozzarella',0.00),(186,53,11,'Mozzarella',180.00),(191,55,9,'Quickmelt',110.00),(192,55,11,'Quickmelt',120.00),(193,55,9,'Mozzarella',140.00),(194,55,11,'Mozzarella',159.00),(195,56,9,'Quickmelt',10.00),(196,56,11,'Quickmelt',12.00),(197,56,9,'Mozzarella',11.00),(198,56,11,'Mozzarella',5.00),(199,57,9,'Quickmelt',1.00),(200,57,11,'Quickmelt',1.00),(201,57,9,'Mozzarella',1.00),(202,57,11,'Mozzarella',1.00);
/*!40000 ALTER TABLE `pizza_variants` ENABLE KEYS */;
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
  `category_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `stock` int DEFAULT '0',
  PRIMARY KEY (`pizza_id`),
  UNIQUE KEY `uq_pizza_name` (`pizza_name`),
  KEY `fk_pizza_category` (`category_id`),
  CONSTRAINT `fk_pizza_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pizzas`
--

LOCK TABLES `pizzas` WRITE;
/*!40000 ALTER TABLE `pizzas` DISABLE KEYS */;
INSERT INTO `pizzas` VALUES (1,'Pizza Supreme',1,'menu/Bestsellers/Pizza Supreme.png',8),(3,'Cookies N Cheese',2,'menu/Kiddies Favorites/Cookies N Cheese.png',9),(4,'Creamy Cheese',2,'menu/Kiddies Favorites/Creamy Cheese.png',10),(5,'Oreo Piña',2,'menu/Kiddies Favorites/Oreo Pina.png',10),(6,'Yummy Hotdog',2,'menu/Kiddies Favorites/Yummy Hotdog.png',5),(7,'Ham Delights',2,'menu/Kiddies Favorites/Ham Delight.png',5),(8,'Chocomallow',2,'menu/Kiddies Favorites/Chocomallow.png',10),(9,'Ham And Egg',2,'menu/Kiddies Favorites/Ham And Egg.png',0),(10,'Chizzo Trio',2,'menu/Kiddies Favorites/Chizzo Trio.png',0),(11,'Mango Graham',2,'menu/Kiddies Favorites/Mango Graham.png',0),(12,'Chogburizo',3,'menu/House Specialties/Chogburizo.png',20),(13,'Buffalo Chicken',3,'menu/House Specialties/Buffalo Chicken.png',0),(14,'Beef Shawarma',3,'menu/House Specialties/Beef Shawarma.png',0),(15,'Shrimp And Mushroom',3,'menu/House Specialties/Shrimp And Mushroom.png',5),(16,'Sisig Twist',3,'menu/House Specialties/Sisig Twist.png',0),(17,'Royal Rumble',3,'menu/House Specialties/Royal Rumble.png',0),(18,'Spanish Sardines',3,'menu/House Specialties/Spanish Sardines.png',0),(19,'Anchovy Pizza',3,'menu/House Specialties/Anchovy Pizza.png',0),(20,'Mango Bacon',3,'menu/House Specialties/Mango Bacon.png',10),(21,'Salad Pizza',3,'menu/House Specialties/Salad Pizza.png',0),(22,'Surf And Turf',3,'menu/House Specialties/Surf And Turf.png',9),(23,'Pizza D Marina',3,'menu/House Specialties/Pizza D Marina.png',0),(24,'Royal Flush',3,'menu/House Specialties/Royal Flush.png',0),(26,'Hawaiian',1,'menu/Bestsellers/Hawaiian.png',8),(27,'Aloha',1,'menu/Bestsellers/Aloha.png',9),(28,'Beef And Mushroom',1,'menu/Bestsellers/Beef And Mushroom.png',20),(29,'All Pepperoni',1,'menu/Bestsellers/All Pepperoni.png',7),(30,'Loaded Hawaiian',1,'menu/Bestsellers/Loaded Hawaiian.png',20),(32,'Albertos Full House',1,'menu/Bestsellers/Albertos Full House.png',0),(33,'Ceamy Cucumber Spinach',1,'menu/Bestsellers/Ceamy Cucumber Spinach.png',20),(34,'Garden Express',4,'menu/Other Flavors/Garden Express.png',0),(35,'Vegetarian',4,'menu/Other Flavors/Vegetarian.png',0),(36,'All Hungarian',4,'menu/Other Flavors/All Hungarian.png',0),(37,'Beef Pepperoni',4,'menu/Other Flavors/Beef Pepperoni.png',0),(38,'Pizza Burger',4,'menu/Other Flavors/Pizza Burger.png',0),(39,'Chicken Garlic',4,'menu/Other Flavors/Chicken Garlic.png',0),(40,'Bacon Mushroom',4,'menu/Other Flavors/Bacon Mushroom.png',0),(41,'Tuna Garlic',4,'menu/Other Flavors/Tuna Garlic.png',0),(42,'Three Of A Kind',4,'menu/Other Flavors/Three Of A Kind.png',0),(43,'Chessy Krainer',4,'menu/Other Flavors/Chessy Krainer.png',0),(45,'Spinach N Chicken Pizza',4,'menu/Other Flavors/Spinach N Chicken Pizza.png',10),(46,'Loaded Pepperoni',4,'menu/Other Flavors/Loaded Pepperoni.png',0),(47,'Spicy Meatzza',5,'menu/New Flavors/Spicy Meatzza.png',0),(48,'Pizza Tropicana',5,'menu/New Flavors/Pizza Tropicana.png',0),(51,'Sample',8,'menu/Samples/Default.png',0),(53,'Random',9,'menu/Random/Default.png',0),(55,'Random Pizza',9,'menu/Random/Default.png',0),(56,'cooler',3,'menu/House Specialties/category_5_1779668084.png',0),(57,'iphone',1,'menu/Bestsellers/category_iphone_1779521794.jpg',20);
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
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'customer1','customer1','customer','2000-01-01','Female','09876543210','customer1@gmail.com','2026-05-04 05:50:57'),(2,'cashier1','cashier1','cashier','1999-01-01','Other','09876543211','cashier1@gmail.com','2026-05-04 05:50:57'),(3,'admin1','admin1','admin','1998-01-01','Female','09876543212','admin1@gmail.com','2026-05-04 05:50:57'),(4,'customer2','customer2','customer','2001-01-01','Male','09876543213','customer2@gmail.com','2026-05-04 07:35:15'),(8,'sample4','sample4','cashier','2010-12-16','Female','98765432112','sample4@gmail.com','2026-05-24 17:16:11'),(9,'driver1','driver1','driver','2009-10-17','Male','09876543211','driver1@gmail.com','2026-05-24 17:56:49'),(10,'customer3','customer3','customer','1989-12-28','Other','09876543876','customer3@gmail.com','2026-05-24 23:35:41'),(11,'customer4','customer4','customer','2008-11-13','Female','09875644134','customer4@gmail.com','2026-05-25 06:10:06'),(12,'JoJo','JoJo','customer','2009-03-19','Other','09876576541','JoJo@email.com','2026-05-25 19:08:49');
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

-- Dump completed on 2026-05-27 16:06:42
