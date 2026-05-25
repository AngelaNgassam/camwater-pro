# CAMWATER PRO — Guide d'installation et d'utilisation

Application back-end **Laravel + MySQL uniquement** pour la gestion
des abonnements, de la facturation et des réclamations de CAMWATER PRO.

---

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 8.0 ou supérieur

---

## Installation pas à pas

### 1. Créer le projet Laravel

```bash
composer create-project laravel/laravel camwater-pro
cd camwater-pro
```

### 2. Copier les fichiers du projet

```
.env                                       → .env (racine)
config/database.php                        → config/database.php
app/Models/Abonne.php                      → app/Models/
app/Models/Facture.php                     → app/Models/
app/Models/Reclamation.php                 → app/Models/
app/Models/Operateur.php                   → app/Models/
app/Models/LogActivite.php                 → app/Models/
app/Http/Controllers/AbonneController.php  → app/Http/Controllers/
app/Http/Controllers/FactureController.php → app/Http/Controllers/
app/Services/LogService.php                → app/Services/  (créer le dossier)
routes/api.php                             → routes/api.php
database/migrations/*.php                  → database/migrations/
database/seeders/DatabaseSeeder.php        → database/seeders/
database/schema.sql                        → database/  (référence SQL)
database/requetes.sql                      → database/  (requêtes SQL)
```

### 3. Installer JWT pour l'authentification

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### 4. Configurer le fichier .env

```bash
cp .env.example .env
php artisan key:generate
```

Modifier les valeurs dans `.env` :
```
DB_DATABASE=camwater_db
DB_USERNAME=camwater_app
DB_PASSWORD=MotDePasse123!
```

### 5. Créer la base et les tables

```bash
# Créer la base MySQL d'abord
mysql -u root -p -e "CREATE DATABASE camwater_db CHARACTER SET utf8mb4;"

# Option A : migrations Laravel (recommandé)
php artisan migrate

# Option B : script SQL direct
mysql -u root -p camwater_db < database/schema.sql
```

### 6. Injecter les données de test

```bash
php artisan db:seed
```

### 7. Démarrer le serveur

```bash
php artisan serve --port=8000
# API accessible sur : http://localhost:8000/api
```

---

## Structure de la base de données (MySQL)

| Table           | Description                                      |
|-----------------|--------------------------------------------------|
| operateurs      | Utilisateurs internes (admin / gestionnaire)     |
| abonnes         | Clients abonnés au service d'eau                 |
| factures        | Factures mensuelles générées                     |
| reclamations    | Réclamations des abonnés sur leurs factures      |
| logs_activite   | Historique des actions (connexions, modifs, ...) |

---

## Endpoints API

| Méthode | URL                   | Description                         |
|---------|-----------------------|-------------------------------------|
| POST    | /api/auth/login       | Connexion opérateur (retourne JWT)  |
| GET     | /api/abonnes          | Lister tous les abonnés             |
| POST    | /api/abonnes          | Créer un abonné                     |
| GET     | /api/abonnes/{id}     | Afficher un abonné                  |
| PUT     | /api/abonnes/{id}     | Modifier un abonné                  |
| DELETE  | /api/abonnes/{id}     | Supprimer un abonné                 |
| POST    | /api/factures/generer | Générer une facture                 |
| GET     | /api/factures/{id}    | Consulter une facture               |

---

## Calcul tarifaire

| Type          | Tranche                | Tarif               |
|---------------|------------------------|---------------------|
| Domestique    | 0 – 10 m³              | 350 FCFA/m³         |
| Domestique    | 11 – 20 m³             | 550 FCFA/m³         |
| Domestique    | Au-delà de 20 m³       | 780 FCFA/m³         |
| Professionnel | Forfait + consommation | 8 500 + 950 FCFA/m³ |

Exemple Domestique 15 m³ : (10 × 350) + (5 × 550) = **6 250 FCFA**
Exemple Professionnel 25 m³ : 8 500 + (25 × 950) = **32 250 FCFA**

---

## Exemples avec curl

```bash
# 1. Se connecter
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"admin@camwater.cm","password":"Admin123!"}'

# 2. Créer un abonné
curl -X POST http://localhost:8000/api/abonnes \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "KAMGA", "prenom": "Serge",
    "ville": "Douala", "quartier": "Deido",
    "numero_compteur": "DLA-DOM-010",
    "type_abonnement": "Domestique"
  }'

# 3. Générer une facture (18 m³ pour l'abonné #1)
curl -X POST http://localhost:8000/api/factures/generer \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"abonne_id": 1, "consommation": 18}'
```
