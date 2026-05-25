# =================================================================
# Script de validation du Monitoring CAMWATER PRO (Windows PowerShell)
# =================================================================
# Ce script teste que le monitoring est correctement configuré
# Exécution: .\test_monitoring.ps1
# =================================================================

$ErrorActionPreference = "Continue"

Write-Host "🧪 Démarrage des tests de monitoring..." -ForegroundColor Cyan
Write-Host ""

# Compteur de tests
$testsPass = 0
$testsFail = 0

# Fonction pour tester un endpoint
function Test-Endpoint {
    param(
        [string]$description,
        [string]$url,
        [string]$expectedCode
    )
    
    Write-Host -NoNewline "Test: $description... "
    
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 2 -ErrorAction SilentlyContinue
        $statusCode = $response.StatusCode.ToString()
    } catch {
        $statusCode = "000"
    }
    
    if ($statusCode -eq $expectedCode) {
        Write-Host "✓ OK (HTTP $statusCode)" -ForegroundColor Green
        return $true
    } else {
        Write-Host "✗ FAILED (HTTP $statusCode, attendu $expectedCode)" -ForegroundColor Red
        return $false
    }
}

# Fonction pour vérifier l'existence de fichiers
function Test-FileExists {
    param([string]$file)
    
    Write-Host -NoNewline "Fichier existe: $file... "
    
    if (Test-Path $file) {
        Write-Host "✓ OK" -ForegroundColor Green
        return $true
    } else {
        Write-Host "✗ MISSING" -ForegroundColor Red
        return $false
    }
}

# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
Write-Host "1️⃣ Vérification des fichiers de configuration..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow

if (Test-FileExists "config/prometheus.php") { $testsPass++ } else { $testsFail++ }
if (Test-FileExists "docker/prometheus/prometheus.yml") { $testsPass++ } else { $testsFail++ }
if (Test-FileExists "docker/prometheus/alert_rules.yml") { $testsPass++ } else { $testsFail++ }
if (Test-FileExists "app/Http/Middleware/PrometheusQueryMetrics.php") { $testsPass++ } else { $testsFail++ }
if (Test-FileExists "app/Traits/PrometheusMetrics.php") { $testsPass++ } else { $testsFail++ }

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
Write-Host "2️⃣ Vérification des services Docker..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow

try {
    $containers = docker ps 2>$null
    
    if ($containers -match "prometheus") {
        Write-Host "Prometheus service en cours d'exécution... " -NoNewline
        Write-Host "✓ OK" -ForegroundColor Green
        $testsPass++
    } else {
        Write-Host "Prometheus service en cours d'exécution... " -NoNewline
        Write-Host "⚠ NOT RUNNING (normal si Docker non démarré)" -ForegroundColor Yellow
    }
    
    if ($containers -match "grafana") {
        Write-Host "Grafana service en cours d'exécution... " -NoNewline
        Write-Host "✓ OK" -ForegroundColor Green
        $testsPass++
    } else {
        Write-Host "Grafana service en cours d'exécution... " -NoNewline
        Write-Host "⚠ NOT RUNNING (normal si Docker non démarré)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Impossible d'accéder à Docker" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
Write-Host "3️⃣ Vérification des endpoints (si Docker est lancé)..." -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow

# Test Laravel app
try {
    $response = Invoke-WebRequest -Uri "http://localhost:9000/up" -UseBasicParsing -TimeoutSec 1 -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        if (Test-Endpoint "Route /prometheus (métriques)" "http://localhost:9000/prometheus" "200") { $testsPass++ } else { $testsFail++ }
    }
} catch {
    Write-Host "⚠ L'application Laravel n'est pas accessible sur localhost:9000" -ForegroundColor Yellow
    Write-Host "  Assurez-vous que Docker est lancé: docker-compose up -d" -ForegroundColor Gray
}

# Test Prometheus
try {
    Test-Endpoint "Prometheus API" "http://localhost:9090/api/v1/rules" "200" | Out-Null
} catch {
    Write-Host "⚠ Prometheus n'est pas accessible sur localhost:9090" -ForegroundColor Yellow
}

# Test Grafana
try {
    Test-Endpoint "Grafana API" "http://localhost:3000/api/health" "200" | Out-Null
} catch {
    Write-Host "⚠ Grafana n'est pas accessible sur localhost:3000" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
Write-Host "📊 Résumé des tests" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow

Write-Host "Tests réussis: " -NoNewline
Write-Host "$testsPass" -ForegroundColor Green
Write-Host "Tests échoués: " -NoNewline
Write-Host "$testsFail" -ForegroundColor Red

if ($testsFail -eq 0) {
    Write-Host ""
    Write-Host "✓ Tous les tests sont passés!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Prochaines étapes:" -ForegroundColor Cyan
    Write-Host "  1. Redémarrer à partir de zéro:"
    Write-Host "     docker-compose down"
    Write-Host "     docker-compose up -d --build"
    Write-Host ""
    Write-Host "  2. Accéder à Prometheus:"
    Write-Host "     http://localhost:9090"
    Write-Host ""
    Write-Host "  3. Accéder à Grafana:"
    Write-Host "     http://localhost:3000"
    Write-Host "     Défaut: admin / admin123"
    Write-Host ""
    Write-Host "  4. Générer du trafic pour voir les métriques:"
    Write-Host "     curl -X POST http://localhost:9000/api/auth/login \"
    Write-Host "       -H ""Content-Type: application/json"" \"
    Write-Host "       -d '{""login"":""test"",""password"":""test""}'"
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "✗ Certains tests ont échoué" -ForegroundColor Red
    Write-Host ""
    Write-Host "🔍 Vérifications à faire:" -ForegroundColor Yellow
    Write-Host "  - Les fichiers de configuration existent"
    Write-Host "  - Docker Compose est lancé"
    Write-Host "  - Les ports 9000, 9090, 3000 ne sont pas utilisés"
}
