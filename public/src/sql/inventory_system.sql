-- ============================================
-- NORMALIZED DATABASE SCHEMA
-- Matches your existing category structure
-- ============================================

-- 1. Account Table (Already normalized)
CREATE TABLE IF NOT EXISTS `account` (
  `account_ID` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `contact_number` VARCHAR(50) DEFAULT NULL,
  `role` ENUM('staff','admin','super_admin') NOT NULL DEFAULT 'staff',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`account_ID`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Product Categories Table (Matches your exact categories)
CREATE TABLE IF NOT EXISTS `product_categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL UNIQUE,
  `category_group` VARCHAR(100) NOT NULL,
  `display_order` INT(11) DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  INDEX `idx_category_group` (`category_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert your predefined categories in exact order
INSERT INTO `product_categories` (`category_name`, `category_group`, `display_order`) VALUES
-- Food & Beverages
('Softdrinks/Juice/Water', 'Food & Beverages', 1),
('Chips/Cookies/Candies', 'Food & Beverages', 2),
('Instant Noodles/Rice/Canned Goods', 'Food & Beverages', 3),
('Spices/Condiments', 'Food & Beverages', 4),
-- Household & Personal Care
('Soap/Shampoo/Toothpaste', 'Household & Personal Care', 5),
('Detergent/Cleaning Supplies', 'Household & Personal Care', 6),
('Toilet Paper/Sanitary Pad', 'Household & Personal Care', 7),
-- Miscellaneous/Others
('Cigarettes/Alcohol', 'Miscellaneous/Others', 8),
('Stationary/Batteries/Small Toys', 'Miscellaneous/Others', 9),
('Frozen Items or Perishables', 'Miscellaneous/Others', 10);

-- 3. Inventory Table (Improved with foreign keys)
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `productID` VARCHAR(100) NOT NULL UNIQUE,
  `productName` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `expiryDate` DATE DEFAULT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `image` LONGTEXT DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_productID` (`productID`),
  KEY `idx_productName` (`productName`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_quantity` (`quantity`),
  KEY `idx_expiry` (`expiryDate`),
  CONSTRAINT `fk_inventory_category` FOREIGN KEY (`category_id`) 
    REFERENCES `product_categories` (`category_id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Customers Table (Separate customer data)
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `contact_number` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `total_purchases` DECIMAL(10,2) DEFAULT 0.00,
  `last_purchase_date` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  INDEX `idx_customer_name` (`customer_name`),
  INDEX `idx_contact` (`contact_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Sales Transactions Table (Header table)
CREATE TABLE IF NOT EXISTS `sales_transactions` (
  `transaction_id` VARCHAR(20) NOT NULL,
  `customer_id` INT(11) DEFAULT NULL,
  `customer_name` VARCHAR(100) DEFAULT NULL,
  `date_time` DATETIME NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `served_by` INT(11) DEFAULT NULL,
  `status` ENUM('completed','cancelled','refunded') DEFAULT 'completed',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `idx_date_time` (`date_time`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_served_by` (`served_by`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) 
    REFERENCES `customers` (`customer_id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_sales_account` FOREIGN KEY (`served_by`) 
    REFERENCES `account` (`account_ID`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Sales Items Table (Detail table - replaces salesreport)
CREATE TABLE IF NOT EXISTS `sales_items` (
  `sale_item_id` INT(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` VARCHAR(20) NOT NULL,
  `product_id` VARCHAR(100) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `category_name` VARCHAR(100) DEFAULT NULL,
  `quantity_sold` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`sale_item_id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_category` (`category_name`),
  CONSTRAINT `fk_sale_transaction` FOREIGN KEY (`transaction_id`) 
    REFERENCES `sales_transactions` (`transaction_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sale_product` FOREIGN KEY (`product_id`) 
    REFERENCES `inventory` (`productID`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- MIGRATION SCRIPT (If you have existing data)
-- ============================================

-- Step 1: Migrate category data from old inventory
-- Update inventory records to use category_id based on exact name matches
UPDATE `inventory` i
JOIN `product_categories` pc ON i.`category` = pc.`category_name`
SET i.`category_id` = pc.`category_id`
WHERE i.`category` IS NOT NULL;

-- Handle old category names that need mapping (if any)
-- Beverage -> Softdrinks/Juice/Water
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Softdrinks/Juice/Water')
WHERE `category` = 'Beverage';

-- Snacks -> Chips/Cookies/Candies
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Chips/Cookies/Candies')
WHERE `category` = 'Snacks';

-- Groceries -> Instant Noodles/Rice/Canned Goods
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Instant Noodles/Rice/Canned Goods')
WHERE `category` = 'Groceries';

-- Spices -> Spices/Condiments
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Spices/Condiments')
WHERE `category` = 'Spices';

-- Personal Care -> Soap/Shampoo/Toothpaste
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Soap/Shampoo/Toothpaste')
WHERE `category` = 'Personal Care';

-- Cleaning Supplies -> Detergent/Cleaning Supplies
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Detergent/Cleaning Supplies')
WHERE `category` = 'Cleaning Supplies';

-- Toilet Sanitaries -> Toilet Paper/Sanitary Pad
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Toilet Paper/Sanitary Pad')
WHERE `category` = 'Toilet Sanitaries';

-- Cigar/Alcohol -> Cigarettes/Alcohol
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Cigarettes/Alcohol')
WHERE `category` = 'Cigar/Alcohol';

-- Stationaries -> Stationary/Batteries/Small Toys
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Stationary/Batteries/Small Toys')
WHERE `category` = 'Stationaries';

-- Frozen Items -> Frozen Items or Perishables
UPDATE `inventory` 
SET `category_id` = (SELECT category_id FROM product_categories WHERE category_name = 'Frozen Items or Perishables')
WHERE `category` = 'Frozen Items';

-- Step 2: Migrate sales data (if salesreport exists)
INSERT INTO `sales_transactions` (`transaction_id`, `customer_name`, `date_time`, `total_amount`, `payment_method`)
SELECT DISTINCT 
  `transaction_ID`,
  `customer_name`,
  `date_time`,
  `order_value`,
  `payment_method`
FROM `salesreport`
WHERE `transaction_ID` IS NOT NULL
ON DUPLICATE KEY UPDATE 
  `customer_name` = VALUES(`customer_name`),
  `total_amount` = VALUES(`total_amount`);

-- Step 3: Migrate individual sale items
INSERT INTO `sales_items` (`transaction_id`, `product_id`, `product_name`, `category_name`, `quantity_sold`, `unit_price`, `subtotal`)
SELECT 
  sr.`transaction_ID`,
  COALESCE(i.`productID`, CONCAT('LEGACY-', sr.`products`)),
  sr.`products`,
  pc.`category_name`,
  sr.`quantity_sold`,
  COALESCE(i.`price`, 0),
  (sr.`quantity_sold` * COALESCE(i.`price`, 0))
FROM `salesreport` sr
LEFT JOIN `inventory` i ON LOWER(TRIM(i.`productName`)) = LOWER(TRIM(sr.`products`))
LEFT JOIN `product_categories` pc ON i.`category_id` = pc.`category_id`
WHERE sr.`transaction_ID` IS NOT NULL;

-- ============================================
-- VIEWS FOR BACKWARD COMPATIBILITY
-- ============================================

-- View that mimics old salesreport structure
CREATE OR REPLACE VIEW `salesreport_view` AS
SELECT 
  si.`sale_item_id`,
  st.`transaction_id` AS `transaction_ID`,
  st.`date_time`,
  si.`product_name` AS `products`,
  si.`quantity_sold`,
  st.`total_amount` AS `order_value`,
  st.`customer_name`,
  st.`payment_method`
FROM `sales_items` si
JOIN `sales_transactions` st ON si.`transaction_id` = st.`transaction_id`
WHERE st.`status` = 'completed';

-- View for inventory with category details
CREATE OR REPLACE VIEW `inventory_with_categories` AS
SELECT 
  i.`id`,
  i.`productID`,
  i.`productName`,
  i.`quantity`,
  i.`unit`,
  i.`price`,
  i.`expiryDate`,
  pc.`category_id`,
  pc.`category_name`,
  pc.`category_group`,
  i.`image`,
  i.`status`,
  i.`created_at`,
  i.`updated_at`,
  CASE 
    WHEN i.`quantity` < 5 AND i.`quantity` > 0 THEN 'Low Stock'
    WHEN i.`quantity` = 0 THEN 'Out of Stock'
    ELSE 'Available'
  END AS `stock_status`
FROM `inventory` i
LEFT JOIN `product_categories` pc ON i.`category_id` = pc.`category_id`;

-- View for sales summary by category
CREATE OR REPLACE VIEW `sales_by_category` AS
SELECT 
  pc.`category_group`,
  pc.`category_name`,
  COUNT(DISTINCT si.`transaction_id`) AS `total_transactions`,
  SUM(si.`quantity_sold`) AS `total_quantity_sold`,
  SUM(si.`subtotal`) AS `total_revenue`
FROM `sales_items` si
JOIN `inventory` i ON si.`product_id` = i.`productID`
JOIN `product_categories` pc ON i.`category_id` = pc.`category_id`
JOIN `sales_transactions` st ON si.`transaction_id` = st.`transaction_id`
WHERE st.`status` = 'completed'
GROUP BY pc.`category_group`, pc.`category_name`
ORDER BY pc.`display_order`;

-- ============================================
-- TRIGGERS FOR DATA INTEGRITY
-- ============================================

-- Trigger: Update inventory quantity after sale
DELIMITER $$
CREATE TRIGGER `after_sale_insert` 
AFTER INSERT ON `sales_items`
FOR EACH ROW
BEGIN
  UPDATE `inventory` 
  SET `quantity` = `quantity` - NEW.`quantity_sold`
  WHERE `productID` = NEW.`product_id`;
END$$

-- Trigger: Update customer stats after purchase
CREATE TRIGGER `after_transaction_insert` 
AFTER INSERT ON `sales_transactions`
FOR EACH ROW
BEGIN
  IF NEW.`customer_id` IS NOT NULL THEN
    UPDATE `customers`
    SET 
      `total_purchases` = `total_purchases` + NEW.`total_amount`,
      `last_purchase_date` = NEW.`date_time`
    WHERE `customer_id` = NEW.`customer_id`;
  END IF;
END$$

-- Trigger: Calculate subtotal automatically
CREATE TRIGGER `before_sale_item_insert` 
BEFORE INSERT ON `sales_items`
FOR EACH ROW
BEGIN
  SET NEW.`subtotal` = NEW.`quantity_sold` * NEW.`unit_price`;
END$$

DELIMITER ;

-- ============================================
-- USEFUL QUERIES
-- ============================================

-- Get low stock items by category
-- SELECT * FROM inventory_with_categories WHERE stock_status = 'Low Stock' ORDER BY category_group, category_name;

-- Get top selling products
-- SELECT product_name, SUM(quantity_sold) as total_sold, SUM(subtotal) as revenue 
-- FROM sales_items GROUP BY product_name ORDER BY total_sold DESC LIMIT 10;

-- Get sales summary by category group
-- SELECT category_group, SUM(total_revenue) as revenue 
-- FROM sales_by_category GROUP BY category_group;