<?php

declare(strict_types=1);

/**
 * Mentions légales et en-tête ticket / facture (à adapter à votre établissement).
 *
 * @return array{
 *     trade_name: string,
 *     legal_name: string,
 *     address: string,
 *     phone: string,
 *     email: string,
 *     ice: string,
 *     if_num: string,
 *     rc: string,
 *     patente: string,
 *     ticket_footer_lines: list<string>,
 * }
 */
return [
    'trade_name'   => 'Gestion magasin',
    'legal_name'   => 'Gestion magasin',
    'address'      => 'Adresse · Code postal · Ville · Royaume du Maroc',
    'phone'        => '+212 123456789',
    'email'        => 'contact@test.ma',
    'ice'          => '', // Identifiant Commun de l’Entreprise (à renseigner)
    'if_num'       => '', // Identifiant fiscal
    'rc'           => '', // Registre de commerce
    'patente'      => '', // Patente
    'ticket_footer_lines' => [
        'Prix affichés en dirhams marocains (MAD), TTC si applicable selon votre régime.',
        'Document établi à titre de preuve d’achat — conservez-le pour toute réclamation.',
        'Merci de votre visite.',
    ],
];
