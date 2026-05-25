-- ============================================================
-- CAMWATER PRO — schema.sql
-- Script de création de la base de données relationnelle
-- Compatible MySQL 8.0+
--
-- Pour exécuter : mysql -u root -p < schema.sql
-- ============================================================

-- Créer la base si elle n'existe pas encore
CREATE DATABASE IF NOT EXISTS camwater_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Sélectionner la base
USE camwater_db;


-- ============================================================
-- TABLE : operateurs
-- Contient les utilisateurs internes de l'application
-- (ceux qui gèrent les abonnés et traitent les réclamations)
-- ============================================================
CREATE TABLE operateurs (
    id         INT UNSIGNED AUTO_INCREMENT,
    nom        VARCHAR(100) NOT NULL,
    prenom     VARCHAR(100) NOT NULL,
    login      VARCHAR(80)  NOT NULL,    -- identifiant de connexion (ex: email)
    password   VARCHAR(255) NOT NULL,    -- mot de passe haché avec bcrypt
    role       ENUM('admin', 'gestionnaire') NOT NULL DEFAULT 'gestionnaire',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Clé primaire
    CONSTRAINT pk_operateurs PRIMARY KEY (id),

    -- Le login doit être unique (deux opérateurs ne peuvent pas avoir le même)
    CONSTRAINT uq_operateurs_login UNIQUE (login)
);

-- Index pour accélérer la recherche par login (connexion fréquente)
CREATE INDEX idx_operateurs_login ON operateurs (login);


-- ============================================================
-- TABLE : abonnes
-- Contient les clients abonnés au service d'eau CAMWATER PRO
-- ============================================================
CREATE TABLE abonnes (
    id               INT UNSIGNED AUTO_INCREMENT,
    nom              VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100) NOT NULL,
    ville            ENUM('Yaoundé', 'Douala', 'Bafoussam', 'Garoua') NOT NULL,
    quartier         VARCHAR(150) NOT NULL,
    numero_compteur  VARCHAR(50)  NOT NULL,  -- numéro physique du compteur d'eau
    type_abonnement  ENUM('Domestique', 'Professionnel') NOT NULL,
    date_creation    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Clé primaire
    CONSTRAINT pk_abonnes PRIMARY KEY (id),

    -- Chaque compteur est unique dans toute la base
    CONSTRAINT uq_abonnes_compteur UNIQUE (numero_compteur)
);

-- Index pour les statistiques par ville et les filtres par type
CREATE INDEX idx_abonnes_ville           ON abonnes (ville);
CREATE INDEX idx_abonnes_type_abonnement ON abonnes (type_abonnement);


-- ============================================================
-- TABLE : factures
-- Contient les factures mensuelles générées pour chaque abonné
-- ============================================================
CREATE TABLE factures (
    id             INT UNSIGNED AUTO_INCREMENT,
    abonne_id      INT UNSIGNED NOT NULL,  -- référence vers l'abonné concerné
    consommation   INT UNSIGNED NOT NULL,  -- consommation en m³ (doit être > 0)
    montant_total  BIGINT UNSIGNED NOT NULL, -- montant en FCFA, arrondi à l'entier supérieur
    date_emission  DATE NOT NULL,           -- date à laquelle la facture a été émise
    statut         ENUM('Emise', 'Payée') NOT NULL DEFAULT 'Emise',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Clé primaire
    CONSTRAINT pk_factures PRIMARY KEY (id),

    -- Clé étrangère : si l'abonné est supprimé, ses factures sont supprimées aussi
    CONSTRAINT fk_factures_abonne
        FOREIGN KEY (abonne_id) REFERENCES abonnes (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- La consommation doit être strictement positive
    CONSTRAINT chk_consommation CHECK (consommation > 0),

    -- Le montant doit être positif
    CONSTRAINT chk_montant CHECK (montant_total > 0)
);

-- Index pour les requêtes fréquentes
CREATE INDEX idx_factures_abonne_id   ON factures (abonne_id);
CREATE INDEX idx_factures_date        ON factures (date_emission);   -- stats par mois
CREATE INDEX idx_factures_statut      ON factures (statut);          -- factures impayées


-- ============================================================
-- TABLE : reclamations
-- Contient les réclamations soumises par les abonnés
-- liées à une facture précise
-- ============================================================
CREATE TABLE reclamations (
    id              INT UNSIGNED AUTO_INCREMENT,
    facture_id      INT UNSIGNED NOT NULL,  -- la facture concernée par la réclamation
    description     TEXT NOT NULL,          -- texte de la réclamation rédigé par l'abonné
    statut          ENUM('En attente', 'En cours', 'Résolue') NOT NULL DEFAULT 'En attente',
    reponse         TEXT,                   -- réponse de l'opérateur (vide au départ)
    operateur_id    INT UNSIGNED,           -- opérateur qui traite la réclamation
    date_soumission TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Clé primaire
    CONSTRAINT pk_reclamations PRIMARY KEY (id),

    -- Si la facture est supprimée, la réclamation l'est aussi
    CONSTRAINT fk_reclamations_facture
        FOREIGN KEY (facture_id) REFERENCES factures (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Si l'opérateur est supprimé, on met NULL (la réclamation reste)
    CONSTRAINT fk_reclamations_operateur
        FOREIGN KEY (operateur_id) REFERENCES operateurs (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- Index pour les filtres fréquents
CREATE INDEX idx_reclamations_statut          ON reclamations (statut);
CREATE INDEX idx_reclamations_facture_id      ON reclamations (facture_id);
CREATE INDEX idx_reclamations_date_soumission ON reclamations (date_soumission);


-- ============================================================
-- VUE : vue_factures_impayees
-- Liste toutes les factures dont le statut est "Emise"
-- avec les coordonnées complètes de l'abonné concerné
-- Usage : SELECT * FROM vue_factures_impayees;
-- ============================================================
CREATE OR REPLACE VIEW vue_factures_impayees AS
    SELECT
        f.id                                AS facture_id,
        f.date_emission,
        f.consommation,
        f.montant_total,
        f.statut,
        a.id                                AS abonne_id,
        CONCAT(a.prenom, ' ', a.nom)        AS nom_complet,
        a.ville,
        a.quartier,
        a.numero_compteur,
        a.type_abonnement
    FROM factures f
    INNER JOIN abonnes a ON a.id = f.abonne_id
    WHERE f.statut = 'Emise'
    ORDER BY f.date_emission DESC;


-- ============================================================
-- TABLE : logs_activite
-- Enregistre toutes les actions importantes de l'application :
--   connexions, générations de factures, modifications d'abonnés
-- Remplace MongoDB : tout est stocké dans MySQL
-- ============================================================
CREATE TABLE logs_activite (
    id            INT UNSIGNED AUTO_INCREMENT,
    type_action   ENUM('connexion', 'generation_facture', 'modification_abonne') NOT NULL,
    operateur_id  INT UNSIGNED,   -- NULL si action système
    abonne_id     INT UNSIGNED,   -- NULL si non applicable (ex: connexion)
    timestamp     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    details       JSON,           -- données spécifiques à l'action
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT pk_logs PRIMARY KEY (id),

    CONSTRAINT fk_logs_operateur
        FOREIGN KEY (operateur_id) REFERENCES operateurs (id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_logs_abonne
        FOREIGN KEY (abonne_id) REFERENCES abonnes (id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE INDEX idx_logs_type_action  ON logs_activite (type_action);
CREATE INDEX idx_logs_operateur_id ON logs_activite (operateur_id);
CREATE INDEX idx_logs_timestamp    ON logs_activite (timestamp);
