-- =============================================================================
-- Données démo enrichies : 12 catégories, 30 produits, 50 ventes
-- =============================================================================
-- Prérequis : base `shop_db` avec tables à jour (dont vat_rate sur products et sale_items).
-- Comptes inchangés : users et suppliers. Auteur des ventes : 1er role `vendeur`, sinon admin.
--
-- Import : mysql -u root shop_db < seed-donnees-demo.sql
-- =============================================================================

SET NAMES utf8mb4;
USE shop_db;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM purchase_order_items;
DELETE FROM purchase_orders;
DELETE FROM sale_items;
DELETE FROM sales;
DELETE FROM products;
DELETE FROM categories;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE sales AUTO_INCREMENT = 1;
ALTER TABLE sale_items AUTO_INCREMENT = 1;

INSERT INTO categories (name) VALUES
('Boissons'),
('Biscuits & goûters'),
('Conserves'),
('Produits frais'),
('Surgelés'),
('Épicerie salée'),
('Épicerie sucrée'),
('Entretien & maison'),
('Petit-déjeuner'),
('Condiments'),
('Hygiène'),
('Boulangerie');

INSERT INTO products (name, description, price_buy, price_sell, vat_rate, stock, stock_alert_threshold, category_id, supplier_id) VALUES
('Eau minérale 1,5 L', 'Pack x6', 2.10, 3.29, 20.00, 180, 5, 1, 1),
('Jus d\'orange 1 L', 'Sans pulpe', 1.25, 1.99, 20.00, 191, 5, 1, 1),
('Cookies chocolat', 'Sachet 200 g', 0.95, 1.59, 20.00, 202, 5, 2, 1),
('Madeleines pur beurre', 'x8', 1.15, 1.89, 20.00, 213, 5, 2, 1),
('Tomates pelées', 'Boîte 400 g', 0.55, 0.99, 10.00, 224, 5, 3, 1),
('Maïs doux', '400 g', 0.65, 1.09, 10.00, 235, 5, 3, 1),
('Yaourt aux fruits', 'Pack x4', 1.05, 1.69, 10.00, 246, 5, 4, 1),
('Salade iceberg', 'Pièce', 0.75, 1.29, 10.00, 257, 5, 4, 1),
('Légumes surgelés', '1 kg', 1.55, 2.49, 20.00, 268, 5, 5, 1),
('Glace vanille', 'Bac 500 ml', 1.80, 2.99, 20.00, 189, 5, 5, 1),
('Riz thaï 1 kg', 'Long grain', 1.55, 2.49, 20.00, 200, 5, 6, 1),
('Pâtes penne 500 g', 'Qualité bronze', 0.88, 1.39, 20.00, 211, 5, 6, 1),
('Confiture fraise', 'Pot 320 g', 1.35, 2.19, 20.00, 222, 5, 7, 1),
('Pâte à tartiner', '350 g', 2.40, 3.89, 20.00, 233, 5, 7, 1),
('Liquide vaisselle', '750 ml', 1.05, 1.79, 20.00, 244, 5, 8, 1),
('Essuie-tout', 'Rouleau x2', 1.30, 1.99, 20.00, 255, 5, 8, 1),
('Céréales complètes', '375 g', 1.55, 2.59, 20.00, 266, 5, 9, 1),
('Barres céréales', 'Pack x6', 1.90, 2.99, 20.00, 187, 5, 9, 1),
('Mayonnaise', 'Flacon 450 g', 0.92, 1.59, 20.00, 198, 5, 10, 1),
('Ketchup', 'Squeeze 560 g', 0.82, 1.49, 20.00, 209, 5, 10, 1),
('Savon de Marseille', '300 g', 0.75, 1.19, 20.00, 220, 5, 11, 1),
('Shampooing', 'Flacon 250 ml', 2.20, 3.49, 20.00, 231, 5, 11, 1),
('Croissants pur beurre', 'x4', 1.40, 2.29, 10.00, 242, 5, 12, 1),
('Pain de mie', '500 g', 0.95, 1.59, 10.00, 253, 5, 12, 1),
('Soda cola 1,5 L', 'Bouteille', 1.10, 1.79, 20.00, 264, 5, 1, 1),
('Thé menthe', 'Sachets x20', 0.88, 1.39, 20.00, 185, 5, 1, 1),
('Harissa', 'Tube 140 g', 0.72, 1.19, 20.00, 196, 5, 10, 1),
('Semoule moyenne', '1 kg', 1.20, 1.99, 20.00, 207, 5, 6, 1),
('Lait UHT', 'Brique 1 L', 1.35, 1.99, 10.00, 218, 5, 4, 1),
('Œufs frais', 'Boîte x6', 1.80, 2.79, 10.00, 229, 5, 4, 1);

