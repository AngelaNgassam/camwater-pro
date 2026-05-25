<?php

return [
    'enabled' => env('PROMETHEUS_ENABLED', true),

    /*
     * The urls that will return metrics.
     */
    'urls' => [
        'default' => 'prometheus',
    ],

    /*
     * Only these IP's will be allowed to visit the above urls.
     * All IP's are allowed when empty.
     */
    'allowed_ips' => [
        // Allow all IPs in Docker Compose monitoring network
        // Add specific IPs for production security
    ],

    /*
     * This is the default namespace that will be
     * used by all metrics
     */
    'default_namespace' => 'camwater',

    /*
     * The middleware that will be applied to the urls above
     */
    'middleware' => [
        Spatie\Prometheus\Http\Middleware\AllowIps::class,
    ],

    /*
     * You can override these classes to customize low-level behaviour of the package.
     */
    'actions' => [
        'render_collectors' => Spatie\Prometheus\Actions\RenderCollectorsAction::class,
    ],

    /**
     * Allow storage to be wiped after a render of data in metrics controller.
     */
    'wipe_storage_after_rendering' => false,

    /**
     * Select a cache to store gauges, counters, summaries and histograms between requests.
     * For distributed setups, use 'redis' to share metrics across nodes.
     */
    'cache' => env('PROMETHEUS_CACHE', 'file'),
];
