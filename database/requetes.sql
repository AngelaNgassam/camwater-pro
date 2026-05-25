-- ============================================================
-- CAMWATER PRO — requetes.sql
-- Requêtes SQL de test et d'exploitation
-- À exécuter après schema.sql
-- ============================================================

USE camwater_db;


-- ============================================================
-- a. INSERT — 5 abonnés et 3 factures
-- ============================================================

-- Insertion de 5 abonnés :
-- • 3 Domestiques (Yaoundé, Douala, Bafoussam)
-- • 2 Professionnels (Yaoundé, Douala)
INSERT INTO abonnes (nom, prenom, ville, quartier, numero_compteur, type_abonnement) VALUES
    ('MBARGA',  'Jean',  'Yaoundé',   'Bastos',        'YDE-DOM-001', 'Domestique'),
    ('BIYA',    'Marie', 'Douala',    'Bonanjo',        'DLA-DOM-002', 'Domestique'),
    ('NKOMO',   'Paul',  'Bafoussam', 'Djeleng',        'BFS-DOM-003', 'Domestique'),
    ('NGUINI',  'Roger', 'Yaoundé',   'Centre-Ville',   'YDE-PRO-001', 'Professionnel'),
    ('FOTSO',   'Alice', 'Douala',    'Akwa',           'DLA-PRO-002', 'Professionnel');

-- Insertion de 3 factures avec les montants pré-calculés :
--   Facture 1 : MBARGA, 8 m³ Domestique  → 8 × 350 = 2 800 FCFA
--   Facture 2 : BIYA,  15 m³ Domestique  → (10×350) + (5×550) = 6 250 FCFA
--   Facture 3 : NGUINI,25 m³ Professionnel → 8500 + (25×950) = 32 250 FCFA
INSERT INTO factures (abonne_id, consommation, montant_total, date_emission, statut) VALUES
    (1,  8, 2800,  CURDATE(), 'Emise'),
    (2, 15, 6250,  CURDATE(), 'Emise'),
    (3, 25, 32250, CURDATE(), 'Emise');


-- ============================================================
-- b. SELECT avec jointure
-- Pour chaque facture : nom complet, ville, consommation, montant
-- Trié par date d'émission du plus récent au plus ancien
-- ============================================================
SELECT
    f.id                            AS facture_id,
    CONCAT(a.prenom, ' ', a.nom)    AS nom_complet,
    a.ville,
    f.consommation                  AS consommation_m3,
    f.montant_total                 AS montant_fcfa,
    f.date_emission,
    f.statut
FROM factures f
INNER JOIN abonnes a ON a.id = f.abonne_id
ORDER BY f.date_emission DESC;


-- ============================================================
-- c. SELECT avec agrégation
-- Total des montants facturés par ville pour le mois en cours
-- ============================================================
SELECT
    a.ville,
    COUNT(f.id)          AS nombre_factures,
    SUM(f.montant_total) AS total_montant_fcfa
FROM factures f
INNER JOIN abonnes a ON a.id = f.abonne_id
WHERE YEAR(f.date_emission)  = YEAR(CURDATE())
  AND MONTH(f.date_emission) = MONTH(CURDATE())
GROUP BY a.ville
ORDER BY total_montant_fcfa DESC;


-- ============================================================
-- d. UPDATE avec sous-requête
-- Marquer comme "Payée" la facture de l'abonné YDE-DOM-001
-- La sous-requête retrouve l'abonne_id à partir du numéro de compteur
-- ============================================================
UPDATE factures
SET    statut     = 'Payée',
       updated_at = NOW()
WHERE  statut     = 'Emise'
  AND  abonne_id  = (
           SELECT id
           FROM   abonnes
           WHERE  numero_compteur = 'YDE-DOM-001'
       );


-- ============================================================
-- e. DELETE avec condition
-- Supprimer les réclamations "Résolue" de plus de 6 mois
-- pour libérer de l'espace en base de données
-- ============================================================
DELETE FROM reclamations
WHERE  statut          = 'Résolue'
  AND  date_soumission < DATE_SUB(NOW(), INTERVAL 6 MONTH);


-- ============================================================
-- f. Vue des factures impayées (déjà créée dans schema.sql)
-- Afficher son contenu
-- ============================================================
SELECT * FROM vue_factures_impayees;


-- ============================================================
-- Question 5 — Créer un utilisateur avec privilèges minimaux
-- L'utilisateur camwater_app ne peut que lire et écrire les données
-- Il ne peut PAS créer/supprimer des tables ni gérer les utilisateurs
-- ============================================================

-- Créer l'utilisateur
CREATE USER IF NOT EXISTS 'camwater_app'@'localhost'
    IDENTIFIED BY 'MotDePasse123!';

-- Accorder uniquement SELECT, INSERT, UPDATE, DELETE sur camwater_db
GRANT SELECT, INSERT, UPDATE, DELETE
    ON camwater_db.*
    TO 'camwater_app'@'localhost';

-- Appliquer les changements immédiatement
FLUSH PRIVILEGES;

-- Vérifier les droits accordés
SHOW GRANTS FOR 'camwater_app'@'localhost';


-- ============================================================
-- PARTIE 4 (adaptée MySQL) — Logs d'activité
-- Toutes les opérations de logs directement en SQL/MySQL
-- ============================================================

-- a. Insérer 3 logs de types différents
INSERT INTO logs_activite (type_action, operateur_id, abonne_id, details) VALUES

    -- Log 1 : connexion d'un opérateur
    ('connexion', 1, NULL,
     JSON_OBJECT(
         'adresse_ip', '192.168.1.45',
         'navigateur', 'Chrome 124',
         'statut',     'succes',
         'message',    'Connexion reussie au tableau de bord'
     )),

    -- Log 2 : génération d'une facture pour l'abonné #1
    ('generation_facture', 1, 1,
     JSON_OBJECT(
         'facture_id',         1,
         'consommation_m3',    8,
         'montant_total_fcfa', 2800,
         'type_abonnement',    'Domestique',
         'ville',              'Yaoundé'
     )),

    -- Log 3 : modification de l'abonné #2
    ('modification_abonne', 1, 2,
     JSON_OBJECT(
         'champs_modifies',   JSON_ARRAY('quartier'),
         'ancienne_valeur',   'Bonanjo',
         'nouvelle_valeur',   'Akwa',
         'raison',            'Déménagement'
     ));


-- b. Lister tous les logs d'un opérateur sur les 7 derniers jours
--    Trié du plus récent au plus ancien
SELECT
    l.id,
    l.type_action,
    l.timestamp,
    CONCAT(o.prenom, ' ', o.nom) AS operateur,
    CONCAT(a.prenom, ' ', a.nom) AS abonne,
    l.details
FROM logs_activite l
LEFT JOIN operateurs o ON o.id = l.operateur_id
LEFT JOIN abonnes    a ON a.id = l.abonne_id
WHERE l.operateur_id = 1
  AND l.timestamp   >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY l.timestamp DESC;