SET @seed_sales_user_id = COALESCE(
    (SELECT MIN(id) FROM users WHERE role = 'vendeur'),
    (SELECT MIN(id) FROM users WHERE role = 'admin'),
    (SELECT MIN(id) FROM users)
);
INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-03 17:17:00', 10.94, 10.94, 0, 0);
SET @sale_1 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_1, 6, 2, 1.09, 10.00), (@sale_1, 13, 4, 2.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-22 18:34:00', 26, 26, 0, 0);
SET @sale_2 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_2, 11, 3, 2.49, 20.00), (@sale_2, 18, 5, 2.99, 20.00), (@sale_2, 25, 2, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-11 19:51:00', 27.07, 27.07, 0, 0);
SET @sale_3 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_3, 16, 4, 1.99, 20.00), (@sale_3, 23, 1, 2.29, 10.00), (@sale_3, 30, 3, 2.79, 10.00), (@sale_3, 7, 5, 1.69, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-30 20:13:00', 5.95, 0, 5.95, 0);
SET @sale_4 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_4, 21, 5, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-18 21:30:00', 6.16, 6.16, 0, 0);
SET @sale_5 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_5, 26, 1, 1.39, 20.00), (@sale_5, 3, 3, 1.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-06 22:47:00', 13.53, 13.53, 0, 0);
SET @sale_6 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_6, 1, 2, 3.29, 20.00), (@sale_6, 8, 4, 1.29, 10.00), (@sale_6, 15, 1, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-25 23:09:00', 21.96, 21.96, 0, 0);
SET @sale_7 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_7, 6, 3, 1.09, 10.00), (@sale_7, 13, 5, 2.19, 20.00), (@sale_7, 20, 2, 1.49, 20.00), (@sale_7, 27, 4, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-10-28 00:26:00', 9.96, 0, 9.96, 0);
SET @sale_8 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_8, 11, 4, 2.49, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-16 01:43:00', 14.53, 14.53, 0, 0);
SET @sale_9 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_9, 16, 5, 1.99, 20.00), (@sale_9, 23, 2, 2.29, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-05 02:05:00', 12.11, 12.11, 0, 0);
SET @sale_10 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_10, 21, 1, 1.19, 20.00), (@sale_10, 28, 3, 1.99, 20.00), (@sale_10, 5, 5, 0.99, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-23 16:22:00', 19.9, 19.9, 0, 0);
SET @sale_11 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_11, 26, 2, 1.39, 20.00), (@sale_11, 3, 4, 1.59, 20.00), (@sale_11, 10, 1, 2.99, 20.00), (@sale_11, 17, 3, 2.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-11 17:39:00', 9.87, 0, 9.87, 0);
SET @sale_12 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_12, 1, 3, 3.29, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-30 18:01:00', 6.55, 6.55, 0, 0);
SET @sale_13 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_13, 6, 4, 1.09, 10.00), (@sale_13, 13, 1, 2.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-18 19:18:00', 25.59, 25.59, 0, 0);
SET @sale_14 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_14, 11, 5, 2.49, 20.00), (@sale_14, 18, 2, 2.99, 20.00), (@sale_14, 25, 4, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-10-20 20:35:00', 26.19, 26.19, 0, 0);
SET @sale_15 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_15, 16, 1, 1.99, 20.00), (@sale_15, 23, 3, 2.29, 10.00), (@sale_15, 30, 5, 2.79, 10.00), (@sale_15, 7, 2, 1.69, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-08 21:52:00', 2.38, 0, 2.38, 0);
SET @sale_16 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_16, 21, 2, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-27 22:14:00', 12.12, 12.12, 0, 0);
SET @sale_17 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_17, 26, 3, 1.39, 20.00), (@sale_17, 3, 5, 1.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-16 23:31:00', 19.82, 19.82, 0, 0);
SET @sale_18 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_18, 1, 4, 3.29, 20.00), (@sale_18, 8, 1, 1.29, 10.00), (@sale_18, 15, 3, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-05 00:48:00', 16.98, 16.98, 0, 0);
SET @sale_19 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_19, 6, 5, 1.09, 10.00), (@sale_19, 13, 2, 2.19, 20.00), (@sale_19, 20, 4, 1.49, 20.00), (@sale_19, 27, 1, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-24 01:10:00', 2.49, 0, 2.49, 0);
SET @sale_20 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_20, 11, 1, 2.49, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-12 02:27:00', 13.14, 13.14, 0, 0);
SET @sale_21 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_21, 16, 2, 1.99, 20.00), (@sale_21, 23, 4, 2.29, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-03-02 16:44:00', 15.5, 15.5, 0, 0);
SET @sale_22 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_22, 21, 3, 1.19, 20.00), (@sale_22, 28, 5, 1.99, 20.00), (@sale_22, 5, 2, 0.99, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-01 17:06:00', 29.07, 29.07, 0, 0);
SET @sale_23 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_23, 26, 4, 1.39, 20.00), (@sale_23, 3, 1, 1.59, 20.00), (@sale_23, 10, 3, 2.99, 20.00), (@sale_23, 17, 5, 2.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-20 18:23:00', 16.45, 0, 16.45, 0);
SET @sale_24 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_24, 1, 5, 3.29, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-09 19:40:00', 7.66, 7.66, 0, 0);
SET @sale_25 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_25, 6, 1, 1.09, 10.00), (@sale_25, 13, 3, 2.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-28 20:02:00', 18.73, 18.73, 0, 0);
SET @sale_26 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_26, 11, 2, 2.49, 20.00), (@sale_26, 18, 4, 2.99, 20.00), (@sale_26, 25, 1, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-16 21:19:00', 29.76, 29.76, 0, 0);
SET @sale_27 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_27, 16, 3, 1.99, 20.00), (@sale_27, 23, 5, 2.29, 10.00), (@sale_27, 30, 2, 2.79, 10.00), (@sale_27, 7, 4, 1.69, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-04 22:36:00', 4.76, 0, 4.76, 0);
SET @sale_28 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_28, 21, 4, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-23 23:53:00', 10.13, 10.13, 0, 0);
SET @sale_29 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_29, 26, 5, 1.39, 20.00), (@sale_29, 3, 2, 1.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-10-26 00:15:00', 16.11, 16.11, 0, 0);
SET @sale_30 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_30, 1, 1, 3.29, 20.00), (@sale_30, 8, 3, 1.29, 10.00), (@sale_30, 15, 5, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-14 01:32:00', 16, 16, 0, 0);
SET @sale_31 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_31, 6, 2, 1.09, 10.00), (@sale_31, 13, 4, 2.19, 20.00), (@sale_31, 20, 1, 1.49, 20.00), (@sale_31, 27, 3, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-03 02:49:00', 7.47, 0, 7.47, 0);
SET @sale_32 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_32, 11, 3, 2.49, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-21 16:11:00', 10.25, 10.25, 0, 0);
SET @sale_33 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_33, 16, 4, 1.99, 20.00), (@sale_33, 23, 1, 2.29, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-09 17:28:00', 13.89, 13.89, 0, 0);
SET @sale_34 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_34, 21, 5, 1.19, 20.00), (@sale_34, 28, 2, 1.99, 20.00), (@sale_34, 5, 4, 0.99, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-28 18:45:00', 26.29, 26.29, 0, 0);
SET @sale_35 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_35, 26, 1, 1.39, 20.00), (@sale_35, 3, 3, 1.59, 20.00), (@sale_35, 10, 5, 2.99, 20.00), (@sale_35, 17, 2, 2.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-16 19:07:00', 6.58, 0, 6.58, 0);
SET @sale_36 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_36, 1, 2, 3.29, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-10-18 20:24:00', 14.22, 14.22, 0, 0);
SET @sale_37 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_37, 6, 3, 1.09, 10.00), (@sale_37, 13, 5, 2.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-06 21:41:00', 18.32, 18.32, 0, 0);
SET @sale_38 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_38, 11, 4, 2.49, 20.00), (@sale_38, 18, 1, 2.99, 20.00), (@sale_38, 25, 3, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-25 22:03:00', 27.38, 27.38, 0, 0);
SET @sale_39 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_39, 16, 5, 1.99, 20.00), (@sale_39, 23, 2, 2.29, 10.00), (@sale_39, 30, 4, 2.79, 10.00), (@sale_39, 7, 1, 1.69, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-14 23:20:00', 1.19, 0, 1.19, 0);
SET @sale_40 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_40, 21, 1, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-03 00:37:00', 9.14, 9.14, 0, 0);
SET @sale_41 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_41, 26, 2, 1.39, 20.00), (@sale_41, 3, 4, 1.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-22 01:54:00', 19.9, 19.9, 0, 0);
SET @sale_42 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_42, 1, 3, 3.29, 20.00), (@sale_42, 8, 5, 1.29, 10.00), (@sale_42, 15, 2, 1.79, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-10 02:16:00', 16.97, 16.97, 0, 0);
SET @sale_43 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_43, 6, 4, 1.09, 10.00), (@sale_43, 13, 1, 2.19, 20.00), (@sale_43, 20, 3, 1.49, 20.00), (@sale_43, 27, 5, 1.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-28 16:33:00', 12.45, 0, 12.45, 0);
SET @sale_44 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_44, 11, 5, 2.49, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-10-30 17:50:00', 8.86, 8.86, 0, 0);
SET @sale_45 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_45, 16, 1, 1.99, 20.00), (@sale_45, 23, 3, 2.29, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-18 18:12:00', 11.33, 11.33, 0, 0);
SET @sale_46 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_46, 21, 2, 1.19, 20.00), (@sale_46, 28, 4, 1.99, 20.00), (@sale_46, 5, 1, 0.99, 10.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-07 19:29:00', 28.46, 28.46, 0, 0);
SET @sale_47 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_47, 26, 3, 1.39, 20.00), (@sale_47, 3, 5, 1.59, 20.00), (@sale_47, 10, 2, 2.99, 20.00), (@sale_47, 17, 4, 2.59, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-26 20:46:00', 13.16, 0, 13.16, 0);
SET @sale_48 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_48, 1, 4, 3.29, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-14 21:08:00', 9.83, 9.83, 0, 0);
SET @sale_49 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_49, 6, 5, 1.09, 10.00), (@sale_49, 13, 2, 2.19, 20.00);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-02 22:25:00', 20.41, 20.41, 0, 0);
SET @sale_50 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES (@sale_50, 11, 1, 2.49, 20.00), (@sale_50, 18, 3, 2.99, 20.00), (@sale_50, 25, 5, 1.79, 20.00);

