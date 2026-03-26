-- =============================================================================
-- Magasin-gestion — schéma + données de démo (fichier unique)
-- =============================================================================
-- Base : shop_db · Encodage UTF-8
-- Comptes démo (tous mot de passe : password) :
--   admin@example.com (admin)
--   vendeur@example.com (vendeur)
--   fournisseur@example.com (fournisseur, lié au seul fournisseur)
-- Réinstallation complète : importer CE FICHIER SEUL (efface shop_db si elle existe).
-- =============================================================================

DROP DATABASE IF EXISTS shop_db;
CREATE DATABASE shop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'vendeur', 'fournisseur') NOT NULL,
    supplier_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(100),
    phone VARCHAR(30),
    email VARCHAR(150),
    address TEXT
);

ALTER TABLE users
    ADD CONSTRAINT fk_users_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price_buy DECIMAL(10,2) NOT NULL,
    price_sell DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_alert_threshold INT NOT NULL DEFAULT 5,
    category_id INT,
    supplier_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    payment_especes DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_carte DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_autre DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('brouillon', 'envoye', 'accepte', 'refuse', 'recu', 'annule') NOT NULL DEFAULT 'brouillon',
    ordered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    received_at DATETIME NULL,
    notes TEXT,
    supplier_reply_note TEXT NULL,
    supplier_replied_at DATETIME NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE purchase_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- --- Données : catégories ---
INSERT INTO categories (name) VALUES
('Fruits & Légumes'),
('Boucherie'),
('Poissonnerie'),
('Produits laitiers'),
('Épicerie salée'),
('Épicerie sucrée'),
('Boissons'),
('Surgelés'),
('Hygiène & beauté'),
('Boulangerie');

-- --- Un seul fournisseur : tous les produits y sont rattachés ---
INSERT INTO suppliers (name, contact, phone, email, address) VALUES
(
    'Grossiste Central Magasin',
    'Service B2B',
    '+212 522 00 00 00',
    'commandes@grossiste-central.test.ma',
    'Zone industrielle · Casablanca · Maroc'
);

-- --- Utilisateurs (fournisseur lié au fournisseur id = 1) ---
INSERT INTO users (name, email, password, role, supplier_id) VALUES
(
    'Administrateur',
    'admin@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    NULL
),
(
    'Marie Vendeur',
    'vendeur@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'vendeur',
    NULL
),
(
    'Contact Grossiste Central',
    'fournisseur@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'fournisseur',
    1
);

