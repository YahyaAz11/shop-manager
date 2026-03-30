-- =============================================================================
-- Données démo : 10 catégories, 20 produits (répartis), 40 ventes + lignes
-- =============================================================================
-- Prérequis : base `shop_db` avec tables du schéma principal déjà créées.
-- Comptes : conserve users / suppliers. Les ventes utilisent le 1er utilisateur
-- avec role `vendeur`, sinon le 1er `admin`, sinon MIN(id) (voir @seed_sales_user_id).
--
-- Import : mysql -u root shop_db < seed-20-products-40-sales.sql
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

-- Sans ceci, l’AUTO_INCREMENT peut rester élevé après DELETE : les catégories
-- n’auraient plus les id 1..10 et les produits (category_id 1..10) échouent (#1452).
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;

INSERT INTO categories (name) VALUES
('Boissons'),
('Biscuits'),
('Conserves'),
('Produits frais'),
('Surgelés'),
('Épicerie salée'),
('Épicerie sucrée'),
('Entretien & maison'),
('Petit-déjeuner'),
('Condiments');

INSERT INTO products (name, description, price_buy, price_sell, stock, stock_alert_threshold, category_id, supplier_id) VALUES
('Eau minérale 1,5 L', 'Pack x6', 2.10, 3.29, 200, 5, 1, 1),
('Jus d\'orange 1 L', 'Sans pulpe', 1.25, 1.99, 207, 5, 1, 1),
('Cookies chocolat', 'Sachet 200 g', 0.95, 1.59, 214, 5, 2, 1),
('Madeleines pur beurre', 'x8', 1.15, 1.89, 221, 5, 2, 1),
('Tomates pelées', 'Boîte 400 g', 0.55, 0.99, 228, 5, 3, 1),
('Maïs doux', '400 g', 0.65, 1.09, 235, 5, 3, 1),
('Yaourt aux fruits', 'Pack x4', 1.05, 1.69, 242, 5, 4, 1),
('Salade iceberg', 'Pièce', 0.75, 1.29, 249, 5, 4, 1),
('Légumes surgelés', '1 kg', 1.55, 2.49, 256, 5, 5, 1),
('Glace vanille', 'Bac 500 ml', 1.80, 2.99, 263, 5, 5, 1),
('Riz thaï 1 kg', 'Long grain', 1.55, 2.49, 270, 5, 6, 1),
('Pâtes penne 500 g', 'Qualité bronze', 0.88, 1.39, 277, 5, 6, 1),
('Confiture fraise', 'Pot 320 g', 1.35, 2.19, 204, 5, 7, 1),
('Pâte à tartiner', '350 g', 2.40, 3.89, 211, 5, 7, 1),
('Liquide vaisselle', '750 ml', 1.05, 1.79, 218, 5, 8, 1),
('Essuie-tout', 'Rouleau x2', 1.30, 1.99, 225, 5, 8, 1),
('Céréales complètes', '375 g', 1.55, 2.59, 232, 5, 9, 1),
('Barres céréales', 'Pack x6', 1.90, 2.99, 239, 5, 9, 1),
('Mayonnaise', 'Flacon 450 g', 0.92, 1.59, 246, 5, 10, 1),
('Ketchup', 'Squeeze 560 g', 0.82, 1.49, 253, 5, 10, 1);

-- Ventes : user_id = premier vendeur, sinon admin, sinon tout utilisateur (#1452 si table users vide)
SET @seed_sales_user_id = COALESCE(
    (SELECT MIN(id) FROM users WHERE role = 'vendeur'),
    (SELECT MIN(id) FROM users WHERE role = 'admin'),
    (SELECT MIN(id) FROM users)
);

-- 40 ventes clients (`sales` / `sale_items`)
INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-18 17:13:00', 11.25, 11.25, 0, 0);
SET @sale_1 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_1, 4, 2, 1.89), (@sale_1, 9, 3, 2.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-05 18:26:00', 13.22, 13.22, 0, 0);
SET @sale_2 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_2, 7, 3, 1.69), (@sale_2, 12, 4, 1.39), (@sale_2, 17, 1, 2.59);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-22 19:39:00', 11.96, 0, 11.96, 0);
SET @sale_3 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_3, 10, 4, 2.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-08 20:52:00', 8.17, 8.17, 0, 0);
SET @sale_4 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_4, 13, 1, 2.19), (@sale_4, 18, 2, 2.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-25 21:10:00', 18.21, 18.21, 0, 0);
SET @sale_5 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_5, 16, 2, 1.99), (@sale_5, 1, 3, 3.29), (@sale_5, 6, 4, 1.09);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-11 22:23:00', 4.77, 0, 4.77, 0);
SET @sale_6 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_6, 19, 3, 1.59);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-28 23:36:00', 9.65, 9.65, 0, 0);
SET @sale_7 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_7, 2, 4, 1.99), (@sale_7, 7, 1, 1.69);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-18 00:49:00', 12.34, 12.34, 0, 0);
SET @sale_8 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_8, 5, 1, 0.99), (@sale_8, 10, 2, 2.99), (@sale_8, 15, 3, 1.79);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-05 01:07:00', 2.58, 0, 2.58, 0);
SET @sale_9 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_9, 8, 2, 1.29);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-21 16:20:00', 15.43, 15.43, 0, 0);
SET @sale_10 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_10, 11, 3, 2.49), (@sale_10, 16, 4, 1.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-07 17:33:00', 20.93, 20.93, 0, 0);
SET @sale_11 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_11, 14, 4, 3.89), (@sale_11, 19, 1, 1.59), (@sale_11, 4, 2, 1.89);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-24 18:46:00', 2.59, 0, 2.59, 0);
SET @sale_12 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_12, 17, 1, 2.59);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-10 19:04:00', 5.95, 5.95, 0, 0);
SET @sale_13 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_13, 20, 2, 1.49), (@sale_13, 5, 3, 0.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-27 20:17:00', 12.12, 12.12, 0, 0);
SET @sale_14 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_14, 3, 3, 1.59), (@sale_14, 8, 4, 1.29), (@sale_14, 13, 1, 2.19);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-16 21:30:00', 4.36, 0, 4.36, 0);
SET @sale_15 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_15, 6, 4, 1.09);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-03 22:43:00', 10.27, 10.27, 0, 0);
SET @sale_16 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_16, 9, 1, 2.49), (@sale_16, 14, 2, 3.89);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-20 23:01:00', 18.51, 18.51, 0, 0);
SET @sale_17 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_17, 12, 2, 1.39), (@sale_17, 17, 3, 2.59), (@sale_17, 2, 4, 1.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-07 00:14:00', 5.37, 0, 5.37, 0);
SET @sale_18 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_18, 15, 3, 1.79);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-24 01:27:00', 13.55, 13.55, 0, 0);
SET @sale_19 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_19, 18, 4, 2.99), (@sale_19, 3, 1, 1.59);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-09 16:40:00', 12.94, 12.94, 0, 0);
SET @sale_20 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_20, 1, 1, 3.29), (@sale_20, 6, 2, 1.09), (@sale_20, 11, 3, 2.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-26 17:53:00', 3.78, 0, 3.78, 0);
SET @sale_21 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_21, 4, 2, 1.89);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-15 18:11:00', 10.63, 10.63, 0, 0);
SET @sale_22 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_22, 7, 3, 1.69), (@sale_22, 12, 4, 1.39);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-02 19:24:00', 16.73, 16.73, 0, 0);
SET @sale_23 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_23, 10, 4, 2.99), (@sale_23, 15, 1, 1.79), (@sale_23, 20, 2, 1.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-19 20:37:00', 2.19, 0, 2.19, 0);
SET @sale_24 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_24, 13, 1, 2.19);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-05 21:50:00', 13.85, 13.85, 0, 0);
SET @sale_25 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_25, 16, 2, 1.99), (@sale_25, 1, 3, 3.29);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-22 22:08:00', 14.82, 14.82, 0, 0);
SET @sale_26 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_26, 19, 3, 1.59), (@sale_26, 4, 4, 1.89), (@sale_26, 9, 1, 2.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-08 23:21:00', 7.96, 0, 7.96, 0);
SET @sale_27 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_27, 2, 4, 1.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-26 00:34:00', 6.97, 6.97, 0, 0);
SET @sale_28 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_28, 5, 1, 0.99), (@sale_28, 10, 2, 2.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-15 01:47:00', 21.11, 21.11, 0, 0);
SET @sale_29 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_29, 8, 2, 1.29), (@sale_29, 13, 3, 2.19), (@sale_29, 18, 4, 2.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-01 16:05:00', 7.47, 0, 7.47, 0);
SET @sale_30 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_30, 11, 3, 2.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-18 17:18:00', 17.15, 17.15, 0, 0);
SET @sale_31 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_31, 14, 4, 3.89), (@sale_31, 19, 1, 1.59);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-04 18:31:00', 11.64, 11.64, 0, 0);
SET @sale_32 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_32, 17, 1, 2.59), (@sale_32, 2, 2, 1.99), (@sale_32, 7, 3, 1.69);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-21 19:44:00', 2.98, 0, 2.98, 0);
SET @sale_33 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_33, 20, 2, 1.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-07 20:02:00', 9.93, 9.93, 0, 0);
SET @sale_34 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_34, 3, 3, 1.59), (@sale_34, 8, 4, 1.29);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-02-24 21:15:00', 10.83, 10.83, 0, 0);
SET @sale_35 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_35, 6, 4, 1.09), (@sale_35, 11, 1, 2.49), (@sale_35, 16, 2, 1.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-13 22:28:00', 2.49, 0, 2.49, 0);
SET @sale_36 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_36, 9, 1, 2.49);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-11-30 23:41:00', 10.55, 10.55, 0, 0);
SET @sale_37 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_37, 12, 2, 1.39), (@sale_37, 17, 3, 2.59);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2025-12-18 00:54:00', 12.32, 12.32, 0, 0);
SET @sale_38 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_38, 15, 3, 1.79), (@sale_38, 20, 4, 1.49), (@sale_38, 5, 1, 0.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-04 01:12:00', 11.96, 0, 11.96, 0);
SET @sale_39 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_39, 18, 4, 2.99);

INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '2026-01-20 16:25:00', 5.47, 5.47, 0, 0);
SET @sale_40 = LAST_INSERT_ID();
INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES (@sale_40, 1, 1, 3.29), (@sale_40, 6, 2, 1.09);

