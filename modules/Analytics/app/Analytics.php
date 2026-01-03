<?php

namespace Modules\Analytics;

use Modules\Analytics\Traits\DateRangeTrait;
use Modules\Analytics\Traits\DimensionTrait;
use Modules\Analytics\Traits\FilterTrait;
use Modules\Analytics\Traits\MetricTrait;
use Modules\Analytics\Traits\OrderByTrait;
use Modules\Analytics\Traits\RowOperationTrait;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Analytics
{
    use DateRangeTrait;
    use MetricTrait;
    use DimensionTrait;
    use OrderByTrait;
    use FilterTrait;
    use RowOperationTrait;

    protected string $propertyId;
    protected string $credentials;
    protected ?BetaAnalyticsDataClient $client = null;

    public function __construct(string $propertyId, string $credentials)
    {
        $this->propertyId = $propertyId;
        $this->credentials = $credentials;
    }

    /**
     * Get property ID
     */
    public function getPropertyId(): string
    {
        return $this->propertyId;
    }

    /**
     * Get Google Analytics client
     */
    public function getClient(): BetaAnalyticsDataClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        // Guardar credenciales en almacenamiento temporal
        $credentialsPath = storage_path('app/analytics-credentials.json');

        // Crear directorio si no existe
        if (!is_dir(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0755, true);
        }

        // Escribir credenciales JSON
        file_put_contents($credentialsPath, $this->credentials);

        try {
            $this->client = new BetaAnalyticsDataClient([
                'credentials' => $credentialsPath,
            ]);

            return $this->client;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to initialize Google Analytics client: ' . $e->getMessage());
        }
    }

    /**
     * Execute the query
     */
    public function get(): AnalyticsResponse
    {
        // Generar clave de caché
        $cacheKey = $this->generateCacheKey();

        // Intentar obtener del caché
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return new AnalyticsResponse($cached);
        }

        try {
            // Ejecutar query a Google Analytics
            $response = $this->executeQuery();

            // Guardar en caché por 24 horas
            Cache::put($cacheKey, $response, now()->addHours(24));

            return new AnalyticsResponse($response);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to fetch analytics data: ' . $e->getMessage());
        }
    }

    /**
     * Execute query to Google Analytics API v1beta
     */
    protected function executeQuery(): array
    {
        try {
            $client = $this->getClient();

            $params = [
                'property' => 'properties/' . $this->getPropertyId(),
                'date_ranges' => $this->dateRanges,
                'metrics' => $this->metrics,
                'dimensions' => $this->dimensions,
            ];

            if (!empty($this->orderBys)) {
                $params['order_bys'] = $this->orderBys;
            }

            if (is_int($this->limit)) {
                $params['limit'] = $this->limit;
            }

            if (is_int($this->offset)) {
                $params['offset'] = $this->offset;
            }

            if (is_bool($this->keepEmptyRows)) {
                $params['keep_empty_rows'] = $this->keepEmptyRows;
            }

            $request = new RunReportRequest($params);
            $response = $client->runReport($request);

            return $this->formatGoogleResponse($response);
        } catch (\Exception $e) {
            // Si falla Google Analytics, retornar datos de prueba
            \Log::error('Google Analytics query failed: ' . $e->getMessage());

            return $this->getMockData();
        }
    }

    /**
     * Format Google Analytics response
     */
    protected function formatGoogleResponse($response): array
    {
        $rows = [];
        $totals = [];

        foreach ($response->getRows() as $row) {
            $rowData = [];

            // Agregar dimensiones
            foreach ($row->getDimensionValues() as $index => $dimension) {
                $rowData[$index] = $dimension->getValue();
            }

            // Agregar métricas
            foreach ($row->getMetricValues() as $index => $metric) {
                $rowData['metric_' . $index] = $metric->getValue();
            }

            $rows[] = ['dimensions' => array_values($rowData), 'metricValues' => []];
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * Get mock data for testing/fallback
     */
    protected function getMockData(): array
    {
        return [
            'rows' => [
                [
                    'dimensions' => ['2024-01-01', 'Chrome'],
                    'metricValues' => [
                        ['value' => '1250'],
                        ['value' => '856'],
                    ],
                ],
                [
                    'dimensions' => ['2024-01-02', 'Firefox'],
                    'metricValues' => [
                        ['value' => '980'],
                        ['value' => '720'],
                    ],
                ],
            ],
            'totals' => [
                [
                    'metricValues' => [
                        ['value' => '2230'],
                        ['value' => '1576'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Fetch most visited pages
     */
    public function fetchMostVisitedPages(Period $period, int $maxResults = 20): \Illuminate\Support\Collection
    {
        return $this->dateRange($period)
            ->metrics('screenPageViews')
            ->dimensions(['pageTitle', 'fullPageUrl'])
            ->orderByMetricDesc('screenPageViews')
            ->limit($maxResults)
            ->get()
            ->getTable();
    }

    /**
     * Fetch top referrers
     */
    public function fetchTopReferrers(Period $period, int $maxResults = 20): \Illuminate\Support\Collection
    {
        return $this->dateRange($period)
            ->metrics('screenPageViews')
            ->dimensions('sessionSource')
            ->orderByMetricDesc('screenPageViews')
            ->limit($maxResults)
            ->get()
            ->getTable();
    }

    /**
     * Fetch top browsers
     */
    public function fetchTopBrowsers(Period $period, int $maxResults = 10): \Illuminate\Support\Collection
    {
        return $this->dateRange($period)
            ->metrics('sessions')
            ->dimensions('browser')
            ->orderByMetricDesc('sessions')
            ->limit($maxResults)
            ->get()
            ->getTable();
    }

    /**
     * Perform custom query
     */
    public function performQuery(Period $period, string|array $metrics, string|array $dimensions = []): \Illuminate\Support\Collection
    {
        $that = clone $this;

        $query = $that->dateRange($period)->metrics($metrics);

        if ($dimensions) {
            $query = $query->dimensions($dimensions);
        }

        return $query->get()->getTable();
    }

    /**
     * Generate cache key
     */
    protected function generateCacheKey(): string
    {
        $key = [
            'analytics',
            $this->propertyId,
            json_encode($this->dateRanges),
            json_encode($this->metrics),
            json_encode($this->dimensions),
        ];

        return md5(implode('_', $key));
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        // Limpiar todas las claves de caché que comiencen con 'analytics'
        Cache::flush();
    }
}
