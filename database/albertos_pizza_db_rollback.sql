-- ============================================================
-- Alberto's Pizza — ROLLBACK to Practical Schema
-- Database: albertos_pizza_db_3nf  (same DB, restructured)
-- ============================================================
-- What this rollback does:
--   orders      → adds back: customer_name, mobile_number,
--                             email, total_amount
--   order_items → adds back: pizza_name, size, cheese,
--                             price, total
--   Drops order_contacts (no longer needed)
--   Keeps: ingredients + pizza_ingredients (1NF fix stays)
--   Keeps: pizzas.category_id FK (3NF fix stays)
--   Keeps: UNIQUE on pizza_variants (dedup fix stays)
--   Keeps: all views (still useful for joins)
-- ============================================================
-- Run in phpMyAdmin: Import this file against albertos_pizza_db_3nf
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: Drop views (will recreate at end)
-- ============================================================
DROP VIEW IF EXISTS v_orders_full;
DROP VIEW IF EXISTS v_order_items_full;
DROP VIEW IF EXISTS v_pizzas_full;

-- ============================================================
-- STEP 2: Drop order_contacts (data moved back into orders)
-- ============================================================
DROP TABLE IF EXISTS order_contacts;

-- ============================================================
-- STEP 3: Recreate orders WITH snapshot columns
-- ============================================================
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;

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
  KEY `fk_order_branch`  (`branch_id`),
  KEY `fk_order_user`    (`user_id`),
  KEY `fk_order_driver`  (`driver_id`),
  CONSTRAINT `fk_order_branch`
    FOREIGN KEY (`branch_id`)  REFERENCES `branches` (`branch_id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_order_user`
    FOREIGN KEY (`user_id`)    REFERENCES `users` (`user_id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_order_driver`
    FOREIGN KEY (`driver_id`)  REFERENCES `users` (`user_id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STEP 4: Recreate order_items WITH snapshot columns
--         pizza_id + variant_id FKs are KEPT as bonus columns
-- ============================================================
CREATE TABLE `order_items` (
  `item_id`    int           NOT NULL AUTO_INCREMENT,
  `order_id`   int           DEFAULT NULL,
  `pizza_id`   int           DEFAULT NULL,
  `variant_id` int           DEFAULT NULL,
  `pizza_name` varchar(255)  DEFAULT NULL,
  `size`       varchar(10)   DEFAULT NULL,
  `cheese`     varchar(50)   DEFAULT NULL,
  `price`      decimal(10,2) DEFAULT NULL,
  `quantity`   int           DEFAULT NULL,
  `total`      decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `fk_item_order`   (`order_id`),
  KEY `fk_item_pizza`   (`pizza_id`),
  KEY `fk_item_variant` (`variant_id`),
  CONSTRAINT `fk_item_order`
    FOREIGN KEY (`order_id`)   REFERENCES `orders` (`order_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_item_pizza`
    FOREIGN KEY (`pizza_id`)   REFERENCES `pizzas` (`pizza_id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_item_variant`
    FOREIGN KEY (`variant_id`) REFERENCES `pizza_variants` (`variant_id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- STEP 5: Re-insert orders (with all snapshot columns)
-- ============================================================
INSERT INTO `orders`
  (`order_id`,`user_id`,`customer_name`,`mobile_number`,`email`,
   `branch_id`,`address`,`order_type`,`payment_method`,`total_amount`,
   `status`,`created_at`,`driver_id`,`updated_at`)
VALUES
(1,NULL,'Guest','09876543210','gil.zivra@gmail.com',93,'Zone 5','PICK-UP','CASH',415.00,'cancelled','2026-05-20 12:21:46',NULL,NULL),
(2,4,'customer2','09876543213','customer2@gmail.com',93,'Zone 5','PICK-UP','CASH',395.00,'pending','2026-05-20 14:49:30',NULL,NULL),
(3,NULL,'Moxie','09876543213','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','ONLINE',850.00,'pending','2026-05-20 15:07:49',NULL,'2026-05-26 04:33:41'),
(4,NULL,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','CASH',1635.00,'delivered','2026-05-20 15:17:52',9,'2026-05-25 07:02:07'),
(5,NULL,'Moxie3','09876543218','Moxie3@gmail.com',92,'Zone 5','PICK-UP','ONLINE',1755.00,'pending','2026-05-20 15:28:16',NULL,'2026-05-26 04:33:41'),
(6,4,'customer2','09876543213','customer2@gmail.com',25,'Zone 1, Baybay, Leyte','PICK-UP','ONLINE',465.00,'pending','2026-05-20 23:51:15',NULL,'2026-05-26 04:33:41'),
(7,4,'customer2','09876543213','customer2@gmail.com',99,'Zone 5','PICK-UP','ONLINE',1935.00,'pending','2026-05-20 23:52:34',NULL,'2026-05-26 04:33:41'),
(8,NULL,'Doxie','09876543219','Doxie@gmail.com',41,'Zone 5','DELIVERY','ONLINE',260.00,'pending','2026-05-20 23:58:13',NULL,'2026-05-26 04:33:41'),
(9,NULL,'Doug','09098877664',NULL,23,'Zone 5','PICK-UP','ONLINE',330.00,'completed','2026-05-21 00:32:31',NULL,'2026-05-26 04:33:41'),
(10,NULL,'Dolly','09876509876','Dolly@gmail.com',21,'Zone 5','PICK-UP','ONLINE',550.00,'completed','2026-05-21 00:57:32',NULL,'2026-05-26 04:33:41'),
(11,4,'customer2','09876543213','customer2@gmail.com',89,'Zone 5','DELIVERY','ONLINE',455.00,'completed','2026-05-21 00:59:08',NULL,'2026-05-26 04:33:41'),
(12,NULL,'Chicharon','09876543217','Chii@gmail.com',99,'Zone 5','PICK-UP','CASH',1315.00,'pending','2026-05-21 03:09:43',NULL,NULL),
(13,NULL,'Moxie','09876543215','Moxie@gmail.com',89,'Zone 1, Baybay, Leyte','DELIVERY','CASH',1635.00,'out_for_delivery','2026-05-21 06:36:30',9,'2026-05-25 07:02:11'),
(14,NULL,'Dolly','09876509876','Dolly@gmail.com',21,'Zone 5','PICK-UP','ONLINE',550.00,'pending','2026-05-23 02:59:38',NULL,'2026-05-26 04:33:41'),
(15,2,'cashier1','09876543211','cashier1@gmail.com',89,'Zone 5','on','ONLINE',455.00,'pending','2026-05-23 05:57:17',NULL,'2026-05-26 04:33:41'),
(16,2,'cashier1','09876543211','cashier1@gmail.com',23,'Zone 5','on','ONLINE',330.00,'pending','2026-05-24 01:26:41',NULL,'2026-05-26 04:33:41'),
(17,2,'cashier1','09876543211','cashier1@gmail.com',89,'Zone 1, Baybay, Leyte','on','CASH',1635.00,'pending','2026-05-24 01:30:52',NULL,NULL),
(18,NULL,'Zivra','09876543211','gil.zivra@gmail.com',41,'0','PICK-UP','CASH',145.00,'completed','2026-05-24 19:40:01',NULL,NULL),
(19,NULL,'Zzz','09876543232',NULL,99,'0','PICK-UP','CASH',580.00,'completed','2026-05-24 19:42:24',NULL,NULL),
(20,2,'cashier1','09876543211','cashier1@gmail.com',99,'0','on','on',580.00,'completed','2026-05-24 19:45:38',NULL,NULL),
(21,1,'customer1','09876543210','customer1@gmail.com',99,'0','PICK-UP','CASH',145.00,'completed','2026-05-25 04:03:24',NULL,NULL),
(22,2,'cashier1','09876543211','cashier1@gmail.com',99,'0','PICK-UP','CASH',475.00,'completed','2026-05-25 05:50:12',NULL,NULL),
(23,2,'cashier1','09876543211','cashier1@gmail.com',41,'0','PICK-UP','CASH',145.00,'completed','2026-05-25 06:19:41',NULL,NULL),
(24,NULL,'Horn','09123123123',NULL,93,'0','DELIVERY','ONLINE',430.00,'pending','2026-05-25 06:33:52',NULL,'2026-05-26 04:33:41'),
(25,NULL,'JoJo','09876565445',NULL,93,'0','DELIVERY','ONLINE',240.00,'delivered','2026-05-26 03:06:47',9,'2026-05-26 04:40:32'),
(26,NULL,'Jen','09876543121','Jen@gmail.com',89,'0','PICK-UP','CASH',145.00,'pending','2026-05-26 03:18:03',NULL,NULL),
(27,2,'cashier1','09876543211','cashier1@gmail.com',89,'0','PICK-UP','CASH',200.00,'completed','2026-05-26 03:28:30',NULL,NULL),
(28,NULL,'Dove','09123124534','Dove@gmail.com',24,'Zone 5','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:01:24',NULL,'2026-05-26 04:33:41'),
(29,NULL,'Kiin','09871235435','Kiin@gmail.com',25,'Sample Zone','DELIVERY','ONLINE',200.00,'completed','2026-05-26 04:03:59',NULL,'2026-05-26 04:33:41'),
(30,2,'cashier1','09876543211','cashier1@gmail.com',25,'Sample Zone','DELIVERY','CASH',200.00,'completed','2026-05-26 04:05:05',NULL,NULL),
(31,NULL,'frog','09876364564',NULL,89,'','PICK-UP','ONLINE',145.00,'pending','2026-05-26 04:20:05',NULL,NULL),
(32,NULL,'devil','09745634534','devil@emailcom',57,'Zone 5','DELIVERY','ONLINE',290.00,'pending','2026-05-26 04:21:18',NULL,NULL),
(33,NULL,'fet','09867854634',NULL,25,'Sample Zone','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:22:11',NULL,'2026-05-26 04:33:41'),
(34,NULL,'CHET','09784356234',NULL,25,'Sample Zone','DELIVERY','ONLINE',145.00,'pending','2026-05-26 04:25:06',NULL,NULL),
(35,NULL,'Del','09873423413',NULL,59,'Sample Zone','DELIVERY','ONLINE',175.00,'pending','2026-05-26 05:36:01',NULL,NULL),
(36,1,'customer1','09876543210','customer1@gmail.com',93,'','PICK-UP','CASH',300.00,'pending','2026-05-27 11:30:16',NULL,NULL),
(38,NULL,'asdasdasdas','0909090909',NULL,99,'','PICK-UP','ONLINE',150.00,'pending','2026-05-27 11:39:54',NULL,NULL),
(39,NULL,'q123123','456456456',NULL,25,'','PICK-UP','CASH',150.00,'pending','2026-05-27 11:40:58',NULL,NULL),
(40,NULL,'juan','0909090909',NULL,41,'zon3','PICK-UP','CASH',145.00,'pending','2026-05-27 11:42:16',NULL,NULL),
(41,1,'customer1','09876543210','customer1@gmail.com',41,'','PICK-UP','CASH',150.00,'pending','2026-05-27 11:43:58',NULL,NULL),
(43,NULL,'miro ma niga','09876543210',NULL,57,'d','DELIVERY','CASH',145.00,'pending','2026-05-27 11:54:58',NULL,NULL),
(44,NULL,'adsasdasd','09876543210',NULL,57,'','PICK-UP','ONLINE',110.00,'pending','2026-05-27 11:55:33',NULL,NULL),
(45,NULL,'asdasd','09876543210',NULL,57,'w','DELIVERY','CASH',200.00,'pending','2026-05-27 11:58:52',NULL,NULL),
(46,1,'customer1','09876543210','customer1@gmail.com',99,'','PICK-UP','CASH',360.00,'pending','2026-05-27 12:00:39',NULL,NULL),
(47,1,'customer1','09876543210','customer1@gmail.com',57,'','PICK-UP','CASH',175.00,'pending','2026-05-27 12:03:52',NULL,NULL);

-- ============================================================
-- STEP 6: Re-insert order_items (with all snapshot columns)
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
(23,9,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(24,9,9,37,'Ham And Egg','9','Quickmelt',180.00,1,180.00),
(25,10,5,21,'Oreo Piña','9','Quickmelt',120.00,1,120.00),
(26,10,10,45,'Chizzo Trio','9','Quickmelt',150.00,1,150.00),
(27,10,48,166,'Pizza Tropicana','11','Mozzarella',280.00,1,280.00),
(28,11,21,75,'Salad Pizza','11','Quickmelt',295.00,1,295.00),
(29,11,7,30,'Ham Delights','9','Mozzarella',160.00,1,160.00),
(30,12,29,103,'All Pepperoni','9','Quickmelt',200.00,1,200.00),
(31,12,17,67,'Royal Rumble','11','Quickmelt',260.00,1,260.00),
(32,12,20,74,'Mango Bacon','11','Mozzarella',285.00,3,855.00),
(33,13,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(34,13,16,66,'Sisig Twist','11','Mozzarella',260.00,1,260.00),
(35,13,32,112,'Albertos Full House','11','Mozzarella',300.00,3,900.00),
(36,13,26,92,'Hawaiian','9','Mozzarella',165.00,2,330.00),
(37,14,5,21,'Oreo Piña','9','Quickmelt',120.00,1,120.00),
(38,14,10,45,'Chizzo Trio','9','Quickmelt',150.00,1,150.00),
(39,14,48,166,'Pizza Tropicana','11','Mozzarella',280.00,1,280.00),
(40,15,21,75,'Salad Pizza','11','Quickmelt',295.00,1,295.00),
(41,15,7,30,'Ham Delights','9','Mozzarella',160.00,1,160.00),
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
(71,35,12,51,'Chogburizo','9','Quickmelt',175.00,1,175.00),
(100,36,1,1,'Pizza Supreme','9','Quickmelt',145.00,1,145.00),
(101,36,6,26,'Yummy Hotdog','9','Mozzarella',155.00,1,155.00),
(103,38,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(104,39,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(105,40,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(106,41,27,95,'Aloha','9','Quickmelt',150.00,1,150.00),
(107,43,26,91,'Hawaiian','9','Quickmelt',145.00,1,145.00),
(108,44,3,9,'Cookies N Cheese','9','Quickmelt',110.00,1,110.00),
(109,45,29,103,'All Pepperoni','9','Quickmelt',200.00,1,200.00),
(110,46,8,33,'Chocomallow','9','Quickmelt',170.00,1,170.00),
(111,46,8,34,'Chocomallow','9','Mozzarella',190.00,1,190.00),
(112,47,12,51,'Chogburizo','9','Quickmelt',175.00,1,175.00);

-- ============================================================
-- STEP 7: Reset AUTO_INCREMENTs
-- ============================================================
ALTER TABLE `orders`      AUTO_INCREMENT = 48;
ALTER TABLE `order_items` AUTO_INCREMENT = 113;

-- ============================================================
-- STEP 8: Recreate views (updated for new column locations)
-- ============================================================

-- v_pizzas_full: pizzas + category name + comma-joined ingredients
CREATE VIEW v_pizzas_full AS
SELECT
    p.pizza_id,
    p.pizza_name,
    p.category_id,
    c.category_name AS category,
    p.image_path,
    p.stock,
    COALESCE(
        (SELECT GROUP_CONCAT(i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ')
         FROM pizza_ingredients pi
         JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
         WHERE pi.pizza_id = p.pizza_id),
        ''
    ) AS ingredients
FROM pizzas p
JOIN categories c ON p.category_id = c.category_id;

-- v_orders_full: orders with branch info (total_amount now a real column)
CREATE VIEW v_orders_full AS
SELECT
    o.*,
    b.branch_name,
    b.location AS branch_location
FROM orders o
LEFT JOIN branches b ON o.branch_id = b.branch_id;

-- v_order_items_full: items with pizza name, size, cheese, price, total
-- (all now real columns — view just adds pizza/variant lookup as bonus)
CREATE VIEW v_order_items_full AS
SELECT
    oi.item_id,
    oi.order_id,
    oi.pizza_id,
    oi.variant_id,
    oi.pizza_name,
    oi.size,
    oi.cheese,
    oi.price,
    oi.quantity,
    oi.total
FROM order_items oi;

-- ============================================================
-- Done. Open phpMyAdmin → orders table → you will see
-- customer_name, mobile_number, email, total_amount directly.
-- ============================================================
