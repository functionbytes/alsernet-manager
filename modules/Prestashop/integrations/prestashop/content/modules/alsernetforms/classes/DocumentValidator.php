<?php

include_once(dirname(__FILE__).'/ApiManager.php');
include_once(dirname(__FILE__).'/loggers/DocumentsEndpointLogger.php');

/**
 * DocumentValidator
 *
 * Wrapper para validación de documentos con soporte para servidor no disponible.
 * Gestiona automáticamente la cola de peticiones pendientes.
 */
class DocumentValidator
{
    private $apiManager;
    private $logger;
    private $validationEndpoint = 'api/orders/validate-documents';

    public function __construct()
    {
        $this->apiManager = new ApiManager();
        $this->logger = new DocumentsEndpointLogger();
    }

    /**
     * Valida documentos de un pedido. Si el servidor no está disponible,
     * registra la petición como pendiente para procesarla más tarde.
     *
     * @param string $uid Identificador único del pedido/token
     * @param string $documentType Tipo de documento (corta, rifle, escopeta, dni, etc.)
     * @param array $additionalContext Contexto adicional (customer_id, order_reference, etc.)
     * @return array Resultado de la validación con estructura estandarizada
     */
    public function validateDocuments($uid, $documentType = null, array $additionalContext = [])
    {
        // Preparar payload para el endpoint de validación
        $payload = [
            'uid' => $uid,
            'document_type' => $documentType,
        ];

        // Añadir contexto adicional si existe
        if (!empty($additionalContext)) {
            $payload = array_merge($payload, $additionalContext);
        }

        // Intentar enviar la petición con verificación de disponibilidad
        $response = $this->apiManager->sendRequest(
            'POST',
            $this->validationEndpoint,
            $payload,
            'documents', // Tipo de petición
            [], // Headers adicionales
            true // Verificar disponibilidad
        );

        // Interpretar la respuesta según el estado
        return $this->parseValidationResponse($response, $uid, $documentType);
    }

    /**
     * Parsea y normaliza la respuesta de validación
     *
     * @param array $response Respuesta del ApiManager
     * @param string $uid UID del pedido
     * @param string|null $documentType Tipo de documento
     * @return array Respuesta normalizada
     */
    private function parseValidationResponse(array $response, $uid, $documentType)
    {
        $status = $response['status'] ?? 'error';

        switch ($status) {
            case 'success':
                // Validación exitosa, retornar datos
                return [
                    'status' => 'success',
                    'type' => $documentType,
                    'label' => $this->getDocumentLabel($documentType),
                    'upload' => true,
                    'data' => $response['response'] ?? [],
                    'message' => 'Validation successful'
                ];

            case 'pending':
                // Servidor no disponible, petición en cola
                return [
                    'status' => 'pending',
                    'type' => $documentType,
                    'label' => $this->getDocumentLabel($documentType),
                    'upload' => false,
                    'data' => [
                        'required_documents' => $this->getRequiredDocumentsByType($documentType),
                        'uploaded_documents' => [],
                        'missing_documents' => $this->getRequiredDocumentsByType($documentType),
                    ],
                    'message' => $response['message'] ?? 'Server unavailable, validation queued',
                    'request_id' => $response['request_id'] ?? null,
                    'reason' => $response['reason'] ?? 'Unknown'
                ];

            case 'error':
            default:
                // Error en la validación
                return [
                    'status' => 'error',
                    'type' => $documentType,
                    'label' => $this->getDocumentLabel($documentType),
                    'upload' => false,
                    'data' => [
                        'required_documents' => $this->getRequiredDocumentsByType($documentType),
                        'uploaded_documents' => [],
                        'missing_documents' => $this->getRequiredDocumentsByType($documentType),
                    ],
                    'message' => $response['message'] ?? 'Validation failed',
                    'error' => $response['response']['error'] ?? 'Unknown error'
                ];
        }
    }

    /**
     * Obtiene la etiqueta traducida para un tipo de documento
     *
     * @param string|null $documentType Tipo de documento
     * @return string Etiqueta traducida
     */
    private function getDocumentLabel($documentType)
    {
        $iso = Context::getContext()->language->iso_code;
        $module = Module::getInstanceByName('alsernetforms');

        $labels = [
            'corta' => $module->l('Handgun permit', 'DocumentValidator', $iso),
            'rifle' => $module->l('Rifle permit', 'DocumentValidator', $iso),
            'escopeta' => $module->l('Shotgun license', 'DocumentValidator', $iso),
            'dni' => $module->l('ID document', 'DocumentValidator', $iso),
        ];

        return $labels[$documentType] ?? $module->l('Document validation', 'DocumentValidator', $iso);
    }

    /**
     * Obtiene la lista de documentos requeridos por tipo
     *
     * @param string|null $documentType Tipo de documento
     * @return array Lista de documentos requeridos
     */
    private function getRequiredDocumentsByType($documentType)
    {
        $iso = Context::getContext()->language->iso_code;
        $module = Module::getInstanceByName('alsernetforms');

        switch ($documentType) {
            case 'corta':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                    $module->l('Handgun permit (type B) or Olympic shooting permit (type F)', 'DocumentValidator', $iso),
                ];
            case 'rifle':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                    $module->l('Rifled long-range firearm permit (type D)', 'DocumentValidator', $iso),
                ];
            case 'escopeta':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                    $module->l('Shotgun license (type E)', 'DocumentValidator', $iso),
                ];
            case 'dni':
                return [
                    $module->l('ID (both sides)', 'DocumentValidator', $iso),
                ];
            default:
                return [
                    $module->l('Passport or driving licence (both sides)', 'DocumentValidator', $iso),
                ];
        }
    }

    /**
     * Verifica el estado de una petición pendiente por su ID
     *
     * @param int $requestId ID de la petición
     * @return array|false Estado de la petición o false si no existe
     */
    public function checkPendingRequestStatus($requestId)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'alsernet_forms_requests
                WHERE id_alsernetforms_request = ' . (int)$requestId;

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Obtiene todas las peticiones pendientes para un UID específico
     *
     * @param string $uid UID del pedido
     * @return array Lista de peticiones pendientes
     */
    public function getPendingRequestsForUid($uid)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'alsernet_forms_requests
                WHERE endpoint_type = "documents"
                AND payload LIKE "%\"uid\":\"' . pSQL($uid) . '\"%"
                AND status IN ("pending", "server_unavailable")
                ORDER BY created_at DESC';

        return \Db::getInstance()->executeS($sql);
    }
}
