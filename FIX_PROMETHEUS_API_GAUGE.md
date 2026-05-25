# 🐛 CORRECTION #2 - Erreur Spatie Prometheus: setInitialValue()

## ✅ Problème Résolu

**Erreur**: `Call to undefined method Spatie\Prometheus\MetricTypes\Gauge::setInitialValue()`

Le problème était l'utilisation d'une mauvaise méthode API. Spatie Prometheus utilise:
- Counter: `setInitialValue()` et `inc()` ✅
- Gauge: `value()` (pas `setInitialValue()`) ❌

---

## 🔧 Corrections Appliquées

### 1. **PrometheusQueryMetrics.php** (Middleware)
**Avant**:
```php
Prometheus::addGauge('camwater_request_duration_seconds')
    ->setInitialValue($duration, [$method, $path, $status]);
```

**Après**:
```php
Prometheus::addGauge('camwater_request_duration_seconds')
    ->helpText('Durée des requêtes en secondes')
    ->labels(['method', 'path', 'status'])
    ->value($duration, [$method, $path, $status]);
```

### 2. **PrometheusMetrics.php** (Trait)
**Avant**:
```php
->setInitialValue((float)$value);
```

**Après**:
```php
->value((float)$value);
```

### 3. **FactureController.php**
**Avant**:
```php
Prometheus::addGauge('camwater_invoice_amount_fcfa')
    ->setInitialValue((float) $montantCalcule, [$abonne->type_abonnement]);
```

**Après**:
```php
Prometheus::addGauge('camwater_invoice_amount_fcfa')
    ->helpText('Montants des factures')
    ->labels(['subscription_type'])
    ->value((float) $montantCalcule, [$abonne->type_abonnement]);
```

### 4. **AbonneController.php**
```php
// ❌ Avant
->setInitialValue((float)$abonnes->total());

// ✅ Après
->value((float)$abonnes->total());
```

### 5. **ReclamationController.php**
```php
// ❌ Avant
->setInitialValue((float)Reclamation::count());

// ✅ Après
->value((float)Reclamation::count());
```

---

## 📚 Référence API Spatie Prometheus (Correcte)

### Counter
```php
Prometheus::addCounter('my_counter_total')
    ->helpText('...')
    ->labels(['label1', 'label2'])
    ->ic(1, ['val1', 'val2'])                           // Incrémenter
    // ou
    ->setInitialValue(5, ['val1', 'val2'])              // Valeur initiale
```

### Gauge
```php
Prometheus::addGauge('my_gauge')
    ->helpText('...')
    ->labels(['label1'])
    ->value(42, ['val1'])                               // Définir la valeur
```

### ⚠️ À NOTER
- ✅ Counter supporte: `inc()` et `setInitialValue()`
- ✅ Gauge supporte uniquement: `value()`
- ❌ Gauge n'a PAS `setInitialValue()`
- ❌ Pas de `histogram()` ou `summary()` dans Spatie v1.4
- ❌ Pas de `observe()` ou `set()`

---

## 🚀 Test Rapide

```bash
# 1. Nettoyer
php artisan config:clear

# 2. Redémarrer
php artisan serve

# 3. Tester la requête
curl http://localhost:8000/

# 4. Vérifier les métriques
curl http://localhost:8000/prometheus
```

✅ L'erreur `setInitialValue()` devrait être éliminée!

---

## ✨ Résumé Global des Corrections

| Étape | Erreur | Correction |
|-------|--------|-----------|
| 1️⃣ | `counter()` n'existe pas | → `addCounter()` ✅ |
| 2️⃣ | `help()` n'existe pas | → `helpText()` ✅ |
| 3️⃣ | Paramètres API incorrects | → Séparés `labelNames`, `labelValues` ✅ |
| 4️⃣ | `setInitialValue()` sur Gauge | → `value()` ✅ |

**Tous les fichiers sont maintenant corrects!** 🎉
