<?php

include_once dirname(__FILE__).'/BaseAction.php';
include_once dirname(__FILE__).'/../loggers/DocumentsEndpointLogger.php';

class DocumentAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

        $this->endpoint = 'api/documents';
        $this->actionType = 'documents';
    }

    protected function createLogger()
    {
        return new DocumentsEndpointLogger;
    }


    /**
     * Request new document for an order (creates document request in Laravel)
     * Uses new RESTful endpoint: POST /api/documents
     *
     * @param array $orderData Order and customer data
     * @param array $context Additional context
     * @return array Response with uid, status, etc.
     */
    public function requestDocument(array $orderData, array $context = [])
    {
        // ✅ NEW: Use RESTful endpoint POST /api/documents (no 'action' needed)
        $payload = $orderData;  // Send only the data, endpoint defines the action

        // Use BaseAction::execute() which handles:
        // - Logging
        // - Availability check (circuit breaker)
        // - HTTP request to POST /api/documents
        // - Response mapping
        return $this->execute($payload, $context);
    }

    /**
     * Validate document by UID (checks document status and requirements)
     * Uses new RESTful endpoint: GET /api/documents/{uid}/validation
     *
     * @param string $uid Document UID
     * @param array $context Additional context
     * @return array Response with document status, requirements, etc.
     */
    public function validate($uid, array $context = [])
    {
        // ✅ NEW: Use RESTful GET endpoint /api/documents/{uid}/validation
        $url = rtrim($this->apiManager->getBaseUrl(), '/').'/'.ltrim($this->endpoint, '/').'/'.$uid.'/validation';

        // Iniciar tracking
        $requestId = $this->logger->logRequest('GET', $url, $context);

        // Verificar disponibilidad
        $availability = $this->checkAvailability($url);

        if (! $availability['available']) {
            if (method_exists($this->logger, 'markAsServerUnavailable')) {
                $this->logger->markAsServerUnavailable(
                    $requestId,
                    $availability['reason'],
                    $availability['next_retry_at'] ?? null
                );
            }

            return $this->mapResponse([
                'status' => 'pending',
                'message' => 'Server unavailable. Request queued.',
                'request_id' => $requestId,
                'reason' => $availability['reason'],
                'response' => [],
            ]);
        }

        // Enviar petición HTTP GET (sin logging interno)
        $httpResponse = $this->apiManager->sendRequestWithoutLogging(
            'GET',
            $this->endpoint.'/'.$uid.'/validation',
            [],  // Sin payload para GET
            []   // headers
        );

        // Actualizar tracking
        $this->logger->updateRequestLog(
            $requestId,
            $httpResponse['status'] === 200 ? 'success' : 'failed',
            $httpResponse['response'] ?? []
        );

        // Preparar respuesta completa
        $finalResponse = array_merge($httpResponse, [
            'request_id' => $requestId,
        ]);

        return $this->mapResponse($finalResponse);
    }

    public function validateToken($token, array $context = [])
    {
        // Construir URL RESTful
        $url = rtrim($this->apiManager->getBaseUrl(), '/').'/'.ltrim($this->endpoint, '/').'/'.$token.'/validation';

        // Iniciar tracking
        $requestId = $this->logger->logRequest('GET', $url, $context);

        // Verificar disponibilidad
        $availability = $this->checkAvailability($url);

        if (! $availability['available']) {
            if (method_exists($this->logger, 'markAsServerUnavailable')) {
                $this->logger->markAsServerUnavailable(
                    $requestId,
                    $availability['reason'],
                    $availability['next_retry_at'] ?? null
                );
            }

            return $this->mapResponse([
                'status' => 'pending',
                'message' => 'Server unavailable. Request queued.',
                'request_id' => $requestId,
                'reason' => $availability['reason'],
                'response' => [],
            ]);
        }

        // Enviar petición HTTP GET (sin logging interno)
        $httpResponse = $this->apiManager->sendRequestWithoutLogging(
            'GET',
            $this->endpoint.'/'.$token.'/validation',
            [],  // Sin payload para GET
            []   // headers
        );

        // Actualizar tracking
        $this->logger->updateRequestLog(
            $requestId,
            $httpResponse['status'] === 200 ? 'success' : 'failed',
            $httpResponse['response'] ?? []
        );

        // Preparar respuesta completa
        $finalResponse = array_merge($httpResponse, [
            'request_id' => $requestId,
        ]);

        return $this->mapResponse($finalResponse);
    }

    /**
     * Verify document existence by order ID
     * Uses new RESTful endpoint: GET /api/documents/verify?order_id={orderId}
     *
     * @param string $orderId Order ID to verify
     * @param array $context Additional context
     * @return array Response with document status and data
     */
    public function verifyByOrderId($orderId, array $context = [])
    {
        // ✅ NEW: Use RESTful GET endpoint /api/documents/verify
        $url = rtrim($this->apiManager->getBaseUrl(), '/').'/'.ltrim($this->endpoint, '/').'/verify?order_id='.$orderId;

        // Iniciar tracking
        $requestId = $this->logger->logRequest('GET', $url, $context);

        // Verificar disponibilidad
        $availability = $this->checkAvailability($url);

        if (! $availability['available']) {
            if (method_exists($this->logger, 'markAsServerUnavailable')) {
                $this->logger->markAsServerUnavailable(
                    $requestId,
                    $availability['reason'],
                    $availability['next_retry_at'] ?? null
                );
            }

            return $this->mapResponse([
                'status' => 'pending',
                'message' => 'Server unavailable. Request queued.',
                'request_id' => $requestId,
                'reason' => $availability['reason'],
                'response' => [],
            ]);
        }

        // Enviar petición HTTP GET (sin logging interno)
        $httpResponse = $this->apiManager->sendRequestWithoutLogging(
            'GET',
            $this->endpoint.'/verify?order_id='.$orderId,
            [],  // Sin payload para GET
            []   // headers
        );

        // Actualizar tracking
        $this->logger->updateRequestLog(
            $requestId,
            $httpResponse['status'] === 200 ? 'success' : 'failed',
            $httpResponse['response'] ?? []
        );

        // Preparar respuesta completa
        $finalResponse = array_merge($httpResponse, [
            'request_id' => $requestId,
        ]);

        return $this->mapResponse($finalResponse);
    }

    private function checkAvailability($url)
    {
        return $this->availabilityChecker->isEndpointAvailable($url, $this->actionType);
    }

    protected function mapResponse(array $response)
    {
        $responseData = $response['response'] ?? [];
        $httpStatus = $response['status'] ?? 500;

        // Mapear HTTP status code a status textual
        if ($httpStatus >= 200 && $httpStatus < 300) {
            $status = $responseData['status'] ?? 'success';  // Laravel devuelve 'success' | 'failed'
        } else {
            $status = $responseData['status'] ?? 'failed';
        }

        // Si fue marcado como pending por circuit breaker, preservar ese status
        if (isset($response['status']) && $response['status'] === 'pending') {
            $status = 'pending';
        }

        return [
            'status' => $status,  // 'success' | 'pending' | 'failed'
            'request_id' => $response['request_id'] ?? null,
            'data' => [
                'uid' => $responseData['data']['uid'] ?? null,
                'document_type' => $responseData['data']['type'] ?? 'dni',
                'order_id' => $responseData['data']['order_id'] ?? null,
                'reference' => $responseData['data']['reference'] ?? null,
                'label' => $responseData['data']['label'] ?? 'N/A',
                'can_upload' => $responseData['data']['can_upload'] ?? false,
                'required_documents' => $responseData['data']['required_documents'] ?? [],
                'uploaded_documents' => $responseData['data']['uploaded_documents'] ?? [],
                'missing_documents' => $responseData['data']['missing_documents'] ?? [],
            ],
            'error' => $responseData['message'] ?? $response['message'] ?? null,
        ];
    }
}
