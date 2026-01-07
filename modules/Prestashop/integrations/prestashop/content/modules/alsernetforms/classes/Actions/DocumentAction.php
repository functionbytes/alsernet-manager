<?php

include_once dirname(__FILE__).'/BaseAction.php';

/**
 * DocumentAction
 *
 * Acción para validación de tokens de documentos.
 * Extiende BaseAction que automatiza:
 * - Envío a ApiManager
 * - Verificación de disponibilidad
 * - Logging automático
 * - Circuit breaker con reintentos
 *
 * Solo necesita implementar mapResponse() para transformar respuestas.
 */
class DocumentAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

        $this->endpoint = 'api/documents';
        $this->actionType = 'documents';
    }

    /**
     * Valida un token de documento
     *
     * @param  string  $token  Token a validar
     * @param  array  $context  Contexto adicional (customer_id, order_id, etc.)
     * @return array Resultado con status, data, request_id
     */
    public function validateToken($token, array $context = [])
    {
        $payload = [
            'action' => 'validate',
            'uid' => $token,
        ];

        return $this->execute($payload, $context);
    }

    /**
     * Mapea respuesta de ApiManager a estructura estándar para templates
     *
     * @param array $response Respuesta de ApiManager
     * @return array Respuesta mapeada
     */
    protected function mapResponse(array $response)
    {
        $responseData = $response['response'] ?? [];

        return [
            'status' => $response['status'],  // 'success' | 'pending' | 'error'
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
            'error' => $response['message'] ?? null,
        ];
    }
}
