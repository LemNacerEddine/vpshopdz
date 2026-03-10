-- AgroYousfi Database Schema for MySQL
-- Version: 1.0

CREATE DATABASE IF NOT EXISTS agroyousfi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agroyousfi;

-- Users Table
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       user_id VARCHAR(50) UNIQUE NOT NULL,
                       email VARCHAR(255) UNIQUE,
                       phone VARCHAR(20),
                       password_hash VARCHAR(255),
                       name VARCHAR(255),
                       role ENUM('customer', 'admin') DEFAULT 'customer',
                       avatar VARCHAR(500),
                       google_id VARCHAR(255),
                       reset_token VARCHAR(255),
                       reset_token_expires DATETIME,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       INDEX idx_email (email),
                       INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- Sessions Table
CREATE TABLE sessions (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          session_id VARCHAR(100) UNIQUE NOT NULL,
                          user_id VARCHAR(50) NOT NULL,
                          expires_at DATETIME NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          INDEX idx_session_id (session_id),
                          INDEX idx_user_id (user_id),
                          FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE categories (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            category_id VARCHAR(50) UNIQUE NOT NULL,
                            name_ar VARCHAR(255) NOT NULL,
                            name_fr VARCHAR(255),
                            name_en VARCHAR(255),
                            description_ar TEXT,
                            description_fr TEXT,
                            description_en TEXT,
                            icon VARCHAR(50) DEFAULT 'Leaf',
                            image VARCHAR(500),
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_category_id (category_id)
) ENGINE=InnoDB;

-- Products Table
CREATE TABLE products (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          product_id VARCHAR(50) UNIQUE NOT NULL,
                          name_ar VARCHAR(255) NOT NULL,
                          name_fr VARCHAR(255),
                          name_en VARCHAR(255),
                          description_ar TEXT,
                          description_fr TEXT,
                          description_en TEXT,
                          price DECIMAL(10,2) NOT NULL,
                          old_price DECIMAL(10,2),
                          discount_percent INT DEFAULT 0,
                          discount_start DATETIME,
                          discount_end DATETIME,
                          stock INT DEFAULT 0,
                          unit VARCHAR(20) DEFAULT 'piece',
                          category_id VARCHAR(50),
                          featured BOOLEAN DEFAULT FALSE,
                          rating DECIMAL(2,1) DEFAULT 0,
                          reviews_count INT DEFAULT 0,
                          sold_count INT DEFAULT 0,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          INDEX idx_product_id (product_id),
                          INDEX idx_category_id (category_id),
                          INDEX idx_featured (featured),
                          FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Product Images Table
CREATE TABLE product_images (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                product_id VARCHAR(50) NOT NULL,
                                image_url VARCHAR(500) NOT NULL,
                                sort_order INT DEFAULT 0,
                                FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Orders Table
CREATE TABLE orders (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        order_id VARCHAR(50) UNIQUE NOT NULL,
                        user_id VARCHAR(50),
                        customer_name VARCHAR(255) NOT NULL,
                        customer_phone VARCHAR(20) NOT NULL,
                        customer_email VARCHAR(255),
                        shipping_address TEXT NOT NULL,
                        wilaya VARCHAR(100),
                        total DECIMAL(10,2) NOT NULL,
                        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
                        payment_method VARCHAR(50) DEFAULT 'cod',
                        notes TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_order_id (order_id),
                        INDEX idx_user_id (user_id),
                        INDEX idx_status (status),
                        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Order Items Table
CREATE TABLE order_items (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             order_id VARCHAR(50) NOT NULL,
                             product_id VARCHAR(50) NOT NULL,
                             product_name VARCHAR(255) NOT NULL,
                             product_image VARCHAR(500),
                             quantity INT NOT NULL,
                             price DECIMAL(10,2) NOT NULL,
                             FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Reviews Table
CREATE TABLE reviews (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         review_id VARCHAR(50) UNIQUE NOT NULL,
                         product_id VARCHAR(50) NOT NULL,
                         user_id VARCHAR(50) NOT NULL,
                         user_name VARCHAR(255),
                         rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                         comment TEXT,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         INDEX idx_product_id (product_id),
                         FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
                         FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Wishlists Table
CREATE TABLE wishlists (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           user_id VARCHAR(50) NOT NULL,
                           product_id VARCHAR(50) NOT NULL,
                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                           UNIQUE KEY unique_wishlist (user_id, product_id),
                           FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                           FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Browsing History Table
CREATE TABLE browsing_history (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  user_id VARCHAR(50),
                                  browser_id VARCHAR(50),
                                  product_id VARCHAR(50) NOT NULL,
                                  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                  INDEX idx_user_id (user_id),
                                  INDEX idx_browser_id (browser_id),
                                  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Carts Table
CREATE TABLE carts (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       user_id VARCHAR(50),
                       browser_id VARCHAR(50),
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       INDEX idx_user_id (user_id),
                       INDEX idx_browser_id (browser_id),
                       UNIQUE KEY unique_user_cart (user_id),
                       FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cart Items Table
CREATE TABLE cart_items (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            cart_id INT NOT NULL,
                            product_id VARCHAR(50) NOT NULL,
                            quantity INT NOT NULL DEFAULT 1,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            UNIQUE KEY unique_cart_product (cart_id, product_id),
                            FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
                            FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Addresses Table
CREATE TABLE addresses (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           address_id VARCHAR(50) UNIQUE NOT NULL,
                           user_id VARCHAR(50) NOT NULL,
                           label VARCHAR(100),
                           full_name VARCHAR(255) NOT NULL,
                           phone VARCHAR(20) NOT NULL,
                           wilaya VARCHAR(100) NOT NULL,
                           commune VARCHAR(100),
                           address_line TEXT NOT NULL,
                           is_default BOOLEAN DEFAULT FALSE,
                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                           FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Shipping: Add product shipping fields
ALTER TABLE products
  ADD COLUMN shipping_type ENUM('standard','free','fixed_price') NOT NULL DEFAULT 'standard',
  ADD COLUMN fixed_shipping_price DECIMAL(10,2) NULL,
  ADD COLUMN weight DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN length DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN width DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN height DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN is_fragile BOOLEAN DEFAULT FALSE;

-- Shipping: Add commune and shipping fields to orders
ALTER TABLE orders
  ADD COLUMN commune VARCHAR(100) AFTER wilaya,
  ADD COLUMN subtotal DECIMAL(10,2) AFTER commune,
  ADD COLUMN shipping_cost DECIMAL(10,2) DEFAULT 0 AFTER subtotal;

-- Shipping Companies Table
CREATE TABLE shipping_companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id VARCHAR(50) UNIQUE NOT NULL,
  name_ar VARCHAR(255) NOT NULL,
  name_fr VARCHAR(255),
  name_en VARCHAR(255),
  logo VARCHAR(500),
  phone VARCHAR(20),
  email VARCHAR(255),
  website VARCHAR(500),
  tracking_url_template VARCHAR(500),
  volumetric_divisor INT DEFAULT 5000,
  included_weight DECIMAL(10,2) DEFAULT 5.00,
  additional_price_per_kg DECIMAL(10,2) DEFAULT 0.00,
  is_active BOOLEAN DEFAULT TRUE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Shipping Rates Table
CREATE TABLE shipping_rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rate_id VARCHAR(50) UNIQUE NOT NULL,
  company_id VARCHAR(50) NOT NULL,
  wilaya VARCHAR(100) NOT NULL,
  commune VARCHAR(100) NULL COMMENT 'NULL = applies to all communes in wilaya',
  shipping_type ENUM('home','office') DEFAULT 'home',
  base_price DECIMAL(10,2) NOT NULL,
  min_delivery_days INT DEFAULT 1,
  max_delivery_days INT DEFAULT 3,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_company_wilaya_commune_type (company_id, wilaya, commune, shipping_type),
  FOREIGN KEY (company_id) REFERENCES shipping_companies(company_id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Shipping Rules Table (free shipping conditions)
CREATE TABLE shipping_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rule_id VARCHAR(50) UNIQUE NOT NULL,
  rule_name VARCHAR(255) NOT NULL,
  rule_type ENUM('min_cart_total','min_cart_items','free_for_category','free_for_product') NOT NULL,
  condition_value VARCHAR(255) NOT NULL,
  shipping_cost_override DECIMAL(10,2) DEFAULT 0.00,
  is_active BOOLEAN DEFAULT TRUE,
  start_date DATETIME NULL,
  end_date DATETIME NULL,
  priority INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Order Shipping Table
CREATE TABLE order_shipping (
  id INT AUTO_INCREMENT PRIMARY KEY,
  shipping_id VARCHAR(50) UNIQUE NOT NULL,
  order_id VARCHAR(50) NOT NULL,
  company_id VARCHAR(50) NOT NULL,
  shipping_type ENUM('home','office') DEFAULT 'home',
  total_weight DECIMAL(10,2),
  billable_weight DECIMAL(10,2),
  shipping_cost DECIMAL(10,2),
  tracking_number VARCHAR(100) NULL,
  status ENUM('pending','confirmed','in_transit','delivered','returned') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seed: Default shipping companies
INSERT INTO shipping_companies (company_id, name_ar, name_fr, name_en, phone, is_active, sort_order) VALUES
  ('ship_co_yalidine', 'يالدين إكسبريس', 'Yalidine Express', 'Yalidine Express', '0560 00 00 00', 1, 1),
  ('ship_co_zr', 'زد آر إكسبريس', 'ZR Express', 'ZR Express', '0550 00 00 00', 1, 2);

-- Seed: Default shipping rule (free shipping over 50000 DZD)
INSERT INTO shipping_rules (rule_id, rule_name, rule_type, condition_value, is_active, priority) VALUES
  ('ship_rule_free50k', 'شحن مجاني للطلبات فوق 50000 دج', 'min_cart_total', '50000', 1, 10);

-- في phpMyAdmin أو MySQL
-- ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email;

CREATE INDEX idx_google_id ON users(google_id);

-- Abandoned Checkouts Table
CREATE TABLE abandoned_checkouts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  checkout_id VARCHAR(50) UNIQUE NOT NULL,
  user_id VARCHAR(50),
  browser_id VARCHAR(50),
  customer_name VARCHAR(255),
  customer_phone VARCHAR(20),
  shipping_address TEXT,
  wilaya VARCHAR(100),
  commune VARCHAR(100),
  items JSON,
  cart_total DECIMAL(10,2) DEFAULT 0,
  item_count INT DEFAULT 0,
  recovered BOOLEAN DEFAULT FALSE,
  recovered_order_id VARCHAR(50),
  notified_at DATETIME,
  send_status ENUM('pending','processing','sent','failed','skipped') DEFAULT 'pending',
  send_attempts INT DEFAULT 0,
  last_attempt_at DATETIME,
  last_error TEXT,
  next_retry_at DATETIME,
  processing_at DATETIME,
  whatsapp_message_id VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_checkout_id (checkout_id),
  INDEX idx_user_id (user_id),
  INDEX idx_browser_id (browser_id),
  INDEX idx_recovered (recovered),
  INDEX idx_customer_phone (customer_phone),
  INDEX idx_created_at (created_at),
  INDEX idx_send_status (send_status)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Store Settings Table (key-value)
CREATE TABLE store_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Default WhatsApp & Abandoned Checkout Settings
INSERT INTO store_settings (setting_key, setting_value) VALUES
  ('whatsapp_enabled', 'false'),
  ('whatsapp_phone_number_id', ''),
  ('whatsapp_access_token', ''),
  ('whatsapp_auto_send', 'false'),
  ('whatsapp_delay_minutes', '30'),
  ('whatsapp_message_ar', 'مرحباً {name}! لاحظنا أنك لم تكمل طلبك في متجرنا. نقدم لك شحن مجاني على طلبك القادم! أكمل طلبك الآن: {link}'),
  ('whatsapp_message_fr', 'Bonjour {name}! Vous n''avez pas finalisé votre commande. Livraison gratuite sur votre prochaine commande! Commandez maintenant: {link}'),
  ('whatsapp_message_en', 'Hi {name}! You didn''t complete your order. We''re offering FREE shipping! Complete your order now: {link}'),
  ('store_url', ''),
  ('fb_pixel_id', ''),
  ('whatsapp_mode', 'green_api'),
  ('green_api_instance_id', ''),
  ('green_api_token', ''),
  ('whatsapp_rate_limit_seconds', '120'),
  ('whatsapp_max_retries', '5'),
  ('whatsapp_max_per_run', '10'),
  ('whatsapp_phone_cooldown_minutes', '1440'),
  ('whatsapp_send_window_start', '9'),
  ('whatsapp_send_window_end', '21');

-- Insert Default Admin User (password: admin123)
INSERT INTO users (user_id, email, password_hash, name, role) VALUES
    ('admin_001', 'admin@agroyousfi.dz', '$2y$10$MGAynS7W0NeIZBr2VjPwHebnvLuFZSPKOEcYftut3g3/GZLP8FxsC', 'مدير النظام', 'admin');

-- Insert Sample Categories
INSERT INTO categories (category_id, name_ar, name_fr, name_en, icon) VALUES
                                                                          ('cat_seeds', 'البذور', 'Semences', 'Seeds', 'Leaf'),
                                                                          ('cat_fertilizers', 'الأسمدة', 'Engrais', 'Fertilizers', 'Droplets'),
                                                                          ('cat_tools', 'أدوات الزراعة', 'Outils Agricoles', 'Farm Tools', 'Wrench'),
                                                                          ('cat_pesticides', 'المبيدات', 'Pesticides', 'Pesticides', 'Shield'),
                                                                          ('cat_irrigation', 'أنظمة الري', 'Systèmes d''Irrigation', 'Irrigation Systems', 'Droplet'),
                                                                          ('cat_greenhouses', 'البيوت البلاستيكية', 'Serres', 'Greenhouses', 'Home');

-- Insert Sample Products
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, price, old_price, stock, category_id, featured, unit, discount_percent, discount_start, discount_end, rating, reviews_count) VALUES
                                                                                                                                                                                                              ('prod_wheat01', 'بذور القمح الصلب', 'Graines de blé dur', 'Hard Wheat Seeds', 'بذور قمح صلب عالية الجودة للزراعة', 5000, NULL, 100, 'cat_seeds', 1, 'kg', 25, '2026-01-01', '2026-01-31', 4.8, 24),
                                                                                                                                                                                                              ('prod_tomato01', 'بذور الطماطم الهجينة', 'Semences de Tomates Hybrides', 'Hybrid Tomato Seeds', 'بذور طماطم هجينة عالية الإنتاجية', 1800, NULL, 200, 'cat_seeds', 1, 'pack', 0, NULL, NULL, 4.5, 18),
                                                                                                                                                                                                              ('prod_pepper01', 'بذور الفلفل الحلو', 'Semences de Poivron Doux', 'Sweet Pepper Seeds', 'بذور فلفل حلو متعددة الألوان', 1500, NULL, 180, 'cat_seeds', 0, 'pack', 0, NULL, NULL, 4.3, 12),
                                                                                                                                                                                                              ('prod_fert01', 'سماد NPK 20-20-20', 'Engrais NPK 20-20-20', 'NPK Fertilizer 20-20-20', 'سماد متوازن NPK لجميع أنواع المحاصيل', 3200, 3800, 100, 'cat_fertilizers', 1, 'kg', 16, '2026-01-01', '2026-02-28', 4.7, 32),
                                                                                                                                                                                                              ('prod_fert02', 'سماد عضوي طبيعي', 'Engrais Organique Naturel', 'Natural Organic Fertilizer', 'سماد عضوي 100% طبيعي', 2800, NULL, 80, 'cat_fertilizers', 0, 'kg', 0, NULL, NULL, 4.6, 15),
                                                                                                                                                                                                              ('prod_tool01', 'مجرفة يدوية احترافية', 'Bêche Manuelle Professionnelle', 'Professional Hand Shovel', 'مجرفة يدوية بمقبض خشبي متين', 2500, NULL, 50, 'cat_tools', 1, 'piece', 0, NULL, NULL, 4.4, 20),
                                                                                                                                                                                                              ('prod_tool02', 'مقص تقليم الأشجار', 'Sécateur d''Arbres', 'Tree Pruning Shears', 'مقص تقليم احترافي بشفرات فولاذية حادة', 3500, NULL, 35, 'cat_tools', 0, 'piece', 0, NULL, NULL, 4.2, 8),
                                                                                                                                                                                                              ('prod_pest01', 'مبيد حشري طبيعي', 'Insecticide Naturel', 'Natural Insecticide', 'مبيد حشري طبيعي وآمن للبيئة', 4200, NULL, 60, 'cat_pesticides', 1, 'liter', 0, NULL, NULL, 4.5, 14),
                                                                                                                                                                                                              ('prod_irrig01', 'نظام ري بالتنقيط', 'Système de Goutte à Goutte', 'Drip Irrigation System', 'نظام ري بالتنقيط كامل', 15000, 18000, 25, 'cat_irrigation', 1, 'kit', 17, '2026-01-01', '2026-02-15', 4.9, 28),
                                                                                                                                                                                                              ('prod_green01', 'بلاستيك بيوت زراعية', 'Plastique pour Serres', 'Greenhouse Plastic Sheet', 'بلاستيك عالي الجودة للبيوت الزراعية', 8500, NULL, 40, 'cat_greenhouses', 0, 'roll', 0, NULL, NULL, 4.6, 11);

-- Insert Product Images
INSERT INTO product_images (product_id, image_url, sort_order) VALUES
                                                                   ('prod_wheat01', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=500', 0),
                                                                   ('prod_tomato01', 'https://images.pexels.com/photos/2290074/pexels-photo-2290074.jpeg', 0),
                                                                   ('prod_tomato01', 'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg', 1),
                                                                   ('prod_pepper01', 'https://images.pexels.com/photos/594137/pexels-photo-594137.jpeg', 0),
                                                                   ('prod_pepper01', 'https://images.pexels.com/photos/128536/pexels-photo-128536.jpeg', 1),
                                                                   ('prod_fert01', 'https://images.pexels.com/photos/5529765/pexels-photo-5529765.jpeg', 0),
                                                                   ('prod_fert02', 'https://images.pexels.com/photos/7728082/pexels-photo-7728082.jpeg', 0),
                                                                   ('prod_tool01', 'https://images.pexels.com/photos/4856725/pexels-photo-4856725.jpeg', 0),
                                                                   ('prod_tool02', 'https://images.pexels.com/photos/12495821/pexels-photo-12495821.jpeg', 0),
                                                                   ('prod_pest01', 'https://images.pexels.com/photos/7457521/pexels-photo-7457521.jpeg', 0),
                                                                   ('prod_irrig01', 'https://images.pexels.com/photos/12495821/pexels-photo-12495821.jpeg', 0),
                                                                   ('prod_green01', 'https://images.pexels.com/photos/176169/pexels-photo-176169.jpeg', 0);

-- Migration: Move included_weight and additional_price_per_kg from shipping_rates to shipping_companies
ALTER TABLE shipping_companies
  ADD COLUMN IF NOT EXISTS included_weight DECIMAL(10,2) DEFAULT 5.00 AFTER volumetric_divisor,
  ADD COLUMN IF NOT EXISTS additional_price_per_kg DECIMAL(10,2) DEFAULT 0.00 AFTER included_weight;

ALTER TABLE shipping_rates
  DROP COLUMN IF EXISTS included_weight,
  DROP COLUMN IF EXISTS additional_price_per_kg;

-- Facebook Ads table
CREATE TABLE IF NOT EXISTS facebook_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id_local VARCHAR(50) NOT NULL UNIQUE,
    product_id VARCHAR(50) NOT NULL,
    campaign_id VARCHAR(100),
    campaign_name VARCHAR(255),
    adset_id VARCHAR(100),
    creative_id VARCHAR(100),
    fb_ad_id VARCHAR(100),
    status ENUM('draft','pending','active','paused','completed','error') DEFAULT 'draft',
    error_message TEXT,
    daily_budget_cents INT DEFAULT 0,
    duration_days INT DEFAULT 7,
    target_country VARCHAR(10) DEFAULT 'DZ',
    target_age_min INT DEFAULT 18,
    target_age_max INT DEFAULT 65,
    target_interests JSON,
    ad_text TEXT,
    ad_headline VARCHAR(255),
    landing_url VARCHAR(500),
    image_hash VARCHAR(255),
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    spend_cents INT DEFAULT 0,
    reach INT DEFAULT 0,
    metrics_updated_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    starts_at DATETIME,
    ends_at DATETIME,
    INDEX idx_fb_product_id (product_id),
    INDEX idx_fb_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Facebook Marketing API settings
INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES
    ('fb_app_id', ''),
    ('fb_app_secret', ''),
    ('fb_access_token', ''),
    ('fb_ad_account_id', ''),
    ('fb_page_id', '');

-- Migration: Add WhatsApp auto-send tracking columns to abandoned_checkouts
ALTER TABLE abandoned_checkouts
  ADD COLUMN send_status ENUM('pending','processing','sent','failed','skipped') DEFAULT 'pending' AFTER notified_at,
  ADD COLUMN send_attempts INT DEFAULT 0 AFTER send_status,
  ADD COLUMN last_attempt_at DATETIME NULL AFTER send_attempts,
  ADD COLUMN last_error TEXT NULL AFTER last_attempt_at,
  ADD COLUMN next_retry_at DATETIME NULL AFTER last_error,
  ADD COLUMN processing_at DATETIME NULL AFTER next_retry_at,
  ADD COLUMN whatsapp_message_id VARCHAR(100) NULL AFTER processing_at;

ALTER TABLE abandoned_checkouts
  ADD INDEX idx_send_status (send_status),
  ADD INDEX idx_next_retry_at (next_retry_at);

-- New WhatsApp auto-send settings
INSERT IGNORE INTO store_settings (setting_key, setting_value) VALUES
  ('whatsapp_max_retries', '5'),
  ('whatsapp_max_per_run', '10'),
  ('whatsapp_phone_cooldown_minutes', '1440'),
  ('whatsapp_send_window_start', '9'),
  ('whatsapp_send_window_end', '21'),
  ('offer_discount_enabled', 'false'),
  ('offer_discount_type', 'percentage'),
  ('offer_discount_value', '10'),
  ('offer_free_shipping', 'false');

-- Fix existing checkouts with NULL send_status
UPDATE abandoned_checkouts SET send_status = 'pending' WHERE send_status IS NULL;

-- Order sequence counter (atomic increment, resets yearly)
CREATE TABLE IF NOT EXISTS order_sequence (
    year SMALLINT UNSIGNED PRIMARY KEY,
    last_number INT UNSIGNED NOT NULL DEFAULT 0
);

-- Migration: Add discount_amount column to orders table
ALTER TABLE orders
  ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER shipping_cost;

-- Migration: Add discount_percentage column to orders table
ALTER TABLE orders
  ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0 AFTER discount_amount;
