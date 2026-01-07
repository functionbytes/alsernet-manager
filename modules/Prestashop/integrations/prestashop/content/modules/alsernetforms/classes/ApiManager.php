<?php

include_once dirname(__FILE__).'/loggers/SubscriptionEndpointLogger.php';
include_once dirname(__FILE__).'/loggers/FormEndpointLogger.php';
include_once dirname(__FILE__).'/loggers/DefaultEndpointLogger.php';
include_once dirname(__FILE__).'/loggers/DocumentsEndpointLogger.php';
include_once dirname(__FILE__).'/EndpointAvailabilityChecker.php';
include_once dirname(__FILE__).'/HttpClient.php';

class ApiManager
{
    private $apiBaseUrl;

    private $availabilityChecker;

    private $httpClient;

    private $checkAvailability = true; // Flag para habilitar/deshabilitar verificación

    public function __construct()
    {
        $this->apiBaseUrl = 'https://webadminpruebas.a-alvarez.com/';
        $this->availabilityChecker = new EndpointAvailabilityChecker;
        $this->httpClient = new HttpClient;
    }

    /**
     * Envía una petición HTTP con verificación de disponibilidad del servidor
     *
     * @param  string  $method  Método HTTP (GET, POST, PUT, DELETE)
     * @param  string  $endpoint  Ruta del endpoint
     * @param  array  $data  Datos a enviar
     * @param  string  $type  Tipo de petición (default, documents, subscription, form)
     * @param  array  $headers  Headers adicionales
     * @param  bool  $checkAvailability  Si se debe verificar disponibilidad antes de enviar
     * @return array Respuesta con estructura ['status' => string, 'message' => string, 'response' => array]
     */
    public function sendRequest($method, $endpoint, array $data = [], $type = 'default', array $headers = [], $checkAvailability = true)
    {
        $url = rtrim($this->apiBaseUrl, '/').'/'.ltrim($endpoint, '/');
        $logger = $this->getLoggerForType($type);
        $requestLog = $logger->logRequest($method, $url, $data);

        // Verificar disponibilidad del endpoint si está habilitado
        if ($checkAvailability && $this->checkAvailability) {
            $availability = $this->availabilityChecker->isEndpointAvailable($url, $type);

            if (! $availability['available']) {
                // Servidor no disponible, marcar como pendiente
                if (method_exists($logger, 'markAsServerUnavailable')) {
                    $logger->markAsServerUnavailable(
                        $requestLog,
                        $availability['reason'],
                        $availability['next_retry_at'] ?? null
                    );
                } else {
                    $logger->updateRequestLog($requestLog, 'server_unavailable', [
                        'error' => $availability['reason'],
                    ]);
                }

                return [
                    'status' => 'pending',
                    'message' => $this->translate('Server unavailable. Request queued for later processing.'),
                    'reason' => $availability['reason'],
                    'request_id' => $requestLog,
                ];
            }
        }

        // Servidor disponible, proceder con la petición usando HttpClient centralizado
        $httpResponse = $this->httpClient->request($method, $url, $data, $headers);

        $httpCode = $httpResponse['status'];
        $curlError = $httpResponse['error'];

        if ($curlError) {
            $logger->updateRequestLog($requestLog, 'failed', ['error' => $curlError]);

            return ['status' => 'error', 'message' => $this->translate('Error connecting to the server')];
        }

        $responseData = $this->httpClient->decodeJson($httpResponse['body']);

        if ($responseData === null && ! empty($httpResponse['body'])) {
            $logger->updateRequestLog($requestLog, 'failed', ['error' => 'Invalid JSON response']);

            return ['status' => 'error', 'message' => $this->translate('Invalid JSON response')];
        }

        $logger->updateRequestLog($requestLog, $httpCode === 200 ? 'success' : 'failed', $responseData);

        return [
            'status' => $httpCode === 200 ? 'success' : 'error',
            'message' => $httpCode === 200 ? 'Request successful' : 'Request failed',
            'response' => $responseData,
        ];
    }

    private function getLoggerForType($type)
    {
        switch ($type) {
            case 'documents':
                return new DocumentsEndpointLogger;
            case 'form':
                return new FormEndpointLogger;
            case 'subscription':
                return new SubscriptionEndpointLogger;
            default:
                return new DefaultEndpointLogger;
        }
    }

    private function translate($message)
    {
        return Context::getContext()->getTranslator()->trans($message, [], 'modules.Tumodulo');
    }
}
