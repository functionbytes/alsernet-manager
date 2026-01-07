<?php

/**
 * AbstractAction
 *
 * Clase base abstracta para todas las acciones que se envían a Laravel.
 * Maneja:
 * - Validación de disponibilidad del servidor
 * - Registro de peticiones en BD (alsernet_forms_requests)
 * - Circuit breaker pattern para reintentos
 * - Ejecución inmediata o almacenamiento como pendiente
 */
abstract class AbstractAction
{
    protected $db;

    protected $endpointUrl;

    protected $endpointType; // 'documents', 'forms', 'chat', etc.

    protected $method = 'POST';

    protected $timeout = 10;

    protected $maxRetries = 5;

    protected $retryIntervals = [60, 300, 900, 1800, 3600]; // 1m, 5m, 15m, 30m, 60m

    public function __construct()
    {
        $this->db = \Db::getInstance();
    }

    /**
     * Ejecuta la acción: valida disponibilidad, registra y envía o almacena como pendiente
     *
     * @param  array  $payload  Datos a enviar a Laravel
     * @param  array  $context  Contexto adicional (customer_id, order_id, etc.)
     * @return array ['status' => 'success|pending|error', 'request_id' => int, 'data' => [...], 'error' => string]
     */
    final public function execute(array $payload, array $context = [])
    {
        try {
            // 1️⃣ REGISTRAR LA PETICIÓN EN BD (SIEMPRE)
            $requestId = $this->registerRequest($payload, $context);

            // 2️⃣ VERIFICAR DISPONIBILIDAD DEL SERVIDOR
            $isAvailable = $this->isEndpointAvailable();

            if (! $isAvailable) {
                // Servidor no disponible: quedará pendiente para el cron
                return [
                    'status' => 'pending',
                    'request_id' => $requestId,
                    'data' => [],
                    'error' => 'Server unavailable. Request saved for later processing.',
                ];
            }

            // 3️⃣ SERVIDOR DISPONIBLE: ENVIAR PETICIÓN INMEDIATAMENTE
            $response = $this->sendRequest($payload);

            // 4️⃣ PROCESAR RESPUESTA
            if ($response['success']) {
                $this->updateRequestStatus($requestId, 'success', $response['data']);

                return [
                    'status' => 'success',
                    'request_id' => $requestId,
                    'data' => $response['data'],
                    'error' => null,
                ];
            } else {
                $this->updateRequestStatus($requestId, 'failed', null, $response['error']);

                return [
                    'status' => 'error',
                    'request_id' => $requestId,
                    'data' => [],
                    'error' => $response['error'],
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'request_id' => $requestId ?? null,
                'data' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Registra la petición en BD (alsernet_forms_requests)
     *
     * @return int ID de la petición registrada
     */
    protected function registerRequest(array $payload, array $context = [])
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            'endpoint_type' => pSQL($this->endpointType),
            'method' => pSQL($this->method),
            'url' => pSQL($this->endpointUrl),
            'payload' => pSQL(json_encode($payload)),
            'status' => 'pending',
            'retry_count' => 0,
            'max_retries' => $this->maxRetries,
            'created_at' => $now,
            'next_retry_at' => $now,
        ];

        $this->db->insert('alsernet_forms_requests', $data);

        return (int) $this->db->Insert_ID();
    }

    /**
     * Actualiza el estado de una petición en BD
     *
     * @param  int  $requestId
     * @param  string  $status  ('success', 'failed', 'pending', 'processing')
     * @param  mixed  $response  Respuesta del servidor
     * @param  string|null  $error  Mensaje de error
     */
    protected function updateRequestStatus($requestId, $status, $response = null, $error = null)
    {
        $updateData = [
            'status' => pSQL($status),
            'response' => $response ? pSQL(json_encode($response)) : null,
            'last_error' => $error ? pSQL($error) : null,
        ];

        if ($status === 'success') {
            $updateData['synced_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'failed') {
            // Calcular próximo intento
            $sql = 'SELECT retry_count FROM '._DB_PREFIX_.'alsernet_forms_requests WHERE id_alsernetforms_request = '.(int) $requestId;
            $currentRetryCount = (int) $this->db->getValue($sql);
            $nextRetryCount = $currentRetryCount + 1;

            if ($nextRetryCount <= $this->maxRetries && isset($this->retryIntervals[$nextRetryCount - 1])) {
                $retryInterval = $this->retryIntervals[$nextRetryCount - 1];
                $updateData['next_retry_at'] = date('Y-m-d H:i:s', time() + $retryInterval);
            }

            $updateData['retry_count'] = $nextRetryCount;
        }

        $this->db->update(
            'alsernet_forms_requests',
            $updateData,
            'id_alsernetforms_request = '.(int) $requestId
        );
    }

    /**
     * Verifica si el endpoint está disponible
     *
     * @return bool
     */
    protected function isEndpointAvailable()
    {
        include_once dirname(__FILE__).'/../EndpointAvailabilityChecker.php';

        $checker = new EndpointAvailabilityChecker;

        $result = $checker->isEndpointAvailable(
            $this->endpointUrl,
            $this->endpointType
        );

        return $result['available'] ?? false;
    }

    /**
     * Envía la petición al endpoint
     *
     * @return array ['success' => bool, 'data' => [...], 'error' => string|null]
     */
    protected function sendRequest(array $payload)
    {
        $ch = curl_init();

        $payloadJson = json_encode($payload);

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpointUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => $payloadJson,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        // Si hay error de conexión
        if ($curlError) {
            return [
                'success' => false,
                'data' => [],
                'error' => "Connection error: {$curlError}",
            ];
        }

        // Parsear respuesta JSON
        $responseData = null;
        if ($response) {
            $responseData = json_decode($response, true);
        }

        // Si el servidor respondió bien
        if ($httpCode === 200 && $responseData) {
            return [
                'success' => true,
                'data' => $responseData,
                'error' => null,
            ];
        }

        // Error HTTP
        return [
            'success' => false,
            'data' => [],
            'error' => "HTTP {$httpCode}: ".($responseData['message'] ?? 'Invalid response'),
        ];
    }

    /**
     * Obtiene las peticiones pendientes para procesar (usará el cron)
     *
     * @param  int  $limit  Número máximo de peticiones a retornar
     * @return array
     */
    public function getPendingRequests($limit = 10)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'alsernet_forms_requests
                WHERE endpoint_type = "'.pSQL($this->endpointType).'"
                AND status = "pending"
                AND (next_retry_at IS NULL OR next_retry_at <= NOW())
                ORDER BY created_at ASC
                LIMIT '.(int) $limit;

        return $this->db->executeS($sql) ?: [];
    }
}
