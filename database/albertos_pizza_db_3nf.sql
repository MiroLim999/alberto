-- ============================================================
-- Alberto's Pizza DB — 3NF Normalized Schema
-- Database: albertos_pizza_db_3nf
-- ============================================================
-- 3NF Changes Made:
--   1. pizzas.category (string) → pizzas.category_id (FK to categories)
--   2. order_items now stores pizza_id + variant_id (FK refs)
--      instead of repeating pizza_name, size, cheese strings
--   3. pizza_variants_temp staging table removed
--   4. All FK constraints properly declared
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: branches
-- (No changes — already in 1NF/2NF/3NF)
-- ============================================================
CREATE TABLE `branches` (
  `branch_id`   int          NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) DEFAULT NULL,
  `location`    varchar(255) DEFAULT NULL,
  PRIMARY KEY (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: categories
-- (No changes — already normalized)
-- ============================================================
CREATE TABLE `categories` (
  `category_id`   int          NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: users
-- (No changes — already normalized)
-- ============================================================
CREATE TABLE `users` (
  `user_id`       int          NOT NULL AUTO_INCREMENT,
  `username`      varchar(50)  NOT NULL,
  `password`      varchar(255) NOT NULL,
  `role`          enum('admin','cashier','customer','driver') NOT NULL,
  `birth_date`    date         NOT NULL,
  `gender`        enum('Male','Female','Other') NOT NULL,
  `mobile_number` varchar(11)  NOT NULL,
  `email`         varchar(100) DEFAULT NULL,
  `created_at`    timestamp    NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pizzas
-- 3NF FIX: category VARCHAR → category_id INT (FK to categories)
-- This removes the transitive dependency:
--   pizza_id → category_name → (category properties)
-- ============================================================
CREATE TABLE `pizzas` (
  `pizza_id`    int          NOT NULL AUTO_INCREMENT,
  `pizza_name`  varchar(100) NOT NULL,
  `category_id` int          NOT NULL,
  `ingredients` text         NOT NULL,
  `image_path`  varchar(255) NOT NULL,
  `stock`       int          DEFAULT 0,
  PRIMARY KEY (`pizza_id`),
  KEY `fk_pizza_category` (`category_id`),
  CONSTRAINT `fk_pizza_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pizza_variants
-- (No structural changes — already normalized)
-- size + cheese are attributes of the variant, not transitive
-- ============================================================
CREATE TABLE `pizza_variants` (
  `variant_id` int           NOT NULL AUTO_INCREMENT,
  `pizza_id`   int           NOT NULL,
  `size`       int           NOT NULL,
  `cheese`     varchar(50)   NOT NULL,
  `price`      decimal(6,2)  NOT NULL,
  PRIMARY KEY (`variant_id`),
  KEY `fk_variant_pizza` (`pizza_id`),
  CONSTRAINT `fk_variant_pizza`
    FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: orders
-- (No structural changes — already normalized)
-- total_amount is kept as a stored value (performance/history)
-- ============================================================
CREATE TABLE `orders` (
  `order_id`       int           NOT NULL AUTO_INCREMENT,
  `user_id`        int           DEFAULT NULL,
  `customer_name`  varchar(255)  DEFAULT NULL,
  `mobile_number`  varchar(20)   DEFAULT NULL,
  `email`          varchar(255)  DEFAULT NULL,
  `branch_id`      int           DEFAULT NULL,
  `address`        text,
  `order_type`     varchar(50)   DEFAULT NULL,
  `payment_method` varchar(50)   DEFAULT NULL,
  `total_amount`   decimal(10,2) DEFAULT NULL,
  `status`         varchar(50)   DEFAULT 'pending',
  `created_at`     timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
  `driver_id`      int           DEFAULT NULL,
  `updated_at`     timestamp     NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `fk_order_branch` (`branch_id`),
  KEY `fk_order_user`   (`user_id`),
  KEY `fk_order_driver` (`driver_id`),
  CONSTRAINT `fk_order_branch`
    FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_order_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_order_driver`
    FOREIGN KEY (`driver_id`) REFERENCES `users` (`user_id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: order_items
-- 3NF FIX: Remove pizza_name, size, cheese, price string columns.
-- Add pizza_id (FK) and variant_id (FK) instead.
-- Previously: order_id → pizza_name → (ingredients, category...)
--             order_id → size, cheese → price (via pizza_variants)
-- Now: all pizza/variant info is looked up via FK joins.
-- pizza_name, size, cheese, price are kept as snapshot columns
-- for historical accuracy (price at time of order may change).
-- variant_id FK added for proper relational integrity.
-- ============================================================
CREATE TABLE `order_items` (
  `item_id`    int           NOT NULL AUTO_INCREMENT,
  `order_id`   int           DEFAULT NULL,
  `pizza_id`   int           DEFAULT NULL,
  `variant_id` int           DEFAULT NULL,
  `pizza_name` varchar(255)  DEFAULT NULL,  -- snapshot at order time
  `size`       varchar(10)   DEFAULT NULL,  -- snapshot at order time
  `cheese`     varchar(50)   DEFAULT NULL,  -- snapshot at order time
  `price`      decimal(10,2) DEFAULT NULL,  -- snapshot at order time
  `quantity`   int           DEFAULT NULL,
  `total`      decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `fk_item_order`   (`order_id`),
  KEY `fk_item_pizza`   (`pizza_id`),
  KEY `fk_item_variant` (`variant_id`),
  CONSTRAINT `fk_item_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_item_pizza`
    FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_item_variant`
    FOREIGN KEY (`variant_id`) REFERENCES `pizza_variants` (`variant_id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATA: branches
-- ============================================================
INSERT INTO `branches` VALUES
(1,'A.S FURTUNA','Unit 3 TP Bldg. A.S. Fortuna St. Banilad, Cebu City'),
(2,'B. RODRIGUEZ (Main)','15 B. Rodriguez St., Capitol Site, Cebu City'),
(3,'CAPITOL','Osmeña Blvd., Cebu City'),
(4,'MABOLO','Pope John Paul II Avenue, Mabolo, Cebu City'),
(5,'BULACAO - PARDO','Bulacao, Pardo, Cebu City'),
(6,'BASAK - PARDO','Basak, Pardo, Cebu City'),
(7,'P.DEL ROSARIO','P. Del Rosario St., Cebu City'),
(8,'PUNTA, LABANGON','F. Llamas St., Cebu City'),
(9,'TALAMBAN','Talamban, Cebu City'),
(10,'A.C CORTES','Mandaue City'),
(11,'CANDUMAN','Mandaue City'),
(12,'TIPOLO','Mandaue City'),
(13,'BASAK - LAPU-LAPU','Lapu-Lapu City'),
(14,'CORDOVA','Cordova, Cebu'),
(15,'PUSOK, LAPU-LAPU CITY','Lapu-Lapu City'),
(16,'POBLACION, LAPU-LAPU CITY','Lapu-Lapu City'),
(17,'SOONG, MACTAN','Lapu-Lapu City'),
(18,'DUMLOG, TALISAY','Talisay City'),
(19,'LARAY TALISAY CITY','Talisay City'),
(20,'TALISAY-TABUNOK','Talisay City'),
(21,'ARGAO','Cebu'),
(22,'BANTAYAN','Cebu'),
(23,'BALAMBAN','Cebu'),
(24,'BARILI','Cebu'),
(25,'BOGO CITY','Cebu'),
(26,'CARCAR CITY','Cebu'),
(27,'CARMEN','Cebu'),
(28,'CONSOLACION','Cebu'),
(29,'DAANBANTAYAN','Cebu'),
(30,'DANAO CITY','Cebu'),
(31,'LILOAN','Cebu'),
(32,'LUTOPAN','Toledo City'),
(33,'MINGLANILLA','Cebu'),
(34,'NAGA','Cebu'),
(35,'SAN REMIGIO','Cebu'),
(36,'SIBONGA','Cebu'),
(37,'TOLEDO CITY','Cebu'),
(38,'ANTIQUE','Antique'),
(39,'BALASAN','Iloilo'),
(40,'BAROTAC NUEVO','Iloilo'),
(41,'BORACAY','Aklan'),
(42,'GEN. LUNA','Iloilo City'),
(43,'JARO','Iloilo City'),
(44,'KALIBO','Aklan'),
(45,'MOLO','Iloilo City'),
(46,'POTOTAN','Iloilo'),
(47,'ROXAS CITY','Capiz'),
(48,'SARA','Iloilo'),
(49,'TAFT NORTH','Iloilo City'),
(50,'TAGBAK','Iloilo City'),
(51,'BACOLOD CITY','Negros Occidental'),
(52,'CERVANTES','Dumaguete City'),
(53,'DUMAGUETE (DARO)','Dumaguete City'),
(54,'BAYAWAN','Negros Oriental'),
(55,'SAN CARLOS CITY','Negros Occidental'),
(56,'SIQUIJOR','Siquijor'),
(57,'BOLTON','Davao City'),
(58,'BAJADA','Davao City'),
(59,'DIGOS CITY','Davao del Sur'),
(60,'KAWAYAN','Davao City'),
(61,'MATI','Davao Oriental'),
(62,'MATINA','Davao City'),
(63,'PANABO','Davao del Norte'),
(64,'STA. CRUZ','Davao del Sur'),
(65,'TAGUM CITY','Davao del Norte'),
(66,'CALUMPANG','General Santos'),
(67,'LAGAO 1','General Santos'),
(68,'LAGAO 2','General Santos'),
(69,'KIDAPAWAN','Cotabato'),
(70,'KORONADAL','South Cotabato'),
(71,'MALAKAS','General Santos'),
(72,'CARMEN - CDO','Cagayan de Oro'),
(73,'DIVISORIA','Cagayan de Oro'),
(74,'EL SALVADOR','Misamis Oriental'),
(75,'GINGOOG','Misamis Oriental'),
(76,'TIBANGA','Iligan City'),
(77,'OZAMIZ','Misamis Occidental'),
(78,'TUBOD','Iligan City'),
(79,'TAGOLOAN','Misamis Oriental'),
(80,'VALENCIA','Bukidnon'),
(81,'MALAYBALAY','Bukidnon'),
(82,'DAPITAN','Zamboanga del Norte'),
(83,'DIPOLOG','Zamboanga del Norte'),
(84,'PAGADIAN','Zamboanga del Sur'),
(85,'SINDANGAN','Zamboanga del Norte'),
(86,'BUTUAN 1','Butuan City'),
(87,'BUTUAN 2','Butuan City'),
(88,'SURIGAO','Surigao City'),
(89,'BAYBAY','Leyte'),
(90,'CARIGARA','Leyte'),
(91,'HILONGOS','Leyte'),
(92,'MAASIN','Southern Leyte'),
(93,'SOGOD','Southern Leyte'),
(94,'NAVAL','Biliran'),
(95,'ORMOC','Leyte'),
(96,'PALO','Leyte'),
(97,'TACLOBAN 1','Leyte'),
(98,'TACLOBAN 2','Leyte'),
(99,'BORONGAN','Eastern Samar'),
(100,'CALBAYOG','Samar'),
(101,'CATBALOGAN','Samar'),
(102,'TAGBILARAN 1','Bohol'),
(103,'TAGBILARAN 2','Bohol'),
(104,'TUBIGON','Bohol'),
(105,'VISAYAS AVENUE','Quezon City');

-- ============================================================
-- DATA: categories
-- ============================================================
INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1,'Bestsellers'),
(2,'Kiddies Favorites'),
(3,'House Specialties'),
(4,'Other Flavors'),
(5,'New Flavors'),
(8,'Samples'),
(9,'Random');

-- ============================================================
-- DATA: users
-- ============================================================
INSERT INTO `users` VALUES
(1,'customer1','customer1','customer','2000-01-01','Female','09876543210','customer1@gmail.com','2026-05-04 13:50:57'),
(2,'cashier1','cashier1','cashier','1999-01-01','Other','09876543211','cashier1@gmail.com','2026-05-04 13:50:57'),
(3,'admin1','admin1','admin','1998-01-01','Female','09876543212','admin1@gmail.com','2026-05-04 13:50:57'),
(4,'customer2','customer2','customer','2001-01-01','Male','09876543213','customer2@gmail.com','2026-05-04 15:35:15'),
(8,'sample4','sample4','cashier','2010-12-16','Female','98765432112','sample4@gmail.com','2026-05-25 01:16:11'),
(9,'driver1','driver1','driver','2009-10-17','Male','09876543211','driver1@gmail.com','2026-05-25 01:56:49'),
(10,'customer3','customer3','customer','1989-12-28','Other','09876543876','customer3@gmail.com','2026-05-25 07:35:41'),
(11,'customer4','customer4','customer','2008-11-13','Female','09875644134','customer4@gmail.com','2026-05-25 14:10:06'),
(12,'JoJo','JoJo','customer','2009-03-19','Other','09876576541','JoJo@email.com','2026-05-26 03:08:49');

-- ============================================================
-- DATA: pizzas  (category_id replaces category string)
-- category_id mapping:
--   1=Bestsellers, 2=Kiddies Favorites, 3=House Specialties
--   4=Other Flavors, 5=New Flavors, 8=Samples, 9=Random
-- ============================================================
INSERT INTO `pizzas` (`pizza_id`,`pizza_name`,`category_id`,`ingredients`,`image_path`,`stock`) VALUES
(1,'Pizza Supreme',1,'Pork pepperoni, bacon, mushroom, onions, pineapple tidbits, black olives, green bell pepper','menu/Bestsellers/Pizza Supreme.png',8),
(3,'Cookies N Cheese',2,'Crushed Oreo Cookies','menu/Kiddies Favorites/Cookies N Cheese.png',10),
(4,'Creamy Cheese',2,'Cream Cheese','menu/Kiddies Favorites/Creamy Cheese.png',10),
(5,'Oreo Piña',2,'crushed Oreo cookies with pineapple tidbits','menu/Kiddies Favorites/Oreo Pina.png',10),
(6,'Yummy Hotdog',2,'All hotdog','menu/Kiddies Favorites/Yummy Hotdog.png',5),
(7,'Ham Delights',2,'All ham','menu/Kiddies Favorites/Ham Delight.png',5),
(8,'Chocomallow',2,'Choco stick, crushed oreo, marshmallow, & choco syrup','menu/Kiddies Favorites/Chocomallow.png',10),
(9,'Ham And Egg',2,'Ham, ham sausage, hamonado, egg, tomatoes, & onions','menu/Kiddies Favorites/Ham And Egg.png',0),
(10,'Chizzo Trio',2,'Quickmelt, Mozzarella, & Cheddar cheese','menu/Kiddies Favorites/Chizzo Trio.png',0),
(11,'Mango Graham',2,'Cream Cheese, Mango, & crushed Graham crackers','menu/Kiddies Favorites/Mango Graham.png',0),
(12,'Chogburizo',3,'Chorizo de Cebu, ham sausage, & onions','menu/House Specialties/Chogburizo.png',20),
(13,'Buffalo Chicken',3,'Chicken minced, pineapple tidbits, onions, red bell pepper, & AP buffalo sauce','menu/House Specialties/Buffalo Chicken.png',0),
(14,'Beef Shawarma',3,'Ground beef, cucumber, lettuce, tomatoes, onions, & AP shawarma sauce','menu/House Specialties/Beef Shawarma.png',0),
(15,'Shrimp And Mushroom',3,'Shrimp, mushroom, red bell pepper, tomatoes, onions, & garlic bits','menu/House Specialties/Shrimp And Mushroom.png',5),
(16,'Sisig Twist',3,'Pork sisig, onions, red bell pepper, garlic bits, calamansi, & AP sisig sauce','menu/House Specialties/Sisig Twist.png',0),
(17,'Royal Rumble',3,'Beef pepperoni, ham, salami, hungarian sausage, cheese krainer, pork pepperoni, hotdog, chicken hotdog, hamonado, shrimp, chicken minced, crab stick, cucumber, onions, pineapple tidbits, black olives, mushroom, & AP Rumble Sauce','menu/House Specialties/Royal Rumble.png',0),
(18,'Spanish Sardines',3,'Spanish sardines, carrots, pickle, tomatoes & onions','menu/House Specialties/Spanish Sardines.png',0),
(19,'Anchovy Pizza',3,'Anchovies, beef pepperoni, black olives, red bell pepper, tomatoes, onions, & garlic bits','menu/House Specialties/Anchovy Pizza.png',0),
(20,'Mango Bacon',3,'Cream cheese, mango, bacon, & green bell pepper','menu/House Specialties/Mango Bacon.png',10),
(21,'Salad Pizza',3,'Crab stick, spinach, cucumber, onions, tomatoes, black olives, cheddar cheese, garlic bits, lettuce, boiled egg, & AP salad dressing','menu/House Specialties/Salad Pizza.png',0),
(22,'Surf And Turf',3,'Corned beef, ground beef, tuna flakes, crab stick, shrimp, onions, & garlic bits','menu/House Specialties/Surf And Turf.png',9),
(23,'Pizza D Marina',3,'Spanish sardines, tuna flakes, crab stick, anchovies, shrimp, carrot, pickles, onions, & tomatoes','menu/House Specialties/Pizza D Marina.png',0),
(24,'Royal Flush',3,'Cheese krainer, pork pepperoni, hungarian sausage, ham sausage, hotdog, chicken hotdog, hamonado, ground beef, chicken minced, tuna flakes, ham, chorizo de Cebu, bacon, salami, & cheddar cheese','menu/House Specialties/Royal Flush.png',0),
(25,'Pizza Supreme',1,'Pork pepperoni, bacon, mushroom, onions, pineapple tidbits, black olives, & green bell pepper','menu/Bestsellers/Pizza Supreme.png',8),
(26,'Hawaiian',1,'Ham, bacon, pineapple tidbits, mushroom, onions, & green bell pepper','menu/Bestsellers/Hawaiian.png',9),
(27,'Aloha',1,'Ham sausage, lots of pineapple tidbits, mushroom, & green bell pepper','menu/Bestsellers/Aloha.png',10),
(28,'Beef And Mushroom',1,'Ground beef, mushroom, red bell pepper, & onions','menu/Bestsellers/Beef And Mushroom.png',0),
(29,'All Pepperoni',1,'Pork pepperoni with AP hot sauce','menu/Bestsellers/All Pepperoni.png',8),
(30,'Loaded Hawaiian',1,'Ham, bacon, pineapple tidbits, mushroom, green bell pepper, & mozzarella cheese','menu/Bestsellers/Loaded Hawaiian.png',0),
(31,'Meaty Royale',1,'Hungarian sausage, pork pepperoni, salami, ham, bacon, & mozzarella cheese','menu/Bestsellers/Meaty Royale.png',0),
(32,'Albertos Full House',1,'Ham, salami, hungarian sausage, bacon, pork pepperoni, chicken hotdog, ground beef, chicken minced, mushroom, pineapple tidbits, black olives, onions, tomatoes, red & green bell pepper','menu/Bestsellers/Albertos Full House.png',0),
(33,'Ceamy Cucumber Spinach',1,'Cream cheese mix, spinach, cucumber, & garlic bits','menu/Bestsellers/Ceamy Cucumber Spinach.png',0),
(34,'Garden Express',4,'Mushroom, pineapple tidbits, black olives, onions, tomatoes, red & green bell pepper','menu/Other Flavors/Garden Express.png',0),
(35,'Vegetarian',4,'Cucumber, lettuce, tomatoes, mushroom, onions, black olives, red & green bell pepper','menu/Other Flavors/Vegetarian.png',0),
(36,'All Hungarian',4,'Hungarian sausage w/ AP hot sauce','menu/Other Flavors/All Hungarian.png',0),
(37,'Beef Pepperoni',4,'Beef pepperoni','menu/Other Flavors/Beef Pepperoni.png',0),
(38,'Pizza Burger',4,'Bacon, ground beef, mushroom, tomatoes, & onions','menu/Other Flavors/Pizza Burger.png',0),
(39,'Chicken Garlic',4,'Chicken hotdog, chicken minced, tomatoes, & onions','menu/Other Flavors/Chicken Garlic.png',0),
(40,'Bacon Mushroom',4,'Bacon, mushroom, tomatoes & onions','menu/Other Flavors/Bacon Mushroom.png',0),
(41,'Tuna Garlic',4,'Tuna flakes, tomatoes, & onions','menu/Other Flavors/Tuna Garlic.png',0),
(42,'Three Of A Kind',4,'Ground beef, chicken minced, & tuna flakes','menu/Other Flavors/Three Of A Kind.png',0),
(43,'Chessy Krainer',4,'All cheese krainer sausage','menu/Other Flavors/Chessy Krainer.png',0),
(44,'Meatlovers Deluxe',4,'Ham, salami, hungarian sausage, pork pepperoni, beef pepperoni, bacon, ground beef, onions, red & green bell pepper','menu/Other Flavors/Meatlovers Deluxe.png',0),
(45,'Spinach N Chicken Pizza',4,'Chicken minced, spinach, garlic bits, & AP spinach white sauce','menu/Other Flavors/Spinach N Chicken Pizza.png',10),
(46,'Loaded Pepperoni',4,'Beef pepperoni, pork pepperoni, & mozzarella cheese','menu/Other Flavors/Loaded Pepperoni.png',0),
(47,'Spicy Meatzza',5,'Chorizo de Cebu, hamonado, hungarian sausage, beef pepperoni, onions, red bell pepper, tomatoes, & AP ruble sauce','menu/New Flavors/Spicy Meatzza.png',0),
(48,'Pizza Tropicana',5,'Chorizo de Cebu, spam, pineapple tidbits','menu/New Flavors/Pizza Tropicana.png',0),
(51,'Sample',8,'samples','menu/Samples/Default.png',0),
(53,'Random',9,'random','menu/Random/Default.png',0),
(55,'Random Pizza',9,'Randomized ingredients.','menu/Random/Default.png',0);

-- ============================================================
-- DATA: pizza_variants (unchanged)
-- ============================================================
INSERT INTO `pizza_variants` VALUES
(1,1,9,'Quickmelt',145.00),(2,1,9,'Mozzarella',165.00),(3,1,11,'Quickmelt',195.00),(4,1,11,'Mozzarella',215.00),
(9,3,9,'Quickmelt',110.00),(11,3,9,'Mozzarella',130.00),(13,3,11,'Quickmelt',150.00),(15,3,11,'Mozzarella',170.00),
(17,4,9,'Quickmelt',115.00),(18,4,9,'Mozzarella',135.00),(19,4,11,'Quickmelt',155.00),(20,4,11,'Mozzarella',175.00),
(21,5,9,'Quickmelt',120.00),(22,5,9,'Mozzarella',135.00),(23,5,11,'Quickmelt',155.00),(24,5,11,'Mozzarella',175.00),
(25,6,9,'Quickmelt',153.00),(26,6,9,'Mozzarella',155.00),(27,6,11,'Quickmelt',185.00),(28,6,11,'Mozzarella',205.00),
(29,7,9,'Quickmelt',140.00),(30,7,9,'Mozzarella',160.00),(31,7,11,'Quickmelt',190.00),(32,7,11,'Mozzarella',210.00),
(33,8,9,'Quickmelt',170.00),(34,8,9,'Mozzarella',190.00),(35,8,11,'Quickmelt',220.00),(36,8,11,'Mozzarella',240.00),
(37,9,9,'Quickmelt',180.00),(38,9,9,'Mozzarella',200.00),(39,9,11,'Quickmelt',230.00),(40,9,11,'Mozzarella',250.00),
(41,9,9,'Quickmelt',180.00),(42,9,9,'Mozzarella',200.00),(43,9,11,'Quickmelt',230.00),(44,9,11,'Mozzarella',250.00),
(45,10,9,'Quickmelt',150.00),(46,10,9,'Mozzarella',150.00),(47,10,11,'Quickmelt',200.00),(48,10,11,'Mozzarella',200.00),
(49,11,11,'Quickmelt',220.00),(50,11,11,'Mozzarella',240.00),
(51,12,9,'Quickmelt',175.00),(52,12,9,'Mozzarella',195.00),(53,12,11,'Quickmelt',225.00),(54,12,11,'Mozzarella',245.00),
(55,13,9,'Quickmelt',180.00),(56,13,9,'Mozzarella',200.00),(57,13,11,'Quickmelt',230.00),(58,13,11,'Mozzarella',250.00),
(59,14,9,'Quickmelt',205.00),(60,14,9,'Mozzarella',225.00),(61,14,11,'Quickmelt',255.00),(62,14,11,'Mozzarella',275.00),
(63,15,11,'Quickmelt',230.00),(64,15,11,'Mozzarella',250.00),
(65,16,11,'Quickmelt',240.00),(66,16,11,'Mozzarella',260.00),
(67,17,11,'Quickmelt',260.00),(68,17,11,'Mozzarella',280.00),
(69,18,11,'Quickmelt',265.00),(70,18,11,'Mozzarella',285.00),
(71,19,11,'Quickmelt',265.00),(72,19,11,'Mozzarella',285.00),
(73,20,11,'Quickmelt',265.00),(74,20,11,'Mozzarella',285.00),
(75,21,11,'Quickmelt',295.00),(76,21,11,'Mozzarella',315.00),
(77,22,11,'Quickmelt',310.00),(78,22,11,'Mozzarella',330.00),
(79,23,11,'Quickmelt',360.00),(80,23,11,'Mozzarella',380.00),
(81,24,11,'Quickmelt',325.00),(82,24,11,'Mozzarella',325.00),
(83,25,9,'Quickmelt',145.00),(84,1,9,'Quickmelt',145.00),(85,25,9,'Mozzarella',165.00),(86,1,9,'Mozzarella',165.00),
(87,25,11,'Quickmelt',195.00),(88,1,11,'Quickmelt',195.00),(89,25,11,'Mozzarella',215.00),(90,1,11,'Mozzarella',215.00),
(91,26,9,'Quickmelt',145.00),(92,26,9,'Mozzarella',165.00),(93,26,11,'Quickmelt',195.00),(94,26,11,'Mozzarella',215.00),
(95,27,9,'Quickmelt',150.00),(96,27,9,'Mozzarella',170.00),(97,27,11,'Quickmelt',200.00),(98,27,11,'Mozzarella',220.00),
(99,28,9,'Quickmelt',170.00),(100,28,9,'Mozzarella',190.00),(101,28,11,'Quickmelt',220.00),(102,28,11,'Mozzarella',240.00),
(103,29,9,'Quickmelt',200.00),(104,29,9,'Mozzarella',220.00),(105,29,11,'Quickmelt',250.00),(106,29,11,'Mozzarella',270.00),
(107,30,11,'Quickmelt',240.00),(108,30,11,'Mozzarella',260.00),
(109,31,11,'Quickmelt',270.00),(110,31,11,'Mozzarella',290.00),
(111,32,11,'Quickmelt',280.00),(112,32,11,'Mozzarella',300.00),
(113,33,11,'Quickmelt',320.00),(114,33,11,'Mozzarella',340.00),
(115,34,9,'Quickmelt',125.00),(116,34,9,'Mozzarella',145.00),(117,34,11,'Quickmelt',175.00),(118,34,11,'Mozzarella',195.00),
(119,35,9,'Quickmelt',155.00),(120,35,9,'Mozzarella',175.00),(121,35,11,'Quickmelt',205.00),(122,35,11,'Mozzarella',225.00),
(123,36,9,'Quickmelt',160.00),(124,36,9,'Mozzarella',180.00),(125,36,11,'Quickmelt',210.00),(126,36,11,'Mozzarella',230.00),
(127,37,9,'Quickmelt',160.00),(128,37,9,'Mozzarella',180.00),(129,37,11,'Quickmelt',210.00),(130,37,11,'Mozzarella',230.00),
(131,38,9,'Quickmelt',160.00),(132,38,9,'Mozzarella',185.00),(133,38,11,'Quickmelt',215.00),(134,38,11,'Mozzarella',235.00),
(135,39,9,'Quickmelt',170.00),(136,39,9,'Mozzarella',190.00),(137,39,11,'Quickmelt',220.00),(138,39,11,'Mozzarella',240.00),
(139,40,9,'Quickmelt',170.00),(140,40,9,'Mozzarella',190.00),(141,40,11,'Quickmelt',220.00),(142,40,11,'Mozzarella',240.00),
(143,41,9,'Quickmelt',180.00),(144,41,9,'Mozzarella',205.00),(145,41,11,'Quickmelt',230.00),(146,41,11,'Mozzarella',250.00),
(147,42,9,'Quickmelt',185.00),(148,42,9,'Mozzarella',205.00),(149,42,11,'Quickmelt',235.00),(150,42,11,'Mozzarella',255.00),
(151,43,9,'Quickmelt',200.00),(152,43,9,'Mozzarella',220.00),(153,43,11,'Quickmelt',250.00),(154,43,11,'Mozzarella',270.00),
(155,44,9,'Quickmelt',210.00),(156,44,9,'Mozzarella',230.00),(157,44,11,'Quickmelt',260.00),(158,44,11,'Mozzarella',280.00),
(159,45,11,'Quickmelt',240.00),(160,45,11,'Mozzarella',260.00),
(161,46,11,'Quickmelt',285.00),(162,46,11,'Mozzarella',305.00),
(163,47,11,'Quickmelt',225.00),(164,47,11,'Mozzarella',245.00),
(165,48,11,'Quickmelt',260.00),(166,48,11,'Mozzarella',280.00),
(175,51,9,'Quickmelt',100.00),(176,51,11,'Quickmelt',0.00),(177,51,9,'Mozzarella',110.00),(178,51,11,'Mozzarella',0.00),
(183,53,9,'Quickmelt',0.00),(184,53,11,'Quickmelt',150.00),(185,53,9,'Mozzarella',0.00),(186,53,11,'Mozzarella',180.00),
(191,55,9,'Quickmelt',110.00),(192,55,11,'Quickmelt',120.00),(193,55,9,'Mozzarella',140.00),(194,55,11,'Mozzarella',159.00);

-- ============================================================
-- DATA: orders (unchanged)
-- ============================================================
INSERT INTO `orders` VALUES
(1,NULL,'Guest','09876543210','gil.zivra@gmail.com',93,'Zone 5','PICK-UP','CASH',415.00,'cancelled','2026-05-20 12:21:46',NULL,NULL),
(2,4,'customer2','09876543213','customer2@gmail.com',93,'Zone 5','PICK-UP','CASH',395.00,'pending','2026-05-20 14:49:30',NULL,NULL),
(3,NULL,'Moxie','09876543213','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','ONLINE',850.00,'pending','2026-05-20 15:07:49',NULL,'2026-05-26 04:33:41'),
(4,NULL,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','CASH',1635.00,'delivered','2026-05-20 15:17:52',9,'2026-05-25 07:02:07'),
(5,NULL,'Moxie3','09876543218','Moxie3@gmail.com',92,'Zone 5','PICK-UP','ONLINE',1755.00,'pending','2026-05-20 15:28:16',NULL,'2026-05-26 04:33:41'),
(6,4,'customer2','09876543213','customer2@gmail.com',25,'Zone 1, Baybay, Leyte','PICK-UP','ONLINE',465.00,'pending','2026-05-20 23:51:15',NULL,'2026-05-26 04:33:41'),
(7,4,'customer2','09876543213','customer2@gmail.com',99,'Zone 5','PICK-UP','ONLINE',1935.00,'pending','2026-05-20 23:52:34',NULL,'2026-05-26 04:33:41'),
(8,NULL,'Doxie','09876543219','Doxie@gmail.com',41,'Zone 5','DELIVERY','ONLINE',260.00,'pending','2026-05-20 23:58:13',NULL,'2026-05-26 04:33:41'),
(9,NULL,'Doug','09098877664','',23,'Zone 5','PICK-UP','ONLINE',330.00,'completed','2026-05-21 00:32:31',NULL,'2026-05-26 04:33:41'),
(10,NULL,'Dolly','09876509876','Dolly@gmail.com',21,'Zone 5','PICK-UP','ONLINE',545.00,'completed','2026-05-21 00:57:32',NULL,'2026-05-26 04:33:41'),
(11,4,'customer2','09876543213','customer2@gmail.com',89,'Zone 5','DELIVERY','ONLINE',455.00,'completed','2026-05-21 00:59:08',NULL,'2026-05-26 04:33:41'),
(12,NULL,'Chicharon','09876543217','Chii@gmail.com',99,'Zone 5','PICK-UP','CASH',1315.00,'pending','2026-05-21 03:09:43',NULL,NULL),
(13,NULL,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','CASH',1635.00,'out_for_delivery','2026-05-21 06:36:30',9,'2026-05-25 07:02:11'),
(14,NULL,'Dolly','09876509876','Dolly@gmail.com',21,'Zone 5','PICK-UP','ONLINE',545.00,'pending','2026-05-23 02:59:38',NULL,'2026-05-26 04:33:41'),
(15,2,'customer2','09876543213','customer2@gmail.com',89,'Zone 5','on','ONLINE',455.00,'pending','2026-05-23 05:57:17',NULL,'2026-05-26 04:33:41'),
(16,2,'Doug','09098877664','',23,'Zone 5','on','ONLINE',330.00,'pending','2026-05-24 01:26:41',NULL,'2026-05-26 04:33:41'),
(17,2,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','on','CASH',1635.00,'pending','2026-05-24 01:30:52',NULL,NULL),
(18,NULL,'Zivra','09876543211','gil.zivra@gmail.com',41,'0','PICK-UP','CASH',145.00,'completed','2026-05-24 19:40:01',NULL,NULL),
(19,NULL,'Zzz','09876543232','',99,'0','PICK-UP','CASH',580.00,'completed','2026-05-24 19:42:24',NULL,NULL),
(20,2,'Zzz','09876543232','',99,'0','on','on',580.00,'completed','2026-05-24 19:45:38',NULL,NULL),
(21,1,'customer1','09876543210','customer1@gmail.com',99,'0','PICK-UP','CASH',145.00,'completed','2026-05-25 04:03:24',NULL,NULL),
(22,2,'customer1','09876543210','customer1@gmail.com',99,'0','PICK-UP','CASH',475.00,'completed','2026-05-25 05:50:12',NULL,NULL),
(23,2,'Zivra','09876543211','gil.zivra@gmail.com',41,'0','PICK-UP','CASH',145.00,'completed','2026-05-25 06:19:41',NULL,NULL),
(24,NULL,'Horn','09123123123','',93,'0','DELIVERY','ONLINE',430.00,'pending','2026-05-25 06:33:52',NULL,'2026-05-26 04:33:41'),
(25,NULL,'JoJo','09876565445','',93,'0','DELIVERY','ONLINE',240.00,'delivered','2026-05-26 03:06:47',9,'2026-05-26 04:40:32'),
(26,NULL,'Jen','09876543121','Jen@gmail.com',89,'0','PICK-UP','CASH',145.00,'pending','2026-05-26 03:18:03',NULL,NULL),
(27,2,'Joy','09876543213','Joy@email.com',89,'0','PICK-UP','CASH',200.00,'completed','2026-05-26 03:28:30',NULL,NULL),
(28,NULL,'Dove','09123124534','Dove@gmail.com',24,'Zone 5','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:01:24',NULL,'2026-05-26 04:33:41'),
(29,NULL,'Kiin','09871235435','Kiin@gmail.com',25,'Sample Zone','DELIVERY','ONLINE',200.00,'completed','2026-05-26 04:03:59',NULL,'2026-05-26 04:33:41'),
(30,2,'Kiin','09871235435','Kiin@gmail.com',25,'Sample Zone','DELIVERY','CASH',200.00,'completed','2026-05-26 04:05:05',NULL,NULL),
(31,NULL,'frog','09876364564','',89,'','PICK-UP','ONLINE',145.00,'pending','2026-05-26 04:20:05',NULL,NULL),
(32,NULL,'devil','09745634534','devil@emailcom',57,'Zone 5','DELIVERY','ONLINE',290.00,'pending','2026-05-26 04:21:18',NULL,NULL),
(33,NULL,'fet','09867854634','',25,'Sample Zone','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:22:11',NULL,'2026-05-26 04:33:41'),
(34,NULL,'CHET','09784356234','',25,'Sample Zone','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:25:06',NULL,NULL),
(35,NULL,'Del','09873423413','',59,'Sample Zone','DELIVERY','ONLINE',175.00,'pending','2026-05-26 05:36:01',NULL,NULL);

-- ============================================================
-- DATA: order_items
-- 3NF FIX: pizza_id and variant_id are hardcoded FKs.
-- Snapshot columns (pizza_name, size, cheese, price) retained
-- for historical accuracy (price at time of order).
-- pizza_id/variant_id resolved from pizza_variants table:
--   Hawaiian=26 (91=9Q,92=9M,93=11Q,94=11M)
--   Pizza Supreme=1 (1=9Q,2=9M,3=11Q,4=11M)
--   Aloha=27 (95=9Q,96=9M,97=11Q,98=11M)
--   Beef And Mushroom=28 (99=9Q,100=9M,101=11Q,102=11M)
--   All Pepperoni=29 (103=9Q,104=9M,105=11Q,106=11M)
--   Loaded Hawaiian=30 (107=11Q,108=11M)
--   Meaty Royale=31 (109=11Q,110=11M)
--   Albertos Full House=32 (111=11Q,112=11M)
--   Surf And Turf=22 (77=11Q,78=11M)
--   Salad Pizza=21 (75=11Q,76=11M)
--   Shrimp And Mushroom=15 (63=11Q,64=11M)
--   Sisig Twist=16 (65=11Q,66=11M)
--   Royal Rumble=17 (67=11Q,68=11M)
--   Mango Bacon=20 (73=11Q,74=11M)
--   Chocomallow=8 (33=9Q,34=9M,35=11Q,36=11M)
--   Bacon Mushroom=40 (139=9Q,140=9M,141=11Q,142=11M)
--   Garden Express=34 (115=9Q,116=9M,117=11Q,118=11M)
--   Mango Graham=11 (49=11Q,50=11M)
--   Cookies N Cheese=3 (9=9Q,11=9M,13=11Q,15=11M)
--   Ham And Egg=9 (37=9Q,38=9M,39=11Q,40=11M)
--   Oreo Piña=5 (21=9Q,22=9M,23=11Q,24=11M)
--   Chizzo Trio=10 (45=9Q,46=9M,47=11Q,48=11M)
--   Pizza Tropicana=48 (165=11Q,166=11M)
--   Ham Delights=7 (29=9Q,30=9M,31=11Q,32=11M)
--   Chogburizo=12 (51=9Q,52=9M,53=11Q,54=11M)
-- ============================================================
INSERT INTO `order_items`
  (`item_id`,`order_id`,`pizza_id`,`variant_id`,`pizza_name`,`size`,`cheese`,`price`,`quantity`,`total`)
VALUES
(1,1,28,101,'Beef And Mushroom','11','Quickmelt',220.00,1,220.00),
(2,1,1,3,'Pizza Supreme','11','Quickmelt',195.00,1,195.00),
(3,2,27,97,'Aloha','11','Quickmelt',200.00,1,200.00),
(4,2,1,3,'Pizza Supreme','11','Quickmelt',195.00,1,195.00),
(5,3,30,108,'Loaded Hawaiian','11','Mozzarella',260.00,2,520.00),
(6,3,22,78,'Surf And Turf','11','Mozzarella',330.00,1,330.00),
(7,4,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(8,4,16,66,'Sisig Twist','11','Mozzarella',260.00,1,260.00),
(9,4,32,112,'Albertos Full House','11','Mozzarella',300.00,3,900.00),
(10,4,26,92,'Hawaiian','9','Mozzarella',165.00,2,330.00),
(11,5,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(12,5,22,77,'Surf And Turf','11','Quickmelt',310.00,2,620.00),
(13,5,11,50,'Mango Graham','11','Mozzarella',240.00,1,240.00),
(14,5,15,64,'Shrimp And Mushroom','11','Mozzarella',250.00,3,750.00),
(15,6,8,33,'Chocomallow','9','Quickmelt',170.00,1,170.00),
(16,6,40,139,'Bacon Mushroom','9','Quickmelt',170.00,1,170.00),
(17,6,34,115,'Garden Express','9','Quickmelt',125.00,1,125.00),
(18,7,22,78,'Surf And Turf','11','Mozzarella',330.00,3,990.00),
(19,7,21,76,'Salad Pizza','11','Mozzarella',315.00,3,945.00),
(20,8,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(21,8,3,9,'Cookies N Cheese','9','Quickmelt',110.00,1,110.00),
(22,9,31,NULL,'Meaty Royale','9','Quickmelt',0.00,1,0.00),  -- variant_id NULL: no 9" variant exists (bad original data)
(23,9,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(24,9,9,37,'Ham And Egg','9','Quickmelt',180.00,1,180.00),
(25,10,5,21,'Oreo Pina','9','Quickmelt',115.00,1,115.00),
(26,10,10,45,'Chizzo Trio','9','Quickmelt',150.00,1,150.00),
(27,10,48,166,'Pizza Tropicana','11','Mozzarella',280.00,1,280.00),
(28,11,21,75,'Salad Pizza','11','Quickmelt',295.00,1,295.00),
(29,11,7,30,'Ham Delight','9','Mozzarella',160.00,1,160.00),
(30,12,29,103,'All Pepperoni','9','Quickmelt',200.00,1,200.00),
(31,12,17,67,'Royal Rumble','11','Quickmelt',260.00,1,260.00),
(32,12,20,74,'Mango Bacon','11','Mozzarella',285.00,3,855.00),
(33,13,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(34,13,16,66,'Sisig Twist','11','Mozzarella',260.00,1,260.00),
(35,13,32,112,'Albertos Full House','11','Mozzarella',300.00,3,900.00),
(36,13,26,92,'Hawaiian','9','Mozzarella',165.00,2,330.00),
(37,14,5,21,'Oreo Pina','9','Quickmelt',115.00,1,115.00),
(38,14,10,45,'Chizzo Trio','9','Quickmelt',150.00,1,150.00),
(39,14,48,166,'Pizza Tropicana','11','Mozzarella',280.00,1,280.00),
(40,15,21,75,'Salad Pizza','11','Quickmelt',295.00,1,295.00),
(41,15,7,30,'Ham Delight','9','Mozzarella',160.00,1,160.00),
(42,16,31,NULL,'Meaty Royale','9','Quickmelt',0.00,1,0.00),  -- variant_id NULL: no 9" variant exists (bad original data)
(43,16,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(44,16,9,37,'Ham And Egg','9','Quickmelt',180.00,1,180.00),
(45,17,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(46,17,16,66,'Sisig Twist','11','Mozzarella',260.00,1,260.00),
(47,17,32,112,'Albertos Full House','11','Mozzarella',300.00,3,900.00),
(48,17,26,92,'Hawaiian','9','Mozzarella',165.00,2,330.00),
(49,18,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(50,19,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(51,19,1,1,'Pizza Supreme','9','Quickmelt',145.00,3,435.00),
(52,20,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(53,20,1,1,'Pizza Supreme','9','Quickmelt',145.00,3,435.00),
(54,21,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(55,22,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(56,22,22,78,'Surf And Turf','11','Mozzarella',330.00,1,330.00),
(57,23,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(58,24,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(59,24,20,74,'Mango Bacon','11','Mozzarella',285.00,1,285.00),
(60,25,5,21,'Oreo Piña','9','Quickmelt',120.00,2,240.00),
(61,26,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(62,27,29,103,'All Pepperoni','9','Quickmelt',200.00,1,200.00),
(63,28,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(64,29,29,103,'All Pepperoni','9','Quickmelt',200.00,1,200.00),
(65,30,29,103,'All Pepperoni','9','Quickmelt',200.00,1,200.00),
(66,31,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(67,32,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(68,32,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(69,33,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(70,34,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(71,35,12,51,'Chogburizo','9','Quickmelt',175.00,1,175.00);

-- ============================================================
-- AUTO_INCREMENT resets
-- ============================================================
ALTER TABLE `branches`      AUTO_INCREMENT = 106;
ALTER TABLE `categories`    AUTO_INCREMENT = 10;
ALTER TABLE `users`         AUTO_INCREMENT = 13;
ALTER TABLE `pizzas`        AUTO_INCREMENT = 56;
ALTER TABLE `pizza_variants` AUTO_INCREMENT = 195;
ALTER TABLE `orders`        AUTO_INCREMENT = 36;
ALTER TABLE `order_items`   AUTO_INCREMENT = 72;

-- ============================================================
-- End of 3NF normalized dump
-- ============================================================
