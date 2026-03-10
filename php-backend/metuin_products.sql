-- =====================================================
-- Metuin Products Database - SQL Insert Statements
-- Generated: 2026-01-04 13:40:52
-- =====================================================

-- استخدم قاعدة البيانات
USE agroyousfi;

-- =====================================================
-- إدراج فئات المنتجات
-- =====================================================
INSERT IGNORE INTO categories (category_id, name_ar, name_fr, name_en, icon) VALUES
('cat_metuin_1', 'الأسمدة والمغذيات', 'Engrais Et Nutriments', 'Fertilizers And Nutrients', 'Droplets'),
('cat_metuin_2', 'المبيدات والمعقمات', 'Pesticides Et Desinfectants', 'Pesticides And Disinfectants', 'Shield');

-- =====================================================
-- إدراج المنتجات
-- =====================================================

INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_001',
    'Biotuin',
    'Biotuin',
    'Biotuin',
    'منتج من شركة Metuin - Biotuin',
    'Produit de Metuin - Biotuin',
    'Metuin Product - Biotuin',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_002',
    'METUIN NPK 12 52 12 + TE',
    'METUIN NPK 12 52 12 + TE',
    'METUIN NPK 12 52 12 + TE',
    'منتج من شركة Metuin - METUIN NPK 12 52 12 + TE',
    'Produit de Metuin - METUIN NPK 12 52 12 + TE',
    'Metuin Product - METUIN NPK 12 52 12 + TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_003',
    'METUIN NPK 12-12-52',
    'METUIN NPK 12-12-52',
    'METUIN NPK 12-12-52',
    'منتج من شركة Metuin - METUIN NPK 12-12-52',
    'Produit de Metuin - METUIN NPK 12-12-52',
    'Metuin Product - METUIN NPK 12-12-52',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_004',
    'METUIN NPK 13 40 13 + TE',
    'METUIN NPK 13 40 13 + TE',
    'METUIN NPK 13 40 13 + TE',
    'منتج من شركة Metuin - METUIN NPK 13 40 13 + TE',
    'Produit de Metuin - METUIN NPK 13 40 13 + TE',
    'Metuin Product - METUIN NPK 13 40 13 + TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_005',
    'METUIN NPK 16 69 09 + TE',
    'METUIN NPK 16 69 09 + TE',
    'METUIN NPK 16 69 09 + TE',
    'منتج من شركة Metuin - METUIN NPK 16 69 09 + TE',
    'Produit de Metuin - METUIN NPK 16 69 09 + TE',
    'Metuin Product - METUIN NPK 16 69 09 + TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_006',
    'METUIN NPK 20 20 20 + TE',
    'METUIN NPK 20 20 20 + TE',
    'METUIN NPK 20 20 20 + TE',
    'منتج من شركة Metuin - METUIN NPK 20 20 20 + TE',
    'Produit de Metuin - METUIN NPK 20 20 20 + TE',
    'Metuin Product - METUIN NPK 20 20 20 + TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_007',
    'METUIN NPK 27 27 27 + TE',
    'METUIN NPK 27 27 27 + TE',
    'METUIN NPK 27 27 27 + TE',
    'منتج من شركة Metuin - METUIN NPK 27 27 27 + TE',
    'Produit de Metuin - METUIN NPK 27 27 27 + TE',
    'Metuin Product - METUIN NPK 27 27 27 + TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_008',
    'METUIN NPK 40 10 10 + TE',
    'METUIN NPK 40 10 10 + TE',
    'METUIN NPK 40 10 10 + TE',
    'منتج من شركة Metuin - METUIN NPK 40 10 10 + TE',
    'Produit de Metuin - METUIN NPK 40 10 10 + TE',
    'Metuin Product - METUIN NPK 40 10 10 + TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_009',
    'POTASSIUM NITRAT 13 00 46',
    'POTASSIUM NITRAT 13 00 46',
    'POTASSIUM NITRAT 13 00 46',
    'منتج من شركة Metuin - POTASSIUM NITRAT 13 00 46',
    'Produit de Metuin - POTASSIUM NITRAT 13 00 46',
    'Metuin Product - POTASSIUM NITRAT 13 00 46',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_010',
    'TuinMap 12 61 00+TE',
    'TuinMap 12 61 00+TE',
    'TuinMap 12 61 00+TE',
    'منتج من شركة Metuin - TuinMap 12 61 00+TE',
    'Produit de Metuin - TuinMap 12 61 00+TE',
    'Metuin Product - TuinMap 12 61 00+TE',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_011',
    'CALBOTUIN - Calcium & Borone',
    'CALBOTUIN - Calcium & Borone',
    'CALBOTUIN - Calcium & Borone',
    'منتج من شركة Metuin - CALBOTUIN - Calcium & Borone',
    'Produit de Metuin - CALBOTUIN - Calcium & Borone',
    'Metuin Product - CALBOTUIN - Calcium & Borone',
    0.00,
    0,
    'cat_metuin_1',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_012',
    'METUICAl -  Antifongique',
    'METUICAl -  Antifongique',
    'METUICAl -  Antifongique',
    'منتج من شركة Metuin - METUICAl -  Antifongique',
    'Produit de Metuin - METUICAl -  Antifongique',
    'Metuin Product - METUICAl -  Antifongique',
    0.00,
    0,
    'cat_metuin_2',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_013',
    'PHOSOHATUIN- Acide phosphorique',
    'PHOSOHATUIN- Acide phosphorique',
    'PHOSOHATUIN- Acide phosphorique',
    'منتج من شركة Metuin - PHOSOHATUIN- Acide phosphorique',
    'Produit de Metuin - PHOSOHATUIN- Acide phosphorique',
    'Metuin Product - PHOSOHATUIN- Acide phosphorique',
    0.00,
    0,
    'cat_metuin_2',
    FALSE
);
INSERT INTO products (product_id, name_ar, name_fr, name_en, description_ar, description_fr, description_en, price, stock, category_id, featured) 
VALUES (
    'prod_metuin_014',
    'SULFATUIN - Acide Sulfurique et Azote',
    'SULFATUIN - Acide Sulfurique et Azote',
    'SULFATUIN - Acide Sulfurique et Azote',
    'منتج من شركة Metuin - SULFATUIN - Acide Sulfurique et Azote',
    'Produit de Metuin - SULFATUIN - Acide Sulfurique et Azote',
    'Metuin Product - SULFATUIN - Acide Sulfurique et Azote',
    0.00,
    0,
    'cat_metuin_2',
    FALSE
);

