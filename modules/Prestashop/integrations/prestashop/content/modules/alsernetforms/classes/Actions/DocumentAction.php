<?php

include_once dirname(__FILE__).'/ApiManager.php';

/**
 * DocumentAction
 *
 * Acción específica para validación de tokens de documentos.
 * Usa ApiManager que ya maneja:
 * - Verificación de disponibilidad del servidor
 * - Registro de peticiones en los loggers
 * - Circuit breaker pattern
 * - Reintentos automáticos
 */
class DocumentAction
{
    private $apiManager;

    public function __construct()
    {
        $this->apiManager = new ApiManager;
    }

    /**
     * Valida un token de documento
     *
     * Envía petición a /api/documents con action='validate'
     * ApiManager se encarga de toda la lógica de disponibilidad y logging
     *
     * @param  string  $token  Token a validar
     * @param  array  $context  Contexto adicional (customer_id, order_id, etc.)
     * @return array Resultado con status, data, request_id
     */
    public function validateToken($token, array $context = [])
    {
        // Preparar payload: action='validate' + uid extraído del token
        $payload = [
            'action' => 'validate',
            'uid' => $token,
        ];

        // ApiManager maneja:
        // - Verificación de disponibilidad
        // - Si disponible: envía inmediatamente
        // - Si NO disponible: guarda como pending y devuelve status='pending'
        $response = $this->apiManager->sendRequest(
            'POST',
            'api/documents',
            $payload,
            'documents',
            [],
            true  // checkAvailability=true
        );

        // Extraer datos de la respuesta
        $responseData = $response['response'] ?? [];

        // Mapear respuesta a estructura esperada
        return [
            'status' => $response['status'],  // 'success' | 'pending' | 'error'
            'request_id' => $response['request_id'] ?? null,
            'data' => [
                'uid' => $responseData['data']['uid'] ?? $token,
                'document_type' => $responseData['data']['type'] ?? 'dni',
                'order_id' => $responseData['data']['order_id'] ?? null,
                'reference' => $responseData['data']['reference'] ?? null,
                'label' => $responseData['data']['label'] ?? 'N/A',
                'can_upload' => $responseData['data']['can_upload'] ?? false,
                'required_documents' => $responseData['data']['required_documents'] ?? [],
                'uploaded_documents' => $responseData['data']['uploaded_documents'] ?? [],
                'missing_documents' => $responseData['data']['missing_documents'] ?? [],
            ],
            'error' => $response['message'] ?? null,
        ];
    }
}
