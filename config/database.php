<?php

// ============================================================
// config/database.php
//
// Ce fichier configure la connexion à la base de données MySQL.
// Les valeurs viennent du fichier .env via la fonction env().
// Exemple : env('DB_HOST') lit la ligne DB_HOST= dans .env
// ============================================================

return [

    // Connexion utilisée par défaut dans l'application
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        // ── Connexion MySQL ──────────────────────────────────────────────────
        // Utilisée pour toutes les données :
        // abonnés, factures, réclamations, opérateurs, logs d'activité
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST',     '127.0.0.1'),
            'port'      => env('DB_PORT',     '3306'),
            'database'  => env('DB_DATABASE', 'camwater_db'),
            'username'  => env('DB_USERNAME', 'camwater_app'),
            'password'  => env('DB_PASSWORD', 'MotDePasse123!'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ],

    ],

];
