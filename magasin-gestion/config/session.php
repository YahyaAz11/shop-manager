<?php

declare(strict_types=1);

/**
 * Session PHP : durée d’inactivité, cookie.
 *
 * - En production derrière HTTPS : le cookie Secure est activé automatiquement
 *   si la requête est détectée comme HTTPS (voir is_https_request() dans helpers).
 * - SameSite=Lax convient à une appli métier ; Strict si tous les liens sont same-site.
 */
return [
    /** Déconnexion après X secondes sans requête HTTP (côté serveur). */
    'idle_timeout_seconds'   => 1800,
    /** 0 = cookie de session (fermeture du navigateur selon le client). */
    'cookie_lifetime'        => 0,
    /** Lax | Strict | None (None impose HTTPS + Secure). */
    'cookie_samesite'        => 'Lax',
    /**
     * Durée de conservation des fichiers de session côté serveur (nettoyage GC).
     * Doit être >= idle_timeout_seconds.
     */
    'gc_maxlifetime_seconds' => 3600,
];
