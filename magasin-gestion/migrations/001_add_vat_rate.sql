-- TVA : taux en % sur le produit ; copié sur chaque ligne de vente (historique).
-- À exécuter une seule fois sur une base créée avant l’ajout de ces colonnes.
-- Si les colonnes existent déjà (après import du schema.sql à jour), ne pas lancer ce fichier.

USE shop_db;

ALTER TABLE products
    ADD COLUMN vat_rate DECIMAL(5,2) NOT NULL DEFAULT 20.00 AFTER price_sell;

ALTER TABLE sale_items
    ADD COLUMN vat_rate DECIMAL(5,2) NOT NULL DEFAULT 20.00 AFTER price;
