<?php

namespace App\Traits;

use Spatie\Prometheus\Facades\Prometheus;

trait PrometheusMetrics
{
    /**
     * Enregistre une action métier avec un compteur.
     * 
     * @param string $actionName Nom de l'action (ex: 'login', 'invoice_created')
     * @param array $labelNames Noms des libellés
     * @param array $labelValues Valeurs des libellés
     */
    public function recordAction(string $actionName, array $labelNames = [], array $labelValues = []): void
    {
        try {
            Prometheus::addCounter('camwater_action_' . $actionName . '_total')
                ->helpText("Nombre d'actions de type: $actionName")
                ->labels($labelNames)
                ->inc(1, $labelValues);
        } catch (\Exception $e) {
            // Silencieusement ignorer les erreurs de métriques pour ne pas interrompre l'app
        }
    }

    /**
     * Enregistre une métrique de jauge (valeur instantanée).
     * 
     * @param string $metricName Nom de la métrique
     * @param float|int $value Valeur à enregistrer
     * @param array $labelNames Noms des libellés
     */
    public function recordGauge(string $metricName, float|int $value, array $labelNames = []): void
    {
        try {
            Prometheus::addGauge('camwater_' . $metricName)
                ->helpText($metricName)
                ->labels($labelNames)
                ->value((float)$value);
        } catch (\Exception $e) {
            // Silencieusement ignorer les erreurs de métriques
        }
    }
}