INSERT INTO products (name, description, price_buy, price_sell, stock, stock_alert_threshold, category_id, supplier_id) VALUES
('Tomates grappe', 'Qualité extra', 1.65, 2.49, 40, 5, 1, 1),
('Carottes lavées 1 kg', 'Sachet', 0.95, 1.59, 120, 5, 1, 1),
('Pommes Golden', 'Calibre moyen', 2.10, 3.29, 80, 5, 1, 1),
('Bananes', 'Origine équateur', 1.40, 2.19, 60, 5, 1, 1),
('Salade batavia', 'Pièce', 0.85, 1.39, 35, 3, 1, 1),
('Courgettes', 'Au kg', 1.20, 1.89, 55, 5, 1, 1),
('Oignons jaunes', 'Filet 1 kg', 0.75, 1.19, 90, 5, 1, 1),
('Pommes de terre', 'Consommation', 0.65, 0.99, 150, 5, 1, 1),
('Poivrons tricolores', 'Barquette', 1.80, 2.79, 28, 2, 1, 1),
('Citron jaune', 'Au kg', 2.40, 3.69, 42, 5, 1, 1),
('Bœuf haché 15% 400g', 'Sous vide', 5.20, 7.99, 45, 5, 2, 1),
('Steak haché x2', '150 g pièce', 3.60, 5.49, 38, 5, 2, 1),
('Poulet entier', 'Label', 3.90, 5.99, 30, 5, 2, 1),
('Escalopes de dinde', '400 g', 4.50, 6.89, 33, 5, 2, 1),
('Merguez x6', 'Barquette', 2.80, 4.29, 50, 5, 2, 1),
('Rôti de veau', 'À partager', 8.90, 13.49, 12, 1, 2, 1),
('Sardines fraîches', 'Kg', 4.20, 6.49, 22, 5, 3, 1),
('Filet de cabillaud', 'Surgelé frais', 6.50, 9.99, 18, 5, 3, 1),
('Crevettes roses', '400 g', 9.80, 14.99, 15, 5, 3, 1),
('Moules de bouchot', '1,4 kg', 5.40, 8.29, 20, 4, 3, 1),
('Lait entier 1 L', 'Brique', 0.95, 1.49, 200, 5, 4, 1),
('Yaourt nature x8', 'Pack', 1.85, 2.79, 85, 5, 4, 1),
('Beurre demi-sel 250 g', 'Plaquette', 2.20, 3.39, 70, 5, 4, 1),
('Mozzarella', 'Boule', 1.95, 2.99, 25, 0, 4, 1),
('Camembert AOP', '250 g', 2.60, 3.99, 40, 3, 4, 1),
('Riz thaï 1 kg', 'Long grain', 1.65, 2.49, 110, 5, 5, 1),
('Huile d’olive 75 cl', 'Vierge extra', 4.80, 7.29, 55, 5, 5, 1),
('Pâtes penne 500 g', 'Qualité bronze', 0.95, 1.49, 130, 5, 5, 1),
('Tomates concassées 400 g', 'Brique', 0.75, 1.19, 95, 5, 5, 1),
('Sel fin 1 kg', 'Iodé', 0.45, 0.79, 75, 5, 5, 1),
('Sucre en poudre 1 kg', 'Cristal', 1.10, 1.69, 88, 5, 6, 1),
('Confiture fraise 370 g', 'Pot verre', 1.95, 2.99, 62, 5, 6, 1),
('Chocolat noir 100 g', 'Tablette', 1.25, 1.89, 100, 3, 6, 1),
('Biscuits sablés 200 g', 'Sachet', 1.40, 2.19, 72, 5, 6, 1),
('Eau minérale 1,5 L x6', 'Pack', 2.30, 3.49, 140, 5, 7, 1),
('Jus d’orange 1 L', 'Sans pulpe', 1.65, 2.49, 65, 5, 7, 1),
('Soda cola 1,5 L', 'Bouteille', 1.20, 1.89, 95, 5, 7, 1),
('Thé vert 25 sachets', 'Boîte', 2.10, 3.19, 48, 5, 7, 1),
('Café moulu 250 g', 'Arabica', 3.40, 5.19, 58, 5, 7, 1),
('Légumes surgelés 1 kg', 'Mélange', 1.85, 2.79, 52, 5, 8, 1),
('Frites allumettes 1 kg', 'Sachet', 1.55, 2.39, 68, 5, 8, 1),
('Glace vanille 1 L', 'Bac', 2.80, 4.29, 35, 4, 8, 1),
('Poisson pané x4', 'Colin', 2.95, 4.49, 40, 5, 8, 1),
('Savon Marseille 300 g', 'Cube', 1.15, 1.79, 90, 1, 9, 1),
('Shampoing 250 ml', 'Cheveux normaux', 2.40, 3.69, 55, 5, 9, 1),
('Papier toilette x12', '2 plis', 3.20, 4.89, 75, 5, 9, 1),
('Lessive liquide 1,5 L', 'Universal', 4.50, 6.89, 44, 5, 9, 1),
('Pain de mie grandes tranches', '500 g', 1.05, 1.59, 60, 5, 10, 1),
('Baguette tradition', 'Pierre', 0.55, 0.89, 100, 5, 10, 1),
('Brioche tressée', '400 g', 1.65, 2.49, 38, 2, 10, 1),
('Croissants pur beurre x4', 'Barquette', 1.95, 2.99, 45, 5, 10, 1),
('Tapenade noire 90 g', 'Pot', 2.10, 3.19, 30, 5, 5, 1),
('Farine T55 1 kg', 'Boulanger', 0.85, 1.29, 85, 5, 5, 1),
('Lentilles vertes 500 g', 'Sachet', 1.40, 2.19, 50, 5, 5, 1),
('Haricots blancs 500 g', 'Sec', 1.25, 1.89, 48, 5, 5, 1),
('Vinaigre balsamique 50 cl', 'Bouteille', 2.80, 4.29, 40, 5, 5, 1),
('Miel de fleurs 500 g', 'Pot', 5.50, 8.39, 22, 2, 6, 1),
('Olives vertes 350 g', 'Bocal', 1.90, 2.89, 42, 5, 5, 1),
('Fromage râpé 200 g', 'Emmental', 2.30, 3.49, 58, 5, 4, 1),
('Crème fraîche 20 cl', 'Épaisse', 0.95, 1.49, 66, 5, 4, 1),
('Œufs x12', 'Gros', 1.80, 2.79, 95, 5, 4, 1),
('Mayonnaise 470 g', 'Flacon souple', 1.55, 2.39, 52, 5, 5, 1),
('Ketchup 560 g', 'Squeeze', 1.35, 2.09, 60, 5, 5, 1),
('Eau de Javel 1 L', 'Ménage', 0.75, 1.19, 70, 5, 9, 1),
('Éponge végétale x3', 'Pack', 0.95, 1.49, 88, 5, 9, 1);
