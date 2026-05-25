#!/bin/bash

# =================================================================
# Script de validation du Monitoring CAMWATER PRO
# =================================================================
# Ce script teste que le monitoring est correctement configuré
# Exécution: bash test_monitoring.sh
# =================================================================

set -e

echo "🧪 Démarrage des tests de monitoring..."
echo ""

# Couleurs pour les résultats
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # Pas de couleur

# Compteur de tests
TESTS_PASSED=0
TESTS_FAILED=0

# Fonction pour tester un endpoint
test_endpoint() {
    local description=$1
    local url=$2
    local expected_code=$3
    
    echo -n "Test: $description... "
    
    response_code=$(curl -s -w "%{http_code}" -o /dev/null "$url" 2>/dev/null || echo "000")
    
    if [ "$response_code" = "$expected_code" ]; then
        echo -e "${GREEN}✓ OK${NC} (HTTP $response_code)"
        ((TESTS_PASSED++))
    else
        echo -e "${RED}✗ FAILED${NC} (HTTP $response_code, attendu $expected_code)"
        ((TESTS_FAILED++))
    fi
}

# Fonction pour vérifier la présence de fichiers
test_file_exists() {
    local file=$1
    echo -n "Fichier existe: $file... "
    
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ OK${NC}"
        ((TESTS_PASSED++))
    else
        echo -e "${RED}✗ MISSING${NC}"
        ((TESTS_FAILED++))
    fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣ Vérification des fichiers de configuration..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

test_file_exists "config/prometheus.php"
test_file_exists "docker/prometheus/prometheus.yml"
test_file_exists "docker/prometheus/alert_rules.yml"
test_file_exists "app/Http/Middleware/PrometheusQueryMetrics.php"
test_file_exists "app/Traits/PrometheusMetrics.php"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣ Vérification des services Docker..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo -n "Prometheus service en cours d'exécution... "
if docker ps | grep -q prometheus; then
    echo -e "${GREEN}✓ OK${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ NOT RUNNING${NC} (normal si Docker non démarré)"
fi

echo -n "Grafana service en cours d'exécution... "
if docker ps | grep -q grafana; then
    echo -e "${GREEN}✓ OK${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ NOT RUNNING${NC} (normal si Docker non démarré)"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣ Vérification des endpoints (si Docker est lancé)..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if curl -s -f http://localhost:9000/up > /dev/null 2>&1; then
    test_endpoint "Route /prometheus (métriques)" "http://localhost:9000/prometheus" "200"
    test_endpoint "Route /metrics (alias)" "http://localhost:9000/metrics" "200" || true
else
    echo -e "${YELLOW}⚠ L'application Laravel n'est pas accessible sur localhost:9000${NC}"
    echo "  Assurez-vous que Docker est lancé: docker-compose up -d"
fi

if curl -s -f http://localhost:9090/metrics > /dev/null 2>&1; then
    test_endpoint "Prometheus API" "http://localhost:9090/api/v1/rules" "200"
    test_endpoint "Prometheus targets" "http://localhost:9090/api/v1/targets" "200"
else
    echo -e "${YELLOW}⚠ Prometheus n'est pas accessible sur localhost:9090${NC}"
fi

if curl -s http://localhost:3000/api/health > /dev/null 2>&1; then
    test_endpoint "Grafana API" "http://localhost:3000/api/health" "200"
else
    echo -e "${YELLOW}⚠ Grafana n'est pas accessible sur localhost:3000${NC}"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Résumé des tests"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo -e "Tests réussis: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests échoués: ${RED}$TESTS_FAILED${NC}"

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "\n${GREEN}✓ Tous les tests sont passés!${NC}"
    echo ""
    echo "📋 Prochaines étapes:"
    echo "  1. Redémarrer à partir de zéro:"
    echo "     docker-compose down"
    echo "     docker-compose up -d --build"
    echo ""
    echo "  2. Accéder à Prometheus:"
    echo "     http://localhost:9090"
    echo ""
    echo "  3. Accéder à Grafana:"
    echo "     http://localhost:3000"
    echo "     Défaut: admin / admin123"
    echo ""
    echo "  4. Générer du trafic pour voir les métriques:"
    echo "     curl -X POST http://localhost:9000/api/auth/login \\"
    echo "       -H \"Content-Type: application/json\" \\"
    echo "       -d '{\"login\":\"test\",\"password\":\"test\"}'"
    exit 0
else
    echo -e "\n${RED}✗ Certains tests ont échoué${NC}"
    echo ""
    echo "🔍 Vérifications à faire:"
    echo "  - Les fichiers de configuration existent"
    echo "  - Docker Compose est lancé"
    echo "  - Les ports 9000, 9090, 3000 ne sont pas utilisés"
    exit 1
fi
