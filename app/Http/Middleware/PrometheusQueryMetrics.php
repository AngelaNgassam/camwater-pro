<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Prometheus\Facades\Prometheus;
use Symfony\Component\HttpFoundation\Response;

class PrometheusQueryMetrics
{
    /**
     * Enregistre les métriques Prometheus pour chaque requête.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);
        $duration = microtime(true) - $startTime;
        
        $method = $request->getMethod();
        $path = $request->path();
        $status = (string)$response->getStatusCode();

        // Enregistrer les requêtes totales (Counter)
        try {
            Prometheus::addCounter('camwater_requests_total')
                ->helpText('Nombre total de requêtes HTTP')
                ->labels(['method', 'path', 'status'])
                ->inc(1, [$method, $path, $status]);
        } catch (\Exception $e) {
            //
        }

        // Enregistrer la durée des requêtes (Gauge)
        try {
            Prometheus::addGauge('camwater_request_duration_seconds')
                ->helpText('Durée des requêtes en secondes')
                ->labels(['method', 'path', 'status'])
                ->value($duration, [$method, $path, $status]);
        } catch (\Exception $e) {
            //
        }

        // Enregistrer les erreurs (Counter)
        try {
            if ((int)$status >= 400) {
                Prometheus::addCounter('camwater_http_errors_total')
                    ->helpText('Nombre total d\'erreurs HTTP')
                    ->labels(['status_code', 'method', 'path'])
                    ->inc(1, [$status, $method, $path]);
            }
        } catch (\Exception $e) {
            //
        }

        return $response;
    }
}
