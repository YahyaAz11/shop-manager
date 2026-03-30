<?php
/**
 * Génère seed-donnees-demo.sql — données démo enrichies (catégories, produits avec TVA, ventes).
 * Exécuter : php scripts/generate_seed_sales_sql.php
 */

$outPath = __DIR__ . '/../seed-donnees-demo.sql';
$out = fopen($outPath, 'wb');

$numSales = 50;
$numCats = 12;
$numProducts = 30;

$header = <<<SQL
-- =============================================================================
-- Données démo enrichies : {$numCats} catégories, {$numProducts} produits, {$numSales} ventes
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

SQL;
fwrite($out, $header);

// nom, description, achat, vente TTC, category_id, vat %
$rows = [
    ['Eau minérale 1,5 L', 'Pack x6', 2.10, 3.29, 1, 20.00],
    ['Jus d\'orange 1 L', 'Sans pulpe', 1.25, 1.99, 1, 20.00],
    ['Cookies chocolat', 'Sachet 200 g', 0.95, 1.59, 2, 20.00],
    ['Madeleines pur beurre', 'x8', 1.15, 1.89, 2, 20.00],
    ['Tomates pelées', 'Boîte 400 g', 0.55, 0.99, 3, 10.00],
    ['Maïs doux', '400 g', 0.65, 1.09, 3, 10.00],
    ['Yaourt aux fruits', 'Pack x4', 1.05, 1.69, 4, 10.00],
    ['Salade iceberg', 'Pièce', 0.75, 1.29, 4, 10.00],
    ['Légumes surgelés', '1 kg', 1.55, 2.49, 5, 20.00],
    ['Glace vanille', 'Bac 500 ml', 1.80, 2.99, 5, 20.00],
    ['Riz thaï 1 kg', 'Long grain', 1.55, 2.49, 6, 20.00],
    ['Pâtes penne 500 g', 'Qualité bronze', 0.88, 1.39, 6, 20.00],
    ['Confiture fraise', 'Pot 320 g', 1.35, 2.19, 7, 20.00],
    ['Pâte à tartiner', '350 g', 2.40, 3.89, 7, 20.00],
    ['Liquide vaisselle', '750 ml', 1.05, 1.79, 8, 20.00],
    ['Essuie-tout', 'Rouleau x2', 1.30, 1.99, 8, 20.00],
    ['Céréales complètes', '375 g', 1.55, 2.59, 9, 20.00],
    ['Barres céréales', 'Pack x6', 1.90, 2.99, 9, 20.00],
    ['Mayonnaise', 'Flacon 450 g', 0.92, 1.59, 10, 20.00],
    ['Ketchup', 'Squeeze 560 g', 0.82, 1.49, 10, 20.00],
    ['Savon de Marseille', '300 g', 0.75, 1.19, 11, 20.00],
    ['Shampooing', 'Flacon 250 ml', 2.20, 3.49, 11, 20.00],
    ['Croissants pur beurre', 'x4', 1.40, 2.29, 12, 10.00],
    ['Pain de mie', '500 g', 0.95, 1.59, 12, 10.00],
    ['Soda cola 1,5 L', 'Bouteille', 1.10, 1.79, 1, 20.00],
    ['Thé menthe', 'Sachets x20', 0.88, 1.39, 1, 20.00],
    ['Harissa', 'Tube 140 g', 0.72, 1.19, 10, 20.00],
    ['Semoule moyenne', '1 kg', 1.20, 1.99, 6, 20.00],
    ['Lait UHT', 'Brique 1 L', 1.35, 1.99, 4, 10.00],
    ['Œufs frais', 'Boîte x6', 1.80, 2.79, 4, 10.00],
];

$esc = static function (string $s): string {
    return str_replace(['\\', "'"], ['\\\\', "\\'"], $s);
};

$n = count($rows);
if ($n !== $numProducts) {
    fwrite(STDERR, "Warning: product count is $n, expected $numProducts\n");
}

$parts = [];
foreach ($rows as $i => $r) {
    $parts[] = sprintf(
        "('%s', '%s', %.2f, %.2f, %.2f, %d, 5, %d, 1)",
        $esc($r[0]),
        $esc($r[1]),
        $r[2],
        $r[3],
        $r[5],
        180 + ($i * 11) % 90,
        $r[4]
    );
}
fwrite($out, implode(",\n", $parts) . ";\n\n");

fwrite($out, <<<'SQL'
SET @seed_sales_user_id = COALESCE(
    (SELECT MIN(id) FROM users WHERE role = 'vendeur'),
    (SELECT MIN(id) FROM users WHERE role = 'admin'),
    (SELECT MIN(id) FROM users)
);

SQL);

$base = strtotime('2025-10-15 08:00:00');
$nProducts = count($rows);

for ($s = 1; $s <= $numSales; $s++) {
    $dayOff = (int) (($s * 19) % 140);
    $hour = 8 + ($s % 11);
    $min = ($s * 17) % 55;
    $ts = $base + $dayOff * 86400 + $hour * 3600 + $min * 60;
    $d = date('Y-m-d H:i:s', $ts);

    $nLines = 1 + ($s % 4);
    $sum = 0;
    $lines = [];
    for ($l = 0; $l < $nLines; $l++) {
        $pid = 1 + (($s * 5 + $l * 7) % $nProducts);
        $qty = 1 + (($s + $l * 2) % 5);
        $price = (float) $rows[$pid - 1][3];
        $vat = (float) $rows[$pid - 1][5];
        $lines[] = [$pid, $qty, $price, $vat];
        $sum += $qty * $price;
    }
    $sum = round($sum, 2);
    $card = $s % 4 === 0 ? round($sum, 2) : 0.0;
    $cash = round($sum - $card, 2);

    fwrite($out, "INSERT INTO sales (user_id, sale_date, total, payment_especes, payment_carte, payment_autre) VALUES (@seed_sales_user_id, '{$d}', {$sum}, {$cash}, {$card}, 0);\n");
    fwrite($out, "SET @sale_{$s} = LAST_INSERT_ID();\n");
    $vals = [];
    foreach ($lines as $ln) {
        $vals[] = sprintf('(@sale_%d, %d, %d, %.2f, %.2f)', $s, $ln[0], $ln[1], $ln[2], $ln[3]);
    }
    fwrite($out, 'INSERT INTO sale_items (sale_id, product_id, quantity, price, vat_rate) VALUES ' . implode(', ', $vals) . ";\n\n");
}

fclose($out);
echo "Wrote {$outPath}\n";
