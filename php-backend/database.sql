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

-- Insert Default Admin User (password: admin123)
INSERT INTO users (user_id, email, password_hash, name, role) VALUES 
('admin_001', 'admin@agroyousfi.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin');

-- Insert Sample Categories
INSERT INTO categories (category_id, name_ar, name_fr, name_en, icon) VALUES
('cat_1', 'البذور', 'Semences', 'Seeds', 'Leaf'),
('cat_2', 'الأسمدة', 'Engrais', 'Fertilizers', 'Droplets'),
('cat_3', 'المعدات', 'Équipements', 'Equipment', 'Wrench'),
('cat_4', 'المبيدات', 'Pesticides', 'Pesticides', 'Shield'),
('cat_5', 'الري', 'Irrigation', 'Irrigation', 'Droplet'),
('cat_seeds', 'البذور', 'Semences', 'Seeds', 'Leaf'),
('cat_fertilizers', 'الأسمدة', 'Engrais', 'Fertilizers', 'Droplets'),
('cat_tools', 'أدوات الزراعة', 'Outils Agricoles', 'Farm Tools', 'Wrench'),
('cat_pesticides', 'المبيدات', 'Pesticides', 'Pesticides', 'Shield'),
('cat_irrigation', 'أنظمة الري', 'Systèmes d''Irrigation', 'Irrigation Systems', 'Droplet'),
('cat_greenhouses', 'البيوت البلاستيكية', 'Serres', 'Greenhouses', 'Home');

-- Insert Sample Products
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, price, old_price, stock, category_id, featured, unit, discount_percent, discount_start, discount_end, rating, reviews_count) VALUES
('prod_wheat01', 'بذور القمح الصلب', 'Graines de blé dur', 'Hard Wheat Seeds', 'بذور قمح صلب عالية الجودة للزراعة', 5000, NULL, 100, 'cat_1', 1, 'kg', 25, '2026-01-01', '2026-01-31', 4.8, 24),
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
