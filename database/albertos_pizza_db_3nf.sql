-- ============================================================
-- Alberto's Pizza DB — STRICT 3NF Schema
-- Database: albertos_pizza_db_3nf
-- ============================================================
-- All 3NF violations eliminated:
--   1. pizzas.category_id → categories  (FK, no string dup)
--   2. pizzas: ingredients moved to ingredients + pizza_ingredients
--   3. orders: removed customer_name, mobile_number, email, total_amount
--   4. order_items: removed pizza_name, size, cheese, price, total
--      (now just variant_id + quantity — the rest is computed)
--   5. New: order_contacts (guest contact info, FK to orders)
--   6. New: ingredients + pizza_ingredients (junction)
--   7. UNIQUE(pizza_id, size, cheese) on pizza_variants (no dupes)
--   8. UNIQUE(pizza_name) on pizzas (no dupes)
--   9. Backward-compat views: v_pizzas_full, v_orders_full, v_order_items_full
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: branches
-- ============================================================
CREATE TABLE `branches` (
  `branch_id`   int          NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) DEFAULT NULL,
  `location`    varchar(255) DEFAULT NULL,
  PRIMARY KEY (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE `categories` (
  `category_id`   int          NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: users
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
-- TABLE: pizzas (no ingredients column — moved to junction)
-- ============================================================
CREATE TABLE `pizzas` (
  `pizza_id`    int          NOT NULL AUTO_INCREMENT,
  `pizza_name`  varchar(100) NOT NULL,
  `category_id` int          NOT NULL,
  `image_path`  varchar(255) NOT NULL,
  `stock`       int          DEFAULT 0,
  PRIMARY KEY (`pizza_id`),
  UNIQUE KEY `uq_pizza_name` (`pizza_name`),
  KEY `fk_pizza_category` (`category_id`),
  CONSTRAINT `fk_pizza_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pizza_variants (UNIQUE constraint added)
-- ============================================================
CREATE TABLE `pizza_variants` (
  `variant_id` int           NOT NULL AUTO_INCREMENT,
  `pizza_id`   int           NOT NULL,
  `size`       int           NOT NULL,
  `cheese`     varchar(50)   NOT NULL,
  `price`      decimal(6,2)  NOT NULL,
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `uq_variant` (`pizza_id`, `size`, `cheese`),
  CONSTRAINT `fk_variant_pizza`
    FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: ingredients (1NF fix — atomic ingredient names)
-- ============================================================
CREATE TABLE `ingredients` (
  `ingredient_id`   int          NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(100) NOT NULL,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `uq_ingredient_name` (`ingredient_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pizza_ingredients (M:N junction)
-- ============================================================
CREATE TABLE `pizza_ingredients` (
  `pizza_id`      int NOT NULL,
  `ingredient_id` int NOT NULL,
  PRIMARY KEY (`pizza_id`, `ingredient_id`),
  KEY `fk_pi_ingredient` (`ingredient_id`),
  CONSTRAINT `fk_pi_pizza`
    FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`pizza_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_pi_ingredient`
    FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: orders (no customer info, no total — fully 3NF)
-- ============================================================
CREATE TABLE `orders` (
  `order_id`       int          NOT NULL AUTO_INCREMENT,
  `user_id`        int          DEFAULT NULL,
  `branch_id`      int          DEFAULT NULL,
  `address`        text,
  `order_type`     varchar(50)  DEFAULT NULL,
  `payment_method` varchar(50)  DEFAULT NULL,
  `status`         varchar(50)  DEFAULT 'pending',
  `created_at`     timestamp    NULL DEFAULT CURRENT_TIMESTAMP,
  `driver_id`      int          DEFAULT NULL,
  `updated_at`     timestamp    NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
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
-- TABLE: order_contacts (guest order contact info)
-- ============================================================
CREATE TABLE `order_contacts` (
  `order_id`      int          NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `mobile_number` varchar(20)  NOT NULL,
  `email`         varchar(255) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  CONSTRAINT `fk_contact_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: order_items (only variant_id + quantity)
-- price/total/snapshot fields removed — derived from pizza_variants
-- ============================================================
CREATE TABLE `order_items` (
  `item_id`    int NOT NULL AUTO_INCREMENT,
  `order_id`   int NOT NULL,
  `variant_id` int NOT NULL,
  `quantity`   int NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `fk_item_order`   (`order_id`),
  KEY `fk_item_variant` (`variant_id`),
  CONSTRAINT `fk_item_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_item_variant`
    FOREIGN KEY (`variant_id`) REFERENCES `pizza_variants` (`variant_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VIEWS — backward compatibility for legacy queries
-- ============================================================

-- v_pizzas_full: pizzas + category name + comma-joined ingredients
CREATE VIEW v_pizzas_full AS
SELECT
    p.pizza_id, p.pizza_name, p.category_id, c.category_name AS category,
    p.image_path, p.stock,
    COALESCE(
        (SELECT GROUP_CONCAT(i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ')
         FROM pizza_ingredients pi
         JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
         WHERE pi.pizza_id = p.pizza_id),
        ''
    ) AS ingredients
FROM pizzas p
JOIN categories c ON p.category_id = c.category_id;

-- v_orders_full: orders + computed customer info + computed total
CREATE VIEW v_orders_full AS
SELECT
    o.order_id, o.user_id, o.branch_id, o.address,
    o.order_type, o.payment_method, o.status,
    o.created_at, o.driver_id, o.updated_at,
    COALESCE(u.username,      oc.customer_name) AS customer_name,
    COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
    COALESCE(u.email,         oc.email)         AS email,
    COALESCE(
        (SELECT SUM(oi.quantity * pv.price)
         FROM order_items oi
         JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
         WHERE oi.order_id = o.order_id),
        0
    ) AS total_amount
FROM orders o
LEFT JOIN users u           ON o.user_id  = u.user_id
LEFT JOIN order_contacts oc ON o.order_id = oc.order_id;

-- v_order_items_full: items + variant info + computed total
CREATE VIEW v_order_items_full AS
SELECT
    oi.item_id, oi.order_id, oi.variant_id, oi.quantity,
    pv.pizza_id, p.pizza_name,
    pv.size, pv.cheese, pv.price,
    (oi.quantity * pv.price) AS total
FROM order_items oi
JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
JOIN pizzas p          ON pv.pizza_id   = p.pizza_id;

-- ============================================================
-- DATA inserts are populated via migrate_strict_3nf.php
-- (Original CSV-style INSERTs would not produce the proper
-- normalized junction rows; the migration script parses the
-- ingredient strings and resolves duplicates programmatically.)
-- ============================================================