-- =====================================================
-- إدراج صور المنتجات
-- =====================================================
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_001', 'https://www.metuin.com/wp-content/uploads/2022/10/biotuin.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_002', 'https://www.metuin.com/wp-content/uploads/2022/10/npk125212.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_003', 'https://www.metuin.com/wp-content/uploads/2022/10/npk-121252.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_004', 'https://www.metuin.com/wp-content/uploads/2022/10/npk134013.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_005', 'https://www.metuin.com/wp-content/uploads/2022/10/npk-166909.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_006', 'https://www.metuin.com/wp-content/uploads/2022/10/npk-202020.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_007', 'https://www.metuin.com/wp-content/uploads/2022/10/npk-272727te.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_008', 'https://www.metuin.com/wp-content/uploads/2022/10/npk-401010.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_009', 'https://www.metuin.com/wp-content/uploads/2022/10/potasium.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_010', 'https://www.metuin.com/wp-content/uploads/2022/10/Tuinmap.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_011', 'https://www.metuin.com/wp-content/uploads/2022/12/Calbotuin_5L-Metuin-Fabrication-dengrais-organiques-chimiques-et-antifongiques-1.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_012', 'https://www.metuin.com/wp-content/uploads/2022/12/METUICAl_5L-Metuin-Fabrication-dengrais-organiques-chimiques-et-antifongiques-1.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_013', 'https://www.metuin.com/wp-content/uploads/2022/12/Phosphatuin_Acid__5L-Metuin-Fabrication-dengrais-organiques-chimiques-et-antifongiques-1.png', 0);
INSERT INTO product_images (product_id, image_url, sort_order) 
VALUES ('prod_metuin_014', 'https://www.metuin.com/wp-content/uploads/2022/12/Sulfatuin_5L-Metuin-Fabrication-dengrais-organiques-chimiques-et-antifongiques-1.png', 0);


-- =====================================================
-- ملاحظات مهمة:
-- =====================================================
-- 1. تم استخراج 14 منتج من موقع Metuin
-- 2. تم استخراج الصور الصحيحة لكل منتج
-- 3. يجب تحديث الأسعار يدويًا حسب قائمة الأسعار الحالية
-- 4. يجب تحديث كميات المخزون حسب توفر المنتجات
-- 5. تم تعيين جميع المنتجات للفئات الافتراضية - يمكن تعديلها حسب الحاجة
-- =====================================================
