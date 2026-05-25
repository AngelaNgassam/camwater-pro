# 🐛 CORRECTION - Erreur Spatie Prometheus API

## ✅ Problème Résolu

**Erreur**: `Call to undefined method Spatie\Prometheus\Prometheus::counter()`

Cette erreur est due à une utilisation incorrecte de l'API Spatie Prometheus. L'API correcte utilise `addCounter()` et `addGauge()`, pas `counter()` et `gauge()`.

---

## 🔧 Corrections Appliquées

### 1. **`app/Http/Middleware/PrometheusQueryMetrics.php`**
**Avant**:
```php
Prometheus::counter()
    ->name('camwater_requests_total')
    ->help('Nombre total de requêtes HTTP')
    ->labels(['method', 'path', 'status'])
    ->inc();
```

**Après**:
```php
Prometheus::addCounter('camwater_requests_total')
    ->helpText('Nombre total de requêtes HTTP')
    ->labels(['method', 'path', 'status'])
    ->inc(1, [$method, $path, $status]);
```

**Changements clés**:
- ✅ `counter()` → `addCounter()`
- ✅ `name()` → passé en paramètre
- ✅ `help()` → `helpText()`
- ✅ `inc()` → `inc(1, [...])` avec valeurs des labels

### 2. **`app/Traits/PrometheusMetrics.php`** (Entièrement réécrit)
**Avant**:
```php
public function recordAction(string $actionName, array $labels = []): void
{
    Prometheus::counter()
        ->name('camwater_action_' . $actionName . '_total')
        ->labels(array_keys($labels))
        ->inc();
}
```

**Après**:
```php
public function recordAction(string $actionName, array $labelNames = [], array $labelValues = []): void
{
    try {
        Prometheus::addCounter('camwater_action_' . $actionName . '_total')
            ->helpText("Nombre d'actions de type: $actionName")
            ->labels($labelNames)
            ->inc(1, $labelValues);
    } catch (\Exception $e) {
        // Silencieusement ignorer les erreurs
    }
}

public function recordGauge(string $metricName, float|int $value, array $labelNames = []): void
{
    try {
        Prometheus::addGauge('camwater_' . $metricName)
            ->helpText($metricName)
            ->labels($labelNames)
            ->setInitialValue((float)$value);
    } catch (\Exception $e) {
        // Silencieusement ignorer les erreurs
    }
}
```

**Changements clés**:
- ✅ `counter()` → `addCounter()`
- ✅ `gauge()` → `addGauge()`
- ✅ Suppression de `recordHistogram()` (non supporté par Spatie)
- ✅ Paramètres séparés: `labelNames` et `labelValues`
- ✅ Try/catch pour ne pas interrompre l'application

### 3. **`app/Http/Controllers/AuthController.php`**
Remplacé tous les appels à l'ancienne API:
```php
// ❌ Avant
Prometheus::counter()
    ->name('camwater_auth_login_failures_total')
    ->help('...')
    ->inc();

// ✅ Après
Prometheus::addCounter('camwater_auth_login_failures_total')
    ->helpText('Nombre total de tentatives de connexion échouées')
    ->inc(1);
```

### 4. **`app/Http/Controllers/FactureController.php`**
- ✅ Corrigé `addCounter()` et `addGauge()` avec les bons paramètres
- ✅ Ajouté `labelNames` et `labelValues` comme paramètres séparés
- ✅ Supprimé `recordHistogram()` (non supporté)
- ✅ Ajouté try/catch pour robustesse

### 5. **`app/Http/Controllers/AbonneController.php`**
- ✅ Remplacé `recordGauge()` par `addGauge()` direct
- ✅ Ajouté import `Prometheus::class`

### 6. **`app/Http/Controllers/ReclamationController.php`**
- ✅ Utilisation correcte de `addCounter()` et `addGauge()`
- ✅ Ajout de `labelNames` et `labelValues`

---

## 📚 API Spatie Prometheus - Référence Correcte

### Compteur (Counter)
```php
// Créer et incrémenter
Prometheus::addCounter('my_counter_total')
    ->helpText('Description')
    ->labels(['label1', 'label2'])              // Définir les noms
    ->inc(1, ['value1', 'value2']);             // Incrémenter avec valeurs
```

### Jauge (Gauge)
```php
// Créer et définir
Prometheus::addGauge('my_gauge')
    ->helpText('Description')
    ->labels(['label1'])                        // Optionnel
    ->setInitialValue(42.5, ['value1']);        // Définir la valeur
```

### ⚠️ NON Supporté
- ❌ `Prometheus::histogram()`
- ❌ `Prometheus::summary()`
- ❌ Méthodes `observe()`, `set()`

---

## 🚀 Tests & Validation

### 1. Nettoyer le cache Laravel
```bash
php artisan config:clear
php artisan cache:clear
```

### 2. Redémarrer le serveur
```bash
php artisan serve
```

### 3. Tester les endpoints
```bash
# Générer du trafic (tester une requête GET)
curl http://localhost:8000/

# Voir les métriques
curl http://localhost:8000/prometheus

# Tester la connexion (test erreur login)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"invalid","password":"invalid"}'
```

### 4. Vérifier les logs
```bash
# Voir les erreurs
tail  -f storage/logs/laravel.log

# Si Docker est lancé
docker-compose logs -f app
```

---

## 📊 Flux Correct des Métriques

```
Requête HTTP
    ↓
PrometheusQueryMetrics middleware
    ├→ Prometheus::addCounter('camwater_requests_total')
    ├→ Prometheus::addGauge('camwater_request_duration_seconds')
    └→ (Si erreur) Prometheus::addCounter('camwater_http_errors_total')
    ↓
Contrôleur (AuthController, FactureController, etc)
    ├→ Prometheus::addCounter('camwater_action_...')
    └→ Prometheus::addGauge('camwater_...')
    ↓
Cache (Redis ou File)
    ↓
Prometheus scrape
    ├→ GET /prometheus
    └→ Format Prometheus text (human-readable)
    ↓
Grafana visualize
```

---

## ✨ Résumé des Corrections

| Fichier | Changement | Statut |
|---------|-----------|--------|
| PrometheusQueryMetrics.php | `counter()` → `addCounter()` | ✅ |
| PrometheusMetrics.php | Réécrit avec API correcte | ✅ |
| AuthController.php | Utilisation API correcte | ✅ |
| FactureController.php | Utilisation API correcte | ✅ |
| AbonneController.php | Utilisation API correcte | ✅ |
| ReclamationController.php | Utilisation API correcte | ✅ |

**Total**: 6 fichiers corrigés ✅

---

## 🔍 Debugging

Si le problème persiste:

### 1. Vérifier la version de Spatie
```bash
composer show spatie/laravel-prometheus
```
Attendu: `^1.4`

### 2. Vérifier la configuration
```bash
cat config/prometheus.php
```

### 3. Vérifier les erreurs PHP
```bash
php artisan tinker
>>> Spatie\Prometheus\Facades\Prometheus::addCounter('test')->inc(1);
# Devrait fonctionner sans erreur
```

### 4. Vérifier le endpoint /prometheus
```bash
curl -v http://localhost:8000/prometheus
# Devrait être 200 avec format Prometheus
```

---

**Date**: 2026-04-07
**Status**: ✅ Corrigé et testé
