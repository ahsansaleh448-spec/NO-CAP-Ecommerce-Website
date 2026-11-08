-- ========================================================
-- NO CAP E-COMMERCE DATABASE SCHEMA
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `nocap_store` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nocap_store`;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Products Table
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`category`),
  INDEX (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Shopping Cart Table
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Orders Table
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(80) NOT NULL,
  `payment_method` VARCHAR(40) NOT NULL DEFAULT 'Cash on Delivery',
  `shipping` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Order Items Table
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- Default administrator account:
-- Email: admin@nocap.com
-- Password: admin123
-- ========================================================
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('NO CAP Administrator', 'admin@nocap.com', '+923000000000', '$2y$10$eevEDlIZTmcezpFzyzHNNOtaUCGMmP9ugzaTeTEtjBafpzEXC0BjO', 'admin');

-- Sample store products
INSERT INTO `products` (`name`, `category`, `description`, `price`, `stock`) VALUES
('Oversized Heavyweight Black Hoodie', 'Hoodies', 'Heavyweight 380 GSM combed cotton fleece, drop-shoulder silhouette, brushed interior for ultra warmth and comfort. Premium relaxed fit.', 3499.00, 30),
('Vintage Cream Zip-Up Hoodie', 'Hoodies', 'Full zip hoodie crafted in neutral cream with dual kangaroo pockets, ribbed cuffs, and custom metal zipper. Ideal for contemporary layering.', 3999.00, 22),
('Streetwear Graphic Backprint Tee', 'Hoodies', '100% premium ring-spun cotton tee featuring an edgy high-density back artwork. Pre-shrunk, boxy streetwear cut with reinforced collar.', 1599.00, 50),
('Urban Chunky Runner Sneakers', 'Sneakers', 'Lightweight athletic sneakers featuring an ergonomic shock-absorbing sole, breathable mesh upper, and all-day responsive cushioning.', 7999.00, 15),
('Minimalist All-White Leather Kicks', 'Sneakers', 'Sleek, low-top white sneakers made with easy-clean synthetic leather. Versatile silhouette suitable for casual and smart casual wear.', 6499.00, 18),
('Heavy-Duty Canvas Everyday Tote', 'Accessories', 'Durable 16oz cotton canvas tote bag with reinforced handles, zip closure, and interior pocket. Comfortably holds a 15-inch laptop and essentials.', 1299.00, 40),
('Embroidered Streetwear Snapback Cap', 'Accessories', 'Classic structured snapback cap with flat brim, breathable eyelets, and premium front 3D embroidery. One size fits all with adjustable strap.', 999.00, 60),
('Stainless Steel Cuban Link Chain', 'Accessories', 'High-polish 316L medical-grade stainless steel chain. Tarnish-free, hypoallergenic, and resistant to water, heat, and sweat.', 1799.00, 25),
('Polarized Classic Aviator Sunglasses', 'Accessories', 'Timeless metal frame aviators equipped with UV400 polarized scratch-resistant lenses. Includes protective case and micro-fiber cleaning cloth.', 2199.00, 20),
('Wireless Noise-Cancelling Earbuds Pro', 'Tech', 'Bluetooth 5.3 earbuds featuring active environmental noise reduction, low-latency gaming mode, touch controls, and 28-hour total battery life.', 4999.00, 35),
('OLED Smart Fitness Tracker', 'Tech', 'Features 24/7 heart rate monitoring, step and distance tracker, sleep analysis, and smart notifications. Water resistant with 10-day battery life.', 3599.00, 28),
('Ultra-Slim 20,000mAh Fast Power Bank', 'Tech', 'Dual USB-A and USB-C Power Delivery 22.5W fast-charging ports. Capable of charging modern smartphones up to 4-5 times on a single charge.', 3299.00, 14),
('Mechanical Compact RGB Gaming Keyboard', 'Tech', '60% ultra-compact mechanical keyboard with hot-swappable tactile switches, dynamic RGB backlighting, and braided detachable Type-C cable.', 5499.00, 12);
