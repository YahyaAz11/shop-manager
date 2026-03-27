<?php

declare(strict_types=1);

/**
 * Actions autorisées par rôle (hors admin = tout).
 * @return array<string, list<string>>
 */
return [
    'vendeur' => [
        'dashboard',
        'products',
        'product_search',
        'low_stock',
        'sale_create',
        'sale_store',
        'sale_show',
        'sale_ticket',
        'my_sales',
    ],
    'fournisseur' => [
        'dashboard',
        'products',
        'product_search',
        'low_stock',
        'suppliers',
        'supplier_purchase_orders',
        'supplier_purchase_order_show',
        'supplier_purchase_order_respond',
    ],
];
