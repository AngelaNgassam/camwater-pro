# 📊 MONITORING - GUIDE DE MISE EN PLACE

## ✅ Changements Effectués

### 1. **Configuration Prometheus**
- ✅ Créé `config/prometheus.php` - Configure Spatie Prometheus
- ✅ Créé `docker/prometheus/alert_rules.yml` - Règles d'alerte
- ✅ Mis à jour `docker/prometheus/prometheus.yml` - Intégration des alertes
- ✅ Mis à jour `docker-compose.yml` - Lien alert_rules.yml

### 2. **Instrumentations de Code**
- ✅ Créé `app/Traits/PrometheusMetrics.php` - Trait pour métriques failes
- ✅ Créé `app/Http/Middleware/PrometheusQueryMetrics.php` - Middleware global
- ✅ Mis à jour `bootstrap/app.php` - Enregistrement du middleware
- ✅ Instrumenté `app/Http/Controllers/AuthController.php`
  - Compteur pour connexions réussies/échouées
- ✅ Instrumenté `app/Http/Controllers/FactureController.php`
  - Compteurs de factures générées
  - Histogrammes de montants et consommations
  - Gauge du montant moyen

### 3. **Métriques Collectées**

#### Métriques de Requêtes Générales
- `camwater_requests_total` (Compteur) - Total requêtes HTTP
- `camwater_request_duration_seconds` (Histogramme) - Temps de réponse
- `camwater_http_errors_total` (Compteur) - Erreurs HTTP

#### Métriques d'Authentification
- `camwater_action_login_success_total` (Compteur) - Connexions réussies
- `camwater_auth_login_failures_total` (Compteur) - Tentatives échouées

#### Métriques de Facturation
- `camwater_action_invoice_created_total` (Compteur) - Factures créées
- `camwater_invoice_amount_fcfa` (Histogramme) - Montants facturés
- `camwater_water_consumption_m3` (Histogramme) - Consommations
- `camwater_avg_invoice_amount_fcfa` (Gauge) - Montant moyen
- `camwater_invoice_generation_errors_total` (Compteur) - Erreurs de génération

### 4. **Alertes Configurées**

#### Application
- 🟡 `HighHttpErrorRate` - Taux d'erreur HTTP > 10% (5min)
- 🟡 `SlowApiResponse` - P95 temps réponse > 1s
- 🔴 `LoginFailuresSpike` - Tentatives échouées > 0.5/sec
- 🟡 `InvoiceGenerationErrors` - Erreurs factures > 5 (5min)
- ℹ️ `NoInvoicesGenerated` - Aucune facture (1h)

#### Système
- 🟡 `HighCpuUsage` - CPU > 80%
- 🟡 `HighMemoryUsage` - Mémoire > 85%
- 🔴 `DiskSpaceLow` - Disque < 10%
- 🔴 `PrometheusDown` - Prometheus inaccessible

---

## 🚀 Comment Démarrer

### 1. Vérifier la Configuration
```bash
# Vérifier que Prometheus est bien configuré
curl http://localhost:9090/metrics
curl http://localhost:9090/api/v1/rules
```

### 2. Vérifier les Métriques de l'App Laravel
```bash
curl http://localhost:9000/prometheus
# ou via Prometheus UI:
# http://localhost:9090
```

### 3. Configurer Grafana
1. Accéder à Grafana: `http://localhost:3000`
2. Identifiants par défaut: `admin/admin123`
3. Ajouter Prometheus comme datasource:
   - URL: `http://prometheus:9090`
4. Importer/créer des dashboards

### 4. Tester les Métriques
```bash
# Générer du trafic
curl -X POST http://localhost:9000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"operateur","password":"pass"}'

# Voir les métriques changer
curl http://localhost:9000/prometheus
```

---

## 📈 Dashboards Recommandés à Créer

### Dashboard 1: Vue d'ensemble
- Taux de requêtes par seconde
- P95 temps de réponse
- Erreurs des 5 dernières minutes
- CPU/Mémoire du système

### Dashboard 2: API Métier
- Connexions/déconnexions (timeline)
- Tentatives de connexion échouées
- Factures générées par heure
- Montant moyen des factures
- Consommation d'eau moyenne

### Dashboard 3: Alertes
- Status des alertes (firing/resolved)
- Incidents par sévérité
- Historique des alertes

---

## 🔧 Commandes Docker Utiles

```bash
# Redémarrer avec monitoring actif
docker-compose up -d

# Vérifier les logs de Prometheus
docker-compose logs prometheus

# Accéder à la console Prometheus
docker exec -it prometheus /bin/bash
```

---

## 📝 Notes Importantes

1. **Route `/prometheus`** est automatiquement enregistrée par Spatie Prometheus
2. **Accessible via**: `http://app:80/prometheus` ou `http://localhost:9000/prometheus`
3. **Cache des métriques**: Actuellement en `'cache' => null` (en mémoire)
   - Pour production, utiliser `'cache' => 'redis'` ou `'cache' => 'file'`
4. **Sécurité**: Ajouter les IPs autorisées dans `config/prometheus.php` → `'allowed_ips'`

---

## 🎯 Prochaines Étapes Optionnelles

1. **ELK/Loki** - Centraliser les logs (au-delà simple Prometheus)
2. **OpenTelemetry** - Traçage distribué
3. **Alertmanager** - Routing avancé des alertes
4. **Custom collectors** - Métriques spécifiques métier

---

Generated: 2026-04-07
